<?php
require "../function.php";
require "../cek.php";

// Pastikan hanya role adminSerpong yang bisa akses
if (!isset($_SESSION['roles']) || $_SESSION['roles'] != 'adminSerpong') {
     header('location: ../index.php');
     exit;
}

$identifier = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin';
$store_name = "Toko 3 Serpong";
$store_prefix = "serpong"; // Gunakan tabel serpong
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Barang Masuk - <?= $store_name; ?></title>
        <link href="../css/styles.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
        <style>
            .zoomable { width: 100px; }
            .zoomable:hover { transform: scale(2.5); transition: 0.3s ease; z-index: 10; position: relative;}
             .table-responsive { overflow-x: visible; }
            td { vertical-align: middle !important; }
        </style>
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark" style="background-color: #003940; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <a class="navbar-brand" href="index.php" style="font-weight: bold; letter-spacing: 1px;">
                Stock <sup style="color: #80DEEA; font-weight: normal;">Mate</sup>
            </a>
            <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#" style="color: #ffffff; transition: transform 0.3s ease;">
                <i class="fas fa-bars fa-lg"></i> 
            </button>
        </nav>
        <div id="layoutSidenav">
             <?php require '_sidebar_admin_serpong.php'; // Include sidebar khusus ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                         <h1 class="mt-4">Barang Masuk <?= $store_name; ?></h1>
                        <div class="card mb-4">
                            <div class="card-header">
                               <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                                <i class="fas fa-plus"></i> Tambah Barang Masuk
                              </button>
                            </div>
                            <div class="card-body">
                             <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Gambar</th>
                                        <th>Nama Barang</th>
                                        <th>Jumlah</th>
                                        <th>Keterangan</th>
                                        <th>Aksi</th> </tr>
                                    </thead>
                                    <tbody>
                                       <?php
                                        $ambilsemuadatastock = mysqli_query($conn, "select * from " . $store_prefix . "_masuk m, " . $store_prefix . "_stock s where s.idbarang = m.idbarang order by m.tanggal DESC");
                                        while($data=mysqli_fetch_array($ambilsemuadatastock)){
                                            $idb = $data['idbarang'];
                                            $idm = $data['idmasuk'];
                                            $tanggal = $data['tanggal'];
                                            $namabarang = htmlspecialchars($data['namabarang']);
                                            $qty = htmlspecialchars($data['qty']);
                                            $keterangan = htmlspecialchars($data['keterangan']);
                                            $gambar = $data['image'];

                                            if($gambar==null || !file_exists("../images/".$gambar)){ $img = 'No Photo'; }
                                            else { $img = '<img src="../images/'.htmlspecialchars($gambar).'" class="zoomable" alt="'.$namabarang.'">'; }
                                        ?>
                                        <tr>
                                            <td><?=$tanggal;?></td>
                                            <td><?=$img;?></td>
                                            <td><?=$namabarang;?></td>
                                            <td><?=$qty;?></td>
                                            <td><?=$keterangan;?></td>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#edit<?=$idm;?>"> <i class="fas fa-edit"></i> Edit</button>
                                                <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#delete<?=$idm;?>"> <i class="fas fa-trash"></i> Delete</button>
                                            </td>
                                        </tr>
                                         <div class="modal fade" id="edit<?=$idm;?>">
                                            <div class="modal-dialog">
                                              <div class="modal-content">
                                                <div class="modal-header">
                                                  <h4 class="modal-title">Edit Barang Masuk <?= $store_name; ?></h4>
                                                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form method="post" action="../function.php">
                                                    <div class="modal-body">
                                                      <input type="text" name="keterangan" value="<?=$keterangan;?>" class="form-control" placeholder="Keterangan/Penerima" required>
                                                      <br>
                                                      <input type="number" name="qty" value="<?=$qty;?>" class="form-control" placeholder="Quantity" required min="1">
                                                      <br>
                                                      <input type="hidden" name="idb" value="<?=$idb;?>">
                                                      <input type="hidden" name="idm" value="<?=$idm;?>">
                                                      <button type="submit" class="btn btn-primary" name="updatebarangmasuk_serpong">Update</button>
                                                    </div>
                                                </form>
                                              </div>
                                            </div>
                                          </div>
                                        <div class="modal fade" id="delete<?=$idm;?>">
                                            <div class="modal-dialog">
                                              <div class="modal-content">
                                                <div class="modal-header">
                                                  <h4 class="modal-title">Hapus Barang Masuk?</h4>
                                                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form method="post" action="../function.php">
                                                    <div class="modal-body">
                                                     Apakah anda yakin ingin menghapus data masuk <?=$namabarang;?> sejumlah <?=$qty;?>?
                                                     <br>(Stok barang akan dikurangi)
                                                      <input type="hidden" name="idb" value="<?=$idb;?>">
                                                      <input type="hidden" name="kty" value="<?=$qty;?>">
                                                      <input type="hidden" name="idm" value="<?=$idm;?>">
                                                      <br><br>
                                                      <button type="submit" class="btn btn-danger" name="hapusbarangmasuk_serpong">Hapus</button>
                                                       <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    </div>
                                                </form>
                                              </div>
                                            </div>
                                          </div>
                                        <?php }; ?>
                                    </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
                <footer class="py-4 bg-light mt-auto">
                      <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; StokMate 2025</div>
                            <div> <a href="#">Privacy Policy</a> &middot; <a href="#">Terms &amp; Conditions</a> </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

         <div class="modal fade" id="myModal">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Tambah Barang Masuk <?= $store_name; ?></h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                 <form method="post" action="../function.php">
                <div class="modal-body">
                 <label>Pilih Barang:</label>
                 <select name="barangnya" class="form-control" required>
                    <option value="">-- Pilih Barang <?= $store_name; ?> --</option>
                    <?php
                    // Ambil barang dari stok Serpong
                    $ambilsemuadatanya = mysqli_query($conn,"select * from " . $store_prefix . "_stock order by namabarang ASC");
                    while($fetcharray = mysqli_fetch_array($ambilsemuadatanya)){
                        $namabarangnya = htmlspecialchars($fetcharray['namabarang']);
                        $idbarangnya = $fetcharray['idbarang'];
                    ?>
                    <option value="<?=$idbarangnya;?>"><?=$namabarangnya;?></option>
                    <?php } ?>
                  </select>
                  <br>
                  <label>Quantity:</label>
                  <input type="number" name="qty" class="form-control" placeholder="Jumlah masuk" required min="1">
                  <br>
                   <label>Keterangan/Pengirim:</label>
                   <input type="text" name="penerima" class="form-control" placeholder="Keterangan / Pengirim" required>
                  <br>
                   <button type="submit" class="btn btn-primary" name="barangmasuk_serpong">Submit</button>
                </div>
                </form>
              </div>
            </div>
          </div>

        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
        <script> $(document).ready(function() { $('#dataTable').DataTable({ stateSave: true }); }); </script>
    </body>
</html>