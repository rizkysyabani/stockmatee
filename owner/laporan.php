<?php
require "../function.php";
require "../cek.php";

// Pastikan hanya role 'owner' yang bisa akses
if (!isset($_SESSION['roles']) || $_SESSION['roles'] != 'owner') {
     header('location: ../index.php');
     exit;
}

$session_username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Owner';

// --- Ambil data filter dari URL (jika ada) ---
$reportType = $_GET['reportType'] ?? null;
$store = $_GET['store'] ?? 'semua';
$tanggalMulai = $_GET['tanggalMulai'] ?? null;
$tanggalAkhir = $_GET['tanggalAkhir'] ?? null;

$results = null; // Variabel untuk menyimpan hasil query
$error_msg = ''; // Variabel untuk pesan error
$reportTitle = 'Laporan'; // Judul default

// --- Fungsi untuk Eksekusi Query & Fetch Data ---
function fetch_report_data($conn, $sql, $params = []) {
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        // Tampilkan SQL jika ada error persiapan (untuk debugging)
        // error_log("SQL Error Prepare: " . $sql . " | Params: " . print_r($params, true) . " | Error: " . mysqli_error($conn));
        return ['error' => "Gagal menyiapkan query: " . mysqli_error($conn)];
    }
    if (!empty($params)) {
        $types = str_repeat('s', count($params)); // Asumsi semua parameter string (tanggal)
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    if (!mysqli_stmt_execute($stmt)) {
         $stmt_error = mysqli_stmt_error($stmt);
         mysqli_stmt_close($stmt);
         // error_log("SQL Error Execute: " . $sql . " | Params: " . print_r($params, true) . " | Error: " . $stmt_error);
         return ['error' => "Gagal mengeksekusi query: " . $stmt_error];
    }
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);
    return ['data' => $data];
}

// --- Proses jika form filter disubmit ---
if ($reportType) {
    $params = [];
    $date_condition = "";
    $base_date_param_count = 0; // Untuk menghitung berapa parameter tanggal dasar

    // Tambahkan filter tanggal jika ada
    if (!empty($tanggalMulai) && !empty($tanggalAkhir)) {
        // Pastikan format tanggal YYYY-MM-DD HH:MM:SS
        $tglMulaiFormatted = $tanggalMulai . ' 00:00:00';
        $tglAkhirFormatted = $tanggalAkhir . ' 23:59:59';
        // Gunakan alias tabel (m atau k atau pp) pada kolom tanggal
        $date_condition = " AND {alias}.tanggal BETWEEN ? AND ? ";
        $params[] = $tglMulaiFormatted;
        $params[] = $tglAkhirFormatted;
        $base_date_param_count = 2;
    } elseif (!empty($tanggalMulai)) {
        $tglMulaiFormatted = $tanggalMulai . ' 00:00:00';
        $date_condition = " AND {alias}.tanggal >= ? ";
        $params[] = $tglMulaiFormatted;
        $base_date_param_count = 1;
    } elseif (!empty($tanggalAkhir)) {
        $tglAkhirFormatted = $tanggalAkhir . ' 23:59:59';
        $date_condition = " AND {alias}.tanggal <= ? ";
        $params[] = $tglAkhirFormatted;
        $base_date_param_count = 1;
    }

    $sql = "";
    $store_name_report = "Semua Toko"; // Default

    if ($store != 'semua') {
        $store_table_prefix = $store; // pusat, parahita, serpong
        $toko_map = ['pusat' => 'Toko 1 Cisauk (Pusat)', 'parahita' => 'Toko 2 Parahita', 'serpong' => 'Toko 3 Serpong'];
        $store_name_report = $toko_map[$store] ?? ucfirst($store);
    }

    switch ($reportType) {
        case 'stok':
            $reportTitle = "Laporan Stok - " . $store_name_report;
            if ($store == 'semua') {
                // Gabungkan stok dari semua toko (UNION ALL)
                // **Perbaikan nama tabel pusatstock**
                $sql = "(SELECT idbarang, namabarang, deskripsi, stock, 'Pusat' as toko FROM pusatstock)
                        UNION ALL
                        (SELECT idbarang, namabarang, deskripsi, stock, 'Parahita' as toko FROM parahita_stock)
                        UNION ALL
                        (SELECT idbarang, namabarang, deskripsi, stock, 'Serpong' as toko FROM serpong_stock)
                        ORDER BY toko, namabarang";
                 $date_condition = ""; // Tanggal tidak relevan
                 $params = [];
            } else {
                 // **Perbaikan nama tabel jika $store == 'pusat'**
                 $stock_table_name = ($store == 'pusat') ? 'pusatstock' : $store . '_stock';
                 $sql = "SELECT idbarang, namabarang, deskripsi, stock FROM {$stock_table_name} ORDER BY namabarang";
                 $date_condition = ""; // Tanggal tidak relevan
                 $params = [];
            }
            break;

        case 'masuk':
            $reportTitle = "Laporan Barang Masuk - " . $store_name_report;
            $final_date_condition_masuk = str_replace('{alias}', 'm', $date_condition); // Ganti alias untuk query masuk

             if ($store == 'semua') {
                 // **Perbaikan nama tabel pusatstock & pusatmasuk**
                 $sql = "(SELECT m.idmasuk, m.tanggal, s.namabarang, m.qty, m.keterangan, 'Pusat' as toko FROM pusatmasuk m JOIN pusatstock s ON m.idbarang = s.idbarang WHERE 1=1 {$final_date_condition_masuk})
                         UNION ALL
                         (SELECT m.idmasuk, m.tanggal, s.namabarang, m.qty, m.keterangan, 'Parahita' as toko FROM parahita_masuk m JOIN parahita_stock s ON m.idbarang = s.idbarang WHERE 1=1 {$final_date_condition_masuk})
                         UNION ALL
                         (SELECT m.idmasuk, m.tanggal, s.namabarang, m.qty, m.keterangan, 'Serpong' as toko FROM serpong_masuk m JOIN serpong_stock s ON m.idbarang = s.idbarang WHERE 1=1 {$final_date_condition_masuk})
                         ORDER BY tanggal DESC, toko";
                 // Duplikasi parameter tanggal untuk setiap subquery UNION
                 if ($base_date_param_count > 0) {
                     $original_params = $params;
                     $params = array_merge($original_params, $original_params, $original_params);
                 }
             } else {
                // **Perbaikan nama tabel jika $store == 'pusat'**
                $masuk_table_name = ($store == 'pusat') ? 'pusatmasuk' : $store . '_masuk';
                $stock_table_name = ($store == 'pusat') ? 'pusatstock' : $store . '_stock';
                $sql = "SELECT m.idmasuk, m.tanggal, s.namabarang, m.qty, m.keterangan
                        FROM {$masuk_table_name} m
                        JOIN {$stock_table_name} s ON m.idbarang = s.idbarang
                        WHERE 1=1 {$final_date_condition_masuk}
                        ORDER BY m.tanggal DESC";
             }
            break;

        case 'keluar':
             $reportTitle = "Laporan Barang Keluar - " . $store_name_report;
             $final_date_condition_keluar = str_replace('{alias}', 'k', $date_condition); // Ganti alias untuk query keluar

              if ($store == 'semua') {
                 // **Perbaikan nama tabel pusatstock & pusatkeluar**
                 $sql = "(SELECT k.idkeluar, k.tanggal, s.namabarang, k.qty, k.penerima, 'Pusat' as toko FROM pusatkeluar k JOIN pusatstock s ON k.idbarang = s.idbarang WHERE 1=1 {$final_date_condition_keluar})
                         UNION ALL
                         (SELECT k.idkeluar, k.tanggal, s.namabarang, k.qty, k.penerima, 'Parahita' as toko FROM parahita_keluar k JOIN parahita_stock s ON k.idbarang = s.idbarang WHERE 1=1 {$final_date_condition_keluar})
                         UNION ALL
                         (SELECT k.idkeluar, k.tanggal, s.namabarang, k.qty, k.penerima, 'Serpong' as toko FROM serpong_keluar k JOIN serpong_stock s ON k.idbarang = s.idbarang WHERE 1=1 {$final_date_condition_keluar})
                         ORDER BY tanggal DESC, toko";
                 // Duplikasi parameter tanggal untuk setiap subquery UNION
                 if ($base_date_param_count > 0) {
                     $original_params = $params;
                     $params = array_merge($original_params, $original_params, $original_params);
                 }
             } else {
                 // **Perbaikan nama tabel jika $store == 'pusat'**
                 $keluar_table_name = ($store == 'pusat') ? 'pusatkeluar' : $store . '_keluar';
                 $stock_table_name = ($store == 'pusat') ? 'pusatstock' : $store . '_stock';
                 $sql = "SELECT k.idkeluar, k.tanggal, s.namabarang, k.qty, k.penerima
                        FROM {$keluar_table_name} k
                        JOIN {$stock_table_name} s ON k.idbarang = s.idbarang
                        WHERE 1=1 {$final_date_condition_keluar}
                        ORDER BY k.tanggal DESC";
             }
            break;

        case 'permintaan':
            $reportTitle = "Laporan Permintaan Barang (PP)";
            $final_date_condition_pp = str_replace('{alias}', 'pp', $date_condition); // Ganti alias untuk query PP
            // PP hanya ada 1 tabel, tidak perlu filter toko, tapi bisa filter tanggal
            // **Perbaikan nama tabel pusatstock**
            $sql = "SELECT pp.idpp, pp.nopp, pp.tanggal, ps.namabarang, pp.qty, pp.penerima, pp.keterangan, pp.status
                    FROM pp
                    JOIN pusatstock ps ON pp.idbarang = ps.idbarang
                    WHERE 1=1 {$final_date_condition_pp}
                    ORDER BY pp.tanggal DESC";
            break;

        default:
            $error_msg = "Jenis laporan tidak valid.";
            break;
    }

    if (empty($error_msg) && !empty($sql)) {
        $queryResult = fetch_report_data($conn, $sql, $params);
        if (isset($queryResult['error'])) {
            $error_msg = $queryResult['error'];
        } else {
            $results = $queryResult['data'];
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Laporan - Owner</title>
        <link href="../css/styles.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <link href="https://cdn.datatables.net/buttons/1.6.5/css/buttons.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
        <style>
            #reportTable th, #reportTable td {
                font-size: 0.9rem;
            }
             /* Pastikan tombol export terlihat */
            .dt-buttons { float: left; margin-bottom: 1em; }
            .dataTables_filter { float: right; }
             /* Clearfix untuk header card agar tombol export tidak tumpang tindih */
            .card-header::after {
                content: "";
                display: table;
                clear: both;
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
        </nav>
        <div id="layoutSidenav">
            <?php require '_sidebar_owner.php'; // Include sidebar owner ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Laporan</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Laporan</li>
                        </ol>

                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-filter"></i> Filter Laporan
                            </div>
                            <div class="card-body">
                                <form method="GET" action="">
                                    <div class="row">
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="form-group">
                                                <label for="reportType">Jenis Laporan:</label>
                                                <select id="reportType" name="reportType" class="form-control">
                                                    <option value="stok" <?= ($reportType == 'stok') ? 'selected' : ''; ?>>Laporan Stok</option>
                                                    <option value="masuk" <?= ($reportType == 'masuk') ? 'selected' : ''; ?>>Laporan Barang Masuk</option>
                                                    <option value="keluar" <?= ($reportType == 'keluar') ? 'selected' : ''; ?>>Laporan Barang Keluar</option>
                                                    <option value="permintaan" <?= ($reportType == 'permintaan') ? 'selected' : ''; ?>>Laporan Permintaan (PP)</option>
                                                </select>
                                            </div>
                                        </div>
                                         <div class="col-lg-3 col-md-6 mb-3">
                                             <div class="form-group">
                                                <label for="storeSelect">Toko:</label>
                                                <select id="storeSelect" name="store" class="form-control">
                                                    <option value="semua" <?= ($store == 'semua') ? 'selected' : ''; ?>>Semua Toko</option>
                                                    <option value="pusat" <?= ($store == 'pusat') ? 'selected' : ''; ?>>Toko 1 Cisauk (Pusat)</option>
                                                    <option value="parahita" <?= ($store == 'parahita') ? 'selected' : ''; ?>>Toko 2 Parahita</option>
                                                    <option value="serpong" <?= ($store == 'serpong') ? 'selected' : ''; ?>>Toko 3 Serpong</option>
                                                    <?php
                                                        // Tambahkan adminBaru secara dinamis
                                                        $query_admin_baru_users_filter = mysqli_query($conn, "SELECT username FROM login WHERE roles = 'admin' AND username NOT IN ('adminPusat', 'adminParahita', 'adminSerpong') ORDER BY username ASC");
                                                        if ($query_admin_baru_users_filter && mysqli_num_rows($query_admin_baru_users_filter) > 0) {
                                                            
                                                            while ($admin_branch = mysqli_fetch_assoc($query_admin_baru_users_filter)) {
                                                                $branch_username = htmlspecialchars($admin_branch['username']);
                                                                $selected_branch = ($store == $branch_username) ? 'selected' : '';
                                                                // Untuk value, kita gunakan username yang sama, karena tabelnya dinamai berdasarkan username
                                                                echo "<option value=\"{$branch_username}\" {$selected_branch}>Toko {$branch_username}</option>";
                                                            }
                                                            echo '</optgroup>';
                                                        }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="form-group">
                                                <label for="tanggalMulai">Tanggal Mulai:</label>
                                                <input type="date" id="tanggalMulai" name="tanggalMulai" class="form-control" value="<?= htmlspecialchars($tanggalMulai ?? ''); ?>">
                                            </div>
                                        </div>
                                         <div class="col-lg-3 col-md-6 mb-3">
                                            <div class="form-group">
                                                <label for="tanggalAkhir">Tanggal Akhir:</label>
                                                <input type="date" id="tanggalAkhir" name="tanggalAkhir" class="form-control" value="<?= htmlspecialchars($tanggalAkhir ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                             <button type="submit" class="btn btn-primary float-right">
                                                <i class="fas fa-eye"></i> Tampilkan Laporan
                                             </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <?php if ($reportType && empty($error_msg)): ?>
                        <div class="card mb-4">
                             <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                <?= htmlspecialchars($reportTitle); ?>
                                <?php if (!empty($tanggalMulai) || !empty($tanggalAkhir)) echo " (Periode: ";
                                      if (!empty($tanggalMulai)) echo htmlspecialchars(date("d-m-Y", strtotime($tanggalMulai)));
                                      if (!empty($tanggalMulai) && !empty($tanggalAkhir)) echo " s/d ";
                                      if (!empty($tanggalAkhir)) echo htmlspecialchars(date("d-m-Y", strtotime($tanggalAkhir)));
                                      if (!empty($tanggalMulai) || !empty($tanggalAkhir)) echo ")";
                                ?>
                             </div>
                             <div class="card-body">
                                 <?php if (!empty($results)): ?>
                                     <div class="table-responsive">
                                         <table class="table table-bordered" id="reportTable" width="100%" cellspacing="0">
                                             <thead>
                                                 <tr>
                                                     <?php
                                                     // Generate header dinamis berdasarkan jenis laporan
                                                     if (!empty($results)) {
                                                        $headers = array_keys($results[0]);
                                                        echo "<th>No</th>"; // Tambah kolom No
                                                        foreach ($headers as $header) {
                                                            // Ganti nama kolom internal menjadi lebih user-friendly
                                                            $displayHeader = $header;
                                                            switch(strtolower($header)) {
                                                                // case 'idbarang': $displayHeader = 'ID Barang'; break; // Seringkali tidak perlu ditampilkan
                                                                case 'namabarang': $displayHeader = 'Nama Barang'; break;
                                                                case 'deskripsi': $displayHeader = 'Deskripsi'; break;
                                                                case 'stock': $displayHeader = 'Stok'; break;
                                                                case 'toko': $displayHeader = 'Asal Toko'; break;
                                                                case 'idmasuk': case 'idkeluar': case 'idpp': case 'idbarang': continue 2; // Skip ID internal
                                                                case 'tanggal': $displayHeader = 'Tanggal'; break;
                                                                case 'qty': $displayHeader = 'Jumlah'; break;
                                                                case 'keterangan': $displayHeader = 'Keterangan'; break;
                                                                case 'penerima': $displayHeader = 'Penerima/Tujuan'; break;
                                                                case 'nopp': $displayHeader = 'No. PP'; break;
                                                                case 'status': $displayHeader = 'Status'; break;
                                                            }
                                                            echo "<th>" . htmlspecialchars(ucwords(str_replace('_', ' ', $displayHeader))) . "</th>";
                                                        }
                                                     } else {
                                                        echo "<th>Info</th>"; // Kolom default jika tidak ada data
                                                     }
                                                     ?>
                                                 </tr>
                                             </thead>
                                             <tbody>
                                                 <?php $i = 1; foreach ($results as $row): ?>
                                                 <tr>
                                                     <td><?= $i++; ?></td>
                                                     <?php
                                                        foreach ($headers as $header) {
                                                            // Skip ID internal di body juga
                                                            if (in_array(strtolower($header), ['idmasuk', 'idkeluar', 'idpp', 'idbarang'])) continue;

                                                            $cellValue = $row[$header];
                                                            if (strtolower($header) == 'tanggal') {
                                                                $cellValue = htmlspecialchars(date("d-m-Y H:i:s", strtotime($cellValue)));
                                                            } elseif (strtolower($header) == 'status') {
                                                                if($cellValue == "PENDING" ) $cellValue = '<span class="badge badge-warning">PENDING</span>';
                                                                elseif ($cellValue == "DITERIMA" ) $cellValue = '<span class="badge badge-success">DITERIMA</span>';
                                                                else $cellValue = '<span class="badge badge-secondary">'.htmlspecialchars(strtoupper($cellValue)).'</span>';
                                                                echo "<td>" . $cellValue . "</td>"; // Langsung echo karena sudah ada tag HTML
                                                                continue;
                                                            } else {
                                                                 $cellValue = htmlspecialchars($cellValue);
                                                            }
                                                            echo "<td>" . $cellValue . "</td>";
                                                        }
                                                     ?>
                                                 </tr>
                                                 <?php endforeach; ?>
                                             </tbody>
                                         </table>
                                     </div>
                                 <?php elseif(isset($_GET['reportType'])): // Hanya tampilkan pesan jika form sudah disubmit tapi hasil kosong ?>
                                     <div class="alert alert-info">Tidak ada data yang ditemukan untuk filter yang dipilih.</div>
                                 <?php endif; ?>
                             </div>
                        </div>
                        <?php elseif (!empty($error_msg)): ?>
                         <div class="alert alert-danger"><?= htmlspecialchars($error_msg); ?></div>
                        <?php endif; ?>

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
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.5/js/dataTables.buttons.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.bootstrap4.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.html5.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/buttons/1.6.5/js/buttons.print.min.js" crossorigin="anonymous"></script>

        <script>
            $(document).ready(function() {
                var table = $('#reportTable').DataTable( {
                     lengthChange: false,
                     buttons: [
                        { extend: 'copy', className: 'btn btn-secondary btn-sm', text: '<i class="fas fa-copy"></i> Copy' },
                        { extend: 'csv', className: 'btn btn-secondary btn-sm', text: '<i class="fas fa-file-csv"></i> CSV' },
                        { extend: 'excel', className: 'btn btn-secondary btn-sm', text: '<i class="fas fa-file-excel"></i> Excel' },
                        { extend: 'pdf', className: 'btn btn-secondary btn-sm', text: '<i class="fas fa-file-pdf"></i> PDF' },
                        { extend: 'print', className: 'btn btn-secondary btn-sm', text: '<i class="fas fa-print"></i> Print' }
                    ]
                } );

                table.buttons().container().appendTo( '#reportTable_wrapper .col-md-6:eq(0)' );


                function toggleDateAndStoreInputs() {
                    var reportTypeVal = $('#reportType').val();
                    var storeVal = $('#storeSelect').val();
                    var disableDatesForStock = (reportTypeVal === 'stok');

                    $('#tanggalMulai').prop('disabled', disableDatesForStock);
                    $('#tanggalAkhir').prop('disabled', disableDatesForStock);

                    if (disableDatesForStock) {
                        $('#tanggalMulai').val('');
                        $('#tanggalAkhir').val('');
                    }

                    if (reportTypeVal === 'permintaan') {
                        $('#storeSelect').prop('disabled', true).val('semua').trigger('change'); // PP selalu semua toko
                        // Pastikan tanggal tetap bisa diisi untuk PP
                        $('#tanggalMulai').prop('disabled', false);
                        $('#tanggalAkhir').prop('disabled', false);
                    } else {
                         $('#storeSelect').prop('disabled', false);
                    }
                }

                $('#reportType, #storeSelect').on('change', toggleDateAndStoreInputs);
                toggleDateAndStoreInputs();

            });
        </script>
    </body>
</html>