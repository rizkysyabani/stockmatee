<?php
// Ambil nama file saat ini untuk menandai menu aktif
$current_page_admin_serpong = basename($_SERVER['PHP_SELF']);
?>
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu" style="background-color:#003940;">
            <div class="nav">
                 <div class="sb-sidenav-menu-heading">admin Serpong</div>
                 <a class="nav-link <?= ($current_page_admin_serpong == 'index.php') ? 'active' : ''; ?>" href="index.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-boxes"></i></div>
                    Stock Barang
                </a>
                <a class="nav-link <?= ($current_page_admin_serpong == 'masuk.php') ? 'active' : ''; ?>" href="masuk.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-dolly-flatbed"></i></div>
                    Barang Masuk
                </a>
                <a class="nav-link <?= ($current_page_admin_serpong == 'keluar.php') ? 'active' : ''; ?>" href="keluar.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-truck-loading"></i></div>
                    Barang Keluar
                </a>
                 <a class="nav-link <?= ($current_page_admin_serpong == 'permintaanbarangkeluar.php') ? 'active' : ''; ?>" href="permintaanbarangkeluar.php"> <div class="sb-nav-link-icon"><i class="fas fa-hand-holding"></i></div>
                    Permintaan Barang
                </a>
                 
                 <div class="sb-sidenav-menu-heading">pengatura</div>
                 <a class="nav-link" href="../logout.php">
                    <div class="sb-nav-link-icon"><i class='fas fa-sign-out-alt' style='color:red;'></i></div>
                    Logout
                </a>
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
             Admin Serpong
        </div>
    </nav>
</div>