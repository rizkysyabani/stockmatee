<?php
// File: new/admin/adminBaru_keluar.php
// Halaman ini diakses oleh Admin Pusat untuk MELIHAT riwayat barang keluar Toko Pengguna Baru

require "../function.php";
require "../cek.php";

if (!isset($_SESSION['roles']) || !in_array($_SESSION['roles'], ['adminPusat', 'owner'])) {
    header('location: ../index.php');
    exit;
}

$view_user_username = $_GET['user'] ?? null;

if (empty($view_user_username)) {
    $_SESSION['error_message'] = "Nama pengguna cabang tidak ditentukan.";
    header('location: index.php');
    exit;
}

$view_user_display = htmlspecialchars($view_user_username);
$view_store_name_display = "Toko " . $view_user_display;
$view_store_prefix = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $view_user_username));

if (empty($view_store_prefix)) {
    $_SESSION['error_message'] = "Format nama pengguna cabang tidak valid.";
    header('location: index.php');
    exit;
}

// Cek apakah tabel untuk user ini memang ada
$check_table_keluar_sql = "SHOW TABLES LIKE '{$view_store_prefix}_keluar'";
$check_table_keluar_result = mysqli_query($conn, $check_table_keluar_sql);
$check_table_stock_sql = "SHOW TABLES LIKE '{$view_store_prefix}_stock'";
$check_table_stock_result = mysqli_query($conn, $check_table_stock_sql);

if (!$check_table_keluar_result || mysqli_num_rows($check_table_keluar_result) == 0 ||
    !$check_table_stock_result || mysqli_num_rows($check_table_stock_result) == 0) {
    $_SESSION['error_message'] = "Data transaksi keluar atau stok untuk toko '{$view_user_display}' tidak ditemukan.";
    header('location: index.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Lihat Barang Keluar - <?= htmlspecialchars($view_store_name_display); ?></title>
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
           <?php require '_sidebar.php'; // Sidebar Admin Pusat ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Riwayat Barang Keluar <?= htmlspecialchars($view_store_name_display); ?></h1>
                         <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard Admin Pusat</a></li>
                            <li class="breadcrumb-item"><a href="adminBaru_index.php?user=<?= urlencode($view_user_username); ?>">Lihat Stock <?= $view_user_display; ?></a></li>
                            <li class="breadcrumb-item active">Riwayat Barang Keluar</li>
                        </ol>
                        <div class="card mb-4">
                             <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Data Barang Keluar <?= $view_user_display; ?>
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
                                        // Query ke tabel dinamis
                                        $sql_keluar = "SELECT k.*, s.namabarang, s.image 
                                                       FROM `{$view_store_prefix}_keluar` k 
                                                       JOIN `{$view_store_prefix}_stock` s ON k.idbarang = s.idbarang 
                                                       ORDER BY k.tanggal DESC";
                                        $ambilsemuadatastock = mysqli_query($conn, $sql_keluar);

                                        if($ambilsemuadatastock){
                                             if(mysqli_num_rows($ambilsemuadatastock) > 0) {
                                                while($data=mysqli_fetch_array($ambilsemuadatastock)){
                                                    $tanggal = $data['tanggal'];
                                                    $namabarang = htmlspecialchars($data['namabarang']);
                                                    $qty = htmlspecialchars($data['qty']);
                                                    $penerima = htmlspecialchars($data['penerima']);
                                                    $gambar = $data['image'];

                                                    if($gambar==null || !file_exists("../images/".$gambar)){ $img = 'No Photo'; }
                                                    else { $img = '<img src="../images/'.htmlspecialchars($gambar).'" class="zoomable" alt="'.htmlspecialchars($namabarang).'">'; }
                                            ?>
                                            <tr>
                                                <td><?=$tanggal;?></td>
                                                <td><?=$img;?></td>
                                                <td><?=$namabarang;?></td>
                                                <td><?=$qty;?></td>
                                                <td><?=$penerima;?></td>
                                            </tr>
                                            <?php
                                                } // end while
                                            } else {
                                                echo "<tr><td colspan='5' class='text-center'>Belum ada data barang keluar untuk toko ini.</td></tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='5' class='text-center'>Gagal mengambil data barang keluar {$view_store_name_display}: " . mysqli_error($conn) . "</td></tr>";
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
        <script> $(document).ready(function() { $('#dataTable').DataTable({ stateSave: true }); }); </script>
    </body>
</html>