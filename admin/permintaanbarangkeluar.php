<?php
require "../function.php";
require "../cek.php";
$email = $_SESSION['email'];
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Permintaan Barang Keluar Pusat - Admin</title>
        <link href="../css/styles.css" rel="stylesheet" />
        <link href="https://cdn.datatables.net/1.10.20/css/dataTables.bootstrap4.min.css" rel="stylesheet" crossorigin="anonymous" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/js/all.min.js" crossorigin="anonymous"></script>
        <style>
            /* Style jika diperlukan */
        </style>
    </head>
    <body class="sb-nav-fixed">
        <nav class="sb-topnav navbar navbar-expand navbar-dark" style="background-color:#003940;">
             <a class="navbar-brand" href="index.php">Stock <sup>Mate</sup></a>
            <button class="btn btn-link btn-sm order-1 order-lg-0" id="sidebarToggle" href="#"><i class="fas fa-bars"></i></button>
        </nav>
        <div id="layoutSidenav">
            <div id="layoutSidenav_nav" >
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu" style="background-color:#003940;">
                        <div class="nav">
                             <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseStok" aria-expanded="false" aria-controls="collapseStok">
                                <div class="sb-nav-link-icon"><i class="fas fa-warehouse"></i></div> Gudang Pusat <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseStok" aria-labelledby="headingOne" data-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="index.php">
                                        <div class="sb-nav-link-icon"><i class='fas fa-boxes' style='font-size:20px;color:#ffae00'></i></div>
                                        Stock Barang Pusat </a>
                                    <a class="nav-link" href="masuk.php">
                                        <div class="sb-nav-link-icon"><i class='fas fa-dolly-flatbed' style='font-size:20px;color:lightgreen;'></i></div> Barang Masuk Pusat </a>
                                    <a class="nav-link" href="keluar.php">
                                        <div class="sb-nav-link-icon"><i class='fas fa-truck-loading' style='font-size:20px;color:lightblue;'></i></div> Barang Keluar Pusat </a>
                                    <a class="nav-link" href="permintaanbarangkeluar.php">
                                        <div class="sb-nav-link-icon"><i class='fas fa-hand-holding' style='font-size:20px;color:#f5c71a;'></i></div> PP Barang Keluar </a>
                                </nav>
                            </div>

                             <a class="nav-link" href="admin.php">
                               <div class="sb-nav-link-icon"><i class='fas fa-user-cog' style='font-size:20px;color:#e2dddd'></i></div> Kelola Admin
                            </a>

                            <a class="nav-link" href="../logout.php">
                                <div class="sb-nav-link-icon"><i class='fas fa-sign-out-alt' style='font-size:20px;color:red'></i></div> Logout
                            </a>
                        </div>
                    </div>
                </nav>
            </div>
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid">
                        <h1 class="mt-4">Permintaan Barang Keluar Pusat (PP)</h1>
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table mr-1"></i>
                                Daftar Permintaan Barang
                            </div>
                            <div class="card-body">
                             <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>No PP</th>
                                    <th>Nama Barang</th>
                                    <th>Penerima</th>
                                    <th>Jumlah</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                                </thead>
                                <tbody>
                                   <?php
                                // Diubah: Menggunakan 'pusatstock'
                                $ambilsemuadatastock = mysqli_query($conn, "select * from pp p, pusatstock s where s.idbarang = p.idbarang");
                                while($data=mysqli_fetch_array($ambilsemuadatastock)){
                                    $idpp = $data['idpp'];
                                    $idb = $data['idbarang'];
                                    $tanggal = $data['tanggal'];
                                    $namabarang = $data['namabarang'];
                                    $qty = $data['qty'];
                                    $penerima = $data['penerima'];
                                    $nopp = $data['nopp'];
                                    $status = $data['status'];
                                ?>
                                <tr>
                                    <td><?=$tanggal;?></td>
                                    <td><?=$nopp;?></td>
                                    <td><?=$namabarang;?></td>
                                    <td><?=$penerima;?></td>
                                    <td><?=$qty;?></td>
                                    <td>
                                        <?php if($status == "PENDING" ): ?>
                                            <span class="badge badge-warning">PENDING</span>
                                        <?php elseif ($status == "DITERIMA" ): ?>
                                            <span class="badge badge-success">DITERIMA</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?=$status;?></span> <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($status == "PENDING" ): ?>
                                            <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#terima<?=$idpp;?>">
                                                Terima
                                            </button>
                                        <?php elseif ($status == "DITERIMA" ): ?>
                                            <button type="button" class="btn btn-secondary btn-sm" disabled>
                                                Sudah Diterima
                                            </button>
                                        <?php endif; ?>
                                         </td>
                                </tr>

                                <div class="modal fade" id="terima<?=$idpp;?>">
                                    <div class="modal-dialog">
                                      <div class="modal-content">
                                        <div class="modal-header">
                                          <h4 class="modal-title">Terima Permintaan Barang Keluar</h4>
                                          <button type="button" class="close" data-dismiss="modal">&times;</button>
                                        </div>
                                        <form method="post">
                                        <div class="modal-body">
                                          Terima permintaan untuk <strong><?=$namabarang;?></strong> sejumlah <strong><?=$qty;?></strong>?
                                          <br><br>(Stok akan berkurang)
                                          <input type="hidden" name="idb" value="<?=$idb;?>">
                                          <input type="hidden" name="idpp" value="<?=$idpp;?>">
                                          <input type="hidden" name="qty" value="<?=$qty;?>">
                                          <input type="hidden" name="penerima" value="<?=$penerima;?>"> <?php // Mengirim penerima ke function ?>
                                          <br><br>
                                          <button type="submit" class="btn btn-success" name="terimabarangkeluar">Ya, Terima</button>
                                        </div>
                                        </form>
                                      </div>
                                    </div>
                                  </div>

                                <?php }; ?>
                            </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </main>
                 <footer class="py-4 bg-light mt-auto">
                     <div class="container-fluid">
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
        <script src="../assets/demo/datatables-demo.js"></script>
    </body>
</html>