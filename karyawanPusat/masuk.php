<?php
require "../function.php";
require "../cek.php";

// Pastikan hanya role karyawanPusat yang bisa akses
if (!isset($_SESSION['roles']) || $_SESSION['roles'] != 'karyawanPusat') {
     header('location: ../index.php');
     exit;
}

$identifier = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Karyawan';
$store_name = "Gudang Pusat";
$store_prefix = "pusat"; // Karyawan pusat hanya akses tabel pusat
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
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
             <?php require '_sidebar_karyawan.php'; // Include sidebar karyawan ?>
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
                                        </tr>
                                    </thead>
                                    <tbody>
                                       <?php
                                        $ambilsemuadatastock = mysqli_query($conn, "select * from " . $store_prefix . "masuk m, " . $store_prefix . "stock s where s.idbarang = m.idbarang order by m.tanggal DESC");
                                        while($data=mysqli_fetch_array($ambilsemuadatastock)){
                                            $idb = $data['idbarang'];
                                            $idm = $data['idmasuk'];
                                            $tanggal = $data['tanggal'];
                                            $namabarang = htmlspecialchars($data['namabarang']);
                                            $qty = htmlspecialchars($data['qty']);
                                            $keterangan = htmlspecialchars($data['keterangan']);
                                            $gambar = $data['image'];

                                            if($gambar==null || !file_exists("../images/".$gambar)){
                                                $img = 'No Photo';
                                            } else {
                                                $img = '<img src="../images/'.htmlspecialchars($gambar).'" class="zoomable" alt="'.$namabarang.'">';
                                            }
                                        ?>
                                        <tr>
                                            <td><?=$tanggal;?></td>
                                            <td><?=$img;?></td>
                                            <td><?=$namabarang;?></td>
                                            <td><?=$qty;?></td>
                                            <td><?=$keterangan;?></td>
                                            </tr>
                                         <?php }; ?>
                                    </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
                <footer class="py-4 bg-light mt-auto">
                     <div class="container-fluid">
                        <div class="d-flex align-items-center justify-content-between small">
                             <div class="text-muted">Copyright &copy; StokMate 2025</div> <div>
                                <a href="#">Privacy Policy</a>
                                &middot;
                                <a href="#">Terms &amp; Conditions</a>
                            </div>
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
                 <form method="post">
                <div class="modal-body">
                 <label>Pilih Barang:</label>
                 <select name="barangnya" class="form-control" required>
                    <option value="">-- Pilih Barang <?= $store_name; ?> --</option>
                    <?php
                    $ambilsemuadatanya = mysqli_query($conn,"select * from " . $store_prefix . "stock order by namabarang ASC");
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
                  
                   <button type="submit" class="btn btn-primary" name="barangmasuk">Submit</button>
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
        <script>
            $(document).ready(function() {
              $('#dataTable').DataTable({
                  stateSave: true // Aktifkan state saving
              });
            });
        </script>
    </body>
</html>