<?php
require "../function.php";
require "../cek.php";

// Pastikan hanya adminPusat atau owner yang bisa akses
if (!isset($_SESSION['roles']) || !in_array($_SESSION['roles'], ['adminPusat', 'owner'])) {
    header('location: ../index.php');
    exit;
}

$email = $_SESSION['email']; // Atau username
$store_name = "Toko 2 Parahita";
// $store_prefix akan digunakan untuk menampilkan data dari tabel parahita_masuk dan parahita_stock
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
         <title>Barang Masuk - <?= htmlspecialchars($store_name); ?></title>
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
             <?php require '_sidebar.php'; // Include sidebar admin pusat ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                         <h1 class="mt-4">Barang Masuk <?= htmlspecialchars($store_name); ?></h1>
                         <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard Admin Pusat</a></li>
                            <li class="breadcrumb-item"><a href="parahita_index.php">Lihat Stock <?= htmlspecialchars($store_name); ?></a></li>
                            <li class="breadcrumb-item active">Riwayat Barang Masuk</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Data Barang Masuk <?= htmlspecialchars($store_name); ?>
                               <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#myModalParahitaMasuk">
                                <i class="fas fa-plus"></i> Tambah Barang Masuk ke <?= htmlspecialchars($store_name); ?>
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
                                        $ambilsemuadatastock = mysqli_query($conn, "select * from " . $store_prefix . "_masuk m, " . $store_prefix . "_stock s where s.idbarang = m.idbarang order by m.tanggal DESC");
                                        if ($ambilsemuadatastock) {
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
                                                    $img = '<img src="../images/'.htmlspecialchars($gambar).'" class="zoomable" alt="'.htmlspecialchars($namabarang).'">';
                                                }
                                        ?>
                                        <tr>
                                            <td><?=$tanggal;?></td>
                                            <td><?=$img;?></td>
                                            <td><?=$namabarang;?></td>
                                            <td><?=$qty;?></td>
                                            <td><?=$keterangan;?></td>
                                        </tr>
                                        <?php
                                            } 
                                        } else {
                                            echo "<tr><td colspan='5' class='text-center'>Gagal mengambil data barang masuk atau belum ada data.</td></tr>";
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
                            <div class="text-muted">Copyright &copy; StokMate <?php echo date("Y"); ?></div>
                            <div> <a href="#">Privacy Policy</a> &middot; <a href="#">Terms &amp; Conditions</a> </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

         <div class="modal fade" id="myModalParahitaMasuk">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Tambah Barang Masuk ke <?= htmlspecialchars($store_name); ?></h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                 <form method="post" action="../function.php">
                    <div class="modal-body">
                         <input type="hidden" name="initiator" value="admin_pusat">
                         <div class="form-group">
                             <label>Pilih Barang (Dari Stok Pusat):</label>
                             <select name="barangnya" class="form-control" required>
                                <option value="">-- Pilih Barang dari Stok Pusat --</option>
                                <?php
                                $ambilsemuadataripusat = mysqli_query($conn,"select * from pusatstock order by namabarang ASC");
                                if($ambilsemuadataripusat){
                                    while($fetcharray_pusat = mysqli_fetch_array($ambilsemuadataripusat)){
                                        $namabarangnya_pusat = htmlspecialchars($fetcharray_pusat['namabarang']);
                                        $idbarangnya_pusat = $fetcharray_pusat['idbarang'];
                                        $stocknya_pusat = htmlspecialchars($fetcharray_pusat['stock']);
                                ?>
                                <option value="<?=$idbarangnya_pusat;?>"><?=$namabarangnya_pusat;?> (Stok Pusat: <?=$stocknya_pusat;?>)</option>
                                <?php 
                                    }
                                }
                                ?>
                              </select>
                         </div>
                         <div class="form-group">
                              <label>Quantity Masuk ke <?= htmlspecialchars($store_name); ?>:</label>
                              <input type="number" name="qty" class="form-control" placeholder="Jumlah masuk" required min="1">
                         </div>
                         <div class="form-group">
                               <label>Keterangan/Pengirim:</label>
                               <input type="text" name="penerima" class="form-control" placeholder="Keterangan / Pengirim" required>
                         </div>
                       <button type="submit" class="btn btn-primary" name="barangmasuk_parahita">Submit</button>
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
                  stateSave: true
              });
            });
        </script>
    </body>
</html>