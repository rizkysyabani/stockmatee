<?php
// new/admin/_sidebar.php

// Pastikan session sudah dimulai dan koneksi database tersedia
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }
// require_once __DIR__ . '/../function.php'; // Sesuaikan path jika perlu

$current_page = basename($_SERVER['PHP_SELF']);

function is_active($pages) {
    global $current_page;
    if (is_array($pages)) {
        return in_array($current_page, $pages);
    }
    return $current_page == $pages;
}

$cisauk_active = is_active(['stock.php', 'masuk.php', 'keluar.php', 'permintaanbarangkeluar.php', 'pp.php']);
$parahita_active = is_active(['parahita_index.php', 'parahita_masuk.php', 'parahita_keluar.php']);
$serpong_active = is_active(['serpong_index.php', 'serpong_masuk.php', 'serpong_keluar.php']);

// --- Mulai Modifikasi untuk Admin Cabang Baru per Username ---
global $conn; // Ambil koneksi dari global scope

$admin_cabang_baru_items = [];

// Query untuk mengambil pengguna dengan role 'admin' (atau role spesifik Anda untuk cabang baru)
// dan bukan admin yang sudah punya menu sendiri (Cisauk, Parahita, Serpong, AdminPusat, Owner)
$sql_admin_cabang_baru = "SELECT username FROM login 
                          WHERE roles = 'admin' 
                          AND username NOT IN ('adminPusat', 'adminParahita', 'adminSerpong', 'owner', 
                                               'karyawanPusat', 'karyawanParahita', 'karyawanSerpong', 
                                               'karyawan') 
                          ORDER BY username ASC";
// Jika Anda punya role spesifik seperti 'adminCabang', gunakan itu:
// $sql_admin_cabang_baru = "SELECT username FROM login WHERE roles = 'adminCabang' ORDER BY username ASC";

$result_admin_cabang_baru = mysqli_query($conn, $sql_admin_cabang_baru);

if ($result_admin_cabang_baru && mysqli_num_rows($result_admin_cabang_baru) > 0) {
    while ($admin_cabang = mysqli_fetch_assoc($result_admin_cabang_baru)) {
        $username_cabang = htmlspecialchars($admin_cabang['username']);
        $username_cabang_safe_id = preg_replace("/[^a-zA-Z0-9]+/", "", $username_cabang); // Buat ID aman untuk HTML

        // **PENTING**: Tentukan nama file berdasarkan username.
        // Jika Anda ingin semua admin baru menggunakan file adminBaru_*.php, maka variabel $pages_for_this_admin
        // akan tetap seperti adminBaru_index.php, dll.
        // Namun, jika setiap username punya file sendiri (misal, adminTokoABC_stock.php), maka Anda harus membuat file-file itu.

        // OPSI 1: Semua admin cabang baru menggunakan file standar adminBaru_*.php
        // (Ini berarti link dari Admin Pusat akan ke file yang sama untuk semua cabang baru,
        // dan di dalam file adminBaru_*.php Anda perlu logika untuk memfilter data berdasarkan siapa Admin Pusat sedang lihat,
        // mungkin dengan parameter GET ?toko=namaUsernameCabang)
        $pages_for_this_admin = [
            'adminBaru_index.php', // Admin Pusat lihat stok adminBaru (dengan parameter ?user=$username_cabang)
            'adminBaru_masuk.php', // Admin Pusat lihat masuk adminBaru (dengan parameter ?user=$username_cabang)
            'adminBaru_keluar.php' // Admin Pusat lihat keluar adminBaru (dengan parameter ?user=$username_cabang)
        ];
        $link_stock = "adminBaru_index.php?user=" . urlencode($username_cabang);
        $link_masuk = "adminBaru_masuk.php?user=" . urlencode($username_cabang);
        $link_keluar = "adminBaru_keluar.php?user=" . urlencode($username_cabang);

        // Cek apakah salah satu halaman untuk admin ini sedang aktif
        // Ini akan sedikit rumit jika semua pakai file adminBaru_*.php karena $current_page tidak cukup.
        // Kita perlu cek juga parameter GET['user'] jika menggunakan Opsi 1.
        $is_current_admin_group_active = false;
        if (isset($_GET['user']) && $_GET['user'] == $username_cabang) {
            if (in_array($current_page, ['adminBaru_index.php', 'adminBaru_masuk.php', 'adminBaru_keluar.php'])) {
                $is_current_admin_group_active = true;
            }
        }


        // OPSI 2: Setiap admin cabang baru memiliki file PHP sendiri di folder new/admin/
        // (misalnya, new/admin/tokoABC_stock.php, new/admin/tokoABC_masuk.php).
        // Jika ini kasusnya, uncomment dan sesuaikan bagian di bawah ini:
        /*
        $pages_for_this_admin = [
            $username_cabang_safe_id . '_stock.php',
            $username_cabang_safe_id . '_masuk.php',
            $username_cabang_safe_id . '_keluar.php'
        ];
        $link_stock = $username_cabang_safe_id . '_stock.php';
        $link_masuk = $username_cabang_safe_id . '_masuk.php';
        $link_keluar = $username_cabang_safe_id . '_keluar.php';
        $is_current_admin_group_active = is_active($pages_for_this_admin);
        */


        $admin_cabang_baru_items[] = [
            'username' => $username_cabang,
            'username_safe_id' => $username_cabang_safe_id,
            'link_stock' => $link_stock,
            'link_masuk' => $link_masuk,
            'link_keluar' => $link_keluar,
            'is_active_group' => $is_current_admin_group_active,
            'pages_to_check_active' => $pages_for_this_admin // untuk is_active
        ];
    }
}
// --- Akhir Modifikasi ---
?>
<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu" style="background-color:#003940;">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">Admin Pusat</div>
                <a class="nav-link <?= is_active('index.php') ? 'active' : ''; ?>" href="index.php"> <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div> Dashboard
                </a>

                <a class="nav-link <?= $cisauk_active ? '' : 'collapsed'; ?>" href="#" data-toggle="collapse" data-target="#collapseCisauk" aria-expanded="<?= $cisauk_active ? 'true' : 'false'; ?>" aria-controls="collapseCisauk">
                    <div class="sb-nav-link-icon"><i class="fas fa-store"></i></div> Toko Pusat (Cisauk) <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?= $cisauk_active ? 'show' : ''; ?>" id="collapseCisauk" aria-labelledby="headingCisauk" data-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?= is_active('stock.php') ? 'active' : ''; ?>" href="stock.php">Stock Barang</a>
                        <a class="nav-link <?= is_active('masuk.php') ? 'active' : ''; ?>" href="masuk.php">Barang Masuk</a>
                        <a class="nav-link <?= is_active('keluar.php') ? 'active' : ''; ?>" href="keluar.php">Barang Keluar</a>
                        <a class="nav-link <?= is_active('pp.php') ? 'active' : ''; ?>" href="pp.php">PP Barang Keluar</a>
                    </nav>
                </div>

                <a class="nav-link <?= $parahita_active ? '' : 'collapsed'; ?>" href="#" data-toggle="collapse" data-target="#collapseParahita" aria-expanded="<?= $parahita_active ? 'true' : 'false'; ?>" aria-controls="collapseParahita">
                    <div class="sb-nav-link-icon"><i class="fas fa-store"></i></div> Lihat Stock adminParahita <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?= $parahita_active ? 'show' : ''; ?>" id="collapseParahita" aria-labelledby="headingParahita" data-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?= is_active('parahita_index.php') ? 'active' : ''; ?>" href="parahita_index.php">Stock Barang</a>
                        <a class="nav-link <?= is_active('parahita_masuk.php') ? 'active' : ''; ?>" href="parahita_masuk.php">Barang Masuk</a>
                        <a class="nav-link <?= is_active('parahita_keluar.php') ? 'active' : ''; ?>" href="parahita_keluar.php">Barang Keluar</a>
                    </nav>
                </div>

                <a class="nav-link <?= $serpong_active ? '' : 'collapsed'; ?>" href="#" data-toggle="collapse" data-target="#collapseSerpong" aria-expanded="<?= $serpong_active ? 'true' : 'false'; ?>" aria-controls="collapseSerpong">
                    <div class="sb-nav-link-icon"><i class="fas fa-store"></i></div> Lihat Stock adminSerpong <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse <?= $serpong_active ? 'show' : ''; ?>" id="collapseSerpong" aria-labelledby="headingSerpong" data-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link <?= is_active('serpong_index.php') ? 'active' : ''; ?>" href="serpong_index.php">Stock Barang</a>
                        <a class="nav-link <?= is_active('serpong_masuk.php') ? 'active' : ''; ?>" href="serpong_masuk.php">Barang Masuk</a>
                        <a class="nav-link <?= is_active('serpong_keluar.php') ? 'active' : ''; ?>" href="serpong_keluar.php">Barang Keluar</a>
                    </nav>
                </div>

                <?php if (!empty($admin_cabang_baru_items)): ?>
                   
                    <?php foreach ($admin_cabang_baru_items as $item): ?>
                        <?php
                        // Cek apakah halaman saat ini adalah salah satu halaman untuk admin ini,
                        // dan jika parameter 'user' cocok dengan username admin ini (jika menggunakan Opsi 1)
                        $isActiveGroup = $item['is_active_group'];
                        ?>
                        <a class="nav-link <?= $isActiveGroup ? '' : 'collapsed'; ?>" href="#" data-toggle="collapse" data-target="#collapse<?= $item['username_safe_id']; ?>" aria-expanded="<?= $isActiveGroup ? 'true' : 'false'; ?>" aria-controls="collapse<?= $item['username_safe_id']; ?>">
                            <div class="sb-nav-link-icon"><i class="fas fa-store"></i></div>
                            Lihat Stock <?= $item['username']; ?>
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse <?= $isActiveGroup ? 'show' : ''; ?>" id="collapse<?= $item['username_safe_id']; ?>" aria-labelledby="heading<?= $item['username_safe_id']; ?>" data-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link <?= ($current_page == 'adminBaru_index.php' && (isset($_GET['user']) && $_GET['user'] == $item['username'])) ? 'active' : ''; ?>" href="<?= $item['link_stock']; ?>">Stock Barang</a>
                                <a class="nav-link <?= ($current_page == 'adminBaru_masuk.php' && (isset($_GET['user']) && $_GET['user'] == $item['username'])) ? 'active' : ''; ?>" href="<?= $item['link_masuk']; ?>">Barang Masuk</a>
                                <a class="nav-link <?= ($current_page == 'adminBaru_keluar.php' && (isset($_GET['user']) && $_GET['user'] == $item['username'])) ? 'active' : ''; ?>" href="<?= $item['link_keluar']; ?>">Barang Keluar</a>
                                </nav>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="sb-sidenav-menu-heading">Pengaturan</div>
                <a class="nav-link <?= is_active('admin.php') ? 'active' : ''; ?>" href="admin.php">
                   <div class="sb-nav-link-icon"><i class='fas fa-user-cog'></i></div> Kelola Admin
                </a>

                <a class="nav-link" href="../logout.php">
                    <div class="sb-nav-link-icon"><i class='fas fa-sign-out-alt' style='color:red;'></i></div> Logout
                </a>
            </div>
        </div>
        <div class="sb-sidenav-footer">
             <div class="small">Logged in as:</div>
             <?= htmlspecialchars(ucfirst($_SESSION['roles'] ?? 'N/A')); ?>
        </div>
    </nav>
</div>