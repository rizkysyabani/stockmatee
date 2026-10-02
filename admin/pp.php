<?php
require "../function.php";
require "../cek.php";

// --- PERBAIKAN: Pemeriksaan Otorisasi Spesifik ---
// Pastikan hanya role adminPusat atau owner yang bisa akses halaman ini
if (!isset($_SESSION['roles']) || ($_SESSION['roles'] != 'adminPusat' && $_SESSION['roles'] != 'owner')) {
   // Jika bukan, redirect ke halaman login atau halaman lain yang sesuai
   header('location: ../index.php');
   // Hentikan eksekusi script selanjutnya
   exit;
}
// --- Akhir Perbaikan ---

// Gunakan username atau email dari session untuk sapaan
$identifier = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : (isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'Admin');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Permintaan Barang Keluar Pusat - Admin</title>
        <link href="../css/styles.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
        <style>
            .badge-warning { color: #212529 !important; } /* Agar teks di badge warning lebih mudah dibaca */
            td { vertical-align: middle !important; }
            .table-responsive { overflow-x: visible; } /* Memastikan modal tidak terpotong */
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
            <?php require '_sidebar.php'; // Memastikan sidebar yang benar dimuat ?>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Permintaan Barang Keluar Pusat (PP)</h1>
                         <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Permintaan Barang Keluar</li>
                        </ol>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Daftar Permintaan Barang (Dipenuhi dari Stok Pusat)
                                 </div>
                            <div class="card-body">
                             <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                    <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>No PP</th>
                                        <th>Nama Barang</th>
                                        <th>Penerima/Tujuan</th>
                                        <th>Jumlah</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                       <?php
                                        $ambilsemuadatastock = mysqli_query($conn, "SELECT pp.*, ps.namabarang
                                                                                    FROM pp
                                                                                    JOIN pusatstock ps ON pp.idbarang = ps.idbarang
                                                                                    ORDER BY pp.tanggal DESC, pp.status ASC"); // Urutkan juga berdasarkan status
                                        
                                        if(!$ambilsemuadatastock) {
                                            echo "<tr><td colspan='7' class='text-center text-danger'>Error mengambil data permintaan: ".mysqli_error($conn)."</td></tr>";
                                        } else {
                                            if(mysqli_num_rows($ambilsemuadatastock) > 0) {
                                                while($data=mysqli_fetch_array($ambilsemuadatastock)){
                                                    $idpp = $data['idpp'];
                                                    $idb = $data['idbarang'];
                                                    $tanggal = $data['tanggal'];
                                                    $namabarang = htmlspecialchars($data['namabarang']); 
                                                    $qty = intval($data['qty']);
                                                    $penerima = htmlspecialchars($data['penerima']);
                                                    $nopp = htmlspecialchars($data['nopp']);
                                                    $status = $data['status'];
                                                    $keterangan = htmlspecialchars($data['keterangan']);
                                        ?>
                                        <tr>
                                            <td><?=$tanggal;?></td>
                                            <td><?=$nopp ? $nopp : '-';?></td>
                                            <td><?=$namabarang;?></td>
                                            <td><?=$penerima;?></td>
                                            <td><?=$qty;?></td>
                                            <td>
                                                <?php if($status == "PENDING" ): ?>
                                                    <span class="badge badge-warning">PENDING</span>
                                                <?php elseif ($status == "DITERIMA" ): ?>
                                                    <span class="badge badge-success">DITERIMA</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary"><?=htmlspecialchars(strtoupper($status));?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if($status == "PENDING" ): ?>
                                                    <button type="button" class="btn btn-success btn-sm mb-1" data-toggle="modal" data-target="#terima<?=$idpp;?>" title="Terima Permintaan">
                                                        <i class="fas fa-check"></i> Terima
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm mb-1" data-toggle="modal" data-target="#hapus<?=$idpp;?>" title="Hapus Permintaan">
                                                        <i class="fas fa-trash"></i> Hapus
                                                    </button>
                                                <?php endif; ?>
                                                <button type="button" class="btn btn-info btn-sm mb-1" data-toggle="modal" data-target="#detail<?=$idpp;?>" title="Lihat Detail">
                                                    <i class="fas fa-eye"></i> Detail
                                                </button>
                                                <?php if($status != "PENDING" ): ?>
                                                    <button type="button" class="btn btn-secondary btn-sm mb-1" disabled>
                                                        Processed
                                                    </button>
                                                <?php endif; ?>
                                             </td>
                                        </tr>

                                        <div class="modal fade" id="terima<?=$idpp;?>">
                                            <div class="modal-dialog">
                                              <div class="modal-content">
                                                <div class="modal-header">
                                                  <h4 class="modal-title">Terima Permintaan Barang Keluar?</h4>
                                                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                </div>
                                                <form method="post" action="../function.php">
                                                    <div class="modal-body">
                                                      Terima permintaan untuk <strong><?=$namabarang;?></strong> sejumlah <strong><?=$qty;?></strong> ke <strong><?= $penerima; ?></strong>?
                                                      <br><br><strong>(Stok Gudang Pusat akan berkurang dan stok tujuan akan bertambah!)</strong>
                                                      <input type="hidden" name="idb" value="<?=$idb;?>">
                                                      <input type="hidden" name="idpp" value="<?=$idpp;?>">
                                                      <input type="hidden" name="qty" value="<?=$qty;?>">
                                                      <input type="hidden" name="penerima" value="<?=$penerima;?>">
                                                      <br><br>
                                                      <button type="submit" class="btn btn-success" name="terimabarangkeluar">Ya, Terima & Proses</button>
                                                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    </div>
                                                </form>
                                              </div>
                                            </div>
                                          </div>

                                          <div class="modal fade" id="hapus<?=$idpp;?>">
                                              <div class="modal-dialog">
                                                  <div class="modal-content">
                                                      <div class="modal-header">
                                                          <h4 class="modal-title">Hapus Permintaan Barang?</h4>
                                                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                      </div>
                                                      <form method="post" action="../function.php"> 
                                                          <div class="modal-body">
                                                              Apakah Anda yakin ingin menghapus permintaan untuk <strong><?=$namabarang;?></strong> (No. PP: <?=$nopp ? $nopp : '-';?>) sejumlah <strong><?=$qty;?></strong>?
                                                              <br><br><strong>(Tindakan ini tidak dapat dibatalkan).</strong>
                                                              <input type="hidden" name="idpp" value="<?=$idpp;?>">
                                                              <br><br>
                                                              <button type="submit" class="btn btn-danger" name="hapuspermintaanpp">Ya, Hapus Permintaan</button>
                                                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                          </div>
                                                      </form>
                                                  </div>
                                              </div>
                                          </div>

                                          <div class="modal fade" id="detail<?=$idpp;?>">
                                              <div class="modal-dialog modal-lg">
                                                  <div class="modal-content">
                                                      <div class="modal-header">
                                                          <h4 class="modal-title">Detail Permintaan (PP)</h4>
                                                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                                                      </div>
                                                      <div class="modal-body">
                                                          <dl class="row">
                                                              <dt class="col-sm-3">No. PP</dt>
                                                              <dd class="col-sm-9">: <?= $nopp ? $nopp : '-'; ?></dd>
                                                              <dt class="col-sm-3">Tanggal Permintaan</dt>
                                                              <dd class="col-sm-9">: <?= $tanggal; ?></dd>
                                                              <dt class="col-sm-3">Nama Barang</dt>
                                                              <dd class="col-sm-9">: <?= $namabarang; ?></dd>
                                                              <dt class="col-sm-3">Jumlah Diminta</dt>
                                                              <dd class="col-sm-9">: <?= $qty; ?></dd>
                                                              <dt class="col-sm-3">Penerima / Tujuan</dt>
                                                              <dd class="col-sm-9">: <?= $penerima; ?></dd>
                                                              <dt class="col-sm-3">Keterangan</dt>
                                                              <dd class="col-sm-9">: <?= $keterangan ? $keterangan : '-'; ?></dd>
                                                              <dt class="col-sm-3">Status</dt>
                                                              <dd class="col-sm-9">:
                                                                <?php if($status == "PENDING" ): ?> <span class="badge badge-warning">PENDING</span>
                                                                <?php elseif ($status == "DITERIMA" ): ?> <span class="badge badge-success">DITERIMA</span>
                                                                <?php else: ?> <span class="badge badge-secondary"><?=htmlspecialchars(strtoupper($status));?></span> <?php endif; ?>
                                                              </dd>
                                                          </dl>
                                                      </div>
                                                       <div class="modal-footer">
                                                           <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                                                       </div>
                                                  </div>
                                              </div>
                                          </div>
                                          <?php
                                                } // End While loop
                                            } else {
                                                 echo "<tr><td colspan='7' class='text-center'>Belum ada permintaan barang.</td></tr>";
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
                             <div class="text-muted">Copyright &copy; StokMate <?php echo date("Y"); ?></div> <div>
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
        <script>
             $(document).ready(function() {
                 $('#dataTable').DataTable({
                      stateSave: true, 
                      "order": [[ 0, "desc" ], [5, "asc"]] // Urutkan berdasarkan tanggal DESC, lalu status ASC (PENDING dulu)
                 });
             });
        </script>
    </body>
</html>