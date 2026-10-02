<?php
require "../function.php"; //
require "../cek.php"; //

// MODIFIKASI: Tambahkan/Sesuaikan cek role
// if (!isset($_SESSION['roles']) || $_SESSION['roles'] != 'karyawanBaru') {
//      header('location: ../login.php');
//      exit;
// }

$identifier = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Karyawan'; //
// MODIFIKASI: Ubah nama toko dan prefix tabel
$store_name = "Toko Admin Baru"; // Nama toko terkait
$store_prefix = "adminBaru"; // Gunakan tabel adminBaru //
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Stock Barang - <?= $store_name; ?></title> 
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
    <body class="sb-nav-fixed" >
        <nav class="sb-topnav navbar navbar-expand navbar-dark" style="background-color: #003940; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <a class="navbar-brand" href="index.php" style="font-weight: bold; letter-spacing: 1px;">
                Stock <sup style="color: #80DEEA; font-weight: normal;">Mate</sup>
            </a>
            <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#" style="color: #ffffff; transition: transform 0.3s ease;">
                <i class="fas fa-bars fa-lg"></i> 
            </button>
        </nav>
        <div id="layoutSidenav">
            <?php require '_sidebar_karyawan_baru.php'; ?> 
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4" >
                        <h1 class="mt-4">Stock Barang <?= isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Pengguna'; ?></h1> 
                        <div class="card mb-4">
                            <div class="card-body">
                            <?php
                            // MODIFIKASI: Pastikan query menggunakan tabel baru
                            $ambildatastock = mysqli_query($conn, "select * from " . $store_prefix . "_stock where stock < 1"); //
                            while($fetch=mysqli_fetch_array($ambildatastock)){ //
                                $barang = htmlspecialchars($fetch['namabarang']); //
                            ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Perhatian!</strong> Stock <?=$barang;?> Telah Habis 
                            </div>
                            <?php } ?> 

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Gambar</th>
                                                <th>Nama Barang</th>
                                                <th>Deskripsi</th>
                                                <th>Stock</th>
                                                </tr>
                                        </thead> 
                                        <tbody>
                                          <?php
                                          // MODIFIKASI: Pastikan query menggunakan tabel baru
                                          $ambilsemuadatastock = mysqli_query($conn, "select * from " . $store_prefix . "_stock"); //
                                          $i = 1; //
                                          while($data=mysqli_fetch_array($ambilsemuadatastock)){ //
                                              $namabarang = htmlspecialchars($data['namabarang']); //
                                              $deskripsi = htmlspecialchars($data['deskripsi']); //
                                              $stock = htmlspecialchars($data['stock']); //
                                              $gambar = $data['image']; //

                                              if($gambar==null || !file_exists("../images/".$gambar)){ //
                                                  $img = 'No Photo'; //
                                              } else { //
                                                  $img = '<img src="../images/'.htmlspecialchars($gambar).'" class="zoomable" alt="'.$namabarang.'">'; //
                                              }
                                          ?>
                                          <tr>
                                              <td><?=$i++;?></td>
                                              <td><?=$img;?></td>
                                              <td><?=$namabarang;?></td>
                                              <td><?=$deskripsi;?></td>
                                              <td><?=$stock;?></td>
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
                     <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; StokMate 2025</div>
                            <div> <a href="#">Privacy Policy</a> &middot; <a href="#">Terms &amp; Conditions</a> </div>
                        </div>
                    </div>
                </footer> 
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