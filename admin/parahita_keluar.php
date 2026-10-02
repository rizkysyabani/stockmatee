<?php
require "../function.php";
require "../cek.php";

$email = $_SESSION['email']; // Atau username
$store_name = "Toko 2 Parahita";
$store_prefix = "parahita";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Barang Keluar - <?= $store_name; ?></title>
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
           <?php require '_sidebar.php'; // Include sidebar baru ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid">
                         <h1 class="mt-4">Barang Keluar <?= $store_name; ?></h1>
                        <div class="card mb-4">
                            <div class="card-header">
                              
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
                                        <th>Penerima</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                       <?php
                                        $ambilsemuadatastock = mysqli_query($conn, "select * from " . $store_prefix . "_keluar k, " . $store_prefix . "_stock s where s.idbarang = k.idbarang order by k.tanggal DESC");
                                        while($data=mysqli_fetch_array($ambilsemuadatastock)){
                                            $idk = $data['idkeluar'];
                                            $idb = $data['idbarang'];
                                            $tanggal = $data['tanggal'];
                                            $namabarang = htmlspecialchars($data['namabarang']);
                                            $qty = htmlspecialchars($data['qty']);
                                            $penerima = htmlspecialchars($data['penerima']);
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
                                            <td><?=$penerima;?></td>
                                          
                                        </tr>
                                        <div class="modal fade" id="edit<?= $store_prefix; ?><?=$idk;?>">
                                            <div class="modal-dialog">
                                              <div class="modal-content">
                                                <div class="modal-header">
                                                  <h4 class="modal-title">Edit Barang Keluar <?= $store_name; ?></h4>
                                                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form method="post">
                                                    <div class="modal-body">
                                                      <input type="text" name="penerima" value="<?=$penerima;?>" class="form-control" placeholder="Penerima" required>
                                                      <br>
                                                      <input type="number" name="qty" value="<?=$qty;?>" class="form-control" placeholder="Quantity" required min="1">
                                                      <br>
                                                      <input type="hidden" name="idb" value="<?=$idb;?>">
                                                      <input type="hidden" name="idk" value="<?=$idk;?>">
                                                      <button type="submit" class="btn btn-primary" name="updatebarangkeluar_<?= $store_prefix; ?>">Update</button>
                                                    </div>
                                                </form>
                                              </div>
                                            </div>
                                        </div>
                                        <div class="modal fade" id="delete<?= $store_prefix; ?><?=$idk;?>">
                                            <div class="modal-dialog">
                                              <div class="modal-content">
                                                <div class="modal-header">
                                                  <h4 class="modal-title">Hapus Barang Keluar?</h4>
                                                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form method="post">
                                                    <div class="modal-body">
                                                     Apakah anda yakin ingin menghapus data keluar <?=$namabarang;?> sejumlah <?=$qty;?>?
                                                     <br>(Stok barang akan dikembalikan)
                                                      <input type="hidden" name="idb" value="<?=$idb;?>">
                                                      <input type="hidden" name="kty" value="<?=$qty;?>">
                                                      <input type="hidden" name="idk" value="<?=$idk;?>">
                                                      <br><br>
                                                       <button type="submit" class="btn btn-danger" name="hapusbarangkeluar_<?= $store_prefix; ?>">Hapus</button>
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


        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
        <script src="../assets/demo/datatables-demo.js"></script>
    </body>
</html>