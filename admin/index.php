 <?php
require "../function.php";
require "../cek.php"; // Pastikan hanya adminPusat atau owner yang bisa akses

if (!isset($_SESSION['roles']) || !in_array($_SESSION['roles'], ['adminPusat', 'owner'])) {
    header('location: ../index.php');
    exit;
}

$identifier = $_SESSION['username'] ?? ($_SESSION['email'] ?? 'Admin');

// --- Ambil Data untuk Dashboard Admin Pusat ---
$query_total_stok_pusat = mysqli_query($conn, "SELECT SUM(stock) as total_stok FROM pusatstock");
$data_total_stok_pusat = mysqli_fetch_assoc($query_total_stok_pusat);
$total_stok_pusat = $data_total_stok_pusat['total_stok'] ?? 0;

$query_jenis_barang_pusat = mysqli_query($conn, "SELECT COUNT(idbarang) as total_jenis FROM pusatstock");
$data_jenis_barang_pusat = mysqli_fetch_assoc($query_jenis_barang_pusat);
$total_jenis_barang_pusat = $data_jenis_barang_pusat['total_jenis'] ?? 0;

$query_pp_pending = mysqli_query($conn, "SELECT COUNT(idpp) as total_pending FROM pp WHERE status='PENDING'");
$data_pp_pending = mysqli_fetch_assoc($query_pp_pending);
$total_pp_pending = $data_pp_pending['total_pending'] ?? 0;

$query_total_pengguna = mysqli_query($conn, "SELECT COUNT(iduser) as total_user FROM login");
$data_total_pengguna = mysqli_fetch_assoc($query_total_pengguna);
$total_pengguna = $data_total_pengguna['total_user'] ?? 0;

// Data untuk Grafik (Sama seperti sebelumnya)
$barangMasukPusatLabels = [];
$barangMasukPusatData = [];
$queryMasukPusat = "SELECT DATE_FORMAT(tanggal, '%Y-%m') AS bulan, SUM(qty) AS total_masuk 
                    FROM pusatmasuk 
                    WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                    GROUP BY DATE_FORMAT(tanggal, '%Y-%m') 
                    ORDER BY bulan ASC";
$resultMasukPusat = mysqli_query($conn, $queryMasukPusat);
if ($resultMasukPusat) {
    while ($row = mysqli_fetch_assoc($resultMasukPusat)) {
        $barangMasukPusatLabels[] = $row['bulan'];
        $barangMasukPusatData[] = (int)$row['total_masuk'];
    }
}

$barangKeluarPusatData = [];
$queryKeluarPusat = "SELECT DATE_FORMAT(tanggal, '%Y-%m') AS bulan, SUM(qty) AS total_keluar 
                     FROM pusatkeluar 
                     WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                     GROUP BY DATE_FORMAT(tanggal, '%Y-%m') 
                     ORDER BY bulan ASC";
$resultKeluarPusat = mysqli_query($conn, $queryKeluarPusat);
$tempKeluarDataForBar = [];
if ($resultKeluarPusat) {
    while ($row = mysqli_fetch_assoc($resultKeluarPusat)) {
        $tempKeluarDataForBar[$row['bulan']] = (int)$row['total_keluar'];
    }
}

$combinedMonths = array_unique(array_merge($barangMasukPusatLabels, array_keys($tempKeluarDataForBar)));
sort($combinedMonths);

$barChartLabels = $combinedMonths;
$barChartDataMasuk = [];
$barChartDataKeluar = [];
$tempMasukDataForBar = array_combine($barangMasukPusatLabels, $barangMasukPusatData);

foreach ($barChartLabels as $month) {
    $barChartDataMasuk[] = $tempMasukDataForBar[$month] ?? 0;
    $barChartDataKeluar[] = $tempKeluarDataForBar[$month] ?? 0;
}

$popup_message_dashboard = "";
$popup_type_dashboard = "";
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Dashboard - Admin Pusat</title>
        <link href="../css/styles.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
        <style>
            .card-footer .small a {
                color: white;
            }
            .card-footer .small a:hover {
                color: #ddd;
            }
            .sb-sidenav-dark {
                 background-color: #003940 !important;
            }
            .sb-topnav {
                background-color: #003940 !important;
                display: flex; /* Memastikan flexbox aktif */
                align-items: center; /* Menyelaraskan item secara vertikal */
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
            
            /* CSS untuk Tulisan Berjalan di Navbar */
            .navbar-running-text-container {
                flex-grow: 1; /* Mengambil sisa ruang di navbar */
                overflow: hidden;
                margin: 0 1rem; /* Memberi sedikit jarak dari elemen sekitarnya */
                height: 100%; /* Menyesuaikan tinggi dengan navbar */
                display: flex; /* Untuk align-items center */
                align-items: center; /* Untuk align-items center */
            }

            .navbar-running-text {
                display: inline-block;
                white-space: nowrap;
                padding-left: 100%; 
                animation: runTextNavbar 20s linear infinite;
                font-weight: 500;
                color: #f8f9fa; /* Warna terang untuk kontras dengan navbar gelap */
                font-size: 0.9rem; /* Ukuran font yang sesuai untuk navbar */
            }

            @keyframes runTextNavbar {
                0% {
                    transform: translateX(0);
                }
                100% {
                    transform: translateX(-150%); 
                }
            }

            /* Pastikan menu pengguna tetap di kanan */
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
                <p class="navbar-running-text">Selamat datang <?= htmlspecialchars($identifier); ?>! Semoga harimu menyenangkan dan penuh produktivitas.</p>
            </div>
        </nav>
        <div id="layoutSidenav">
            <?php require '_sidebar.php'; ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Dashboard</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Admin Pusat Overview</li>
                        </ol>
                        
                        <div class="row">
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4 h-100">  
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">  
                                            <i class="fas fa-cubes fa-3x"></i>  
                                            <div class="text-right">
                                                <div class="h3 mb-0"><?= number_format($total_stok_pusat); ?> Unit</div>
                                                <p class="small mb-0">Unit Total Stok Gudang Pusat</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="stock.php">View Details</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-warning text-white mb-4 h-100">  
                                     <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <i class="fas fa-tags fa-3x"></i>
                                            <div class="text-right">
                                                <div class="h3 mb-0"><?= number_format($total_jenis_barang_pusat); ?> Jenis</div>
                                                <p class="small mb-0">Jenis Barang (Pusat)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="stock.php">View Details</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4 h-100">  
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <i class="fas fa-hand-holding-usd fa-3x"></i>
                                            <div class="text-right">
                                                <div class="h3 mb-0"><?= number_format($total_pp_pending); ?> Permintaan</div>
                                                <p class="small mb-0">PP Barang Pending</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="pp.php">View Details</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-danger text-white mb-4 h-100">  
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <i class="fas fa-users fa-3x"></i>
                                            <div class="text-right">
                                                <div class="h3 mb-0"><?= number_format($total_pengguna); ?> Pengguna</div>
                                                <p class="small mb-0">Total Pengguna Sistem</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="admin.php">View Details</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-chart-area me-1"></i>
                                        Tren Barang Masuk Gudang Pusat (6 Bulan Terakhir)
                                    </div>
                                    <div class="card-body"><canvas id="pusatAreaChartMasuk" width="100%" height="40"></canvas></div>
                                </div>
                            </div>
                            <div class="col-xl-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        Barang Masuk vs Keluar Gudang Pusat (6 Bulan Terakhir)
                                    </div>
                                    <div class="card-body"><canvas id="pusatBarChartInOut" width="100%" height="40"></canvas></div>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Stok Barang Gudang Pusat (Contoh)
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="dataTableStockPusat" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Nama Barang</th>
                                                <th>Deskripsi</th>
                                                <th>Stock</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query_stok_pusat_tabel = mysqli_query($conn, "SELECT namabarang, deskripsi, stock FROM pusatstock ORDER BY namabarang ASC LIMIT 10");
                                            if ($query_stok_pusat_tabel) {
                                                while ($item_stok_tabel = mysqli_fetch_assoc($query_stok_pusat_tabel)) {
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($item_stok_tabel['namabarang']); ?></td>
                                                <td><?= htmlspecialchars($item_stok_tabel['deskripsi']); ?></td>
                                                <td><?= htmlspecialchars($item_stok_tabel['stock']); ?></td>
                                            </tr>
                                            <?php
                                                }
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            $(document).ready(function() {
                $('#dataTableStockPusat').DataTable();

                const popupMessage = "<?php echo addslashes($popup_message_dashboard); ?>";
                const popupType = "<?php echo addslashes($popup_type_dashboard); ?>";
                // ... (logika SweetAlert jika ada) ...

                var barangMasukPusatLabels = <?php echo json_encode($barangMasukPusatLabels); ?>;
                var barangMasukPusatData = <?php echo json_encode($barangMasukPusatData); ?>;
                
                var barChartLabels = <?php echo json_encode($barChartLabels); ?>;
                var barChartDataMasuk = <?php echo json_encode($barChartDataMasuk); ?>;
                var barChartDataKeluar = <?php echo json_encode($barChartDataKeluar); ?>;

                // Area Chart
                var ctxAreaPusat = document.getElementById("pusatAreaChartMasuk");
                if (ctxAreaPusat) {
                    var myAreaChartPusat = new Chart(ctxAreaPusat, {
                        type: 'line',
                        data: {
                            labels: barangMasukPusatLabels,
                            datasets: [{
                                label: "Barang Masuk",
                                lineTension: 0.3,
                                backgroundColor: "rgba(2,117,216,0.2)",
                                borderColor: "rgba(2,117,216,1)",
                                pointRadius: 5,
                                pointBackgroundColor: "rgba(2,117,216,1)",
                                pointBorderColor: "rgba(255,255,255,0.8)",
                                pointHoverRadius: 5,
                                pointHoverBackgroundColor: "rgba(2,117,216,1)",
                                pointHitRadius: 50,
                                pointBorderWidth: 2,
                                data: barangMasukPusatData,
                            }],
                        },
                        options: { scales: { xAxes: [{ time: { unit: 'month' }, gridLines: { display: false }, ticks: { maxTicksLimit: 6 } }], yAxes: [{ ticks: { min: 0, maxTicksLimit: 5 }, gridLines: { color: "rgba(0, 0, 0, .125)" } }], }, legend: { display: false } }
                    });
                }

                // Bar Chart
                var ctxBarPusat = document.getElementById("pusatBarChartInOut");
                if (ctxBarPusat) {
                    var myBarChartPusat = new Chart(ctxBarPusat, {
                        type: 'bar',
                        data: {
                            labels: barChartLabels,
                            datasets: [{
                                label: "Barang Masuk",
                                backgroundColor: "rgba(2,117,216,1)",
                                borderColor: "rgba(2,117,216,1)",
                                data: barChartDataMasuk,
                            }, {
                                label: "Barang Keluar",
                                backgroundColor: "rgba(220,53,69,1)",
                                borderColor: "rgba(220,53,69,1)",
                                data: barChartDataKeluar,
                            }],
                        },
                        options: { scales: { xAxes: [{ time: { unit: 'month' }, gridLines: { display: false }, ticks: { maxTicksLimit: 6 } }], yAxes: [{ ticks: { min: 0, maxTicksLimit: 5 }, gridLines: { display: true } }], }, legend: { display: true } }
                    });
                }
            });
        </script>
    </body>
</html>