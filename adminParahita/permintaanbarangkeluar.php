<?php
require "../function.php";
require "../cek.php";

// Pastikan hanya role adminParahita yang bisa akses
if (!isset($_SESSION['roles']) || $_SESSION['roles'] != 'adminParahita') {
     header('location: ../index.php');
     exit;
}

$identifier = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Admin';
$store_name = "Toko 2 Parahita";
// Identifier ini digunakan untuk memfilter PP yang ditampilkan dan yang boleh dimodifikasi
$store_identifier_for_pp = "Toko Parahita"; // Sesuaikan dengan nilai yang disimpan di kolom 'penerima' pada tabel 'pp' saat adminParahita membuat PP

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Permintaan Barang - <?= $store_name; ?></title>
        <link href="../css/styles.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
        <style> 
            .badge-warning { color: #212529 !important; } 
            td { vertical-align: middle !important; }
            .table-responsive { overflow-x: visible; }
            /* Style untuk tombol aksi agar lebih rapi jika ada banyak tombol */
            .action-buttons .btn {
                margin-right: 5px; /* Jarak antar tombol */
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
            <?php require '_sidebar_admin_parahita.php'; // Include sidebar khusus admin parahita ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Permintaan Barang Keluar dari Pusat</h1>
                         <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Stock <?= $store_name ?></a></li>
                            <li class="breadcrumb-item active">Permintaan Barang</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                               <i class="fas fa-file-invoice me-1"></i>
                                Buat Permintaan Baru ke Gudang Pusat
                              <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#modalBuatPermintaan">
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
                                        <th width="180px">Aksi</th> </tr>
                                    </thead>
                                    <tbody>
                                       <?php
                                        $ambilsemuadatastock = mysqli_query($conn, "SELECT pp.*, ps.namabarang AS namabarang_pusat, ps.stock AS stock_pusat
                                                                                    FROM pp
                                                                                    JOIN pusatstock ps ON pp.idbarang = ps.idbarang
                                                                                    WHERE pp.penerima = '".mysqli_real_escape_string($conn, $store_identifier_for_pp)."'
                                                                                    ORDER BY pp.tanggal DESC");
                                        if(!$ambilsemuadatastock) {
                                            echo "<tr><td colspan='7' class='text-center text-danger'>Error query: ".mysqli_error($conn)."</td></tr>";
                                        } else {
                                            if (mysqli_num_rows($ambilsemuadatastock) > 0) {
                                                while($data=mysqli_fetch_array($ambilsemuadatastock)){
                                                    $idpp = $data['idpp'];
                                                    $idbarang_diminta = $data['idbarang']; 
                                                    $tanggal = $data['tanggal'];
                                                    $namabarang = htmlspecialchars($data['namabarang_pusat']);
                                                    $qty = intval($data['qty']);
                                                    $nopp_display = htmlspecialchars($data['nopp']); 
                                                    $status = $data['status'];
                                                    $keterangan_display = htmlspecialchars($data['keterangan']);
                                                    $stock_pusat_saat_ini = $data['stock_pusat'];
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
                                             <td class="action-buttons"> <button type="button" class="btn btn-info btn-sm mb-1" data-toggle="modal" data-target="#detailPP<?=$idpp;?>" title="Lihat Detail">
                                                       <i class="fas fa-eye"></i> Detail
                                                  </button>
                                                <?php if($status == "PENDING"): ?>
                                                    <button type="button" class="btn btn-warning btn-sm mb-1" data-toggle="modal" data-target="#editPP<?=$idpp;?>" title="Edit Permintaan">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm mb-1" data-toggle="modal" data-target="#hapusPP<?=$idpp;?>" title="Hapus Permintaan">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                <?php endif; ?>
                                              </td>
                                        </tr>

                                        <div class="modal fade" id="detailPP<?=$idpp;?>">
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
                                        <div class="modal fade" id="editPP<?=$idpp;?>">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header"><h4 class="modal-title">Edit Permintaan Barang</h4><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                                                    <form method="post" action="../function.php">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="idpp" value="<?=$idpp;?>">
                                                            <div class="form-group">
                                                                <label for="nopp_edit<?=$idpp;?>">No. PP (Opsional):</label>
                                                                <input type="text" id="nopp_edit<?=$idpp;?>" name="nopp" value="<?=$data['nopp'];?>" placeholder="Nomor Permintaan Pembelian" class="form-control">
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="barangnya_edit<?=$idpp;?>">Pilih Barang (Stok Pusat):</label>
                                                                <select id="barangnya_edit<?=$idpp;?>" name="barangnya" class="form-control" required>
                                                                    <option value="">-- Pilih Barang --</option>
                                                                    <?php
                                                                    $ambilbarapusat_edit = mysqli_query($conn,"select * from pusatstock order by namabarang asc"); 
                                                                    while($fetchbar_edit = mysqli_fetch_array($ambilbarapusat_edit)){
                                                                        $idbarangnya_edit = $fetchbar_edit['idbarang'];
                                                                        $namabarangnya_edit = htmlspecialchars($fetchbar_edit['namabarang']);
                                                                        $stockbarangnya_edit = htmlspecialchars($fetchbar_edit['stock']);
                                                                        $selected_edit = ($idbarangnya_edit == $idbarang_diminta) ? 'selected' : '';
                                                                    ?>
                                                                    <option value="<?=$idbarangnya_edit;?>" <?=$selected_edit;?>><?=$namabarangnya_edit;?> (Stok Pusat: <?=$stockbarangnya_edit;?>)</option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="qty_edit<?=$idpp;?>">Quantity:</label>
                                                                <input type="number" id="qty_edit<?=$idpp;?>" name="qty" value="<?=$qty;?>" class="form-control" placeholder="Jumlah diminta" required min="1">
                                                                <small>Stok Pusat Saat Ini untuk Barang Terpilih: (Lihat dropdown)</small>
                                                            </div>
                                                            <input type="hidden" name="penerima" value="<?= htmlspecialchars($store_identifier_for_pp); ?>">
                                                            <div class="form-group">
                                                                <label for="keterangan_edit<?=$idpp;?>">Keterangan (Opsional):</label>
                                                                <textarea id="keterangan_edit<?=$idpp;?>" name="keterangan" class="form-control" rows="3" placeholder="Keterangan tambahan jika ada"><?=$data['keterangan'];?></textarea>
                                                            </div>
                                                            <button type="submit" class="btn btn-primary" name="updatepermintaanpp_cabang">Update Permintaan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="hapusPP<?=$idpp;?>">
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
                                                echo "<tr><td colspan='7' class='text-center'>Belum ada permintaan barang dari toko ini.</td></tr>";
                                            }
                                        } // End Else !$ambilsemuadatastock
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

        <div class="modal fade" id="modalBuatPermintaan">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title">Request Barang dari Pusat</h4>
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="post" action="../function.php"> 
                    <div class="modal-body">
                    <div class="form-group">
                        <label for="nopp_buat">No. PP (Opsional):</label>
                        <input type="text" id="nopp_buat" name="nopp" placeholder="Nomor Permintaan Pembelian" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="barangnya_buat">Pilih Barang (Stok Pusat):</label>
                         <select id="barangnya_buat" name="barangnya" class="form-control" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php
                            $ambilbarapusat_buat = mysqli_query($conn,"select * from pusatstock where stock > 0 order by namabarang asc");
                            while($fetchbar_buat = mysqli_fetch_array($ambilbarapusat_buat)){
                                $namabarangnya_buat = htmlspecialchars($fetchbar_buat['namabarang']);
                                $idbarangnya_buat = $fetchbar_buat['idbarang'];
                                $stockbarang_buat = htmlspecialchars($fetchbar_buat['stock']);
                            ?>
                            <option value="<?=$idbarangnya_buat;?>"><?=$namabarangnya_buat;?> (Stok Pusat: <?=$stockbarang_buat;?>)</option>
                            <?php } ?>
                         </select>
                    </div>
                    <div class="form-group">
                         <label for="qty_buat">Quantity:</label>
                         <input type="number" id="qty_buat" name="qty" class="form-control" placeholder="Jumlah diminta" required min="1">
                    </div>
                    <div class="form-group">
                         <label for="penerima_buat">Penerima / Tujuan:</label>
                          <input type="text" id="penerima_buat" name="penerima" class="form-control" value="<?= htmlspecialchars($store_identifier_for_pp); ?>" readonly required> 
                    </div>
                     <div class="form-group">
                        <label for="keterangan_buat">Keterangan (Opsional):</label>
                        <textarea id="keterangan_buat" name="keterangan" class="form-control" rows="3" placeholder="Keterangan tambahan jika ada"></textarea>
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