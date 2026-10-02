<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Mulai session hanya jika belum dimulai
}

// Membuat koneksi ke database
// Ganti dengan kredensial database Anda yang sebenarnya
$conn = mysqli_connect("localhost", "root", "12345678", "stockbarang");

// Cek koneksi
if (!$conn) {
    error_log("Koneksi Gagal: " . mysqli_connect_error()); // Catat error ke log server
    die("Koneksi Database Gagal. Silakan coba lagi nanti."); // Tampilkan pesan umum ke user
}

// =================================================================
// ===> SOLUSI: TAMBAHKAN BARIS INI UNTUK MEMPERBAIKI MASALAH WAKTU <===
// Mengatur zona waktu koneksi database ke WIB (UTC+7)
mysqli_query($conn, "SET time_zone = '+07:00'");
// =================================================================

// Fungsi helper untuk redirect dan alert (Menggunakan JavaScript)
function redirect_with_alert($message, $location) {
    // ... (sisa kode function.php Anda tidak perlu diubah) ...
    // Pastikan path dimulai dengan / jika menuju root web
    if (strpos($location, '/') !== 0 && strpos($location, 'http') !== 0) {
        $base_path = '/stockmate/'; 
        $location = $base_path . ltrim($location, '/');
    }

    if (!headers_sent()) {
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Redirecting...</title></head><body>';
        echo '<script type="text/javascript">';
        echo 'alert("'.addslashes($message).'");'; // Gunakan addslashes untuk handle kutip dalam pesan
        echo 'window.location.href="'.$location.'";';
        echo '</script>';
        echo '<noscript>';
        echo '<meta http-equiv="refresh" content="0;url='.$location.'" />';
        echo '</noscript>';
        echo '</body></html>';
    } else {
        echo "<br>Error Terjadi: " . htmlspecialchars($message) . "<br>Silakan kembali dan coba lagi.";
        echo '<br><a href="'.$location.'">Kembali</a>';
    }
    exit;
}


// Fungsi helper untuk menghapus gambar
function delete_image_file($image_name) {
    if (!empty($image_name)) {
        $base_path = __DIR__ . '/images/';
        if (!is_dir($base_path)) {
             error_log("Direktori gambar tidak ditemukan: " . $base_path);
             return;
        }
         if (!is_writable($base_path)) {
            error_log("Direktori gambar tidak writable: " . $base_path);
            return;
        }

        $img_path = $base_path . $image_name;
        if (file_exists($img_path) && is_writable($img_path)) {
            @unlink($img_path);
        } elseif (file_exists($img_path)) {
            error_log("Permission error: Cannot delete image file ".$img_path);
        }
    }
}


// Fungsi helper untuk upload gambar
function upload_image_file($file_input_name, $redirect_page) {
    global $conn;
    $image = null;
    $upload_dir = __DIR__ . '/images/';

    if (!is_dir($upload_dir)) {
        if (!@mkdir($upload_dir, 0775, true)) {
             $error = error_get_last();
             redirect_with_alert("Gagal membuat direktori upload! Error: " . ($error['message'] ?? 'Unknown error'), $redirect_page);
             return null;
        }
    }
    if (!is_writable($upload_dir)) {
        @chmod($upload_dir, 0775);
        if (!is_writable($upload_dir)) {
            error_log("Upload directory not writable: " . $upload_dir);
            redirect_with_alert("Direktori upload tidak writable!", $redirect_page);
            return null;
        }
    }


    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == UPLOAD_ERR_OK && $_FILES[$file_input_name]['size'] > 0) {
        $allowed_extension = array('png', 'jpg', 'jpeg', 'gif');

        $nama = basename($_FILES[$file_input_name]['name']);
        $dot = explode('.', $nama);
        $ekstensi = strtolower(end($dot));
        $ukuran = $_FILES[$file_input_name]['size'];
        $file_tmp = $_FILES[$file_input_name]['tmp_name'];

        if (in_array($ekstensi, $allowed_extension)) {
            if ($ukuran < 15000000) { // 15MB
                $image = md5(uniqid(basename($nama), true) . time()) . '.' . $ekstensi;
                $destination = $upload_dir . $image;

                if (!move_uploaded_file($file_tmp, $destination)) {
                    $upload_error = $_FILES[$file_input_name]['error'];
                    error_log("move_uploaded_file() failed. Source: $file_tmp, Dest: $destination, Error code: $upload_error");
                    redirect_with_alert("Gagal menyimpan gambar yang diupload! Error code: $upload_error. Periksa log server.", $redirect_page);
                    return null;
                }
                return $image;
            } else {
                 redirect_with_alert("Ukuran gambar terlalu besar (Max 15MB)!", $redirect_page);
                 return null;
            }
        } else {
            redirect_with_alert("Ekstensi gambar tidak diizinkan (Hanya PNG, JPG, JPEG, GIF)!", $redirect_page);
            return null;
        }
    } elseif (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] != UPLOAD_ERR_NO_FILE) {
        $upload_error = $_FILES[$file_input_name]['error'];
        error_log("Upload error reported by PHP. Error code: $upload_error");
        redirect_with_alert("Terjadi error saat upload gambar! Error code: $upload_error. Periksa log server.", $redirect_page);
        return null;
    }
    return null;
}


// ==============================================
// FUNGSI UNTUK TOKO PUSAT (Cisauk)
// ==============================================
// Menambah barang baru Pusat
if (isset($_POST['addnewbarang'])) {
    $namabarang = mysqli_real_escape_string($conn, $_POST['namabarang']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $stock = intval($_POST['stock']);
    $stock_table_name = 'pusatstock';

    $redirect_page = '/stockmate/admin/stock.php'; 
    if (isset($_SESSION['roles']) && $_SESSION['roles'] == 'owner') {
        $redirect_page = '/stockmate/owner/index.php';  
    }

    $image = upload_image_file('file', $redirect_page);

    $cek = mysqli_query($conn, "SELECT namabarang FROM $stock_table_name WHERE namabarang ='$namabarang' LIMIT 1");
    if (!$cek) redirect_with_alert("Error cek duplikat: ".mysqli_error($conn), $redirect_page);

    if (mysqli_num_rows($cek) < 1) {
        $image_sql_value = ($image !== null) ? "'".mysqli_real_escape_string($conn, $image)."'" : "NULL";
        $query = "INSERT INTO $stock_table_name (namabarang, deskripsi, stock, image) VALUES ('$namabarang', '$deskripsi', '$stock', $image_sql_value)";
        $addtotable = mysqli_query($conn, $query);
        if ($addtotable) {
            header('location:' . $redirect_page); exit;
        } else {
             if ($image !== null) { delete_image_file($image); }
            redirect_with_alert("Gagal menambahkan barang! Error: " . mysqli_error($conn), $redirect_page);
        }
    } else {
        if ($image !== null) { delete_image_file($image); }
        redirect_with_alert("Nama barang sudah terdaftar di toko ini!", $redirect_page);
    }
}

// Menambah barang masuk Pusat
if (isset($_POST['barangmasuk'])) {
    $barangnya = intval($_POST['barangnya']);
    $penerima = mysqli_real_escape_string($conn, $_POST['penerima']);
    $qty = intval($_POST['qty']);
    $stock_table_name = 'pusatstock';
    $masuk_table_name = 'pusatmasuk';

    $current_role = $_SESSION['roles'] ?? '';
    if ($current_role == 'karyawanPusat') $redirect_page = '/stockmate/karyawanPusat/masuk.php'; 
    elseif ($current_role == 'owner') $redirect_page = '/stockmate/owner/masuk.php';  
    else $redirect_page = '/stockmate/admin/masuk.php';  

    if ($qty <= 0) redirect_with_alert("Quantity harus lebih dari 0!", $redirect_page);
    if ($barangnya <= 0) redirect_with_alert("Barang belum dipilih!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $cekstocksekarang = mysqli_query($conn, "SELECT stock FROM $stock_table_name WHERE idbarang='$barangnya' FOR UPDATE");
        if (!$cekstocksekarang || mysqli_num_rows($cekstocksekarang) == 0) throw new Exception("Barang tidak ditemukan.");
        $ambildatanya = mysqli_fetch_array($cekstocksekarang);
        $stocksekarang = $ambildatanya['stock'];
        $stockbaru = $stocksekarang + $qty;

        $addtomasuk = mysqli_query($conn, "INSERT INTO $masuk_table_name (idbarang, keterangan, qty) VALUES ('$barangnya', '$penerima', '$qty')");
        if (!$addtomasuk) throw new Exception("Gagal mencatat barang masuk. Error: ".mysqli_error($conn));

        $updatestockmasuk = mysqli_query($conn, "UPDATE $stock_table_name SET stock='$stockbaru' WHERE idbarang='$barangnya'");
        if (!$updatestockmasuk) throw new Exception("Gagal update stok barang. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal: " . $e->getMessage(), $redirect_page);
    }
}

// Menambah barang keluar Pusat
if (isset($_POST['addbarangkeluar'])) {
    $barangnya = intval($_POST['barangnya']);
    $penerima = mysqli_real_escape_string($conn, $_POST['penerima']);
    $qty = intval($_POST['qty']);
    $stock_table_name = 'pusatstock';
    $keluar_table_name = 'pusatkeluar';

    $current_role = $_SESSION['roles'] ?? '';
    if ($current_role == 'karyawanPusat') $redirect_page = '/stockmate/karyawanPusat/keluar.php'; 
    elseif ($current_role == 'owner') $redirect_page = '/stockmate/owner/keluar.php';  
    else $redirect_page = '/stockmate/admin/keluar.php';  

    if ($qty <= 0) redirect_with_alert("Quantity harus lebih dari 0!", $redirect_page);
    if ($barangnya <= 0) redirect_with_alert("Barang belum dipilih!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $cekstocksekarang = mysqli_query($conn, "SELECT stock FROM $stock_table_name WHERE idbarang='$barangnya' FOR UPDATE");
        if (!$cekstocksekarang || mysqli_num_rows($cekstocksekarang) == 0) throw new Exception("Barang tidak ditemukan.");
        $ambildatanya = mysqli_fetch_array($cekstocksekarang);
        $stocksekarang = $ambildatanya['stock'];

        if ($stocksekarang >= $qty) {
            $stockbaru = $stocksekarang - $qty;
            $addtokeluar = mysqli_query($conn, "INSERT INTO $keluar_table_name (idbarang, penerima, qty) VALUES ('$barangnya', '$penerima', '$qty')");
            if (!$addtokeluar) throw new Exception("Gagal mencatat barang keluar. Error: ".mysqli_error($conn));
            $updatestockkeluar = mysqli_query($conn, "UPDATE $stock_table_name SET stock='$stockbaru' WHERE idbarang='$barangnya'");
            if (!$updatestockkeluar) throw new Exception("Gagal update stok barang. Error: ".mysqli_error($conn));
            mysqli_commit($conn);
            header('location:' . $redirect_page); exit;
        } else {
            mysqli_rollback($conn); 
            redirect_with_alert("Stock saat ini tidak mencukupi (" . $stocksekarang . ")", $redirect_page);
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal: " . $e->getMessage(), $redirect_page);
    }
}

// Update info barang Pusat
if (isset($_POST['updatebarang'])) {
    $idb = intval($_POST['idb']);
    $namabarang = mysqli_real_escape_string($conn, $_POST['namabarang']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $stock_table_name = 'pusatstock';

    $redirect_page = '/stockmate/admin/stock.php'; 
    if (isset($_SESSION['roles']) && $_SESSION['roles'] == 'owner') {
         $redirect_page = '/stockmate/owner/index.php';  
    }

    $image_update_sql = "";
    $new_image_name = upload_image_file('file', $redirect_page);

    if ($new_image_name !== null) {
        $gambar_lama_q = mysqli_query($conn, "SELECT image FROM $stock_table_name WHERE idbarang='$idb'");
        if(!$gambar_lama_q) redirect_with_alert("Gagal query gambar lama: ".mysqli_error($conn), $redirect_page);
        $get_gambar_lama = mysqli_fetch_array($gambar_lama_q);
        delete_image_file($get_gambar_lama['image']);
        $escaped_new_image_name = mysqli_real_escape_string($conn, $new_image_name);
        $image_update_sql = ", image='$escaped_new_image_name'";
    }

    $update = mysqli_query($conn, "UPDATE $stock_table_name SET namabarang='$namabarang', deskripsi='$deskripsi' ".$image_update_sql." WHERE idbarang ='$idb'");
    if ($update) {
        header('location:' . $redirect_page); exit;
    } else {
        if ($new_image_name !== null) { delete_image_file($new_image_name); }
        redirect_with_alert("Gagal mengupdate barang! Error: ".mysqli_error($conn), $redirect_page);
    }
}

// Menghapus barang dari stock Pusat
if (isset($_POST['hapusbarang'])) {
    $idb = intval($_POST['idb']);
    $stock_table_name = 'pusatstock';
    $masuk_table_name = 'pusatmasuk';
    $keluar_table_name = 'pusatkeluar';

    $redirect_page = '/stockmate/admin/stock.php'; 
    if (isset($_SESSION['roles']) && $_SESSION['roles'] == 'owner') {
         $redirect_page = '/stockmate/owner/index.php';  
    }

    mysqli_begin_transaction($conn);
    try {
        $gambar_query = mysqli_query($conn, "SELECT image FROM $stock_table_name WHERE idbarang='$idb'");
        if (!$gambar_query) throw new Exception("Gagal query gambar. Error: ".mysqli_error($conn));
        $get = mysqli_fetch_array($gambar_query);

        $cek_pp = mysqli_query($conn, "SELECT idpp FROM pp WHERE idbarang='$idb' LIMIT 1");
        if (!$cek_pp) throw new Exception("Gagal cek relasi PP: ".mysqli_error($conn));

        if (mysqli_num_rows($cek_pp) > 0) {
             throw new Exception("Tidak bisa hapus barang karena terkait dengan Permintaan Barang (PP). Hapus atau selesaikan PP terkait terlebih dahulu.");
        }
        
        $hapus_masuk = mysqli_query($conn, "DELETE FROM $masuk_table_name WHERE idbarang='$idb'");
        if(!$hapus_masuk) throw new Exception("Gagal menghapus histori barang masuk terkait. Error: ".mysqli_error($conn));

        $hapus_keluar = mysqli_query($conn, "DELETE FROM $keluar_table_name WHERE idbarang='$idb'");
        if(!$hapus_keluar) throw new Exception("Gagal menghapus histori barang keluar terkait. Error: ".mysqli_error($conn));

        $hapus = mysqli_query($conn, "DELETE FROM $stock_table_name WHERE idbarang='$idb'");
        if (!$hapus) throw new Exception("Gagal menghapus barang dari database. Error: ".mysqli_error($conn));

        delete_image_file($get['image']);
        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Gagal menghapus barang: " . $e->getMessage(), $redirect_page);
    }
}

// Mengubah data barang masuk Pusat
if (isset($_POST['updatebarangmasuk'])) {
    $idb = intval($_POST['idb']);
    $idm = intval($_POST['idm']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $qty_baru = intval($_POST['qty']);
    $stock_table_name = 'pusatstock';
    $masuk_table_name = 'pusatmasuk';

    $redirect_page = '/stockmate/admin/masuk.php'; 
    if (isset($_SESSION['roles']) && $_SESSION['roles'] == 'owner') {
         $redirect_page = '/stockmate/owner/masuk.php';  
    }

    if ($qty_baru <= 0) redirect_with_alert("Quantity baru harus lebih dari 0!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $qtyskrg_query = mysqli_query($conn, "SELECT qty FROM $masuk_table_name WHERE idmasuk='$idm' FOR UPDATE");
        if (!$qtyskrg_query || mysqli_num_rows($qtyskrg_query) == 0) throw new Exception("Data barang masuk tidak ditemukan.");
        $qtynya = mysqli_fetch_array($qtyskrg_query);
        $qtyskrg = $qtynya['qty'];

        $lihatstock = mysqli_query($conn, "SELECT stock FROM $stock_table_name WHERE idbarang='$idb' FOR UPDATE");
        if (!$lihatstock || mysqli_num_rows($lihatstock) == 0) throw new Exception("Data stok barang tidak ditemukan.");
        $stocknya = mysqli_fetch_array($lihatstock);
        $stockskrg = $stocknya['stock'];

        $selisih = $qty_baru - $qtyskrg;
        $stockbaru = $stockskrg + $selisih;

        $updatenya = mysqli_query($conn, "UPDATE $masuk_table_name SET qty='$qty_baru', keterangan='$deskripsi' WHERE idmasuk='$idm'");
        if (!$updatenya) throw new Exception("Gagal update data barang masuk. Error: ".mysqli_error($conn));

        $updatestocknya = mysqli_query($conn, "UPDATE $stock_table_name SET stock='$stockbaru' WHERE idbarang='$idb'");
        if (!$updatestocknya) throw new Exception("Gagal update stok barang. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal: " . $e->getMessage(), $redirect_page);
    }
}

// Menghapus barang masuk Pusat
if (isset($_POST['hapusbarangmasuk'])) {
    $idb = intval($_POST['idb']);
    $qty_dihapus = intval($_POST['kty']);
    $idm = intval($_POST['idm']);
    $stock_table_name = 'pusatstock';
    $masuk_table_name = 'pusatmasuk';

    $redirect_page = '/stockmate/admin/masuk.php'; 
    if (isset($_SESSION['roles']) && $_SESSION['roles'] == 'owner') {
         $redirect_page = '/stockmate/owner/masuk.php';  
    }

    if ($qty_dihapus <= 0) redirect_with_alert("Quantity yang dihapus tidak valid!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $getdatastock = mysqli_query($conn, "SELECT stock FROM $stock_table_name WHERE idbarang='$idb' FOR UPDATE");
        if (!$getdatastock || mysqli_num_rows($getdatastock) == 0) throw new Exception("Data stok barang tidak ditemukan.");
        $data = mysqli_fetch_array($getdatastock);
        $stock = $data['stock'];

        $stockbaru = $stock - $qty_dihapus;
        if ($stockbaru < 0) { 
             throw new Exception("Operasi tidak valid. Stok akan menjadi negatif.");
        }

        $update = mysqli_query($conn, "UPDATE $stock_table_name SET stock='$stockbaru' WHERE idbarang='$idb'");
        if (!$update) throw new Exception("Gagal update stok barang. Error: ".mysqli_error($conn));

        $hapusdata = mysqli_query($conn, "DELETE FROM $masuk_table_name WHERE idmasuk='$idm'");
        if (!$hapusdata) throw new Exception("Gagal menghapus data barang masuk. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal: " . $e->getMessage(), $redirect_page);
    }
}

// Mengubah data barang keluar Pusat
if (isset($_POST['updatebarangkeluar'])) {
    $idb = intval($_POST['idb']);
    $idk = intval($_POST['idk']);
    $penerima = mysqli_real_escape_string($conn, $_POST['penerima']);
    $qty_baru = intval($_POST['qty']);
    $stock_table_name = 'pusatstock';
    $keluar_table_name = 'pusatkeluar';

    $redirect_page = '/stockmate/admin/keluar.php'; 
    if (isset($_SESSION['roles']) && $_SESSION['roles'] == 'owner') {
         $redirect_page = '/stockmate/owner/keluar.php';  
    }

    if ($qty_baru <= 0) redirect_with_alert("Quantity baru harus lebih dari 0!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $qtyskrg_query = mysqli_query($conn, "SELECT qty FROM $keluar_table_name WHERE idkeluar='$idk' FOR UPDATE");
        if (!$qtyskrg_query || mysqli_num_rows($qtyskrg_query) == 0) throw new Exception("Data barang keluar tidak ditemukan.");
        $qtynya = mysqli_fetch_array($qtyskrg_query);
        $qtyskrg = $qtynya['qty'];

        $lihatstock = mysqli_query($conn, "SELECT stock FROM $stock_table_name WHERE idbarang='$idb' FOR UPDATE");
        if (!$lihatstock || mysqli_num_rows($lihatstock) == 0) throw new Exception("Data stok barang tidak ditemukan.");
        $stocknya = mysqli_fetch_array($lihatstock);
        $stockskrg = $stocknya['stock'];

        $stockbaru = ($stockskrg + $qtyskrg) - $qty_baru;

        if ($stockbaru < 0) throw new Exception("Stok tidak mencukupi untuk jumlah keluar yang baru. Sisa stok akan menjadi: " . $stockbaru);

        $updatenya = mysqli_query($conn, "UPDATE $keluar_table_name SET qty='$qty_baru', penerima='$penerima' WHERE idkeluar='$idk'");
        if (!$updatenya) throw new Exception("Gagal update data barang keluar. Error: ".mysqli_error($conn));

        $updatestocknya = mysqli_query($conn, "UPDATE $stock_table_name SET stock='$stockbaru' WHERE idbarang='$idb'");
        if (!$updatestocknya) throw new Exception("Gagal update stok barang. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal: " . $e->getMessage(), $redirect_page);
    }
}

// Menghapus barang keluar Pusat
if (isset($_POST['hapusbarangkeluar'])) {
    $idb = intval($_POST['idb']);
    $qty_dikembalikan = intval($_POST['kty']);
    $idk = intval($_POST['idk']);
    $stock_table_name = 'pusatstock';
    $keluar_table_name = 'pusatkeluar';

    $redirect_page = '/stockmate/admin/keluar.php'; 
    if (isset($_SESSION['roles']) && $_SESSION['roles'] == 'owner') {
         $redirect_page = '/stockmate/owner/keluar.php';  
    }

    if ($qty_dikembalikan <= 0) redirect_with_alert("Quantity yang dihapus tidak valid!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $getdatastock = mysqli_query($conn, "SELECT stock FROM $stock_table_name WHERE idbarang='$idb' FOR UPDATE");
        if (!$getdatastock || mysqli_num_rows($getdatastock) == 0) throw new Exception("Data stok barang tidak ditemukan.");
        $data = mysqli_fetch_array($getdatastock);
        $stock = $data['stock'];

        $stockbaru = $stock + $qty_dikembalikan;

        $update = mysqli_query($conn, "UPDATE $stock_table_name SET stock='$stockbaru' WHERE idbarang='$idb'");
        if (!$update) throw new Exception("Gagal update stok barang. Error: ".mysqli_error($conn));

        $hapusdata = mysqli_query($conn, "DELETE FROM $keluar_table_name WHERE idkeluar='$idk'");
        if (!$hapusdata) throw new Exception("Gagal menghapus data barang keluar. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal: " . $e->getMessage(), $redirect_page);
    }
}

// ==============================================
// FUNGSI UNTUK TOKO PARAHITA
// ==============================================

// Menambah barang baru Parahita
if (isset($_POST['addnewbarang_parahita'])) {
    $stock_table_name = 'parahita_stock';
    $redirect_page = '/stockmate/adminParahita/index.php'; 

    $idbarang_pusat = isset($_POST['idbarang_pusat']) ? mysqli_real_escape_string($conn, $_POST['idbarang_pusat']) : null;
    $stock_parahita_to_insert = intval($_POST['stock']);

    $namabarang_final = "";
    $deskripsi_final = "";
    $image_from_pusat = null;

    if (!empty($idbarang_pusat)) {
        $query_get_pusat_item = mysqli_query($conn, "SELECT namabarang, deskripsi, image FROM pusatstock WHERE idbarang = '$idbarang_pusat'"); 
        if ($item_pusat = mysqli_fetch_assoc($query_get_pusat_item)) {
            $namabarang_final = mysqli_real_escape_string($conn, $item_pusat['namabarang']);
            $deskripsi_final = mysqli_real_escape_string($conn, $item_pusat['deskripsi']);
            $image_from_pusat = $item_pusat['image'];
        } else {
            redirect_with_alert("Gagal mengambil detail barang dari pusat!", $redirect_page);
            exit;
        }
    } else {
        redirect_with_alert("Silakan pilih barang dari Stok Pusat atau pastikan form input manual tersedia dan diisi.", $redirect_page);
        exit;
    }

    $cek_parahita = mysqli_query($conn, "SELECT namabarang FROM $stock_table_name WHERE namabarang ='$namabarang_final' LIMIT 1");
    if (!$cek_parahita) {
        redirect_with_alert("Error cek duplikat parahita: ".mysqli_error($conn), $redirect_page);
        exit;
    }
    if (mysqli_num_rows($cek_parahita) > 0) {
        redirect_with_alert("Nama barang '$namabarang_final' sudah terdaftar di Toko Parahita!", $redirect_page);
        exit;
    }

    $image_final_name = null;
    $new_image_uploaded = false;
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK && $_FILES['file']['size'] > 0) {
        $image_final_name = upload_image_file('file', $redirect_page);
        if ($image_final_name === null) exit;
        $new_image_uploaded = true;
    } elseif ($image_from_pusat && !empty($image_from_pusat)) {
        $path_to_pusat_image = __DIR__ . '/images/' . basename($image_from_pusat);
        if (file_exists($path_to_pusat_image)) {
            $image_final_name = basename($image_from_pusat); 
        }
    }
    $image_sql_value = ($image_final_name !== null) ? "'".mysqli_real_escape_string($conn, $image_final_name)."'" : "NULL";

    $query_insert_parahita = "INSERT INTO $stock_table_name (namabarang, deskripsi, stock, image) VALUES ('$namabarang_final', '$deskripsi_final', '$stock_parahita_to_insert', $image_sql_value)";
    $addtotable_parahita = mysqli_query($conn, $query_insert_parahita);

    if ($addtotable_parahita) {
        header('location:' . $redirect_page);
        exit;
    } else {
        if ($new_image_uploaded && $image_final_name !== null) delete_image_file($image_final_name);
        redirect_with_alert("Gagal menambahkan barang ke Parahita! Error: " . mysqli_error($conn), $redirect_page);
    }
}


// Menambah barang masuk Parahita (Dipicu dari new/admin/parahita_masuk.php oleh AdminPusat/Owner ATAU new/adminParahita/masuk.php oleh AdminParahita/KaryawanParahita)
if (isset($_POST['barangmasuk_parahita'])) {
    $idbarang_input = intval($_POST['barangnya']);
    $keterangan_input = mysqli_real_escape_string($conn, $_POST['penerima']); 
    $qty_input = intval($_POST['qty']);

    $current_role = $_SESSION['roles'] ?? '';
    $current_username = $_SESSION['username'] ?? '';
    $initiator_flag = $_POST['initiator'] ?? ''; 

    $redirect_page = '/stockmate/index.php'; 
    $is_admin_pusat_initiating_transfer = false;

    if ($initiator_flag == 'admin_pusat' && ($current_role == 'adminPusat' || $current_role == 'owner')) {
        $is_admin_pusat_initiating_transfer = true;
        $redirect_page = '/stockmate/admin/parahita_masuk.php'; 
    } elseif ($current_role == 'adminParahita') {
        $redirect_page = '/stockmate/adminParahita/masuk.php'; 
    } elseif ($current_role == 'karyawanParahita') {
        $redirect_page = '/stockmate/karyawanParahita/masuk.php'; 
    } else {
        redirect_with_alert("Aksi tidak diizinkan untuk role Anda atau initiator tidak valid.", '/stockmate/index.php'); 
        exit;
    }

    if ($qty_input <= 0) {
        redirect_with_alert("Quantity harus lebih dari 0!", $redirect_page);
        exit;
    }
    if ($idbarang_input <= 0) {
        redirect_with_alert("Barang belum dipilih!", $redirect_page);
        exit;
    }

    mysqli_begin_transaction($conn);
    try {
        $idbarang_parahita_final = null; 
        $nama_barang_target_parahita = ''; 

        if ($is_admin_pusat_initiating_transfer) {
            $cek_pusat_stock = mysqli_query($conn, "SELECT namabarang, deskripsi, image, stock FROM pusatstock WHERE idbarang='$idbarang_input' FOR UPDATE");
            if (!$cek_pusat_stock || mysqli_num_rows($cek_pusat_stock) == 0) {
                throw new Exception("Barang sumber (ID: {$idbarang_input}) tidak ditemukan di Stok Pusat.");
            }
            $data_pusat_stock = mysqli_fetch_assoc($cek_pusat_stock);
            $stock_pusat_sekarang = intval($data_pusat_stock['stock']);
            
            $nama_barang_target_parahita = $data_pusat_stock['namabarang']; 
            $deskripsi_target_parahita = $data_pusat_stock['deskripsi'];
            $image_target_parahita = $data_pusat_stock['image'];

            if ($stock_pusat_sekarang < $qty_input) {
                throw new Exception("Stok Pusat untuk barang '{$nama_barang_target_parahita}' (ID: {$idbarang_input}) tidak mencukupi. Stok tersedia: {$stock_pusat_sekarang}, Diminta: {$qty_input}.");
            }
            $stock_pusat_baru = $stock_pusat_sekarang - $qty_input;
            if (!mysqli_query($conn, "UPDATE pusatstock SET stock='$stock_pusat_baru' WHERE idbarang='$idbarang_input'")) {
                throw new Exception("Gagal update stok di Pusat. Error: " . mysqli_error($conn));
            }

            $penerima_untuk_pusat_keluar = "Toko 2 Parahita (Transfer by: " . $current_username . ")";
            if (!mysqli_query($conn, "INSERT INTO pusatkeluar (idbarang, penerima, qty) VALUES ('$idbarang_input', '".mysqli_real_escape_string($conn, $penerima_untuk_pusat_keluar)."', '$qty_input')")) {
                throw new Exception("Gagal mencatat barang keluar dari Pusat. Error: " . mysqli_error($conn));
            }

            $nama_barang_target_esc = mysqli_real_escape_string($conn, $nama_barang_target_parahita);
            $cek_parahita_item = mysqli_query($conn, "SELECT idbarang, stock FROM parahita_stock WHERE namabarang='$nama_barang_target_esc' FOR UPDATE");
            if (!$cek_parahita_item) throw new Exception("Error cek item di Parahita: ". mysqli_error($conn));

            if ($data_parahita_item = mysqli_fetch_assoc($cek_parahita_item)) {
                $idbarang_parahita_final = $data_parahita_item['idbarang'];
                $stock_parahita_sekarang = intval($data_parahita_item['stock']);
                $stock_parahita_baru = $stock_parahita_sekarang + $qty_input;
                if (!mysqli_query($conn, "UPDATE parahita_stock SET stock='$stock_parahita_baru' WHERE idbarang='$idbarang_parahita_final'")) {
                    throw new Exception("Gagal update stok Parahita (existing item). Error: " . mysqli_error($conn));
                }
            } else {
                $deskripsi_target_esc = mysqli_real_escape_string($conn, $deskripsi_target_parahita);
                $image_sql_val = $image_target_parahita ? "'".mysqli_real_escape_string($conn, $image_target_parahita)."'" : "NULL";
                $insert_parahita_stock = mysqli_query($conn, "INSERT INTO parahita_stock (namabarang, deskripsi, stock, image) VALUES ('$nama_barang_target_esc', '$deskripsi_target_esc', '$qty_input', $image_sql_val)");
                if (!$insert_parahita_stock) {
                    throw new Exception("Gagal insert item baru ke Parahita. Error: " . mysqli_error($conn));
                }
                $idbarang_parahita_final = mysqli_insert_id($conn);
            }

        } else { 
            $idbarang_parahita_final = $idbarang_input; 

            $cek_parahita_stock = mysqli_query($conn, "SELECT stock FROM parahita_stock WHERE idbarang='$idbarang_parahita_final' FOR UPDATE");
            if (!$cek_parahita_stock || mysqli_num_rows($cek_parahita_stock) == 0) {
                throw new Exception("Barang (ID: {$idbarang_parahita_final}) tidak ditemukan di Stok Parahita. Ini seharusnya untuk Admin/Karyawan Parahita.");
            }
            $data_parahita_stock = mysqli_fetch_assoc($cek_parahita_stock);
            $stock_parahita_sekarang = intval($data_parahita_stock['stock']);
            $stock_parahita_baru = $stock_parahita_sekarang + $qty_input;

            if (!mysqli_query($conn, "UPDATE parahita_stock SET stock='$stock_parahita_baru' WHERE idbarang='$idbarang_parahita_final'")) {
                throw new Exception("Gagal update stok Parahita. Error: " . mysqli_error($conn));
            }
        }

        if ($idbarang_parahita_final > 0) {
            $final_keterangan_parahita_masuk = $keterangan_input;
            if ($is_admin_pusat_initiating_transfer) {
                $final_keterangan_parahita_masuk = "Dari Pusat (via {$current_username}). Ket: {$keterangan_input}";
            }

            if (!mysqli_query($conn, "INSERT INTO parahita_masuk (idbarang, keterangan, qty) VALUES ('$idbarang_parahita_final', '".mysqli_real_escape_string($conn, $final_keterangan_parahita_masuk)."', '$qty_input')")) {
                throw new Exception("Gagal mencatat barang masuk ke Parahita. Error: " . mysqli_error($conn));
            }
        } else {
            throw new Exception("ID Barang Parahita tidak valid untuk pencatatan barang masuk.");
        }

        mysqli_commit($conn);
        $alert_message = $is_admin_pusat_initiating_transfer ? "Transfer barang ke Toko Parahita berhasil." : "Barang masuk ke Toko Parahita berhasil dicatat.";
        redirect_with_alert($alert_message, $redirect_page);
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal: " . $e->getMessage(), $redirect_page);
        exit;
    }
}


// Menambah barang keluar Parahita
if (isset($_POST['addbarangkeluar_parahita'])) {
    $barangnya = intval($_POST['barangnya']);
    $penerima = mysqli_real_escape_string($conn, $_POST['penerima']);
    $qty = intval($_POST['qty']);
    $stock_table_name = 'parahita_stock';
    $keluar_table_name = 'parahita_keluar';

    $current_role = $_SESSION['roles'] ?? '';
    if ($current_role == 'karyawanParahita') $redirect_page = '/stockmate/karyawanParahita/keluar.php'; 
    else $redirect_page = '/stockmate/adminParahita/keluar.php'; 

    if ($qty <= 0) redirect_with_alert("Quantity harus lebih dari 0!", $redirect_page);
    if ($barangnya <= 0) redirect_with_alert("Barang belum dipilih!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $cekstocksekarang = mysqli_query($conn, "SELECT stock FROM $stock_table_name WHERE idbarang='$barangnya' FOR UPDATE");
        if (!$cekstocksekarang || mysqli_num_rows($cekstocksekarang) == 0) throw new Exception("Barang Parahita tidak ditemukan.");
        $ambildatanya = mysqli_fetch_array($cekstocksekarang);
        $stocksekarang = $ambildatanya['stock'];

        if ($stocksekarang >= $qty) {
            $stockbaru = $stocksekarang - $qty;
            $addtokeluar = mysqli_query($conn, "INSERT INTO $keluar_table_name (idbarang, penerima, qty) VALUES ('$barangnya', '$penerima', '$qty')");
            if (!$addtokeluar) throw new Exception("Gagal mencatat barang keluar Parahita. Error: ".mysqli_error($conn));
            $updatestockkeluar = mysqli_query($conn, "UPDATE $stock_table_name SET stock='$stockbaru' WHERE idbarang='$barangnya'");
            if (!$updatestockkeluar) throw new Exception("Gagal update stok barang Parahita. Error: ".mysqli_error($conn));
            mysqli_commit($conn);
            header('location:' . $redirect_page); exit;
        } else {
            mysqli_rollback($conn);
            redirect_with_alert("Stock Parahita saat ini tidak mencukupi (" . $stocksekarang . ")", $redirect_page);
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal Parahita: " . $e->getMessage(), $redirect_page);
    }
}

// Update info barang Parahita
if (isset($_POST['updatebarang_parahita'])) {
    $idb = intval($_POST['idb']);
    $namabarang = mysqli_real_escape_string($conn, $_POST['namabarang']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $stock_table_name = 'parahita_stock';
    $redirect_page = '/stockmate/adminParahita/index.php'; 

    $image_update_sql = "";
    $new_image_name = upload_image_file('file', $redirect_page);

    if ($new_image_name !== null) {
        $gambar_lama_q = mysqli_query($conn, "SELECT image FROM $stock_table_name WHERE idbarang='$idb'");
        if(!$gambar_lama_q) redirect_with_alert("Gagal query gambar lama Parahita: ".mysqli_error($conn), $redirect_page);
        $get_gambar_lama = mysqli_fetch_array($gambar_lama_q);
        delete_image_file($get_gambar_lama['image']);
        $escaped_new_image_name = mysqli_real_escape_string($conn, $new_image_name);
        $image_update_sql = ", image='$escaped_new_image_name'";
    }

    $update = mysqli_query($conn, "UPDATE $stock_table_name SET namabarang='$namabarang', deskripsi='$deskripsi' ".$image_update_sql." WHERE idbarang ='$idb'");
    if ($update) {
        header('location:' . $redirect_page); exit;
    } else {
        if ($new_image_name !== null) { delete_image_file($new_image_name); }
        redirect_with_alert("Gagal mengupdate barang Parahita! Error: ".mysqli_error($conn), $redirect_page);
    }
}

// Menghapus barang dari stock Parahita
if (isset($_POST['hapusbarang_parahita'])) {
    $idb = intval($_POST['idb']);
    $stock_table_name = 'parahita_stock';
    $masuk_table_name = 'parahita_masuk';
    $keluar_table_name = 'parahita_keluar';
    $redirect_page = '/stockmate/adminParahita/index.php'; 

    mysqli_begin_transaction($conn);
    try {
        $gambar_query = mysqli_query($conn, "SELECT image FROM $stock_table_name WHERE idbarang='$idb'");
        if (!$gambar_query) throw new Exception("Gagal query gambar Parahita. Error: ".mysqli_error($conn));
        $get = mysqli_fetch_array($gambar_query);

        $hapus_masuk = mysqli_query($conn, "DELETE FROM $masuk_table_name WHERE idbarang='$idb'");
        if(!$hapus_masuk) throw new Exception("Gagal menghapus histori barang masuk terkait (Parahita). Error: ".mysqli_error($conn));

        $hapus_keluar = mysqli_query($conn, "DELETE FROM $keluar_table_name WHERE idbarang='$idb'");
        if(!$hapus_keluar) throw new Exception("Gagal menghapus histori barang keluar terkait (Parahita). Error: ".mysqli_error($conn));

        $hapus = mysqli_query($conn, "DELETE FROM $stock_table_name WHERE idbarang='$idb'");
        if (!$hapus) throw new Exception("Gagal menghapus barang Parahita dari database. Error: ".mysqli_error($conn));

        delete_image_file($get['image']);
        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Gagal menghapus barang Parahita: " . $e->getMessage(), $redirect_page);
    }
}

// Mengubah data barang masuk Parahita
if (isset($_POST['updatebarangmasuk_parahita'])) {
    $idb = intval($_POST['idb']);
    $idm = intval($_POST['idm']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $qty_baru = intval($_POST['qty']);
    $stock_table_name = 'parahita_stock';
    $masuk_table_name = 'parahita_masuk';
    $redirect_page = '/stockmate/adminParahita/masuk.php'; 

    if ($qty_baru <= 0) redirect_with_alert("Quantity baru harus lebih dari 0!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $qtyskrg_query = mysqli_query($conn, "SELECT qty FROM $masuk_table_name WHERE idmasuk='$idm' FOR UPDATE");
        if (!$qtyskrg_query || mysqli_num_rows($qtyskrg_query) == 0) throw new Exception("Data barang masuk Parahita tidak ditemukan.");
        $qtynya = mysqli_fetch_array($qtyskrg_query);
        $qtyskrg = $qtynya['qty'];

        $lihatstock = mysqli_query($conn, "SELECT stock FROM $stock_table_name WHERE idbarang='$idb' FOR UPDATE");
        if (!$lihatstock || mysqli_num_rows($lihatstock) == 0) throw new Exception("Data stok barang Parahita tidak ditemukan.");
        $stocknya = mysqli_fetch_array($lihatstock);
        $stockskrg = $stocknya['stock'];

        $selisih = $qty_baru - $qtyskrg;
        $stockbaru = $stockskrg + $selisih;

        $updatenya = mysqli_query($conn, "UPDATE $masuk_table_name SET qty='$qty_baru', keterangan='$deskripsi' WHERE idmasuk='$idm'");
        if (!$updatenya) throw new Exception("Gagal update data barang masuk Parahita. Error: ".mysqli_error($conn));

        $updatestocknya = mysqli_query($conn, "UPDATE $stock_table_name SET stock='$stockbaru' WHERE idbarang='$idb'");
        if (!$updatestocknya) throw new Exception("Gagal update stok barang Parahita. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal Parahita: " . $e->getMessage(), $redirect_page);
    }
}

// Menghapus barang masuk Parahita
if (isset($_POST['hapusbarangmasuk_parahita'])) {
    $idb = intval($_POST['idb']);
    $qty_dihapus = intval($_POST['kty']);
    $idm = intval($_POST['idm']);
    $stock_table_name = 'parahita_stock';
    $masuk_table_name = 'parahita_masuk';
    $redirect_page = '/stockmate/adminParahita/masuk.php'; 

    if ($qty_dihapus <= 0) redirect_with_alert("Quantity yang dihapus tidak valid!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $getdatastock = mysqli_query($conn, "SELECT stock FROM $stock_table_name WHERE idbarang='$idb' FOR UPDATE");
        if (!$getdatastock || mysqli_num_rows($getdatastock) == 0) throw new Exception("Data stok barang Parahita tidak ditemukan.");
        $data = mysqli_fetch_array($getdatastock);
        $stock = $data['stock'];

        $stockbaru = $stock - $qty_dihapus;
        if ($stockbaru < 0) throw new Exception("Operasi tidak valid. Stok akan menjadi negatif.");

        $update = mysqli_query($conn, "UPDATE $stock_table_name SET stock='$stockbaru' WHERE idbarang='$idb'");
        if (!$update) throw new Exception("Gagal update stok barang Parahita. Error: ".mysqli_error($conn));

        $hapusdata = mysqli_query($conn, "DELETE FROM $masuk_table_name WHERE idmasuk='$idm'");
        if (!$hapusdata) throw new Exception("Gagal menghapus data barang masuk Parahita. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal Parahita: " . $e->getMessage(), $redirect_page);
    }
}

// Mengubah data barang keluar Parahita
if (isset($_POST['updatebarangkeluar_parahita'])) {
    $idb = intval($_POST['idb']);
    $idk = intval($_POST['idk']);
    $penerima = mysqli_real_escape_string($conn, $_POST['penerima']);
    $qty_baru = intval($_POST['qty']);
    $stock_table_name = 'parahita_stock';
    $keluar_table_name = 'parahita_keluar';
    $redirect_page = '/stockmate/adminParahita/keluar.php'; 

    if ($qty_baru <= 0) redirect_with_alert("Quantity baru harus lebih dari 0!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $qtyskrg_query = mysqli_query($conn, "SELECT qty FROM $keluar_table_name WHERE idkeluar='$idk' FOR UPDATE");
        if (!$qtyskrg_query || mysqli_num_rows($qtyskrg_query) == 0) throw new Exception("Data barang keluar Parahita tidak ditemukan.");
        $qtynya = mysqli_fetch_array($qtyskrg_query);
        $qtyskrg = $qtynya['qty'];

        $lihatstock = mysqli_query($conn, "SELECT stock FROM $stock_table_name WHERE idbarang='$idb' FOR UPDATE");
        if (!$lihatstock || mysqli_num_rows($lihatstock) == 0) throw new Exception("Data stok barang Parahita tidak ditemukan.");
        $stocknya = mysqli_fetch_array($lihatstock);
        $stockskrg = $stocknya['stock'];

        $stockbaru = ($stockskrg + $qtyskrg) - $qty_baru;

        if ($stockbaru < 0) throw new Exception("Stok Parahita tidak mencukupi untuk jumlah keluar yang baru ($stockbaru).");

        $updatenya = mysqli_query($conn, "UPDATE $keluar_table_name SET qty='$qty_baru', penerima='$penerima' WHERE idkeluar='$idk'");
        if (!$updatenya) throw new Exception("Gagal update data barang keluar Parahita. Error: ".mysqli_error($conn));

        $updatestocknya = mysqli_query($conn, "UPDATE $stock_table_name SET stock='$stockbaru' WHERE idbarang='$idb'");
        if (!$updatestocknya) throw new Exception("Gagal update stok barang Parahita. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal Parahita: " . $e->getMessage(), $redirect_page);
    }
}

// Menghapus barang keluar Parahita
if (isset($_POST['hapusbarangkeluar_parahita'])) {
    $idb = intval($_POST['idb']);
    $qty_dikembalikan = intval($_POST['kty']);
    $idk = intval($_POST['idk']);
    $stock_table_name = 'parahita_stock';
    $keluar_table_name = 'parahita_keluar';
    $redirect_page = '/stockmate/adminParahita/keluar.php'; 

    if ($qty_dikembalikan <= 0) redirect_with_alert("Quantity yang dihapus tidak valid!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $getdatastock = mysqli_query($conn, "SELECT stock FROM $stock_table_name WHERE idbarang='$idb' FOR UPDATE");
        if (!$getdatastock || mysqli_num_rows($getdatastock) == 0) throw new Exception("Data stok barang Parahita tidak ditemukan.");
        $data = mysqli_fetch_array($getdatastock);
        $stock = $data['stock'];

        $stockbaru = $stock + $qty_dikembalikan;

        $update = mysqli_query($conn, "UPDATE $stock_table_name SET stock='$stockbaru' WHERE idbarang='$idb'");
        if (!$update) throw new Exception("Gagal update stok barang Parahita. Error: ".mysqli_error($conn));

        $hapusdata = mysqli_query($conn, "DELETE FROM $keluar_table_name WHERE idkeluar='$idk'");
        if (!$hapusdata) throw new Exception("Gagal menghapus data barang keluar Parahita. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal Parahita: " . $e->getMessage(), $redirect_page);
    }
}


// ==============================================
// FUNGSI UNTUK TOKO SERPONG
// ==============================================
// Menambah barang baru Serpong
if (isset($_POST['addnewbarang_serpong'])) {
    $stock_table_name = 'serpong_stock';
    $redirect_page = '/stockmate/adminSerpong/index.php'; 

    $idbarang_pusat = isset($_POST['idbarang_pusat']) ? mysqli_real_escape_string($conn, $_POST['idbarang_pusat']) : null;
    $stock_serpong = intval($_POST['stock']);

    $namabarang_final = "";
    $deskripsi_final = "";
    $image_from_pusat = null;

    if (!empty($idbarang_pusat)) {
        $query_get_pusat_item = mysqli_query($conn, "SELECT namabarang, deskripsi, image FROM pusatstock WHERE idbarang = '$idbarang_pusat'");
        if ($item_pusat = mysqli_fetch_assoc($query_get_pusat_item)) {
            $namabarang_final = mysqli_real_escape_string($conn, $item_pusat['namabarang']);
            $deskripsi_final = mysqli_real_escape_string($conn, $item_pusat['deskripsi']);
            $image_from_pusat = $item_pusat['image'];
        } else {
            redirect_with_alert("Gagal mengambil detail barang dari pusat!", $redirect_page);
            exit;
        }
    } else {
         redirect_with_alert("Silakan pilih barang dari Stok Pusat atau pastikan form input manual tersedia dan diisi.", $redirect_page);
        exit;
    }

    $cek_serpong = mysqli_query($conn, "SELECT namabarang FROM $stock_table_name WHERE namabarang ='$namabarang_final' LIMIT 1");
    if (!$cek_serpong) {
        redirect_with_alert("Error cek duplikat Serpong: ".mysqli_error($conn), $redirect_page);
        exit;
    }
    if (mysqli_num_rows($cek_serpong) > 0) {
        redirect_with_alert("Nama barang '$namabarang_final' sudah terdaftar di Toko Serpong!", $redirect_page);
        exit;
    }

    $image_final_name = null;
    $new_image_uploaded = false;
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK && $_FILES['file']['size'] > 0) {
        $image_final_name = upload_image_file('file', $redirect_page);
        if ($image_final_name === null) exit;
        $new_image_uploaded = true;
    } elseif ($image_from_pusat && !empty($image_from_pusat)) {
        $path_to_pusat_image = __DIR__ . '/images/' . basename($image_from_pusat);
        if (file_exists($path_to_pusat_image)) {
            $image_final_name = basename($image_from_pusat);
        }
    }
    $image_sql_value = ($image_final_name !== null) ? "'".mysqli_real_escape_string($conn, $image_final_name)."'" : "NULL";

    $query_insert_serpong = "INSERT INTO $stock_table_name (namabarang, deskripsi, stock, image) VALUES ('$namabarang_final', '$deskripsi_final', '$stock_serpong', $image_sql_value)";
    $addtotable_serpong = mysqli_query($conn, $query_insert_serpong);

    if ($addtotable_serpong) {
        header('location:' . $redirect_page);
        exit;
    } else {
        if ($new_image_uploaded && $image_final_name !== null) delete_image_file($image_final_name);
        redirect_with_alert("Gagal menambahkan barang ke Serpong! Error: " . mysqli_error($conn), $redirect_page);
    }
}

// Menambah barang masuk Serpong (Dipicu dari new/admin/serpong_masuk.php oleh AdminPusat/Owner ATAU new/adminSerpong/masuk.php oleh AdminSerpong/KaryawanSerpong)
if (isset($_POST['barangmasuk_serpong'])) {
    $idbarang_input = intval($_POST['barangnya']);
    $keterangan_input = mysqli_real_escape_string($conn, $_POST['penerima']); 
    $qty_input = intval($_POST['qty']);

    $current_role = $_SESSION['roles'] ?? '';
    $current_username = $_SESSION['username'] ?? '';
    $initiator_flag = $_POST['initiator'] ?? ''; 

    $redirect_page = '/stockmate/index.php'; 
    $is_admin_pusat_initiating_transfer = false;

    if ($initiator_flag == 'admin_pusat' && ($current_role == 'adminPusat' || $current_role == 'owner')) {
        $is_admin_pusat_initiating_transfer = true;
        $redirect_page = '/stockmate/admin/serpong_masuk.php'; 
    } elseif ($current_role == 'adminSerpong') {
        $redirect_page = '/stockmate/adminSerpong/masuk.php'; 
    } elseif ($current_role == 'karyawanSerpong') {
        $redirect_page = '/stockmate/karyawanSerpong/masuk.php'; 
    } else {
        redirect_with_alert("Aksi tidak diizinkan untuk role Anda atau initiator tidak valid (Serpong).", '/stockmate/index.php'); 
        exit;
    }

    if ($qty_input <= 0) {
        redirect_with_alert("Quantity harus lebih dari 0!", $redirect_page);
        exit;
    }
    if ($idbarang_input <= 0) {
        redirect_with_alert("Barang belum dipilih!", $redirect_page);
        exit;
    }

    mysqli_begin_transaction($conn);
    try {
        $idbarang_serpong_final = null; 
        $nama_barang_target_serpong = ''; 

        if ($is_admin_pusat_initiating_transfer) {
            $cek_pusat_stock = mysqli_query($conn, "SELECT namabarang, deskripsi, image, stock FROM pusatstock WHERE idbarang='$idbarang_input' FOR UPDATE");
            if (!$cek_pusat_stock || mysqli_num_rows($cek_pusat_stock) == 0) {
                throw new Exception("Barang sumber (ID: {$idbarang_input}) tidak ditemukan di Stok Pusat.");
            }
            $data_pusat_stock = mysqli_fetch_assoc($cek_pusat_stock);
            $stock_pusat_sekarang = intval($data_pusat_stock['stock']);
            
            $nama_barang_target_serpong = $data_pusat_stock['namabarang']; 
            $deskripsi_target_serpong = $data_pusat_stock['deskripsi'];
            $image_target_serpong = $data_pusat_stock['image'];

            if ($stock_pusat_sekarang < $qty_input) {
                throw new Exception("Stok Pusat untuk barang '{$nama_barang_target_serpong}' (ID: {$idbarang_input}) tidak mencukupi. Stok tersedia: {$stock_pusat_sekarang}, Diminta: {$qty_input}.");
            }
            $stock_pusat_baru = $stock_pusat_sekarang - $qty_input;
            if (!mysqli_query($conn, "UPDATE pusatstock SET stock='$stock_pusat_baru' WHERE idbarang='$idbarang_input'")) {
                throw new Exception("Gagal update stok di Pusat. Error: " . mysqli_error($conn));
            }

            $penerima_untuk_pusat_keluar = "Toko 3 Serpong (Transfer by: " . $current_username . ")";
            if (!mysqli_query($conn, "INSERT INTO pusatkeluar (idbarang, penerima, qty) VALUES ('$idbarang_input', '".mysqli_real_escape_string($conn, $penerima_untuk_pusat_keluar)."', '$qty_input')")) {
                throw new Exception("Gagal mencatat barang keluar dari Pusat. Error: " . mysqli_error($conn));
            }

            $nama_barang_target_esc = mysqli_real_escape_string($conn, $nama_barang_target_serpong);
            $cek_serpong_item = mysqli_query($conn, "SELECT idbarang, stock FROM serpong_stock WHERE namabarang='$nama_barang_target_esc' FOR UPDATE");
            if (!$cek_serpong_item) throw new Exception("Error cek item di Serpong: ". mysqli_error($conn));

            if ($data_serpong_item = mysqli_fetch_assoc($cek_serpong_item)) {
                $idbarang_serpong_final = $data_serpong_item['idbarang'];
                $stock_serpong_sekarang = intval($data_serpong_item['stock']);
                $stock_serpong_baru = $stock_serpong_sekarang + $qty_input;
                if (!mysqli_query($conn, "UPDATE serpong_stock SET stock='$stock_serpong_baru' WHERE idbarang='$idbarang_serpong_final'")) {
                    throw new Exception("Gagal update stok Serpong (existing item). Error: " . mysqli_error($conn));
                }
            } else {
                $deskripsi_target_esc = mysqli_real_escape_string($conn, $deskripsi_target_serpong);
                $image_sql_val = $image_target_serpong ? "'".mysqli_real_escape_string($conn, $image_target_serpong)."'" : "NULL";
                $insert_serpong_stock = mysqli_query($conn, "INSERT INTO serpong_stock (namabarang, deskripsi, stock, image) VALUES ('$nama_barang_target_esc', '$deskripsi_target_esc', '$qty_input', $image_sql_val)");
                if (!$insert_serpong_stock) {
                    throw new Exception("Gagal insert item baru ke Serpong. Error: " . mysqli_error($conn));
                }
                $idbarang_serpong_final = mysqli_insert_id($conn);
            }

        } else { 
            $idbarang_serpong_final = $idbarang_input; 

            $cek_serpong_stock = mysqli_query($conn, "SELECT stock FROM serpong_stock WHERE idbarang='$idbarang_serpong_final' FOR UPDATE");
            if (!$cek_serpong_stock || mysqli_num_rows($cek_serpong_stock) == 0) {
                throw new Exception("Barang (ID: {$idbarang_serpong_final}) tidak ditemukan di Stok Serpong. Ini seharusnya untuk Admin/Karyawan Serpong.");
            }
            $data_serpong_stock = mysqli_fetch_assoc($cek_serpong_stock);
            $stock_serpong_sekarang = intval($data_serpong_stock['stock']);
            $stock_serpong_baru = $stock_serpong_sekarang + $qty_input;

            if (!mysqli_query($conn, "UPDATE serpong_stock SET stock='$stock_serpong_baru' WHERE idbarang='$idbarang_serpong_final'")) {
                throw new Exception("Gagal update stok Serpong. Error: " . mysqli_error($conn));
            }
        }

        if ($idbarang_serpong_final > 0) {
            $final_keterangan_serpong_masuk = $keterangan_input;
            if ($is_admin_pusat_initiating_transfer) {
                $final_keterangan_serpong_masuk = "Dari Pusat (via {$current_username}). Ket: {$keterangan_input}";
            }

            if (!mysqli_query($conn, "INSERT INTO serpong_masuk (idbarang, keterangan, qty) VALUES ('$idbarang_serpong_final', '".mysqli_real_escape_string($conn, $final_keterangan_serpong_masuk)."', '$qty_input')")) {
                throw new Exception("Gagal mencatat barang masuk ke Serpong. Error: " . mysqli_error($conn));
            }
        } else {
            throw new Exception("ID Barang Serpong tidak valid untuk pencatatan barang masuk.");
        }

        mysqli_commit($conn);
        $alert_message = $is_admin_pusat_initiating_transfer ? "Transfer barang ke Toko Serpong berhasil." : "Barang masuk ke Toko Serpong berhasil dicatat.";
        redirect_with_alert($alert_message, $redirect_page);
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal: " . $e->getMessage(), $redirect_page);
        exit;
    }
}

// Menambah barang keluar Serpong
if (isset($_POST['addbarangkeluar_serpong'])) {
    $barangnya = intval($_POST['barangnya']);
    $penerima = mysqli_real_escape_string($conn, $_POST['penerima']);
    $qty = intval($_POST['qty']);
    $stock_table_name = 'serpong_stock';
    $keluar_table_name = 'serpong_keluar';

    $current_role = $_SESSION['roles'] ?? '';
    if ($current_role == 'karyawanSerpong') $redirect_page = '/stockmate/karyawanSerpong/keluar.php'; 
    else $redirect_page = '/stockmate/adminSerpong/keluar.php'; 

    if ($qty <= 0) redirect_with_alert("Quantity harus lebih dari 0!", $redirect_page);
    if ($barangnya <= 0) redirect_with_alert("Barang belum dipilih!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $cekstocksekarang = mysqli_query($conn, "SELECT stock FROM $stock_table_name WHERE idbarang='$barangnya' FOR UPDATE");
        if (!$cekstocksekarang || mysqli_num_rows($cekstocksekarang) == 0) throw new Exception("Barang Serpong tidak ditemukan.");
        $ambildatanya = mysqli_fetch_array($cekstocksekarang);
        $stocksekarang = $ambildatanya['stock'];

        if ($stocksekarang >= $qty) {
            $stockbaru = $stocksekarang - $qty;
            $addtokeluar = mysqli_query($conn, "INSERT INTO $keluar_table_name (idbarang, penerima, qty) VALUES ('$barangnya', '$penerima', '$qty')");
            if (!$addtokeluar) throw new Exception("Gagal mencatat barang keluar Serpong. Error: ".mysqli_error($conn));
            $updatestockkeluar = mysqli_query($conn, "UPDATE $stock_table_name SET stock='$stockbaru' WHERE idbarang='$barangnya'");
            if (!$updatestockkeluar) throw new Exception("Gagal update stok barang Serpong. Error: ".mysqli_error($conn));
            mysqli_commit($conn);
            header('location:' . $redirect_page); exit;
        } else {
            mysqli_rollback($conn);
            redirect_with_alert("Stock Serpong saat ini tidak mencukupi (" . $stocksekarang . ")", $redirect_page);
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal Serpong: " . $e->getMessage(), $redirect_page);
    }
}
// Update info barang Serpong
if (isset($_POST['updatebarang_serpong'])) {
    $idb = intval($_POST['idb']);
    $namabarang = mysqli_real_escape_string($conn, $_POST['namabarang']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $stock_table_name = 'serpong_stock';
    $redirect_page = '/stockmate/adminSerpong/index.php'; 

    $image_update_sql = "";
    $new_image_name = upload_image_file('file', $redirect_page);

    if ($new_image_name !== null) {
        $gambar_lama_q = mysqli_query($conn, "SELECT image FROM $stock_table_name WHERE idbarang='$idb'");
        if(!$gambar_lama_q) redirect_with_alert("Gagal query gambar lama Serpong: ".mysqli_error($conn), $redirect_page);
        $get_gambar_lama = mysqli_fetch_array($gambar_lama_q);
        delete_image_file($get_gambar_lama['image']);
        $escaped_new_image_name = mysqli_real_escape_string($conn, $new_image_name);
        $image_update_sql = ", image='$escaped_new_image_name'";
    }

    $update = mysqli_query($conn, "UPDATE $stock_table_name SET namabarang='$namabarang', deskripsi='$deskripsi' ".$image_update_sql." WHERE idbarang ='$idb'");
    if ($update) {
        header('location:' . $redirect_page); exit;
    } else {
        if ($new_image_name !== null) { delete_image_file($new_image_name); }
        redirect_with_alert("Gagal mengupdate barang Serpong! Error: ".mysqli_error($conn), $redirect_page);
    }
}
// Menghapus barang dari stock Serpong
if (isset($_POST['hapusbarang_serpong'])) {
    $idb = intval($_POST['idb']);
    $stock_table_name = 'serpong_stock';
    $masuk_table_name = 'serpong_masuk';
    $keluar_table_name = 'serpong_keluar';
    $redirect_page = '/stockmate/adminSerpong/index.php'; 

    mysqli_begin_transaction($conn);
    try {
        $gambar_query = mysqli_query($conn, "SELECT image FROM $stock_table_name WHERE idbarang='$idb'");
        if (!$gambar_query) throw new Exception("Gagal query gambar Serpong. Error: ".mysqli_error($conn));
        $get = mysqli_fetch_array($gambar_query);

        $hapus_masuk = mysqli_query($conn, "DELETE FROM $masuk_table_name WHERE idbarang='$idb'");
        if(!$hapus_masuk) throw new Exception("Gagal menghapus histori barang masuk terkait (Serpong). Error: ".mysqli_error($conn));

        $hapus_keluar = mysqli_query($conn, "DELETE FROM $keluar_table_name WHERE idbarang='$idb'");
        if(!$hapus_keluar) throw new Exception("Gagal menghapus histori barang keluar terkait (Serpong). Error: ".mysqli_error($conn));

        $hapus = mysqli_query($conn, "DELETE FROM $stock_table_name WHERE idbarang='$idb'");
        if (!$hapus) throw new Exception("Gagal menghapus barang Serpong dari database. Error: ".mysqli_error($conn));

        delete_image_file($get['image']);
        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Gagal menghapus barang Serpong: " . $e->getMessage(), $redirect_page);
    }
}
// Mengubah data barang masuk Serpong
if (isset($_POST['updatebarangmasuk_serpong'])) {
    $idb = intval($_POST['idb']);
    $idm = intval($_POST['idm']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $qty_baru = intval($_POST['qty']);
    $stock_table_name = 'serpong_stock';
    $masuk_table_name = 'serpong_masuk';
    $redirect_page = '/stockmate/adminSerpong/masuk.php'; 

    if ($qty_baru <= 0) redirect_with_alert("Quantity baru harus lebih dari 0!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $qtyskrg_query = mysqli_query($conn, "SELECT qty FROM $masuk_table_name WHERE idmasuk='$idm' FOR UPDATE");
        if (!$qtyskrg_query || mysqli_num_rows($qtyskrg_query) == 0) throw new Exception("Data barang masuk Serpong tidak ditemukan.");
        $qtynya = mysqli_fetch_array($qtyskrg_query);
        $qtyskrg = $qtynya['qty'];

        $lihatstock = mysqli_query($conn, "SELECT stock FROM $stock_table_name WHERE idbarang='$idb' FOR UPDATE");
        if (!$lihatstock || mysqli_num_rows($lihatstock) == 0) throw new Exception("Data stok barang Serpong tidak ditemukan.");
        $stocknya = mysqli_fetch_array($lihatstock);
        $stockskrg = $stocknya['stock'];

        $selisih = $qty_baru - $qtyskrg;
        $stockbaru = $stockskrg + $selisih;

        $updatenya = mysqli_query($conn, "UPDATE $masuk_table_name SET qty='$qty_baru', keterangan='$deskripsi' WHERE idmasuk='$idm'");
        if (!$updatenya) throw new Exception("Gagal update data barang masuk Serpong. Error: ".mysqli_error($conn));

        $updatestocknya = mysqli_query($conn, "UPDATE $stock_table_name SET stock='$stockbaru' WHERE idbarang='$idb'");
        if (!$updatestocknya) throw new Exception("Gagal update stok barang Serpong. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal Serpong: " . $e->getMessage(), $redirect_page);
    }
}
// Menghapus barang masuk Serpong
if (isset($_POST['hapusbarangmasuk_serpong'])) {
    $idb = intval($_POST['idb']);
    $qty_dihapus = intval($_POST['kty']);
    $idm = intval($_POST['idm']);
    $stock_table_name = 'serpong_stock';
    $masuk_table_name = 'serpong_masuk';
    $redirect_page = '/stockmate/adminSerpong/masuk.php'; 

    if ($qty_dihapus <= 0) redirect_with_alert("Quantity yang dihapus tidak valid!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $getdatastock = mysqli_query($conn, "SELECT stock FROM $stock_table_name WHERE idbarang='$idb' FOR UPDATE");
        if (!$getdatastock || mysqli_num_rows($getdatastock) == 0) throw new Exception("Data stok barang Serpong tidak ditemukan.");
        $data = mysqli_fetch_array($getdatastock);
        $stock = $data['stock'];

        $stockbaru = $stock - $qty_dihapus;
        if ($stockbaru < 0) throw new Exception("Operasi tidak valid. Stok akan menjadi negatif.");

        $update = mysqli_query($conn, "UPDATE $stock_table_name SET stock='$stockbaru' WHERE idbarang='$idb'");
        if (!$update) throw new Exception("Gagal update stok barang Serpong. Error: ".mysqli_error($conn));

        $hapusdata = mysqli_query($conn, "DELETE FROM $masuk_table_name WHERE idmasuk='$idm'");
        if (!$hapusdata) throw new Exception("Gagal menghapus data barang masuk Serpong. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal Serpong: " . $e->getMessage(), $redirect_page);
    }
}
// Mengubah data barang keluar Serpong
if (isset($_POST['updatebarangkeluar_serpong'])) {
    $idb = intval($_POST['idb']);
    $idk = intval($_POST['idk']);
    $penerima = mysqli_real_escape_string($conn, $_POST['penerima']);
    $qty_baru = intval($_POST['qty']);
    $stock_table_name = 'serpong_stock';
    $keluar_table_name = 'serpong_keluar';
    $redirect_page = '/stockmate/adminSerpong/keluar.php'; 

    if ($qty_baru <= 0) redirect_with_alert("Quantity baru harus lebih dari 0!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $qtyskrg_query = mysqli_query($conn, "SELECT qty FROM $keluar_table_name WHERE idkeluar='$idk' FOR UPDATE");
        if (!$qtyskrg_query || mysqli_num_rows($qtyskrg_query) == 0) throw new Exception("Data barang keluar Serpong tidak ditemukan.");
        $qtynya = mysqli_fetch_array($qtyskrg_query);
        $qtyskrg = $qtynya['qty'];

        $lihatstock = mysqli_query($conn, "SELECT stock FROM $stock_table_name WHERE idbarang='$idb' FOR UPDATE");
        if (!$lihatstock || mysqli_num_rows($lihatstock) == 0) throw new Exception("Data stok barang Serpong tidak ditemukan.");
        $stocknya = mysqli_fetch_array($lihatstock);
        $stockskrg = $stocknya['stock'];

        $stockbaru = ($stockskrg + $qtyskrg) - $qty_baru;

        if ($stockbaru < 0) throw new Exception("Stok Serpong tidak mencukupi untuk jumlah keluar yang baru ($stockbaru).");

        $updatenya = mysqli_query($conn, "UPDATE $keluar_table_name SET qty='$qty_baru', penerima='$penerima' WHERE idkeluar='$idk'");
        if (!$updatenya) throw new Exception("Gagal update data barang keluar Serpong. Error: ".mysqli_error($conn));

        $updatestocknya = mysqli_query($conn, "UPDATE $stock_table_name SET stock='$stockbaru' WHERE idbarang='$idb'");
        if (!$updatestocknya) throw new Exception("Gagal update stok barang Serpong. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal Serpong: " . $e->getMessage(), $redirect_page);
    }
}
// Menghapus barang keluar Serpong
if (isset($_POST['hapusbarangkeluar_serpong'])) {
    $idb = intval($_POST['idb']);
    $qty_dikembalikan = intval($_POST['kty']);
    $idk = intval($_POST['idk']);
    $stock_table_name = 'serpong_stock';
    $keluar_table_name = 'serpong_keluar';
    $redirect_page = '/stockmate/adminSerpong/keluar.php'; 

    if ($qty_dikembalikan <= 0) redirect_with_alert("Quantity yang dihapus tidak valid!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $getdatastock = mysqli_query($conn, "SELECT stock FROM $stock_table_name WHERE idbarang='$idb' FOR UPDATE");
        if (!$getdatastock || mysqli_num_rows($getdatastock) == 0) throw new Exception("Data stok barang Serpong tidak ditemukan.");
        $data = mysqli_fetch_array($getdatastock);
        $stock = $data['stock'];

        $stockbaru = $stock + $qty_dikembalikan;

        $update = mysqli_query($conn, "UPDATE $stock_table_name SET stock='$stockbaru' WHERE idbarang='$idb'");
        if (!$update) throw new Exception("Gagal update stok barang Serpong. Error: ".mysqli_error($conn));

        $hapusdata = mysqli_query($conn, "DELETE FROM $keluar_table_name WHERE idkeluar='$idk'");
        if (!$hapusdata) throw new Exception("Gagal menghapus data barang keluar Serpong. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal Serpong: " . $e->getMessage(), $redirect_page);
    }
}


// ===================================================================================
// FUNGSI UNTUK CABANG/TOKO BARU (DIKELOLA OLEH USER DENGAN ROLE 'admin' atau 'karyawan' generik)
// ===================================================================================

// Menambah barang baru untuk Cabang Dinamis
if (isset($_POST['addnewbarang_dynamic_branch'])) {
    $acting_username = $_SESSION['username'] ?? null;
    $acting_role = $_SESSION['roles'] ?? null;

    if (!$acting_username || !in_array($acting_role, ['admin', 'karyawan'])) { 
        redirect_with_alert("Akses ditolak atau sesi tidak valid.", '/stockmate/index.php'); 
        exit;
    }
    
    $specific_usernames = ['adminPusat', 'karyawanPusat', 'adminParahita', 'karyawanParahita', 'adminSerpong', 'karyawanSerpong', 'owner'];
    if (in_array($acting_username, $specific_usernames)) {
        redirect_with_alert("Operasi ini tidak untuk pengguna dengan role spesifik.", '/stockmate/index.php'); 
        exit;
    }

    $table_prefix_dynamic = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $acting_username));
    if (empty($table_prefix_dynamic)) {
        redirect_with_alert("Nama pengguna tidak valid untuk operasi tabel.", '/stockmate/index.php'); 
        exit;
    }

    $stock_table_name = $table_prefix_dynamic . '_stock';
    $redirect_page = ($acting_role == 'admin') ? "/stockmate/adminBarU/index.php" : "/stockmate/karyawanBaru/index.php"; 


    $idbarang_pusat = isset($_POST['idbarang_pusat']) ? mysqli_real_escape_string($conn, $_POST['idbarang_pusat']) : null;
    $stock_input = intval($_POST['stock']);

    $namabarang_final = "";
    $deskripsi_final = "";
    $image_from_pusat = null;

    if (!empty($idbarang_pusat)) {
        $query_get_pusat_item = mysqli_query($conn, "SELECT namabarang, deskripsi, image FROM pusatstock WHERE idbarang = '$idbarang_pusat'");
        if ($item_pusat = mysqli_fetch_assoc($query_get_pusat_item)) {
            $namabarang_final = mysqli_real_escape_string($conn, $item_pusat['namabarang']);
            $deskripsi_final = mysqli_real_escape_string($conn, $item_pusat['deskripsi']);
            $image_from_pusat = $item_pusat['image'];
        } else {
            redirect_with_alert("Gagal mengambil detail barang dari stok pusat!", $redirect_page);
            exit;
        }
    } else {
        $namabarang_final = mysqli_real_escape_string($conn, $_POST['namabarang_manual'] ?? ''); 
        $deskripsi_final = mysqli_real_escape_string($conn, $_POST['deskripsi_manual'] ?? ''); 
        
        if (empty($namabarang_final) || empty($deskripsi_final)) {
            redirect_with_alert("Nama barang dan deskripsi manual wajib diisi jika tidak memilih dari stok pusat.", $redirect_page);
            exit;
        }
    }

    $cek_dynamic_branch_stock = mysqli_query($conn, "SELECT namabarang FROM `{$stock_table_name}` WHERE namabarang ='$namabarang_final' LIMIT 1");
    if (!$cek_dynamic_branch_stock) {
        redirect_with_alert("Error cek duplikat di toko {$acting_username}: ".mysqli_error($conn), $redirect_page);
        exit;
    }
    if (mysqli_num_rows($cek_dynamic_branch_stock) > 0) {
        redirect_with_alert("Nama barang '$namabarang_final' sudah terdaftar di toko Anda!", $redirect_page);
        exit;
    }

    $image_final_to_db = null;
    $new_image_uploaded_for_dynamic = false;
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK && $_FILES['file']['size'] > 0) {
        $image_final_to_db = upload_image_file('file', $redirect_page);
        if ($image_final_to_db === null) exit;
        $new_image_uploaded_for_dynamic = true;
    } elseif ($image_from_pusat && !empty($image_from_pusat)) {
        $path_to_pusat_image = __DIR__ . '/images/' . basename($image_from_pusat);
        if (file_exists($path_to_pusat_image)) {
            $image_final_to_db = basename($image_from_pusat);
        }
    }
    $image_sql_value = ($image_final_to_db !== null) ? "'".mysqli_real_escape_string($conn, $image_final_to_db)."'" : "NULL";

    $query_insert_dynamic_branch = "INSERT INTO `{$stock_table_name}` (namabarang, deskripsi, stock, image) VALUES ('$namabarang_final', '$deskripsi_final', '$stock_input', $image_sql_value)";
    $addtotable_dynamic_branch = mysqli_query($conn, $query_insert_dynamic_branch);

    if ($addtotable_dynamic_branch) {
        redirect_with_alert("Barang berhasil ditambahkan ke toko Anda!", $redirect_page);
        exit;
    } else {
        if ($new_image_uploaded_for_dynamic && $image_final_to_db !== null) delete_image_file($image_final_to_db);
        redirect_with_alert("Gagal menambahkan barang ke toko Anda! Error: " . mysqli_error($conn), $redirect_page);
        exit;
    }
}


// Menambah barang masuk untuk Cabang Dinamis
if (isset($_POST['barangmasuk_dynamic_branch'])) {
    $acting_username = $_SESSION['username'] ?? null;
    $acting_role = $_SESSION['roles'] ?? null;

    if (!$acting_username || !in_array($acting_role, ['admin', 'karyawan'])) {
        redirect_with_alert("Akses ditolak atau sesi tidak valid.", '/stockmate/index.php'); exit; 
    }
    $specific_usernames = ['adminPusat', 'karyawanPusat', 'adminParahita', 'karyawanParahita', 'adminSerpong', 'karyawanSerpong', 'owner'];
    if (in_array($acting_username, $specific_usernames)) {
        redirect_with_alert("Operasi ini tidak untuk pengguna dengan role spesifik.", '/stockmate/index.php'); exit; 
    }

    $table_prefix_dynamic = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $acting_username));
    if (empty($table_prefix_dynamic)) {
        redirect_with_alert("Nama pengguna tidak valid untuk operasi tabel.", '/stockmate/index.php'); exit; 
    }

    $stock_table_name = $table_prefix_dynamic . '_stock';
    $masuk_table_name = $table_prefix_dynamic . '_masuk';
    $redirect_page = ($acting_role == 'admin') ? "/stockmate/adminBaru/masuk.php" : "/stockmate/karyawanBaru/masuk.php"; 

    $barangnya = intval($_POST['barangnya']);
    $penerima = mysqli_real_escape_string($conn, $_POST['penerima']);
    $qty = intval($_POST['qty']);

    if ($qty <= 0) redirect_with_alert("Quantity harus lebih dari 0!", $redirect_page);
    if ($barangnya <= 0) redirect_with_alert("Barang belum dipilih!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $cekstocksekarang = mysqli_query($conn, "SELECT stock FROM `{$stock_table_name}` WHERE idbarang='$barangnya' FOR UPDATE");
        if (!$cekstocksekarang || mysqli_num_rows($cekstocksekarang) == 0) throw new Exception("Barang di toko {$acting_username} tidak ditemukan.");
        $ambildatanya = mysqli_fetch_array($cekstocksekarang);
        $stocksekarang = $ambildatanya['stock'];
        $stockbaru = $stocksekarang + $qty;

        $addtomasuk = mysqli_query($conn, "INSERT INTO `{$masuk_table_name}` (idbarang, keterangan, qty) VALUES ('$barangnya', '$penerima', '$qty')");
        if (!$addtomasuk) throw new Exception("Gagal mencatat barang masuk toko {$acting_username}. Error: ".mysqli_error($conn));

        $updatestockmasuk = mysqli_query($conn, "UPDATE `{$stock_table_name}` SET stock='$stockbaru' WHERE idbarang='$barangnya'");
        if (!$updatestockmasuk) throw new Exception("Gagal update stok barang toko {$acting_username}. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal Toko {$acting_username}: " . $e->getMessage(), $redirect_page);
    }
}

// Menambah barang keluar untuk Cabang Dinamis
if (isset($_POST['addbarangkeluar_dynamic_branch'])) {
    $acting_username = $_SESSION['username'] ?? null;
    $acting_role = $_SESSION['roles'] ?? null;

    if (!$acting_username || !in_array($acting_role, ['admin', 'karyawan'])) {
        redirect_with_alert("Akses ditolak atau sesi tidak valid.", '/stockmate/index.php'); exit; 
    }
    $specific_usernames = ['adminPusat', 'karyawanPusat', 'adminParahita', 'karyawanParahita', 'adminSerpong', 'karyawanSerpong', 'owner'];
    if (in_array($acting_username, $specific_usernames)) {
        redirect_with_alert("Operasi ini tidak untuk pengguna dengan role spesifik.", '/stockmate/index.php'); exit; 
    }
    
    $table_prefix_dynamic = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $acting_username));
    if (empty($table_prefix_dynamic)) {
        redirect_with_alert("Nama pengguna tidak valid untuk operasi tabel.", '/stockmate/index.php'); exit; 
    }

    $stock_table_name = $table_prefix_dynamic . '_stock';
    $keluar_table_name = $table_prefix_dynamic . '_keluar';
    $redirect_page = ($acting_role == 'admin') ? "/stockmate/adminBaru/keluar.php" : "/stockmate/karyawanBaru/keluar.php"; 

    $barangnya = intval($_POST['barangnya']);
    $penerima = mysqli_real_escape_string($conn, $_POST['penerima']);
    $qty = intval($_POST['qty']);

    if ($qty <= 0) redirect_with_alert("Quantity harus lebih dari 0!", $redirect_page);
    if ($barangnya <= 0) redirect_with_alert("Barang belum dipilih!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $cekstocksekarang = mysqli_query($conn, "SELECT stock FROM `{$stock_table_name}` WHERE idbarang='$barangnya' FOR UPDATE");
        if (!$cekstocksekarang || mysqli_num_rows($cekstocksekarang) == 0) throw new Exception("Barang di toko {$acting_username} tidak ditemukan.");
        $ambildatanya = mysqli_fetch_array($cekstocksekarang);
        $stocksekarang = $ambildatanya['stock'];

        if ($stocksekarang >= $qty) {
            $stockbaru = $stocksekarang - $qty;
            $addtokeluar = mysqli_query($conn, "INSERT INTO `{$keluar_table_name}` (idbarang, penerima, qty) VALUES ('$barangnya', '$penerima', '$qty')");
            if (!$addtokeluar) throw new Exception("Gagal mencatat barang keluar toko {$acting_username}. Error: ".mysqli_error($conn));
            $updatestockkeluar = mysqli_query($conn, "UPDATE `{$stock_table_name}` SET stock='$stockbaru' WHERE idbarang='$barangnya'");
            if (!$updatestockkeluar) throw new Exception("Gagal update stok barang toko {$acting_username}. Error: ".mysqli_error($conn));
            mysqli_commit($conn);
            header('location:' . $redirect_page); exit;
        } else {
            mysqli_rollback($conn);
            redirect_with_alert("Stock toko {$acting_username} saat ini tidak mencukupi (" . $stocksekarang . ")", $redirect_page);
        }
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal Toko {$acting_username}: " . $e->getMessage(), $redirect_page);
    }
}

// Update info barang untuk Cabang Dinamis (Hanya oleh Admin Cabang Dinamis)
if (isset($_POST['updatebarang_dynamic_branch'])) {
    $acting_username = $_SESSION['username'] ?? null;
    $acting_role = $_SESSION['roles'] ?? null;

    if (!$acting_username || $acting_role != 'admin') { 
        redirect_with_alert("Akses ditolak atau sesi tidak valid.", '/stockmate/index.php'); exit; 
    }
    $specific_usernames = ['adminPusat', 'adminParahita', 'adminSerpong', 'owner']; 
    if (in_array($acting_username, $specific_usernames)) {
        redirect_with_alert("Operasi ini tidak untuk pengguna dengan role spesifik.", '/stockmate/index.php'); exit; 
    }

    $table_prefix_dynamic = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $acting_username));
    if (empty($table_prefix_dynamic)) {
        redirect_with_alert("Nama pengguna tidak valid untuk operasi tabel.", '/stockmate/index.php'); exit; 
    }
    
    $stock_table_name = $table_prefix_dynamic . '_stock';
    $redirect_page = "/stockmate/adminBaru/index.php";  

    $idb = intval($_POST['idb']);
    $namabarang = mysqli_real_escape_string($conn, $_POST['namabarang']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    $image_update_sql = "";
    $new_image_name = upload_image_file('file', $redirect_page);

    if ($new_image_name !== null) {
        $gambar_lama_q = mysqli_query($conn, "SELECT image FROM `{$stock_table_name}` WHERE idbarang='$idb'");
        if(!$gambar_lama_q) redirect_with_alert("Gagal query gambar lama {$acting_username}: ".mysqli_error($conn), $redirect_page);
        $get_gambar_lama = mysqli_fetch_array($gambar_lama_q);
        delete_image_file($get_gambar_lama['image']);
        $escaped_new_image_name = mysqli_real_escape_string($conn, $new_image_name);
        $image_update_sql = ", image='$escaped_new_image_name'";
    }

    $update = mysqli_query($conn, "UPDATE `{$stock_table_name}` SET namabarang='$namabarang', deskripsi='$deskripsi' ".$image_update_sql." WHERE idbarang ='$idb'");
    if ($update) {
        header('location:' . $redirect_page); exit;
    } else {
        if ($new_image_name !== null) { delete_image_file($new_image_name); }
        redirect_with_alert("Gagal mengupdate barang toko {$acting_username}! Error: ".mysqli_error($conn), $redirect_page);
    }
}

// Menghapus barang dari stock Cabang Dinamis (Hanya oleh Admin Cabang Dinamis)
if (isset($_POST['hapusbarang_dynamic_branch'])) {
    $acting_username = $_SESSION['username'] ?? null;
    $acting_role = $_SESSION['roles'] ?? null;

    if (!$acting_username || $acting_role != 'admin') {
        redirect_with_alert("Akses ditolak atau sesi tidak valid.", '/stockmate/index.php'); exit; 
    }
    $specific_usernames = ['adminPusat', 'adminParahita', 'adminSerpong', 'owner'];
    if (in_array($acting_username, $specific_usernames)) {
        redirect_with_alert("Operasi ini tidak untuk pengguna dengan role spesifik.", '/stockmate/index.php'); exit; 
    }

    $table_prefix_dynamic = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $acting_username));
    if (empty($table_prefix_dynamic)) {
        redirect_with_alert("Nama pengguna tidak valid untuk operasi tabel.", '/stockmate/index.php'); exit; 
    }
    
    $stock_table_name = $table_prefix_dynamic . '_stock';
    $masuk_table_name = $table_prefix_dynamic . '_masuk';
    $keluar_table_name = $table_prefix_dynamic . '_keluar';
    $redirect_page = "/stockmate/adminBaru/index.php"; 

    $idb = intval($_POST['idb']);

    mysqli_begin_transaction($conn);
    try {
        $gambar_query = mysqli_query($conn, "SELECT image FROM `{$stock_table_name}` WHERE idbarang='$idb'");
        if (!$gambar_query) throw new Exception("Gagal query gambar {$acting_username}. Error: ".mysqli_error($conn));
        $get = mysqli_fetch_array($gambar_query);

        $hapus_masuk_dyn = mysqli_query($conn, "DELETE FROM `{$masuk_table_name}` WHERE idbarang='$idb'");
        if(!$hapus_masuk_dyn) throw new Exception("Gagal menghapus histori barang masuk terkait (Toko: {$acting_username}). Error: ".mysqli_error($conn));

        $hapus_keluar_dyn = mysqli_query($conn, "DELETE FROM `{$keluar_table_name}` WHERE idbarang='$idb'");
        if(!$hapus_keluar_dyn) throw new Exception("Gagal menghapus histori barang keluar terkait (Toko: {$acting_username}). Error: ".mysqli_error($conn));
        
        $hapus = mysqli_query($conn, "DELETE FROM `{$stock_table_name}` WHERE idbarang='$idb'");
        if (!$hapus) throw new Exception("Gagal menghapus barang toko {$acting_username} dari database. Error: ".mysqli_error($conn));

        delete_image_file($get['image']);
        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Gagal menghapus barang toko {$acting_username}: " . $e->getMessage(), $redirect_page);
    }
}

// Mengubah data barang masuk untuk Cabang Dinamis (Hanya oleh Admin Cabang Dinamis)
if (isset($_POST['updatebarangmasuk_dynamic_branch'])) {
    $acting_username = $_SESSION['username'] ?? null;
    $acting_role = $_SESSION['roles'] ?? null;

    if (!$acting_username || $acting_role != 'admin') {
        redirect_with_alert("Akses ditolak atau sesi tidak valid.", '/stockmate/index.php'); exit; 
    }
     $specific_usernames = ['adminPusat', 'adminParahita', 'adminSerpong', 'owner'];
    if (in_array($acting_username, $specific_usernames)) {
        redirect_with_alert("Operasi ini tidak untuk pengguna dengan role spesifik.", '/stockmate/index.php'); exit; 
    }

    $table_prefix_dynamic = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $acting_username));
    if (empty($table_prefix_dynamic)) {
        redirect_with_alert("Nama pengguna tidak valid untuk operasi tabel.", '/stockmate/index.php'); exit; 
    }

    $stock_table_name = $table_prefix_dynamic . '_stock';
    $masuk_table_name = $table_prefix_dynamic . '_masuk';
    $redirect_page = "/stockmate/adminBaru/masuk.php"; 

    $idb = intval($_POST['idb']);
    $idm = intval($_POST['idm']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $qty_baru = intval($_POST['qty']);

    if ($qty_baru <= 0) redirect_with_alert("Quantity baru harus lebih dari 0!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $qtyskrg_query = mysqli_query($conn, "SELECT qty FROM `{$masuk_table_name}` WHERE idmasuk='$idm' FOR UPDATE");
        if (!$qtyskrg_query || mysqli_num_rows($qtyskrg_query) == 0) throw new Exception("Data barang masuk toko {$acting_username} tidak ditemukan.");
        $qtynya = mysqli_fetch_array($qtyskrg_query);
        $qtyskrg = $qtynya['qty'];

        $lihatstock = mysqli_query($conn, "SELECT stock FROM `{$stock_table_name}` WHERE idbarang='$idb' FOR UPDATE");
        if (!$lihatstock || mysqli_num_rows($lihatstock) == 0) throw new Exception("Data stok barang toko {$acting_username} tidak ditemukan.");
        $stocknya = mysqli_fetch_array($lihatstock);
        $stockskrg = $stocknya['stock'];

        $selisih = $qty_baru - $qtyskrg;
        $stockbaru = $stockskrg + $selisih;

        $updatenya = mysqli_query($conn, "UPDATE `{$masuk_table_name}` SET qty='$qty_baru', keterangan='$deskripsi' WHERE idmasuk='$idm'");
        if (!$updatenya) throw new Exception("Gagal update data barang masuk toko {$acting_username}. Error: ".mysqli_error($conn));

        $updatestocknya = mysqli_query($conn, "UPDATE `{$stock_table_name}` SET stock='$stockbaru' WHERE idbarang='$idb'");
        if (!$updatestocknya) throw new Exception("Gagal update stok barang toko {$acting_username}. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal Toko {$acting_username}: " . $e->getMessage(), $redirect_page);
    }
}

// Menghapus barang masuk untuk Cabang Dinamis (Hanya oleh Admin Cabang Dinamis)
if (isset($_POST['hapusbarangmasuk_dynamic_branch'])) {
    $acting_username = $_SESSION['username'] ?? null;
    $acting_role = $_SESSION['roles'] ?? null;

    if (!$acting_username || $acting_role != 'admin') {
        redirect_with_alert("Akses ditolak atau sesi tidak valid.", '/stockmate/index.php'); exit; 
    }
    $specific_usernames = ['adminPusat', 'adminParahita', 'adminSerpong', 'owner'];
    if (in_array($acting_username, $specific_usernames)) {
        redirect_with_alert("Operasi ini tidak untuk pengguna dengan role spesifik.", '/stockmate/index.php'); exit; 
    }

    $table_prefix_dynamic = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $acting_username));
    if (empty($table_prefix_dynamic)) {
        redirect_with_alert("Nama pengguna tidak valid untuk operasi tabel.", '/stockmate/index.php'); exit; 
    }
    
    $stock_table_name = $table_prefix_dynamic . '_stock';
    $masuk_table_name = $table_prefix_dynamic . '_masuk';
    $redirect_page = "/stockmate/adminBaru/masuk.php"; 

    $idb = intval($_POST['idb']);
    $qty_dihapus = intval($_POST['kty']);
    $idm = intval($_POST['idm']);

    if ($qty_dihapus <= 0) redirect_with_alert("Quantity yang dihapus tidak valid!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $getdatastock = mysqli_query($conn, "SELECT stock FROM `{$stock_table_name}` WHERE idbarang='$idb' FOR UPDATE");
        if (!$getdatastock || mysqli_num_rows($getdatastock) == 0) throw new Exception("Data stok barang toko {$acting_username} tidak ditemukan.");
        $data = mysqli_fetch_array($getdatastock);
        $stock = $data['stock'];

        $stockbaru = $stock - $qty_dihapus;
        if ($stockbaru < 0) throw new Exception("Operasi tidak valid. Stok akan menjadi negatif.");

        $update = mysqli_query($conn, "UPDATE `{$stock_table_name}` SET stock='$stockbaru' WHERE idbarang='$idb'");
        if (!$update) throw new Exception("Gagal update stok barang toko {$acting_username}. Error: ".mysqli_error($conn));

        $hapusdata = mysqli_query($conn, "DELETE FROM `{$masuk_table_name}` WHERE idmasuk='$idm'");
        if (!$hapusdata) throw new Exception("Gagal menghapus data barang masuk toko {$acting_username}. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal Toko {$acting_username}: " . $e->getMessage(), $redirect_page);
    }
}

// Mengubah data barang keluar untuk Cabang Dinamis (Hanya oleh Admin Cabang Dinamis)
if (isset($_POST['updatebarangkeluar_dynamic_branch'])) {
    $acting_username = $_SESSION['username'] ?? null;
    $acting_role = $_SESSION['roles'] ?? null;

     if (!$acting_username || $acting_role != 'admin') {
        redirect_with_alert("Akses ditolak atau sesi tidak valid.", '/stockmate/index.php'); exit; 
    }
    $specific_usernames = ['adminPusat', 'adminParahita', 'adminSerpong', 'owner'];
    if (in_array($acting_username, $specific_usernames)) {
        redirect_with_alert("Operasi ini tidak untuk pengguna dengan role spesifik.", '/stockmate/index.php'); exit; 
    }

    $table_prefix_dynamic = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $acting_username));
    if (empty($table_prefix_dynamic)) {
        redirect_with_alert("Nama pengguna tidak valid untuk operasi tabel.", '/stockmate/index.php'); exit; 
    }

    $stock_table_name = $table_prefix_dynamic . '_stock';
    $keluar_table_name = $table_prefix_dynamic . '_keluar';
    $redirect_page = "/stockmate/adminBaru/keluar.php"; 

    $idb = intval($_POST['idb']);
    $idk = intval($_POST['idk']);
    $penerima = mysqli_real_escape_string($conn, $_POST['penerima']);
    $qty_baru = intval($_POST['qty']);

    if ($qty_baru <= 0) redirect_with_alert("Quantity baru harus lebih dari 0!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $qtyskrg_query = mysqli_query($conn, "SELECT qty FROM `{$keluar_table_name}` WHERE idkeluar='$idk' FOR UPDATE");
        if (!$qtyskrg_query || mysqli_num_rows($qtyskrg_query) == 0) throw new Exception("Data barang keluar toko {$acting_username} tidak ditemukan.");
        $qtynya = mysqli_fetch_array($qtyskrg_query);
        $qtyskrg = $qtynya['qty'];

        $lihatstock = mysqli_query($conn, "SELECT stock FROM `{$stock_table_name}` WHERE idbarang='$idb' FOR UPDATE");
        if (!$lihatstock || mysqli_num_rows($lihatstock) == 0) throw new Exception("Data stok barang toko {$acting_username} tidak ditemukan.");
        $stocknya = mysqli_fetch_array($lihatstock);
        $stockskrg = $stocknya['stock'];

        $stockbaru = ($stockskrg + $qtyskrg) - $qty_baru;

        if ($stockbaru < 0) throw new Exception("Stok toko {$acting_username} tidak mencukupi untuk jumlah keluar yang baru ($stockbaru).");

        $updatenya = mysqli_query($conn, "UPDATE `{$keluar_table_name}` SET qty='$qty_baru', penerima='$penerima' WHERE idkeluar='$idk'");
        if (!$updatenya) throw new Exception("Gagal update data barang keluar toko {$acting_username}. Error: ".mysqli_error($conn));

        $updatestocknya = mysqli_query($conn, "UPDATE `{$stock_table_name}` SET stock='$stockbaru' WHERE idbarang='$idb'");
        if (!$updatestocknya) throw new Exception("Gagal update stok barang toko {$acting_username}. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal Toko {$acting_username}: " . $e->getMessage(), $redirect_page);
    }
}

// Menghapus barang keluar untuk Cabang Dinamis (Hanya oleh Admin Cabang Dinamis)
if (isset($_POST['hapusbarangkeluar_dynamic_branch'])) {
     $acting_username = $_SESSION['username'] ?? null;
    $acting_role = $_SESSION['roles'] ?? null;

    if (!$acting_username || $acting_role != 'admin') {
        redirect_with_alert("Akses ditolak atau sesi tidak valid.", '/stockmate/index.php'); exit; 
    }
    $specific_usernames = ['adminPusat', 'adminParahita', 'adminSerpong', 'owner'];
    if (in_array($acting_username, $specific_usernames)) {
        redirect_with_alert("Operasi ini tidak untuk pengguna dengan role spesifik.", '/stockmate/index.php'); exit; 
    }

    $table_prefix_dynamic = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $acting_username));
    if (empty($table_prefix_dynamic)) {
        redirect_with_alert("Nama pengguna tidak valid untuk operasi tabel.", '/stockmate/index.php'); exit; 
    }
    
    $stock_table_name = $table_prefix_dynamic . '_stock';
    $keluar_table_name = $table_prefix_dynamic . '_keluar';
    $redirect_page = "/stockmate/adminBaru/keluar.php"; 

    $idb = intval($_POST['idb']);
    $qty_dikembalikan = intval($_POST['kty']);
    $idk = intval($_POST['idk']);

    if ($qty_dikembalikan <= 0) redirect_with_alert("Quantity yang dihapus tidak valid!", $redirect_page);

    mysqli_begin_transaction($conn);
    try {
        $getdatastock = mysqli_query($conn, "SELECT stock FROM `{$stock_table_name}` WHERE idbarang='$idb' FOR UPDATE");
        if (!$getdatastock || mysqli_num_rows($getdatastock) == 0) throw new Exception("Data stok barang toko {$acting_username} tidak ditemukan.");
        $data = mysqli_fetch_array($getdatastock);
        $stock = $data['stock'];

        $stockbaru = $stock + $qty_dikembalikan;

        $update = mysqli_query($conn, "UPDATE `{$stock_table_name}` SET stock='$stockbaru' WHERE idbarang='$idb'");
        if (!$update) throw new Exception("Gagal update stok barang toko {$acting_username}. Error: ".mysqli_error($conn));

        $hapusdata = mysqli_query($conn, "DELETE FROM `{$keluar_table_name}` WHERE idkeluar='$idk'");
        if (!$hapusdata) throw new Exception("Gagal menghapus data barang keluar toko {$acting_username}. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        header('location:' . $redirect_page); exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal Toko {$acting_username}: " . $e->getMessage(), $redirect_page);
    }
}

// Transfer Barang dari Pusat ke Cabang Dinamis oleh Admin Pusat/Owner (dipicu dari new/admin/adminBaru_masuk.php)
if (isset($_POST['barangmasuk_transfer_dynamic_branch'])) {
    $idbarang_input_pusat = intval($_POST['barangnya']); 
    $keterangan_input = mysqli_real_escape_string($conn, $_POST['penerima']); 
    $qty_input = intval($_POST['qty']);
    $target_branch_username = mysqli_real_escape_string($conn, $_POST['target_branch_username']);

    $current_role = $_SESSION['roles'] ?? '';
    $current_username = $_SESSION['username'] ?? ''; 

    if (empty($_POST['initiator']) || $_POST['initiator'] != 'admin_pusat_transfer_to_dynamic_branch' || !in_array($current_role, ['adminPusat', 'owner'])) {
        redirect_with_alert("Aksi tidak diizinkan atau initiator tidak valid.", '/stockmate/index.php'); 
        exit;
    }
    
    $redirect_page = '/stockmate/admin/adminBaru_masuk.php?user=' . urlencode($target_branch_username); 

    if (empty($target_branch_username)) {
        redirect_with_alert("Username cabang tujuan tidak valid.", $redirect_page);
        exit;
    }

    $target_table_prefix = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $target_branch_username));
    if (empty($target_table_prefix)) {
        redirect_with_alert("Format username cabang tujuan tidak valid untuk operasi tabel.", $redirect_page);
        exit;
    }

    $target_stock_table_name = "`{$target_table_prefix}_stock`";
    $target_masuk_table_name = "`{$target_table_prefix}_masuk`";


    if ($qty_input <= 0) {
        redirect_with_alert("Quantity harus lebih dari 0!", $redirect_page);
        exit;
    }
    if ($idbarang_input_pusat <= 0) {
        redirect_with_alert("Barang dari Stok Pusat belum dipilih!", $redirect_page);
        exit;
    }

    mysqli_begin_transaction($conn);
    try {
        $cek_pusat_stock = mysqli_query($conn, "SELECT namabarang, deskripsi, image, stock FROM pusatstock WHERE idbarang='$idbarang_input_pusat' FOR UPDATE");
        if (!$cek_pusat_stock || mysqli_num_rows($cek_pusat_stock) == 0) {
            throw new Exception("Barang sumber (ID: {$idbarang_input_pusat}) tidak ditemukan di Stok Pusat.");
        }
        $data_pusat_stock = mysqli_fetch_assoc($cek_pusat_stock);
        $stock_pusat_sekarang = intval($data_pusat_stock['stock']);
        
        $nama_barang_target = $data_pusat_stock['namabarang']; 
        $deskripsi_target = $data_pusat_stock['deskripsi'];
        $image_target = $data_pusat_stock['image'];

        if ($stock_pusat_sekarang < $qty_input) {
            throw new Exception("Stok Pusat untuk barang '{$nama_barang_target}' (ID: {$idbarang_input_pusat}) tidak mencukupi. Stok: {$stock_pusat_sekarang}, Diminta: {$qty_input}.");
        }
        $stock_pusat_baru = $stock_pusat_sekarang - $qty_input;
        if (!mysqli_query($conn, "UPDATE pusatstock SET stock='$stock_pusat_baru' WHERE idbarang='$idbarang_input_pusat'")) {
            throw new Exception("Gagal update stok di Pusat. Error: " . mysqli_error($conn));
        }

        $penerima_untuk_pusat_keluar = "Toko {$target_branch_username} (Transfer by: {$current_username})";
        if (!mysqli_query($conn, "INSERT INTO pusatkeluar (idbarang, penerima, qty) VALUES ('$idbarang_input_pusat', '".mysqli_real_escape_string($conn, $penerima_untuk_pusat_keluar)."', '$qty_input')")) {
            throw new Exception("Gagal mencatat barang keluar dari Pusat. Error: " . mysqli_error($conn));
        }

        $nama_barang_target_esc = mysqli_real_escape_string($conn, $nama_barang_target);
        $cek_target_item = mysqli_query($conn, "SELECT idbarang, stock FROM {$target_stock_table_name} WHERE namabarang='$nama_barang_target_esc' FOR UPDATE");
        if (!$cek_target_item) throw new Exception("Error cek item di Toko {$target_branch_username}: ". mysqli_error($conn));

        $idbarang_target_final;
        if ($data_target_item = mysqli_fetch_assoc($cek_target_item)) {
            $idbarang_target_final = $data_target_item['idbarang'];
            $stock_target_sekarang = intval($data_target_item['stock']);
            $stock_target_baru = $stock_target_sekarang + $qty_input;
            if (!mysqli_query($conn, "UPDATE {$target_stock_table_name} SET stock='$stock_target_baru' WHERE idbarang='$idbarang_target_final'")) {
                throw new Exception("Gagal update stok Toko {$target_branch_username} (existing item). Error: " . mysqli_error($conn));
            }
        } else {
            $deskripsi_target_esc = mysqli_real_escape_string($conn, $deskripsi_target);
            $image_sql_val = $image_target ? "'".mysqli_real_escape_string($conn, $image_target)."'" : "NULL";
            $insert_target_stock = mysqli_query($conn, "INSERT INTO {$target_stock_table_name} (namabarang, deskripsi, stock, image) VALUES ('$nama_barang_target_esc', '$deskripsi_target_esc', '$qty_input', $image_sql_val)");
            if (!$insert_target_stock) {
                throw new Exception("Gagal insert item baru ke Toko {$target_branch_username}. Error: " . mysqli_error($conn));
            }
            $idbarang_target_final = mysqli_insert_id($conn);
        }

        if ($idbarang_target_final > 0) {
            $final_keterangan_target_masuk = "Dari Pusat (via {$current_username}). Ket: {$keterangan_input}";
            if (!mysqli_query($conn, "INSERT INTO {$target_masuk_table_name} (idbarang, keterangan, qty) VALUES ('$idbarang_target_final', '".mysqli_real_escape_string($conn, $final_keterangan_target_masuk)."', '$qty_input')")) {
                throw new Exception("Gagal mencatat barang masuk ke Toko {$target_branch_username}. Error: " . mysqli_error($conn));
            }
        } else {
            throw new Exception("ID Barang Toko {$target_branch_username} tidak valid untuk pencatatan barang masuk.");
        }

        mysqli_commit($conn);
        $_SESSION['admin_action_message'] = "Transfer barang ke Toko {$target_branch_username} berhasil."; 
        $_SESSION['admin_action_type'] = "success";
        header('Location: ' . $redirect_page); 
        exit;

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['admin_action_message'] = "Transfer Gagal: " . $e->getMessage(); 
        $_SESSION['admin_action_type'] = "danger";
        header('Location: ' . $redirect_page); 
        exit;
    }
}


// ==============================================
// FUNGSI ADMINISTRASI & PP (TETAP SAMA)
// ==============================================
// Menambah admin/pengguna baru
if (isset($_POST['addadmin'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    $roles = mysqli_real_escape_string($conn, $_POST['roles']);

    $redirect_page = '/stockmate/admin/admin.php';
    if (isset($_SESSION['roles']) && $_SESSION['roles'] == 'owner') {
        $redirect_page = '/stockmate/owner/admin.php';
    }

    $allowed_roles_to_add = ['admin', 'karyawan'];
                                                 
    if (!in_array($roles, $allowed_roles_to_add)) {
        redirect_with_alert("Role yang diizinkan untuk ditambah hanya 'admin' atau 'karyawan' untuk cabang baru!", $redirect_page);
    }
    if (empty($password)) {
        redirect_with_alert("Password tidak boleh kosong!", $redirect_page);
    }

    $cekusername = mysqli_query($conn, "SELECT username FROM login WHERE username='$username'");
    if (!$cekusername) {
        redirect_with_alert("Error cek username: ".mysqli_error($conn), $redirect_page);
    }
    if (mysqli_num_rows($cekusername) > 0) {
        redirect_with_alert("Username '$username' sudah terdaftar!", $redirect_page);
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        if (!$hashed_password) {
            redirect_with_alert("Gagal hashing password!", $redirect_page);
        }

        // Tentukan apakah ini pengguna cabang baru
        $is_new_branch_user = false;
        $specific_store_roles_or_usernames = ['adminPusat', 'karyawanPusat', 'owner', 'adminParahita', 'karyawanParahita', 'adminSerpong', 'karyawanSerpong'];
        if (($roles == 'admin' || $roles == 'karyawan') && !in_array($username, $specific_store_roles_or_usernames)) {
            $is_new_branch_user = true;
        }

        if ($is_new_branch_user) {
            // Untuk pengguna cabang baru, lakukan insert user dan create table dalam satu transaksi
            $table_prefix = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $username));

            if (empty($table_prefix) || in_array($table_prefix, ['pusat', 'parahita', 'serpong'])) {
                redirect_with_alert("Nama pengguna '$username' tidak valid untuk pembuatan tabel data cabang baru (kemungkinan konflik atau format salah).", $redirect_page);
            }

            mysqli_begin_transaction($conn);
            try {
                // 1. Insert pengguna ke tabel login
                $queryinsert = mysqli_query($conn, "INSERT INTO login (username, password, roles) VALUES ('$username', '$hashed_password', '$roles')");
                if (!$queryinsert) {
                    throw new Exception("Gagal menambahkan pengguna ke tabel login: " . mysqli_error($conn));
                }
                // $id_user_baru = mysqli_insert_id($conn); // Dapatkan ID user baru jika diperlukan

                // 2. Buat tabel-tabel spesifik untuk cabang dengan kolasi yang lebih umum
                $sql_create_stock = "CREATE TABLE IF NOT EXISTS `{$table_prefix}_stock` (
                    `idbarang` int(11) NOT NULL AUTO_INCREMENT, `namabarang` varchar(50) NOT NULL, `deskripsi` varchar(50) NOT NULL,
                    `stock` int(11) NOT NULL DEFAULT '0', `image` varchar(99) DEFAULT NULL, PRIMARY KEY (`idbarang`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"; // Diubah di sini
                if (!mysqli_query($conn, $sql_create_stock)) {
                    throw new Exception("Gagal membuat tabel {$table_prefix}_stock: " . mysqli_error($conn));
                }

                $sql_create_masuk = "CREATE TABLE IF NOT EXISTS `{$table_prefix}_masuk` (
                    `idmasuk` int(11) NOT NULL AUTO_INCREMENT, `idbarang` int(11) NOT NULL, `tanggal` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    `keterangan` varchar(50) NOT NULL, `qty` int(11) NOT NULL, PRIMARY KEY (`idmasuk`),
                    KEY `fk_{$table_prefix}_masuk_barang` (`idbarang`),
                    CONSTRAINT `fk_{$table_prefix}_masuk_barang` FOREIGN KEY (`idbarang`) REFERENCES `{$table_prefix}_stock` (`idbarang`) ON DELETE RESTRICT ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"; 
                if (!mysqli_query($conn, $sql_create_masuk)) {
                    throw new Exception("Gagal membuat tabel {$table_prefix}_masuk: " . mysqli_error($conn));
                }

                $sql_create_keluar = "CREATE TABLE IF NOT EXISTS `{$table_prefix}_keluar` (
                    `idkeluar` int(11) NOT NULL AUTO_INCREMENT, `idbarang` int(11) NOT NULL, `tanggal` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    `penerima` varchar(50) NOT NULL, `qty` int(11) NOT NULL, PRIMARY KEY (`idkeluar`),
                    KEY `fk_{$table_prefix}_keluar_barang` (`idbarang`),
                    CONSTRAINT `fk_{$table_prefix}_keluar_barang` FOREIGN KEY (`idbarang`) REFERENCES `{$table_prefix}_stock` (`idbarang`) ON DELETE RESTRICT ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;"; 
                if (!mysqli_query($conn, $sql_create_keluar)) {
                    throw new Exception("Gagal membuat tabel {$table_prefix}_keluar: " . mysqli_error($conn));
                }
                
                mysqli_commit($conn);
                $_SESSION['admin_action_message'] = "Pengguna {$username} dan tabel datanya berhasil dibuat.";
                $_SESSION['admin_action_type'] = "success";
                header('location:' . $redirect_page);
                exit;

            } catch (Exception $e) {
                mysqli_rollback($conn);
                error_log("Gagal membuat pengguna dan tabel untuk {$username}: " . $e->getMessage());
                redirect_with_alert("Gagal total menambahkan pengguna dan tabel: " . $e->getMessage(), $redirect_page);
            }

        } else {
            // Untuk pengguna yang bukan cabang baru
            $queryinsert = mysqli_query($conn, "INSERT INTO login (username, password, roles) VALUES ('$username', '$hashed_password', '$roles')");
            if ($queryinsert) {
                $_SESSION['admin_action_message'] = "Pengguna {$username} berhasil ditambahkan (bukan pengguna cabang baru, jadi tidak ada tabel data tambahan dibuat).";
                $_SESSION['admin_action_type'] = "success";
                header('location:' . $redirect_page);
                exit;
            } else {
                redirect_with_alert("Gagal menambahkan pengguna! Error: ".mysqli_error($conn), $redirect_page);
            }
        }
    }
}


// Edit data admin/pengguna
if (isset($_POST['updateadmin'])) {
    $usernamebaru = mysqli_real_escape_string($conn, $_POST['usernameadmin']);
    $passwordbaru = $_POST['passwordbaru'];
    $rolesbaru = mysqli_real_escape_string($conn, $_POST['rolesbaru']);
    $idnya = intval($_POST['id']);

     $redirect_page = '/stockmate/admin/admin.php'; 
     if (isset($_SESSION['roles']) && $_SESSION['roles'] == 'owner') {
        $redirect_page = '/stockmate/owner/admin.php'; 
    }

    $allowed_roles_for_edit = ['adminPusat', 'karyawanPusat', 'adminParahita', 'karyawanParahita', 'adminSerpong', 'karyawanSerpong', 'admin', 'karyawan'];
    if (isset($_SESSION['roles']) && $_SESSION['roles'] == 'owner') {
        $allowed_roles_for_edit[] = 'owner'; 
    }
    if (!in_array($rolesbaru, $allowed_roles_for_edit)) {
        redirect_with_alert("Role tujuan tidak valid untuk di-assign oleh Anda!", $redirect_page);
    }

     $cek_user_lama_q = mysqli_query($conn, "SELECT username, roles FROM login WHERE iduser='$idnya'");
     if (!$cek_user_lama_q || mysqli_num_rows($cek_user_lama_q) == 0) {
        redirect_with_alert("Pengguna yang akan diedit tidak ditemukan!", $redirect_page);
     }
     $user_lama = mysqli_fetch_assoc($cek_user_lama_q);
     $role_lama = $user_lama['roles'];
     $username_lama = $user_lama['username'];

     if (($role_lama == 'owner' && $rolesbaru != 'owner') && (!isset($_SESSION['roles']) || $_SESSION['roles'] != 'owner')) {
         redirect_with_alert("Hanya Owner yang dapat mengubah role dari Owner lain.", $redirect_page);
     }
     if ($rolesbaru == 'owner' && (!isset($_SESSION['roles']) || $_SESSION['roles'] != 'owner')) {
          redirect_with_alert("Hanya Owner yang dapat menetapkan role Owner.", $redirect_page);
     }

     $is_dynamic_branch_user_lama = false;
     $specific_store_roles_or_usernames = ['adminPusat', 'karyawanPusat', 'owner', 'adminParahita', 'karyawanParahita', 'adminSerpong', 'karyawanSerpong'];
     if (($role_lama == 'admin' || $role_lama == 'karyawan') && !in_array($username_lama, $specific_store_roles_or_usernames)) {
         $is_dynamic_branch_user_lama = true;
     }

     if ($is_dynamic_branch_user_lama && $username_lama != $usernamebaru) {
        $_SESSION['admin_action_message'] = "PERINGATAN: Username untuk pengguna cabang dinamis '{$username_lama}' telah diubah menjadi '{$usernamebaru}'. Tabel data lama ({$username_lama}_stock, dll.) TIDAK di-rename. Data lama mungkin tidak bisa diakses oleh username baru.";
        $_SESSION['admin_action_type'] = "warning";
     }


     $cekusername_edit = mysqli_query($conn, "SELECT iduser FROM login WHERE username='$usernamebaru' AND iduser != '$idnya'");
     if (!$cekusername_edit) redirect_with_alert("Error cek username: ".mysqli_error($conn), $redirect_page);
    if(mysqli_num_rows($cekusername_edit) > 0) redirect_with_alert("Username '$usernamebaru' sudah digunakan oleh admin lain!", $redirect_page);

    $sql_update = "UPDATE login SET username='$usernamebaru', roles='$rolesbaru'";
    if (!empty($passwordbaru)) {
        $hashed_password_baru = password_hash($passwordbaru, PASSWORD_DEFAULT);
        if (!$hashed_password_baru) redirect_with_alert("Gagal hashing password baru!", $redirect_page);
        $sql_update .= ", password='$hashed_password_baru'";
    }
    $sql_update .= " WHERE iduser='$idnya'";

    $queryupdate = mysqli_query($conn, $sql_update);
    if ($queryupdate) {
        if (!isset($_SESSION['admin_action_message'])) { 
            $_SESSION['admin_action_message'] = "Data pengguna berhasil diupdate.";
            $_SESSION['admin_action_type'] = "success";
        }
        header('location:' . $redirect_page); exit;
    } else {
        redirect_with_alert("Gagal mengupdate admin! Error: ".mysqli_error($conn), $redirect_page);
    }
}


// Hapus admin/pengguna
if (isset($_POST['hapusadmin'])) {
    $id_user_to_delete = intval($_POST['id']);

    $redirect_page_target = '/stockmate/admin/admin.php'; 
    if (isset($_SESSION['roles']) && $_SESSION['roles'] == 'owner') {
         $redirect_page_target = '/stockmate/owner/admin.php'; 
    }

    $get_user_info_query = mysqli_query($conn, "SELECT username, roles FROM login WHERE iduser='$id_user_to_delete'");
    if (!$get_user_info_query) {
        $_SESSION['admin_action_message'] = "Error query info pengguna: " . mysqli_error($conn);
        $_SESSION['admin_action_type'] = "danger";
        header('Location: ' . $redirect_page_target); exit;
    }

    if (mysqli_num_rows($get_user_info_query) > 0) {
        $data_user_to_delete = mysqli_fetch_assoc($get_user_info_query);
        $username_to_delete = $data_user_to_delete['username'];
        $role_to_delete = $data_user_to_delete['roles'];
        $session_username = $_SESSION['username'] ?? null;
        $session_role = $_SESSION['roles'] ?? null;

        if ($session_username && $username_to_delete === $session_username) {
            $_SESSION['admin_action_message'] = "Anda tidak dapat menghapus akun Anda sendiri!";
            $_SESSION['admin_action_type'] = "danger";
            header('Location: ' . $redirect_page_target); exit;
        }
        if ($role_to_delete == 'owner' && $session_role != 'owner') {
            $_SESSION['admin_action_message'] = "Hanya Owner yang dapat menghapus akun Owner.";
            $_SESSION['admin_action_type'] = "danger";
            header('Location: ' . $redirect_page_target); exit;
        }

        $is_dynamic_branch_user_to_delete = false;
        $specific_store_roles_or_usernames = ['adminPusat', 'karyawanPusat', 'owner', 'adminParahita', 'karyawanParahita', 'adminSerpong', 'karyawanSerpong'];
        if (($role_to_delete == 'admin' || $role_to_delete == 'karyawan') && !in_array($username_to_delete, $specific_store_roles_or_usernames)) {
            $is_dynamic_branch_user_to_delete = true;
        }

        mysqli_begin_transaction($conn);
        try {
            if ($is_dynamic_branch_user_to_delete) {
                $table_prefix_to_drop = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $username_to_delete));

                if (!empty($table_prefix_to_drop)) {
                    $img_query = mysqli_query($conn, "SELECT image FROM `{$table_prefix_to_drop}_stock`");
                    if ($img_query) {
                        while($img_data = mysqli_fetch_assoc($img_query)) {
                            delete_image_file($img_data['image']);
                        }
                    }
                    mysqli_query($conn, "DROP TABLE IF EXISTS `{$table_prefix_to_drop}_keluar`");
                    mysqli_query($conn, "DROP TABLE IF EXISTS `{$table_prefix_to_drop}_masuk`");
                    mysqli_query($conn, "DROP TABLE IF EXISTS `{$table_prefix_to_drop}_stock`");
                }
                $escaped_username_to_del_pp = mysqli_real_escape_string($conn, $username_to_delete);
                if (!mysqli_query($conn, "DELETE FROM pp WHERE penerima = '$escaped_username_to_del_pp'")) {
                    throw new Exception("Gagal menghapus data PP terkait untuk {$username_to_delete}: " . mysqli_error($conn));
                }
            }

            $querydelete_login = mysqli_query($conn, "DELETE FROM login WHERE iduser='$id_user_to_delete'");
            if (!$querydelete_login) {
                throw new Exception("Gagal menghapus pengguna dari tabel login: " . mysqli_error($conn));
            }

            mysqli_commit($conn);
            $_SESSION['admin_action_message'] = "Pengguna '{$username_to_delete}' " . ($is_dynamic_branch_user_to_delete ? "dan tabel datanya " : "") . "berhasil dihapus.";
            $_SESSION['admin_action_type'] = "success";
            header('Location: ' . $redirect_page_target); exit;

        } catch (Exception $e) {
            mysqli_rollback($conn);
            error_log("Error saat menghapus pengguna {$username_to_delete}: " . $e->getMessage());
            $_SESSION['admin_action_message'] = "Gagal menghapus pengguna '{$username_to_delete}'. Error: " . $e->getMessage();
            $_SESSION['admin_action_type'] = "danger";
            header('Location: ' . $redirect_page_target); exit;
        }

    } else {
        $_SESSION['admin_action_message'] = "Pengguna yang akan dihapus tidak ditemukan!";
        $_SESSION['admin_action_type'] = "danger";
        header('Location: ' . $redirect_page_target); exit;
    }
}


// Menambah pp barang keluar 
if (isset($_POST['addppbarangkeluar'])) {
    $barangnya = intval($_POST['barangnya']);
    $penerima = mysqli_real_escape_string($conn, $_POST['penerima']); 
    $qty = intval($_POST['qty']);
    $nopp = mysqli_real_escape_string($conn, $_POST['nopp']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);

    $requesting_role = $_SESSION['roles'] ?? '';
    $requesting_username = $_SESSION['username'] ?? '';

    $redirect_page_after_pp_request = '/stockmate/index.php';  
    if ($requesting_role == 'adminParahita') $redirect_page_after_pp_request = '/stockmate/adminParahita/permintaanbarangkeluar.php'; 
    elseif ($requesting_role == 'adminSerpong') $redirect_page_after_pp_request = '/stockmate/adminSerpong/permintaanbarangkeluar.php'; 
    elseif ($requesting_role == 'admin' && !in_array($requesting_username, ['adminPusat', 'adminParahita', 'adminSerpong', 'owner'])) {
        $redirect_page_after_pp_request = '/stockmate/adminBaru/permintaanbarangkeluar.php'; 
    } else {
        redirect_with_alert("Anda tidak berhak membuat permintaan barang.", '/stockmate/index.php'); 
        exit;
    }

    if ($qty <= 0) redirect_with_alert("Quantity harus lebih dari 0!", $redirect_page_after_pp_request);
    if ($barangnya <= 0) redirect_with_alert("Barang belum dipilih!", $redirect_page_after_pp_request);

    $cek_stock_pusat_query = mysqli_query($conn, "SELECT namabarang, stock FROM pusatstock WHERE idbarang='$barangnya'");
    if (!$cek_stock_pusat_query || mysqli_num_rows($cek_stock_pusat_query) == 0) {
        redirect_with_alert("Barang tidak ditemukan di gudang pusat.", $redirect_page_after_pp_request);
        exit;
    }
    $data_stock_pusat = mysqli_fetch_assoc($cek_stock_pusat_query);
    $nama_barang_pusat = htmlspecialchars($data_stock_pusat['namabarang']);
    $stock_sekarang_pusat = intval($data_stock_pusat['stock']);

    if ($qty > $stock_sekarang_pusat) {
        $pesan_alert = "Permintaan gagal! Kuantitas ({$qty}) untuk '{$nama_barang_pusat}' melebihi stok Pusat ({$stock_sekarang_pusat}).";
        redirect_with_alert($pesan_alert, $redirect_page_after_pp_request);
        exit;
    }

    $addtopp = mysqli_query($conn, "INSERT INTO pp (idbarang, penerima, qty, status, nopp, keterangan) VALUES ('$barangnya', '$penerima', '$qty', 'PENDING', '$nopp', '$keterangan')");
    if ($addtopp) {
        redirect_with_alert("Permintaan barang berhasil diajukan.", $redirect_page_after_pp_request);
        exit;
    } else {
        redirect_with_alert("Gagal menambahkan permintaan barang keluar! Error: ".mysqli_error($conn), $redirect_page_after_pp_request);
        exit;
    }
}


// Menerima Permintaan Barang Keluar (PP) 
if (isset($_POST['terimabarangkeluar'])) {
    $idpp = intval($_POST['idpp']);
    $idb = intval($_POST['idb']);
    $qty_diminta = intval($_POST['qty']);
    $penerima_pp = mysqli_real_escape_string($conn, $_POST['penerima']); 

    $stock_table_pusat = 'pusatstock';
    $keluar_table_pusat = 'pusatkeluar';
    $redirect_page = '/stockmate/admin/pp.php'; 

    if ($qty_diminta <= 0) redirect_with_alert("Quantity PP tidak valid!", $redirect_page);
    if (!isset($_SESSION['roles']) || !in_array($_SESSION['roles'], ['adminPusat', 'owner'])) {
       redirect_with_alert("Anda tidak punya izin memproses PP!", $redirect_page);
    }

    mysqli_begin_transaction($conn);
    try {
        $query_cek_pusat = "SELECT namabarang, deskripsi, image, stock FROM $stock_table_pusat WHERE idbarang='$idb' FOR UPDATE";
        $result_cek_pusat = mysqli_query($conn, $query_cek_pusat);
        if (!$result_cek_pusat || mysqli_num_rows($result_cek_pusat) == 0) throw new Exception("Barang ID $idb tidak ditemukan di stok pusat.");
        $datastock_pusat = mysqli_fetch_assoc($result_cek_pusat);
        $stocksekarang_pusat = intval($datastock_pusat['stock']);
        $nama_barang_pusat = $datastock_pusat['namabarang'];
        $deskripsi_barang_pusat = $datastock_pusat['deskripsi'];
        $image_barang_pusat = $datastock_pusat['image'];

        if ($stocksekarang_pusat < $qty_diminta) throw new Exception("Stok pusat '{$nama_barang_pusat}' tidak cukup (Stok: {$stocksekarang_pusat}, Diminta: {$qty_diminta}).");

        $stockbaru_pusat = $stocksekarang_pusat - $qty_diminta;
        if (!mysqli_query($conn, "UPDATE $stock_table_pusat SET stock='$stockbaru_pusat' WHERE idbarang='$idb'")) throw new Exception("Gagal update stok pusat: ".mysqli_error($conn));
        if (!mysqli_query($conn, "INSERT INTO $keluar_table_pusat (idbarang, penerima, qty) VALUES ('$idb', '$penerima_pp', '$qty_diminta')")) throw new Exception("Gagal catat keluar pusat: ".mysqli_error($conn));

        $target_stock_table_name = "";
        $target_masuk_table_name = "";
        $nama_toko_cabang_log = htmlspecialchars($penerima_pp); 

        if (strtolower($penerima_pp) == 'toko parahita') {
            $target_stock_table_name = 'parahita_stock';
            $target_masuk_table_name = 'parahita_masuk';
        } elseif (strtolower($penerima_pp) == 'toko serpong') {
            $target_stock_table_name = 'serpong_stock';
            $target_masuk_table_name = 'serpong_masuk';
        } else {
            $penerima_pp_sanitized_for_table = strtolower(preg_replace("/[^a-zA-Z0-9_]+/", "", $penerima_pp));
            if (!empty($penerima_pp_sanitized_for_table) && !in_array($penerima_pp_sanitized_for_table, ['pusat', 'parahita', 'serpong'])) {
                $check_table_exists_sql = "SHOW TABLES LIKE '{$penerima_pp_sanitized_for_table}_stock'";
                $table_exists_result = mysqli_query($conn, $check_table_exists_sql);
                if ($table_exists_result && mysqli_num_rows($table_exists_result) > 0) {
                    $target_stock_table_name = $penerima_pp_sanitized_for_table . '_stock';
                    $target_masuk_table_name = $penerima_pp_sanitized_for_table . '_masuk';
                } else {
                    throw new Exception("Tabel stok untuk penerima '{$penerima_pp}' tidak ditemukan.");
                }
            } else {
                throw new Exception("Penerima PP '{$penerima_pp}' tidak valid atau tidak dikenal untuk penentuan tabel tujuan.");
            }
        }

        if (!empty($target_stock_table_name) && !empty($target_masuk_table_name)) {
            $query_cek_cabang = "SELECT idbarang, stock FROM `{$target_stock_table_name}` WHERE namabarang='".mysqli_real_escape_string($conn, $nama_barang_pusat)."' LIMIT 1 FOR UPDATE";
            $result_cek_cabang = mysqli_query($conn, $query_cek_cabang);
            if (!$result_cek_cabang) throw new Exception("Error cek stok di {$nama_toko_cabang_log}: " . mysqli_error($conn));

            $idbarang_cabang = null;

            if (mysqli_num_rows($result_cek_cabang) > 0) {
                $data_item_cabang = mysqli_fetch_assoc($result_cek_cabang);
                $idbarang_cabang = $data_item_cabang['idbarang']; 
                $stock_sekarang_cabang = intval($data_item_cabang['stock']);
                $stock_baru_cabang = $stock_sekarang_cabang + $qty_diminta;
                if (!mysqli_query($conn, "UPDATE `{$target_stock_table_name}` SET stock='$stock_baru_cabang' WHERE idbarang='$idbarang_cabang'")) throw new Exception("Gagal update stok di {$nama_toko_cabang_log}: ".mysqli_error($conn));
            } else {
                $nama_barang_esc = mysqli_real_escape_string($conn, $nama_barang_pusat);
                $deskripsi_barang_esc = mysqli_real_escape_string($conn, $deskripsi_barang_pusat);
                $image_sql_val = $image_barang_pusat ? "'".mysqli_real_escape_string($conn, $image_barang_pusat)."'" : "NULL";
                $insert_item_cabang_query = "INSERT INTO `{$target_stock_table_name}` (namabarang, deskripsi, stock, image) VALUES ('$nama_barang_esc', '$deskripsi_barang_esc', '$qty_diminta', $image_sql_val)";
                if (!mysqli_query($conn, $insert_item_cabang_query)) throw new Exception("Gagal tambah item baru ke stok {$nama_toko_cabang_log}: ".mysqli_error($conn));
                $idbarang_cabang = mysqli_insert_id($conn); 
            }

            if ($idbarang_cabang) { 
                $keterangan_masuk_cabang = "Diterima dari Gudang Pusat (PP ID: $idpp)";
                $keterangan_masuk_cabang_esc = mysqli_real_escape_string($conn, $keterangan_masuk_cabang);
                $catat_masuk_cabang_query = "INSERT INTO `{$target_masuk_table_name}` (idbarang, keterangan, qty) VALUES ('$idbarang_cabang', '$keterangan_masuk_cabang_esc', '$qty_diminta')";
                if (!mysqli_query($conn, $catat_masuk_cabang_query)) throw new Exception("Gagal catat barang masuk di {$nama_toko_cabang_log}: ".mysqli_error($conn));
            } else {
                 throw new Exception("Tidak bisa mendapatkan ID Barang di tabel {$target_stock_table_name} untuk pencatatan barang masuk.");
            }
        }

        if (!mysqli_query($conn, "UPDATE pp SET status='DITERIMA' WHERE idpp='$idpp'")) throw new Exception("Gagal update status PP: ".mysqli_error($conn));

        mysqli_commit($conn);
        redirect_with_alert("Permintaan barang berhasil diproses. Stok pusat berkurang, stok {$nama_toko_cabang_log} bertambah.", $redirect_page);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Transaksi Gagal: " . $e->getMessage(), $redirect_page);
    }
}

// Menghapus Permintaan Barang (PP) oleh Admin Pusat/Owner
if (isset($_POST['hapuspermintaanpp'])) {
    $idpp_to_delete = intval($_POST['idpp']);
    $redirect_page_pp = '/stockmate/admin/pp.php'; 

    if (!isset($_SESSION['roles']) || !in_array($_SESSION['roles'], ['adminPusat', 'owner'])) {
       redirect_with_alert("Anda tidak punya izin menghapus permintaan ini!", $redirect_page_pp);
       exit;
    }
    if ($idpp_to_delete <= 0) redirect_with_alert("ID Permintaan tidak valid!", $redirect_page_pp);

    mysqli_begin_transaction($conn);
    try {
        $cek_status_query = mysqli_query($conn, "SELECT status FROM pp WHERE idpp='$idpp_to_delete'");
        if ($cek_status_query && mysqli_num_rows($cek_status_query) > 0) {
            $data_pp = mysqli_fetch_assoc($cek_status_query);
            if ($data_pp['status'] != 'PENDING') {
                throw new Exception("Hanya permintaan dengan status PENDING yang dapat dihapus.");
            }
        } else {
            throw new Exception("Permintaan tidak ditemukan.");
        }

        $delete_pp_query = mysqli_query($conn, "DELETE FROM pp WHERE idpp='$idpp_to_delete'");
        if (!$delete_pp_query) throw new Exception("Gagal menghapus permintaan dari database. Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        redirect_with_alert("Permintaan barang (PP) berhasil dihapus.", $redirect_page_pp);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Gagal menghapus permintaan: " . $e->getMessage(), $redirect_page_pp);
    }
    exit;
}

// UPDATE PERMINTAAN PP OLEH ADMIN CABANG (HANYA JIKA STATUS PENDING)
if (isset($_POST['updatepermintaanpp_cabang'])) {
    $idpp = intval($_POST['idpp']);
    $barangnya_baru = intval($_POST['barangnya']); 
    $qty_baru = intval($_POST['qty']);
    $nopp_baru = mysqli_real_escape_string($conn, $_POST['nopp']);
    $keterangan_baru = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $penerima_form = mysqli_real_escape_string($conn, $_POST['penerima']);

    $current_role = $_SESSION['roles'] ?? '';
    $current_username = $_SESSION['username'] ?? '';
    $redirect_page_cabang_pp = '/stockmate/index.php';  
    $session_penerima_identifier = '';

    if ($current_role == 'adminParahita') {
        $redirect_page_cabang_pp = '/stockmate/adminParahita/permintaanbarangkeluar.php'; 
        $session_penerima_identifier = 'Toko Parahita';
    } elseif ($current_role == 'adminSerpong') {
        $redirect_page_cabang_pp = '/stockmate/adminSerpong/permintaanbarangkeluar.php'; 
        $session_penerima_identifier = 'Toko Serpong';
    } elseif ($current_role == 'admin' && !in_array($current_username, ['adminPusat', 'adminParahita', 'adminSerpong', 'owner'])) {
        $redirect_page_cabang_pp = '/stockmate/adminBaru/permintaanbarangkeluar.php'; 
        $session_penerima_identifier = $current_username; 
    } else {
        redirect_with_alert("Role tidak diizinkan untuk mengupdate permintaan ini.", '/stockmate/index.php'); 
        exit;
    }

    if ($qty_baru <= 0) redirect_with_alert("Quantity baru harus lebih dari 0!", $redirect_page_cabang_pp);
    if ($barangnya_baru <= 0) redirect_with_alert("Barang baru belum dipilih!", $redirect_page_cabang_pp);

    mysqli_begin_transaction($conn);
    try {
        $query_cek_pp = mysqli_query($conn, "SELECT * FROM pp WHERE idpp='$idpp' FOR UPDATE");
        if (!$query_cek_pp || mysqli_num_rows($query_cek_pp) == 0) throw new Exception("Permintaan (PP) tidak ditemukan.");
        $data_pp_lama = mysqli_fetch_assoc($query_cek_pp);

        if ($data_pp_lama['status'] != 'PENDING') throw new Exception("Hanya permintaan dengan status PENDING yang dapat diedit.");
        if ($data_pp_lama['penerima'] != $session_penerima_identifier) throw new Exception("Anda tidak berhak mengedit permintaan ini.");
        if ($penerima_form != $session_penerima_identifier) throw new Exception("Data penerima form tidak konsisten.");

        $cek_stock_pusat_query_edit = mysqli_query($conn, "SELECT namabarang, stock FROM pusatstock WHERE idbarang='$barangnya_baru'");
        if (!$cek_stock_pusat_query_edit || mysqli_num_rows($cek_stock_pusat_query_edit) == 0) throw new Exception("Barang baru yang dipilih tidak ditemukan di gudang pusat.");
        $data_stock_pusat_edit = mysqli_fetch_assoc($cek_stock_pusat_query_edit);
        $nama_barang_pusat_edit = htmlspecialchars($data_stock_pusat_edit['namabarang']);
        $stock_sekarang_pusat_edit = intval($data_stock_pusat_edit['stock']);

        if ($qty_baru > $stock_sekarang_pusat_edit) {
            throw new Exception("Update gagal! Kuantitas baru ({$qty_baru}) untuk barang '{$nama_barang_pusat_edit}' melebihi stok tersedia di Pusat ({$stock_sekarang_pusat_edit}).");
        }

        $update_pp_query = mysqli_query($conn, "UPDATE pp SET idbarang='$barangnya_baru', qty='$qty_baru', nopp='$nopp_baru', keterangan='$keterangan_baru' WHERE idpp='$idpp' AND status='PENDING' AND penerima='".mysqli_real_escape_string($conn, $session_penerima_identifier)."'");
        if (!$update_pp_query) throw new Exception("Gagal mengupdate permintaan (PP). Error: ".mysqli_error($conn));

        mysqli_commit($conn);
        redirect_with_alert("Permintaan barang (PP) berhasil diupdate.", $redirect_page_cabang_pp);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Update Gagal: " . $e->getMessage(), $redirect_page_cabang_pp);
    }
    exit;
}


// DELETE PERMINTAAN PP OLEH ADMIN CABANG (HANYA JIKA STATUS PENDING)
if (isset($_POST['hapuspermintaanpp_cabang'])) {
    $idpp_to_delete = intval($_POST['idpp']);
    $penerima_check_form = mysqli_real_escape_string($conn, $_POST['penerima_check']);

    $current_role = $_SESSION['roles'] ?? '';
    $current_username = $_SESSION['username'] ?? '';
    $redirect_page_cabang_pp_delete = '/stockmate/index.php';  
    $session_penerima_identifier = '';

    if ($current_role == 'adminParahita') {
        $redirect_page_cabang_pp_delete = '/stockmate/adminParahita/permintaanbarangkeluar.php'; 
        $session_penerima_identifier = 'Toko Parahita';
    } elseif ($current_role == 'adminSerpong') {
        $redirect_page_cabang_pp_delete = '/stockmate/adminSerpong/permintaanbarangkeluar.php'; 
        $session_penerima_identifier = 'Toko Serpong';
    } elseif ($current_role == 'admin' && !in_array($current_username, ['adminPusat', 'adminParahita', 'adminSerpong', 'owner'])) {
        $redirect_page_cabang_pp_delete = '/stockmate/adminBaru/permintaanbarangkeluar.php'; 
        $session_penerima_identifier = $current_username;
    } else {
        redirect_with_alert("Akses ditolak atau role tidak valid untuk aksi ini.", '/stockmate/index.php'); 
        exit;
    }
    if (empty($session_penerima_identifier)) {
        redirect_with_alert("Tidak dapat mengidentifikasi pengguna.", '/stockmate/index.php'); 
        exit;
    }
    if ($idpp_to_delete <= 0) {
        redirect_with_alert("ID Permintaan tidak valid!", $redirect_page_cabang_pp_delete);
        exit;
    }

    mysqli_begin_transaction($conn);
    try {
        $query_cek_pp_hapus = mysqli_query($conn, "SELECT * FROM pp WHERE idpp='$idpp_to_delete' FOR UPDATE");
        if (!$query_cek_pp_hapus || mysqli_num_rows($query_cek_pp_hapus) == 0) throw new Exception("Permintaan (PP) tidak ditemukan.");
        $data_pp_to_delete = mysqli_fetch_assoc($query_cek_pp_hapus);

        if ($data_pp_to_delete['status'] != 'PENDING') throw new Exception("Hanya permintaan dengan status PENDING yang dapat dihapus.");
        if ($data_pp_to_delete['penerima'] != $session_penerima_identifier) throw new Exception("Anda tidak berhak menghapus permintaan ini (data penerima DB tidak cocok).");
        if ($penerima_check_form != $session_penerima_identifier) throw new Exception("Data validasi penerima form tidak cocok.");

        $delete_pp_cabang_query = mysqli_query($conn, "DELETE FROM pp WHERE idpp='$idpp_to_delete' AND status='PENDING' AND penerima='".mysqli_real_escape_string($conn, $session_penerima_identifier)."'");
        if (!$delete_pp_cabang_query) throw new Exception("Gagal menghapus permintaan (PP) dari database. Error: ".mysqli_error($conn));
        if (mysqli_affected_rows($conn) == 0) throw new Exception("Permintaan tidak dapat dihapus (kemungkinan status/penerima berubah).");

        mysqli_commit($conn);
        redirect_with_alert("Permintaan barang (PP) berhasil dihapus.", $redirect_page_cabang_pp_delete);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        redirect_with_alert("Gagal menghapus permintaan: " . $e->getMessage(), $redirect_page_cabang_pp_delete);
    }
    exit;
}

?>