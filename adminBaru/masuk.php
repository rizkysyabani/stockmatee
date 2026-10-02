<?php
require "../function.php";
require "../cek.php";

// USER IDENTIFICATION AND ROLE CHECK
$username_session = $_SESSION['username'] ?? '';
$role_session = $_SESSION['roles'] ?? '';

$specific_admin_usernames = ['adminPusat', 'adminParahita', 'adminSerpong', 'owner'];
if ($role_session != 'admin' || in_array($username_session, $specific_admin_usernames) || empty($username_session)) {
    header('location: ../index.php');
    exit;
}

$username_display = htmlspecialchars($username_session);
$store_name_display = "Toko " . $username_display;
$store_prefix = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $username_session));

if (empty($store_prefix)) {
    echo "Error: Konfigurasi toko tidak valid.";
    exit;
}
$can_manage_transactions = true; // Admins of dynamic branches can manage their transactions

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Barang Masuk - <?= htmlspecialchars($store_name_display); ?></title>
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
             <?php require '_sidebar_admin_baru.php'; ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Barang Masuk <?= htmlspecialchars($store_name_display); ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard <?= $username_display; ?></a></li>
                            <li class="breadcrumb-item active">Barang Masuk</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                               <?php if ($can_manage_transactions): ?>
                               <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                                <i class="fas fa-plus"></i> Tambah Barang Masuk
                               </button>
                               <?php endif; ?>
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
                                        <?php if ($can_manage_transactions): ?>
                                        <th>Aksi</th>
                                        <?php endif; ?>
                                    </tr>
                                    </thead>
                                    <tbody>
                                       <?php
                                        $ambilsemuadatastock = mysqli_query($conn, "SELECT m.*, s.namabarang, s.image FROM `{$store_prefix}_masuk` m JOIN `{$store_prefix}_stock` s ON m.idbarang = s.idbarang ORDER BY m.tanggal DESC");
                                        if ($ambilsemuadatastock) {
                                            while($data=mysqli_fetch_array($ambilsemuadatastock)){
                                                $idb = $data['idbarang'];
                                                $idm = $data['idmasuk'];
                                                $tanggal = $data['tanggal'];
                                                $namabarang = htmlspecialchars($data['namabarang']);
                                                $qty = htmlspecialchars($data['qty']);
                                                $keterangan = htmlspecialchars($data['keterangan']);
                                                $gambar = $data['image'];

                                                if($gambar==null || !file_exists("../images/".$gambar)){ $img = 'No Photo'; }
                                                else { $img = '<img src="../images/'.htmlspecialchars($gambar).'" class="zoomable" alt="'.htmlspecialchars($namabarang).'">'; }
                                        ?>
                                        <tr>
                                            <td><?=$tanggal;?></td>
                                            <td><?=$img;?></td>
                                            <td><?=$namabarang;?></td>
                                            <td><?=$qty;?></td>
                                            <td><?=$keterangan;?></td>
                                            <?php if ($can_manage_transactions): ?>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm mb-1" data-toggle="modal" data-target="#edit<?=$idm;?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm mb-1" data-toggle="modal" data-target="#delete<?=$idm;?>">
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                        <?php if ($can_manage_transactions): ?>
                                         <div class="modal fade" id="edit<?=$idm;?>">
                                            <div class="modal-dialog">
                                              <div class="modal-content">
                                                <div class="modal-header">
                                                  <h4 class="modal-title">Edit Barang Masuk <?= htmlspecialchars($store_name_display); ?></h4>
                                                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form method="post" action="../function.php">
                                                    <div class="modal-body">
                                                      <input type="text" name="keterangan" value="<?=$keterangan;?>" class="form-control mb-2" placeholder="Keterangan/Pengirim" required>
                                                      <input type="number" name="qty" value="<?=$qty;?>" class="form-control mb-2" placeholder="Quantity" required min="1">
                                                      <input type="hidden" name="idb" value="<?=$idb;?>">
                                                      <input type="hidden" name="idm" value="<?=$idm;?>">
                                                      <button type="submit" class="btn btn-primary" name="updatebarangmasuk_dynamic_branch">Update</button>
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
                                                      <button type="submit" class="btn btn-danger" name="hapusbarangmasuk_dynamic_branch">Hapus</button>
                                                       <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    </div>
                                                </form>
                                              </div>
                                            </div>
                                          </div>
                                          <?php endif; ?>
                                        <?php
                                            } // end while
                                        } else {
                                             echo "<tr><td colspan='".($can_manage_transactions ? 6 : 5)."' class='text-center'>Gagal mengambil data atau belum ada barang masuk: " . mysqli_error($conn) . "</td></tr>";
                                        }
                                        ?>
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
        <?php if ($can_manage_transactions): ?>
         <div class="modal fade" id="myModal">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Tambah Barang Masuk <?= htmlspecialchars($store_name_display); ?></h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                 <form method="post" action="../function.php">
                <div class="modal-body">
                 <label>Pilih Barang:</label>
                 <select name="barangnya" class="form-control mb-2" required>
                    <option value="">-- Pilih Barang <?= htmlspecialchars($store_name_display); ?> --</option>
                    <?php
                    $ambilsemuadatanya_modal = mysqli_query($conn,"SELECT * FROM `{$store_prefix}_stock` ORDER BY namabarang ASC");
                    if ($ambilsemuadatanya_modal) {
                        while($fetcharray_modal = mysqli_fetch_array($ambilsemuadatanya_modal)){
                            $namabarangnya_modal = htmlspecialchars($fetcharray_modal['namabarang']);
                            $idbarangnya_modal = $fetcharray_modal['idbarang'];
                    ?>
                    <option value="<?=$idbarangnya_modal;?>"><?=$namabarangnya_modal;?></option>
                    <?php
                        }
                    }
                    ?>
                  </select>
                  <label>Quantity:</label>
                  <input type="number" name="qty" class="form-control mb-2" placeholder="Jumlah masuk" required min="1">
                   <label>Keterangan/Pengirim:</label>
                   <input type="text" name="penerima" class="form-control mb-2" placeholder="Keterangan / Pengirim" required>
                   <button type="submit" class="btn btn-primary" name="barangmasuk_dynamic_branch">Submit</button>
                </div>
                </form>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
        <script> $(document).ready(function() { $('#dataTable').DataTable({ stateSave: true }); }); </script>
    </body>
</html>