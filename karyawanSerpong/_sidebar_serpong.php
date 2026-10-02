<?php
// Ambil nama file saat ini untuk menandai menu aktif
$current_page_karyawan_serpong = basename($_SERVER['PHP_SELF']);
?>
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu" style="background-color:#003940;">
            <div class="nav">
                 <div class="sb-sidenav-menu-heading">karyawan Serpong</div>
                 <a class="nav-link <?= ($current_page_karyawan_serpong == 'index.php') ? 'active' : ''; ?>" href="index.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-boxes"></i></div>
                    Stock Barang Serpong
                </a>
                <a class="nav-link <?= ($current_page_karyawan_serpong == 'masuk.php') ? 'active' : ''; ?>" href="masuk.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-dolly-flatbed"></i></div>
                    Barang Masuk Serpong
                </a>
                <a class="nav-link <?= ($current_page_karyawan_serpong == 'keluar.php') ? 'active' : ''; ?>" href="keluar.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-truck-loading"></i></div>
                    Barang Keluar Serpong
                </a>
                 <div class="sb-sidenav-menu-heading">pengaturan</div>
                 <a class="nav-link" href="../logout.php">
                    <div class="sb-nav-link-icon"><i class='fas fa-sign-out-alt' style='color:red;'></i></div>
                    Logout
                </a>
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
             Karyawan Serpong
        </div>
    </nav>
</div>