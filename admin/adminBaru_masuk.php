<?php
// File: new/admin/adminBaru_masuk.php
// Halaman ini diakses oleh Admin Pusat untuk MELIHAT dan SEKARANG JUGA MENGINPUT riwayat barang masuk Toko Pengguna Baru

require "../function.php";
require "../cek.php";

if (!isset($_SESSION['roles']) || !in_array($_SESSION['roles'], ['adminPusat', 'owner'])) {
    header('location: ../index.php');
    exit;
}

$view_user_username = $_GET['user'] ?? null;

if (empty($view_user_username)) {
    // Menggunakan pesan error yang sudah ada di session jika ada, atau set default
    if (!isset($_SESSION['error_message'])) {
         $_SESSION['error_message'] = "Nama pengguna cabang tidak ditentukan.";
    }
    // Menambahkan alert jika ada pesan error dari operasi sebelumnya
    if (isset($_SESSION['admin_action_message']) && isset($_SESSION['admin_action_type'])) {
        // Ini bisa ditampilkan di bawah breadcrumb atau di atas tabel
    }
    header('location: index.php'); // Redirect ke dashboard admin pusat jika tidak ada user
    exit;
}

$view_user_display = htmlspecialchars($view_user_username);
$view_store_name_display = "Toko " . $view_user_display;
// Prefix tabel untuk toko yang sedang dilihat (digunakan untuk menampilkan data masuk toko tersebut)
$view_store_prefix = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $view_user_username));

if (empty($view_store_prefix)) {
    $_SESSION['error_message'] = "Format nama pengguna cabang tidak valid.";
    header('location: index.php');
    exit;
}

// Cek apakah tabel untuk user ini memang ada (untuk menampilkan data)
$check_table_masuk_sql = "SHOW TABLES LIKE '{$view_store_prefix}_masuk'";
$check_table_masuk_result = mysqli_query($conn, $check_table_masuk_sql);
$check_table_stock_sql = "SHOW TABLES LIKE '{$view_store_prefix}_stock'";
$check_table_stock_result = mysqli_query($conn, $check_table_stock_sql);

if ((!$check_table_masuk_result || mysqli_num_rows($check_table_masuk_result) == 0) ||
    (!$check_table_stock_result || mysqli_num_rows($check_table_stock_result) == 0)) {
    // Jika tabel tidak ada, Admin Pusat tidak bisa input barang masuk ke toko yang belum terdefinisi strukturnya
    // Atau belum ada barang sama sekali di tokonya sehingga tabel _masuk belum ada.
    // Kita bisa mengizinkan input jika tabel _stock ada, karena tabel _masuk akan dibuat jika belum ada oleh function.php
     if (mysqli_num_rows($check_table_stock_result) == 0) {
        $_SESSION['error_message'] = "Tabel stok untuk toko '{$view_user_display}' tidak ditemukan. Tidak dapat melanjutkan.";
        header('location: adminBaru_index.php?user='.urlencode($view_user_username)); // Kembali ke halaman stoknya
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Lihat & Input Barang Masuk - <?= htmlspecialchars($view_store_name_display); ?></title>
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
                        <h1 class="mt-4">Riwayat & Input Barang Masuk <?= htmlspecialchars($view_store_name_display); ?></h1>
                         <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard Admin Pusat</a></li>
                            <li class="breadcrumb-item"><a href="adminBaru_index.php?user=<?= urlencode($view_user_username); ?>">Lihat Stock <?= $view_user_display; ?></a></li>
                            <li class="breadcrumb-item active">Riwayat & Input Barang Masuk</li>
                        </ol>
                        <?php
                        if (isset($_SESSION['admin_action_message'])) {
                            $alert_type = $_SESSION['admin_action_type'] ?? 'info';
                            echo '<div class="alert alert-' . htmlspecialchars($alert_type) . ' alert-dismissible fade show" role="alert">';
                            echo htmlspecialchars($_SESSION['admin_action_message']);
                            echo '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
                            echo '</div>';
                            unset($_SESSION['admin_action_message']);
                            unset($_SESSION['admin_action_type']);
                        }
                        ?>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Data Barang Masuk <?= $view_user_display; ?>
                                <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#modalAdminBaruMasuk">
                                 <i class="fas fa-plus"></i> Tambah Barang Masuk ke <?= htmlspecialchars($view_store_name_display); ?>
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
                                        // Query ke tabel dinamis milik user yang sedang dilihat
                                        $sql_masuk = "SELECT m.*, s.namabarang, s.image 
                                                      FROM `{$view_store_prefix}_masuk` m 
                                                      JOIN `{$view_store_prefix}_stock` s ON m.idbarang = s.idbarang 
                                                      ORDER BY m.tanggal DESC";
                                        $ambilsemuadatastock = mysqli_query($conn, $sql_masuk);
                                        
                                        if ($ambilsemuadatastock) {
                                            if(mysqli_num_rows($ambilsemuadatastock) > 0) {
                                                while($data=mysqli_fetch_array($ambilsemuadatastock)){
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
                                            </tr>
                                            <?php
                                                } 
                                            } else {
                                                echo "<tr><td colspan='5' class='text-center'>Belum ada data barang masuk untuk toko ini.</td></tr>";
                                            }
                                        } else {
                                             echo "<tr><td colspan='5' class='text-center'>Gagal mengambil data barang masuk {$view_user_display}: " . mysqli_error($conn) . "</td></tr>";
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

        <div class="modal fade" id="modalAdminBaruMasuk">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Tambah Barang Masuk ke <?= htmlspecialchars($view_store_name_display); ?></h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                 <form method="post" action="../function.php">
                    <div class="modal-body">
                         <input type="hidden" name="initiator" value="admin_pusat_transfer_to_dynamic_branch">
                         <input type="hidden" name="target_branch_username" value="<?= htmlspecialchars($view_user_username); ?>">
                         
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
                              <label>Quantity Masuk ke <?= htmlspecialchars($view_store_name_display); ?>:</label>
                              <input type="number" name="qty" class="form-control" placeholder="Jumlah masuk" required min="1">
                         </div>
                         <div class="form-group">
                               <label>Keterangan/Pengirim:</label>
                               <input type="text" name="penerima" class="form-control" placeholder="Keterangan / Pengirim" required>
                         </div>
                       <button type="submit" class="btn btn-primary" name="barangmasuk_transfer_dynamic_branch">Submit Transfer</button>
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
        <script> $(document).ready(function() { $('#dataTable').DataTable({ stateSave: true }); }); </script>
    </body>
</html>