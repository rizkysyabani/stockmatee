<?php
require "../function.php";
require "../cek.php";

// USER IDENTIFICATION AND ROLE CHECK
$username_session = $_SESSION['username'] ?? '';
$role_session = $_SESSION['roles'] ?? '';

$specific_admin_usernames = ['adminPusat', 'adminParahita', 'adminSerpong', 'owner'];
if ($role_session != 'admin' || in_array($username_session, $specific_admin_usernames) || empty($username_session)) {
    header('location: ../index.php');
    exit;
}

$identifier_display = htmlspecialchars($username_session);
// For PP, the 'penerima' field should be the username of this admin
$store_identifier_for_pp = $username_session;

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Permintaan Barang - Toko <?= $identifier_display; ?></title>
        <link href="../css/styles.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
        <style>
            .badge-warning { color: #212529 !important; }
            td { vertical-align: middle !important; }
            .table-responsive { overflow-x: visible; }
            .action-buttons .btn {
                margin-right: 5px;
                margin-bottom: 5px; /* Add some bottom margin for better spacing on small screens */
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
            <?php require '_sidebar_admin_baru.php'; ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Permintaan Barang Keluar dari Gudang Pusat</h1>
                         <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard <?= $identifier_display; ?></a></li>
                            <li class="breadcrumb-item active">Permintaan Barang (PP)</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                               <i class="fas fa-file-invoice me-1"></i>
                                Buat Permintaan Barang ke Gudang Pusat
                              <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#modalBuatPermintaanDynamic">
                                <i class="fas fa-plus"></i> Request Barang
                              </button>
                            </div>
                            <div class="card-body">
                             <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>No PP</th>
                                        <th>Nama Barang (Dari Pusat)</th>
                                        <th>Jumlah</th>
                                        <th>Keterangan</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                       <?php
                                        $ambilsemuadata_pp = mysqli_query($conn, "SELECT pp.*, ps.namabarang AS namabarang_pusat, ps.stock AS stock_pusat
                                                                                    FROM pp
                                                                                    JOIN pusatstock ps ON pp.idbarang = ps.idbarang
                                                                                    WHERE pp.penerima = '".mysqli_real_escape_string($conn, $store_identifier_for_pp)."'
                                                                                    ORDER BY pp.tanggal DESC");
                                        if(!$ambilsemuadata_pp) {
                                            echo "<tr><td colspan='7' class='text-center text-danger'>Error query: ".mysqli_error($conn)."</td></tr>";
                                        } else {
                                            if (mysqli_num_rows($ambilsemuadata_pp) > 0) {
                                                while($data=mysqli_fetch_array($ambilsemuadata_pp)){
                                                    $idpp = $data['idpp'];
                                                    $idbarang_diminta = $data['idbarang'];
                                                    $tanggal = $data['tanggal'];
                                                    $namabarang = htmlspecialchars($data['namabarang_pusat']);
                                                    $qty = intval($data['qty']);
                                                    $nopp_display = htmlspecialchars($data['nopp']);
                                                    $status = $data['status'];
                                                    $keterangan_display = htmlspecialchars($data['keterangan']);
                                        ?>
                                        <tr>
                                            <td><?=$tanggal;?></td>
                                            <td><?=$nopp_display ? $nopp_display : '-';?></td>
                                            <td><?=$namabarang;?></td>
                                            <td><?=$qty;?></td>
                                            <td><?=$keterangan_display ? $keterangan_display : '-';?></td>
                                            <td>
                                                <?php if($status == "PENDING" ): ?> <span class="badge badge-warning">PENDING</span>
                                                <?php elseif ($status == "DITERIMA" ): ?> <span class="badge badge-success">DITERIMA</span>
                                                <?php else: ?> <span class="badge badge-secondary"><?=htmlspecialchars(strtoupper($status));?></span>
                                                <?php endif; ?>
                                            </td>
                                             <td class="action-buttons">
                                                  <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#detailPPDynamic<?=$idpp;?>" title="Lihat Detail">
                                                       <i class="fas fa-eye"></i>
                                                  </button>
                                                <?php if($status == "PENDING"): ?>
                                                    <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editPPDynamic<?=$idpp;?>" title="Edit Permintaan">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#hapusPPDynamic<?=$idpp;?>" title="Hapus Permintaan">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                              </td>
                                        </tr>

                                        <div class="modal fade" id="detailPPDynamic<?=$idpp;?>">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header"><h4 class="modal-title">Detail Permintaan (PP)</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                                    <div class="modal-body">
                                                        <dl class="row">
                                                            <dt class="col-sm-3">No. PP</dt><dd class="col-sm-9">: <?= $nopp_display ? $nopp_display : '-'; ?></dd>
                                                            <dt class="col-sm-3">Tanggal</dt><dd class="col-sm-9">: <?= $tanggal; ?></dd>
                                                            <dt class="col-sm-3">Nama Barang</dt><dd class="col-sm-9">: <?= $namabarang; ?></dd>
                                                            <dt class="col-sm-3">Jumlah</dt><dd class="col-sm-9">: <?= $qty; ?></dd>
                                                            <dt class="col-sm-3">Penerima</dt><dd class="col-sm-9">: <?= htmlspecialchars($data['penerima']); ?></dd>
                                                            <dt class="col-sm-3">Keterangan</dt><dd class="col-sm-9">: <?= $keterangan_display ? $keterangan_display : '-'; ?></dd>
                                                            <dt class="col-sm-3">Status</dt>
                                                            <dd class="col-sm-9">:
                                                                <?php if($status == "PENDING" ): ?> <span class="badge badge-warning">PENDING</span>
                                                                <?php elseif ($status == "DITERIMA" ): ?> <span class="badge badge-success">DITERIMA</span>
                                                                <?php else: ?> <span class="badge badge-secondary"><?=htmlspecialchars(strtoupper($status));?></span> <?php endif; ?>
                                                            </dd>
                                                        </dl>
                                                    </div>
                                                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button></div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php if($status == "PENDING"): ?>
                                        <div class="modal fade" id="editPPDynamic<?=$idpp;?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header"><h4 class="modal-title">Edit Permintaan Barang</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                                    <form method="post" action="../function.php">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="idpp" value="<?=$idpp;?>">
                                                            <div class="form-group">
                                                                <label for="nopp_edit_dynamic<?=$idpp;?>">No. PP (Opsional):</label>
                                                                <input type="text" id="nopp_edit_dynamic<?=$idpp;?>" name="nopp" value="<?=htmlspecialchars($data['nopp']);?>" placeholder="Nomor Permintaan Pembelian" class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="barangnya_edit_dynamic<?=$idpp;?>">Pilih Barang (Stok Pusat):</label>
                                                                <select id="barangnya_edit_dynamic<?=$idpp;?>" name="barangnya" class="form-control" required>
                                                                    <option value="">-- Pilih Barang --</option>
                                                                    <?php
                                                                    $ambilbarapusat_edit_dynamic = mysqli_query($conn,"select * from pusatstock order by namabarang asc");
                                                                    while($fetchbar_edit_dynamic = mysqli_fetch_array($ambilbarapusat_edit_dynamic)){
                                                                        $idbarangnya_edit_dynamic = $fetchbar_edit_dynamic['idbarang'];
                                                                        $namabarangnya_edit_dynamic = htmlspecialchars($fetchbar_edit_dynamic['namabarang']);
                                                                        $stockbarangnya_edit_dynamic = htmlspecialchars($fetchbar_edit_dynamic['stock']);
                                                                        $selected_edit_dynamic = ($idbarangnya_edit_dynamic == $idbarang_diminta) ? 'selected' : '';
                                                                    ?>
                                                                    <option value="<?=$idbarangnya_edit_dynamic;?>" <?=$selected_edit_dynamic;?>><?=$namabarangnya_edit_dynamic;?> (Stok Pusat: <?=$stockbarangnya_edit_dynamic;?>)</option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="qty_edit_dynamic<?=$idpp;?>">Quantity:</label>
                                                                <input type="number" id="qty_edit_dynamic<?=$idpp;?>" name="qty" value="<?=$qty;?>" class="form-control" placeholder="Jumlah diminta" required min="1">
                                                            </div>
                                                            <input type="hidden" name="penerima" value="<?= htmlspecialchars($store_identifier_for_pp); ?>">
                                                            <div class="form-group">
                                                                <label for="keterangan_edit_dynamic<?=$idpp;?>">Keterangan (Opsional):</label>
                                                                <textarea id="keterangan_edit_dynamic<?=$idpp;?>" name="keterangan" class="form-control" rows="3" placeholder="Keterangan tambahan jika ada"><?=htmlspecialchars($data['keterangan']);?></textarea>
                                                            </div>
                                                            <button type="submit" class="btn btn-primary" name="updatepermintaanpp_cabang">Update Permintaan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="hapusPPDynamic<?=$idpp;?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header"><h4 class="modal-title">Hapus Permintaan Barang?</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                                    <form method="post" action="../function.php">
                                                        <div class="modal-body">
                                                            Apakah Anda yakin ingin menghapus permintaan untuk <strong><?=$namabarang;?></strong> (No. PP: <?=$nopp_display ? $nopp_display : '-';?>)?
                                                            <input type="hidden" name="idpp" value="<?=$idpp;?>">
                                                            <input type="hidden" name="penerima_check" value="<?= htmlspecialchars($store_identifier_for_pp); ?>">
                                                            <br><br>
                                                            <button type="submit" class="btn btn-danger" name="hapuspermintaanpp_cabang">Ya, Hapus</button>
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endif; // End if status PENDING for Edit/Delete Modals ?>
                                        <?php
                                                } // end while
                                            } else {
                                                echo "<tr><td colspan='7' class='text-center'>Belum ada permintaan barang dari toko Anda.</td></tr>";
                                            }
                                        } // End Else !$ambilsemuadata_pp
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

        <div class="modal fade" id="modalBuatPermintaanDynamic">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Request Barang dari Gudang Pusat</h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="post" action="../function.php">
                    <div class="modal-body">
                    <div class="form-group">
                        <label for="nopp_buat_dynamic">No. PP (Opsional):</label>
                        <input type="text" id="nopp_buat_dynamic" name="nopp" placeholder="Nomor Permintaan Pembelian" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="barangnya_buat_dynamic">Pilih Barang (Stok Pusat):</label>
                         <select id="barangnya_buat_dynamic" name="barangnya" class="form-control" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php
                            $ambilbarapusat_buat_dynamic = mysqli_query($conn,"select * from pusatstock where stock > 0 order by namabarang asc");
                            while($fetchbar_buat_dynamic = mysqli_fetch_array($ambilbarapusat_buat_dynamic)){
                                $namabarangnya_buat_dynamic = htmlspecialchars($fetchbar_buat_dynamic['namabarang']);
                                $idbarangnya_buat_dynamic = $fetchbar_buat_dynamic['idbarang'];
                                $stockbarang_buat_dynamic = htmlspecialchars($fetchbar_buat_dynamic['stock']);
                            ?>
                            <option value="<?=$idbarangnya_buat_dynamic;?>"><?=$namabarangnya_buat_dynamic;?> (Stok Pusat: <?=$stockbarang_buat_dynamic;?>)</option>
                            <?php } ?>
                         </select>
                    </div>
                    <div class="form-group">
                         <label for="qty_buat_dynamic">Quantity:</label>
                         <input type="number" id="qty_buat_dynamic" name="qty" class="form-control" placeholder="Jumlah diminta" required min="1">
                    </div>
                    <div class="form-group">
                         <label for="penerima_buat_dynamic">Penerima / Tujuan:</label>
                          <input type="text" id="penerima_buat_dynamic" name="penerima" class="form-control" value="<?= htmlspecialchars($store_identifier_for_pp); ?>" readonly required>
                    </div>
                     <div class="form-group">
                        <label for="keterangan_buat_dynamic">Keterangan (Opsional):</label>
                        <textarea id="keterangan_buat_dynamic" name="keterangan" class="form-control" rows="3" placeholder="Keterangan tambahan jika ada"></textarea>
                    </div>
                   <button type="submit" class="btn btn-primary" name="addppbarangkeluar">Ajukan Permintaan</button>
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
                    stateSave: true,
                    "order": [[ 0, "desc" ]]
                });
            });
        </script>
    </body>
</html>