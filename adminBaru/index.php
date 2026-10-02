<?php
require "../function.php"; // Path relatif ke function.php
require "../cek.php";    // Path relatif ke cek.php

// USER IDENTIFICATION AND ROLE CHECK
$username_session = $_SESSION['username'] ?? '';
$role_session = $_SESSION['roles'] ?? '';

// User should be logged in and have the 'admin' role.
// Additionally, this section is for generic admins, not specific ones like adminPusat, adminParahita, etc.
$specific_admin_usernames = ['adminPusat', 'adminParahita', 'adminSerpong', 'owner']; // Usernames with dedicated UIs
if ($role_session != 'admin' || in_array($username_session, $specific_admin_usernames) || empty($username_session)) {
    // Redirect if not a generic admin or session is invalid
    header('location: ../index.php');
    exit;
}

$username_display = htmlspecialchars($username_session);
$store_name_display = "Toko " . $username_display;
// Dynamic table prefix based on the logged-in username
$store_prefix = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $username_session));

if (empty($store_prefix)) {
    // Handle cases where username might result in an empty prefix (though unlikely with proper validation)
    // error_log("Invalid store prefix for username: " . $username_session);
    // For now, redirect or show error, as table operations will fail
    echo "Error: Konfigurasi toko tidak valid. Silakan hubungi administrator.";
    exit;
}


$can_manage_stock = true; // Assumed true if they passed the role check above for this page.

// Ambil data dari pusatstock untuk dropdown
$query_pusat_stock = mysqli_query($conn, "SELECT idbarang, namabarang, stock FROM pusatstock WHERE stock > 0 ORDER BY namabarang ASC");
$pusat_stock_items = [];
if ($query_pusat_stock) {
    while ($item_pusat = mysqli_fetch_assoc($query_pusat_stock)) {
        $pusat_stock_items[] = $item_pusat;
    }
} else {
    // error_log("Gagal mengambil data pusatstock: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Stock Barang - <?= htmlspecialchars($store_name_display); ?></title>
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
            <?php require '_sidebar_admin_baru.php'; // Sidebar untuk adminBaru ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4" >
                        <h1 class="mt-4">Stock Barang <?= htmlspecialchars($store_name_display); ?></h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard <?= $username_display; ?></a></li>
                            <li class="breadcrumb-item active">Stock Barang</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                              <?php if ($can_manage_stock): ?>
                              <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">
                                <i class="fas fa-plus"></i> Tambah Barang
                              </button>
                              <a href="export.php" class="btn btn-info"> <i class="fas fa-file-export"></i> Export Data</a>
                              <?php endif; ?>
                            </div>
                            <div class="card-body">
                            <?php
                            // Alert stok habis (dari tabel dinamis)
                            $ambildatastock_alert = mysqli_query($conn, "SELECT * FROM `{$store_prefix}_stock` WHERE stock < 1");
                            if ($ambildatastock_alert) {
                                while($fetch_alert = mysqli_fetch_array($ambildatastock_alert)){
                                    $barang_alert = htmlspecialchars($fetch_alert['namabarang']);
                            ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <strong>Perhatian!</strong> Stock <?= $barang_alert; ?> Telah Habis
                            </div>
                            <?php
                                } // end while alert
                            } // end if ambildatastock_alert
                            ?>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>Gambar</th>
                                                <th>Nama Barang</th>
                                                <th>Deskripsi</th>
                                                <th>Stock</th>
                                                <?php if ($can_manage_stock): ?>
                                                <th>Aksi</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                          <?php
                                          $ambilsemuadatastock = mysqli_query($conn, "SELECT * FROM `{$store_prefix}_stock` ORDER BY namabarang ASC");
                                          if ($ambilsemuadatastock) {
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
                                              <?php if ($can_manage_stock): ?>
                                              <td>
                                                  <button type="button" class="btn btn-warning btn-sm mb-1" data-toggle="modal" data-target="#edit<?=$idb;?>">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                  <button type="button" class="btn btn-danger btn-sm mb-1" data-toggle="modal" data-target="#delete<?=$idb;?>">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                              </td>
                                              <?php endif; ?>
                                          </tr>
                                            <?php if ($can_manage_stock): ?>
                                            <div class="modal fade" id="edit<?=$idb;?>">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">Edit Barang <?= htmlspecialchars($store_name_display); ?></h4>
                                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                        </div>
                                                        <form method="post" action="../function.php" enctype="multipart/form-data">
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label for="namaEdit_dynamic<?=$idb;?>">Nama Barang:</label>
                                                                    <input type="text" id="namaEdit_dynamic<?=$idb;?>" name="namabarang" value="<?=$namabarang;?>" class="form-control" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="descEdit_dynamic<?=$idb;?>">Deskripsi:</label>
                                                                    <input type="text" id="descEdit_dynamic<?=$idb;?>" name="deskripsi" value="<?=$deskripsi;?>" class="form-control" required>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>Gambar Saat Ini:</label><br>
                                                                    <?php
                                                                      if($gambar==null || !file_exists("../images/".$gambar)){
                                                                        echo '(Tidak ada gambar)';
                                                                      } else {
                                                                        echo '<img src="../images/'.htmlspecialchars($gambar).'" style="max-width: 100px; margin-bottom: 10px;" alt="Gambar saat ini">';
                                                                      }
                                                                    ?>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label for="fileEdit_dynamic<?=$idb;?>">Ganti Gambar (Opsional):</label>
                                                                    <input type="file" id="fileEdit_dynamic<?=$idb;?>" name="file" class="form-control-file">
                                                                    <small class="form-text text-muted">Kosongkan jika tidak ingin mengganti gambar.</small>
                                                                </div>
                                                                <input type="hidden" name="idb" value="<?=$idb;?>">
                                                                <button type="submit" class="btn btn-primary" name="updatebarang_dynamic_branch">Update</button>
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
                                                        <form method="post" action="../function.php">
                                                            <div class="modal-body">
                                                                Apakah anda yakin ingin menghapus <strong><?=$namabarang;?></strong>?
                                                                <input type="hidden" name="idb" value="<?=$idb;?>">
                                                                <br><br>
                                                                 <button type="submit" class="btn btn-danger" name="hapusbarang_dynamic_branch">Hapus</button>
                                                                 <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; // end if $can_manage_stock for modals ?>
                                          <?php 
                                              } // end while data barang
                                          } else {
                                              echo "<tr><td colspan='".($can_manage_stock ? 6 : 5)."' class='text-center'>Gagal mengambil data stok atau belum ada barang: " . mysqli_error($conn) . "</td></tr>";
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
        
        <?php if ($can_manage_stock): ?>
        <div class="modal fade" id="myModal">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Tambah Barang <?= htmlspecialchars($store_name_display); ?></h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="post" action="../function.php" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="idbarang_pusat_dynamic">Pilih Barang dari Stock Pusat (Opsional)</label>
                            <select name="idbarang_pusat" id="idbarang_pusat_dynamic" class="form-control">
                                <option value="">-- Tambah Barang Baru Manual atau Pilih dari Pusat --</option>
                                <?php if (!empty($pusat_stock_items)): ?>
                                    <?php foreach ($pusat_stock_items as $item_pusat): ?>
                                        <option value="<?= htmlspecialchars($item_pusat['idbarang']); ?>">
                                            <?= htmlspecialchars($item_pusat['namabarang']); ?> (Stok Pusat: <?= htmlspecialchars($item_pusat['stock']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="" disabled>Tidak ada barang di stok pusat</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <hr>
                        
                        <div class="form-group">
                            <label for="stock_dynamic">Stock Awal untuk Toko Anda:</label>
                            <input type="number" id="stock_dynamic" name="stock" class="form-control" placeholder="Stock Awal" required min="0">
                        </div>
                        <div class="form-group">
                            <label for="file_dynamic">Gambar Barang (Opsional):</label>
                            <input type="file" id="file_dynamic" name="file" class="form-control-file">
                            <small class="form-text text-muted">Ukuran maks 15MB (jpg, png, jpeg). Jika memilih dari stok pusat, gambar akan disalin jika tidak ada upload baru.</small>
                        </div>
                        <br>
                       <button type="submit" class="btn btn-primary" name="addnewbarang_dynamic_branch">Submit</button>
                    </div>
                </form>
              </div>
            </div>
        </div>
        <?php endif; // end if $can_manage_stock for modal tambah ?>

        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.datatables.net/1.10.20/js/dataTables.bootstrap4.min.js" crossorigin="anonymous"></script>
        <script>
            $(document).ready(function() {
                $('#dataTable').DataTable({
                    "order": [[2, "asc"]] 
                });

                var selectPusatDynamic = document.getElementById('idbarang_pusat_dynamic');
                var namaManualDynamic = document.getElementById('namabarang_manual_dynamic');
                var deskManualDynamic = document.getElementById('deskripsi_manual_dynamic');

                function toggleManualFieldsDynamic() {
                    if (!selectPusatDynamic || !namaManualDynamic || !deskManualDynamic) return;

                    if (selectPusatDynamic.value !== "") { // Jika barang dari pusat dipilih
                        namaManualDynamic.value = '';       // Kosongkan manual
                        deskManualDynamic.value = '';
                        namaManualDynamic.required = false; // Tidak wajib diisi
                        deskManualDynamic.required = false;
                        // namaManualDynamic.disabled = true; // Bisa juga di-disable
                        // deskManualDynamic.disabled = true;
                    } else { // Jika barang manual
                        namaManualDynamic.required = true;  // Wajib diisi
                        deskManualDynamic.required = true;
                        // namaManualDynamic.disabled = false;
                        // deskManualDynamic.disabled = false;
                    }
                }

                if (selectPusatDynamic) { 
                    selectPusatDynamic.addEventListener('change', toggleManualFieldsDynamic);
                    toggleManualFieldsDynamic(); // Panggil saat load untuk set state awal
                }

                $('#myModal').on('hidden.bs.modal', function () {
                    if (document.getElementById('myModal')) {
                        $(this).find('form')[0].reset();
                        if (selectPusatDynamic) {
                            selectPusatDynamic.value = "";
                        }
                        toggleManualFieldsDynamic();
                    }
                });
            });
        </script>
    </body>
</html>