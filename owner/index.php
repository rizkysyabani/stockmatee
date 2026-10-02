<?php
require "../function.php";
require "../cek.php";

// Pastikan hanya role 'owner' yang bisa akses
if (!isset($_SESSION['roles']) || $_SESSION['roles'] != 'owner') {
     header('location: ../index.php'); // Redirect ke login jika bukan owner
     exit;
}

$session_username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Owner';

// --- Ambil Data untuk Dashboard Owner ---
// Jumlah Item Berbeda di Setiap Toko
$count_cisauk_item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(idbarang) as total FROM pusatstock"))['total'] ?? 0;
$count_parahita_item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(idbarang) as total FROM parahita_stock"))['total'] ?? 0;
$count_serpong_item = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(idbarang) as total FROM serpong_stock"))['total'] ?? 0;

// Menghitung item dari toko admin dinamis
$count_dynamic_admin_items = 0;
$sql_dynamic_admins = "SELECT username FROM login WHERE roles = 'admin' AND username NOT IN ('adminPusat', 'adminParahita', 'adminSerpong', 'owner')";
$result_dynamic_admins = mysqli_query($conn, $sql_dynamic_admins);
if ($result_dynamic_admins) {
    while ($admin = mysqli_fetch_assoc($result_dynamic_admins)) {
        $table_prefix = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $admin['username']));
        $stock_table_name = "`{$table_prefix}_stock`";
        // Periksa apakah tabel ada sebelum query
        $check_table_sql = "SHOW TABLES LIKE '{$table_prefix}_stock'";
        $check_table_result = mysqli_query($conn, $check_table_sql);
        if ($check_table_result && mysqli_num_rows($check_table_result) > 0) {
            $count_dynamic_admin_items += mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(idbarang) as total FROM {$stock_table_name}"))['total'] ?? 0;
        }
    }
}
$total_items = $count_cisauk_item + $count_parahita_item + $count_serpong_item + $count_dynamic_admin_items;


// Total Stok di Setiap Toko
$count_cisauk_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stock) as total FROM pusatstock"))['total'] ?? 0;
$count_parahita_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stock) as total FROM parahita_stock"))['total'] ?? 0;
$count_serpong_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stock) as total FROM serpong_stock"))['total'] ?? 0;

// Menghitung stok dari toko admin dinamis
$total_dynamic_admin_stock = 0;
$result_dynamic_admins_stock = mysqli_query($conn, $sql_dynamic_admins); // Query ulang jika perlu atau gunakan hasil sebelumnya
if ($result_dynamic_admins_stock) {
    while ($admin = mysqli_fetch_assoc($result_dynamic_admins_stock)) {
        $table_prefix = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $admin['username']));
        $stock_table_name = "`{$table_prefix}_stock`";
        $check_table_sql = "SHOW TABLES LIKE '{$table_prefix}_stock'";
        $check_table_result = mysqli_query($conn, $check_table_sql);
        if ($check_table_result && mysqli_num_rows($check_table_result) > 0) {
            $total_dynamic_admin_stock += mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stock) as total FROM {$stock_table_name}"))['total'] ?? 0;
        }
    }
}
$total_stock = $count_cisauk_stock + $count_parahita_stock + $count_serpong_stock + $total_dynamic_admin_stock;


// Jumlah Item Stok Rendah (misal <= 5)
$low_stock_limit = 5;
$count_cisauk_low = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(idbarang) as total FROM pusatstock WHERE stock <= $low_stock_limit"))['total'] ?? 0;
$count_parahita_low = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(idbarang) as total FROM parahita_stock WHERE stock <= $low_stock_limit"))['total'] ?? 0;
$count_serpong_low = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(idbarang) as total FROM serpong_stock WHERE stock <= $low_stock_limit"))['total'] ?? 0;

// Menghitung item stok rendah dari toko admin dinamis
$total_dynamic_admin_low_stock = 0;
$result_dynamic_admins_low = mysqli_query($conn, $sql_dynamic_admins);
if ($result_dynamic_admins_low) {
    while ($admin = mysqli_fetch_assoc($result_dynamic_admins_low)) {
        $table_prefix = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $admin['username']));
        $stock_table_name = "`{$table_prefix}_stock`";
        $check_table_sql = "SHOW TABLES LIKE '{$table_prefix}_stock'";
        $check_table_result = mysqli_query($conn, $check_table_sql);
        if ($check_table_result && mysqli_num_rows($check_table_result) > 0) {
            $total_dynamic_admin_low_stock += mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(idbarang) as total FROM {$stock_table_name} WHERE stock <= $low_stock_limit"))['total'] ?? 0;
        }
    }
}
$total_low_stock = $count_cisauk_low + $count_parahita_low + $count_serpong_low + $total_dynamic_admin_low_stock;


// Jumlah Permintaan Barang (PP) Pending
$count_pp_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(idpp) as total FROM pp WHERE status='PENDING'"))['total'] ?? 0;

// Jumlah Admin Terdaftar (selain owner)
$count_admins = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(iduser) as total FROM login WHERE roles != 'owner'"))['total'] ?? 0;

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Dashboard - Owner</title>
        <link href="../css/styles.css" rel="stylesheet" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
        <style>
            /* Gaya umum dan kartu seperti di admin/index.php */
            .card-footer .small a { color: white; }
            .card-footer .small a:hover { color: #ddd; }
            .sb-sidenav-dark { background-color: #003940 !important; }
            .sb-topnav {
                background-color: #003940 !important;
                display: flex;
                align-items: center;
            }
             .h-100 .card-body {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }
            .h-100 .card-body .text-right,
            .h-100 .card-body .d-flex.justify-content-between > div:last-child {
                margin-top: auto;
            }

            /* CSS untuk Tulisan Berjalan di Navbar Owner */
            .navbar-running-text-container {
                flex-grow: 1; 
                overflow: hidden;
                margin: 0 1rem; 
                height: 100%; 
                display: flex; 
                align-items: center; 
            }

            .navbar-running-text {
                display: inline-block;
                white-space: nowrap;
                padding-left: 100%; 
                animation: runTextNavbarOwner 20s linear infinite;
                font-weight: 500;
                color: #f8f9fa; 
                font-size: 0.9rem; 
            }

            @keyframes runTextNavbarOwner {
                0% { transform: translateX(0); }
                100% { transform: translateX(-150%); }
            }
            .sb-topnav .navbar-nav.ml-auto {
                margin-left: auto !important;
            }
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
             <div class="navbar-running-text-container">
                <p class="navbar-running-text">Selamat datang Bapak, <?= $session_username; ?>! Pantau seluruh operasional bisnismu.</p>
            </div>
        </nav>
        
        
        
        
        <div id="layoutSidenav">
            <?php require '_sidebar_owner.php'; // Include sidebar owner ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Dashboard Owner</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Ringkasan Sistem Keseluruhan</li>
                        </ol>
                        <div class="row">
                             <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4 h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div> <i class="fas fa-boxes fa-2x"></i> </div>
                                            <div class="text-right">
                                                <div class="h3"><?= number_format($total_items); ?></div>
                                                <div>Total Jenis Barang (All Stores)</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between small">
                                        <span>
                                            Pst:<?= number_format($count_cisauk_item); ?>, 
                                            Prh:<?= number_format($count_parahita_item); ?>, 
                                            Srp:<?= number_format($count_serpong_item); ?>,
                                            Dyn:<?= number_format($count_dynamic_admin_items); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                             <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4 h-100">
                                    <div class="card-body">
                                         <div class="d-flex justify-content-between align-items-start">
                                            <div> <i class="fas fa-dolly-flatbed fa-2x"></i> </div>
                                            <div class="text-right">
                                                <div class="h3"><?= number_format($total_stock); ?></div>
                                                <div>Total Kuantitas Stok (All Stores)</div>
                                            </div>
                                        </div>
                                    </div>
                                     <div class="card-footer d-flex align-items-center justify-content-between small">
                                         <span>
                                            Pst:<?= number_format($count_cisauk_stock); ?>, 
                                            Prh:<?= number_format($count_parahita_stock); ?>, 
                                            Srp:<?= number_format($count_serpong_stock); ?>,
                                            Dyn:<?= number_format($total_dynamic_admin_stock); ?>
                                         </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-warning text-dark mb-4 h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                             <div> <i class="fas fa-exclamation-triangle fa-2x"></i> </div>
                                            <div class="text-right">
                                                <div class="h3"><?= number_format($total_low_stock); ?></div>
                                                <div>Item Stok Rendah (&le;<?= $low_stock_limit; ?>)</div>
                                            </div>
                                        </div>
                                    </div>
                                     <div class="card-footer d-flex align-items-center justify-content-between small">
                                         <span class="text-dark">
                                            Pst:<?= number_format($count_cisauk_low); ?>, 
                                            Prh:<?= number_format($count_parahita_low); ?>, 
                                            Srp:<?= number_format($count_serpong_low); ?>,
                                            Dyn:<?= number_format($total_dynamic_admin_low_stock); ?>
                                         </span>
                                    </div>
                                </div>
                            </div>
                            
                             
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="card mb-4">
                                    <div class="card-header"><i class="fas fa-search me-1"></i>Navigasi Cepat</div>
                                    <div class="card-body">
                                        <a href="laporan.php" class="btn btn-lg btn-primary btn-block">
                                            <i class="fas fa-chart-line"></i> Lihat Laporan Detail Antar Cabang
                                        </a>
                                       
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; StokMate <?php echo date("Y"); ?></div>
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
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        </body>
</html>