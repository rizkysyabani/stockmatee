<?php
// Ambil nama file saat ini untuk menandai menu aktif
$current_page_owner = basename($_SERVER['PHP_SELF']);
?>
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu" style="background-color:#003940;">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">Owner</div>
                <a class="nav-link <?= ($current_page_owner == 'index.php') ? 'active' : ''; ?>" href="index.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
                <a class="nav-link <?= ($current_page_owner == 'laporan.php') ? 'active' : ''; ?>" href="laporan.php">
                    <div class="sb-nav-link-icon"><i class="fas fa-chart-area"></i></div>
                    Laporan Antar Cabang
                </a>
                 <div class="sb-sidenav-menu-heading">Pengaturan</div>
                 
                 <a class="nav-link" href="../logout.php">
                    <div class="sb-nav-link-icon"><i class='fas fa-sign-out-alt' style='color:red;'></i></div>
                    Logout
                </a>
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
             Owner
        </div>
    </nav>
</div>