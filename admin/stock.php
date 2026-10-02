<?php
require "../function.php";
require "../cek.php";

$email = $_SESSION['email']; // Atau username
$store_name = "Toko 1 Cisauk (Pusat)"; // Nama Toko Default
$store_prefix = "pusat"; // Prefix tabel default
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Stock Barang - <?= $store_name; ?></title>
        <link href="../css/styles.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
        <style>
            .zoomable { width: 100px; }
            .zoomable:hover { transform: scale(2.5); transition: 0.3s ease; }
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
            <?php require '_sidebar.php'; // Include sidebar ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid" >
                        <h1 class="mt-4">Stock Barang <?= $store_name; ?></h1>
                        <div class="card mb-4">
                            <div class="card-header">
                              <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                                <i class="fas fa-plus"></i> Tambah Barang
                              </button>
                              <a href="export.php" class="btn btn-info"> <i class="fas fa-file-export"></i> Export Data</a>
                            </div>
                            <div class="card-body">
                            <?php
                            // Alert stok habis (menggunakan tabel pusat)
                            $ambildatastock = mysqli_query($conn, "select * from " . $store_prefix . "stock where stock < 1");
                            while($fetch=mysqli_fetch_array($ambildatastock)){
                                $barang = htmlspecialchars($fetch['namabarang']);
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
                                                <th>Stock/Lusin</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                          <?php
                                          // Mengambil data dari tabel pusat
                                          $ambilsemuadatastock = mysqli_query($conn, "select * from " . $store_prefix . "stock");
                                          $i = 1;
                                          while($data=mysqli_fetch_array($ambilsemuadatastock)){
                                              $namabarang = htmlspecialchars($data['namabarang']);
                                              $deskripsi = htmlspecialchars($data['deskripsi']);
                                              $stock = htmlspecialchars($data['stock']);
                                              $idb = $data['idbarang'];
                                              $gambar = $data['image'];

                                              if($gambar==null || !file_exists("../images/".$gambar)){
                                                  $img = 'No Photo';
                                              } else {
                                                  $img = '<img src="../images/'.htmlspecialchars($gambar).'" class="zoomable" alt="'.$namabarang.'">';
                                              }
                                          ?>
                                          <tr>
                                              <td><?=$i++;?></td>
                                              <td><?=$img;?></td>
                                              <td><?=$namabarang;?></td>
                                              <td><?=$deskripsi;?></td>
                                              <td><?=$stock;?></td>
                                              <td>
                                                  <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#edit<?=$idb;?>"> <i class="fas fa-edit"></i> Edit</button>
                                                  <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#delete<?=$idb;?>"> <i class="fas fa-trash"></i> Delete</button>
                                              </td>
                                          </tr>

                                          <div class="modal fade" id="edit<?=$idb;?>">
                                              <div class="modal-dialog">
                                                  <div class="modal-content">
                                                      <div class="modal-header">
                                                          <h4 class="modal-title">Edit Barang <?= $store_name; ?></h4>
                                                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                      </div>
                                                      <form method="post" enctype="multipart/form-data">
                                                          <div class="modal-body">
                                                              <input type="text" name="namabarang" value="<?=$namabarang;?>" class="form-control" required>
                                                              <br>
                                                              <input type="text" name="deskripsi" value="<?=$deskripsi;?>" class="form-control" required>
                                                              <br>
                                                              <label>Ganti Gambar (Opsional):</label>
                                                              <input type="file" name="file" class="form-control-file">
                                                              <br>
                                                              <input type="hidden" name="idb" value="<?=$idb;?>">
                                                              <button type="submit" class="btn btn-primary" name="updatebarang">Update</button>
                                                          </div>
                                                      </form>
                                                  </div>
                                              </div>
                                          </div>

                                          <div class="modal fade" id="delete<?=$idb;?>">
                                              <div class="modal-dialog">
                                                  <div class="modal-content">
                                                      <div class="modal-header">
                                                          <h4 class="modal-title">Hapus Barang?</h4>
                                                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                      </div>
                                                      <form method="post">
                                                          <div class="modal-body">
                                                              Apakah anda yakin ingin menghapus <?=$namabarang;?>?
                                                              <input type="hidden" name="idb" value="<?=$idb;?>">
                                                              <br><br>
                                                              <button type="submit" class="btn btn-danger" name="hapusbarang">Hapus</button>
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
                    <div class="container-fluid">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; StokMate 2025</div>
                            <div>
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
                  <h4 class="modal-title">Tambah Barang <?= $store_name; ?></h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="post" enctype="multipart/form-data">
                    <div class="modal-body">
                      <input type="text" name="namabarang" placeholder="Nama Barang" class="form-control" required>
                      <br>
                      <input type="text" name="deskripsi" placeholder="Deskripsi barang" class="form-control" required>
                      <br>
                      <input type="number" name="stock" class="form-control" placeholder="Stock Awal" required min="0">
                      <br>
                      <input type="file" name="file" class="form-control-file">
                      <small class="form-text text-muted">Ukuran maks 15MB (jpg, png, jpeg).</small>
                      <br>
                       <button type="submit" class="btn btn-primary" name="addnewbarang">Submit</button>
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
        <script src="../assets/demo/datatables-demo.js"></script>
    </body>
</html>