<?php
require "function.php";
require "cek.php";

$koneksi = mysqli_connect("localhost", "u115277227_stockbarang", "Stockmate1234#", "u115277227_stockbarang");

if (isset($_POST['login'])) {
    $username = $_POST['username']; // Ganti 'email' dengan 'username'
    $password = $_POST['password'];

    $cekuser = mysqli_query($koneksi, "SELECT * FROM login WHERE username='$username' AND password='$password'");
    $hitung = mysqli_num_rows($cekuser);

    if ($hitung > 0) {
        // jika data ditemukan
        $ambildatarole = mysqli_fetch_array($cekuser);
        $role = $ambildatarole['roles'];

        if ($role == 'adminPusat') {
            $_SESSION['log'] = 'Logged';
            $_SESSION['roles'] = 'admin';
            header('location:admin');
            exit();
        } else if ($role == 'karyawanPusat') { // Pastikan untuk memeriksa role karyawan
            $_SESSION['log'] = 'Logged';
            $_SESSION['roles'] = 'karyawanPusat';
            header('location:main/karyawanPusat'); // Arahkan ke main/user
            exit();
        }
    } else {
        echo 'Data tidak ditemukan';
    }
}
?>