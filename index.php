<?php
// Mulai session di awal
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Koneksi ke database
$conn = mysqli_connect("localhost", "root", "12345678", "stockbarang");
if (!$conn) { die("Koneksi Gagal: " . mysqli_connect_error()); }

// Fungsi helper redirect
function redirect_login($location) {
    $base_path = '/stockmate/'; // Sesuaikan jika base path aplikasi Anda berbeda

    // Normalisasi base_path agar selalu diakhiri satu slash
    $normalized_base_path = rtrim($base_path, '/') . '/';

    $final_location = '';

    // Cek apakah location sudah merupakan path lengkap (dimulai dengan base_path atau http/https)
    if (strpos($location, $normalized_base_path) === 0 || preg_match('/^(http:\/\/|https:\/\/|\/\/)/i', $location)) {
        $final_location = $location;
    } else {
        // Normalisasi location agar tidak diawali slash jika akan digabung dengan base_path
        $normalized_location = ltrim($location, '/');
        $final_location = $normalized_base_path . $normalized_location;
    }

    if (!headers_sent()) {
        header("Location: " . $final_location);
        exit();
    } else {
        // Fallback jika header sudah terkirim
        echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Redirecting...</title></head><body>';
        // loadingOverlay akan hilang saat halaman baru dimuat.
        echo '<script type="text/javascript">window.location.href="'.$final_location.'";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url='.$final_location.'" /></noscript>';
        echo '</body></html>';
        exit;
    }
}

// --- Pengecekan Jika User Sudah Login ---
if (isset($_SESSION['log']) && $_SESSION['log'] == 'Logged' && isset($_SESSION['roles'])) {
    $role = $_SESSION['roles'];
    $username_session = $_SESSION['username'] ?? '';

    // Daftar role dan path redirect bisa juga disimpan dalam array untuk kemudahan pengelolaan jika sangat banyak
    $role_paths = [
        'adminPusat'       => 'admin/',
        'karyawanPusat'    => 'karyawanPusat/',
        'owner'            => 'owner/',
        'adminParahita'    => 'adminParahita/',
        'adminSerpong'     => 'adminSerpong/',
        'karyawanParahita' => 'karyawanParahita/',
        'karyawanSerpong'  => 'karyawanSerpong/'
    ];

    if (array_key_exists($role, $role_paths)) {
        redirect_login($role_paths[$role]);
    } elseif ($role == 'admin' && !in_array($username_session, ['adminPusat', 'adminParahita', 'adminSerpong', 'owner', 'karyawanPusat', 'karyawanParahita', 'karyawanSerpong', 'karyawan'])) {
        redirect_login('adminBaru/');
    } elseif ($role == 'karyawan' && !in_array($username_session, ['adminPusat', 'adminParahita', 'adminSerpong', 'owner', 'karyawanPusat', 'karyawanParahita', 'karyawanSerpong', 'admin'])) {
        redirect_login('karyawanBaru/');
    }
}

// --- Proses Login Jika Form Disubmit ---
$login_error = '';
if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password_input = $_POST['password'];

    if (empty($username) || empty($password_input)) {
        $login_error = 'Username dan password tidak boleh kosong!';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT iduser, password, roles FROM login WHERE username=?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) === 1) {
            $userdata = mysqli_fetch_assoc($result);
            $hashed_password_db = $userdata['password'];
            $role = $userdata['roles'];
            $iduser = $userdata['iduser'];

            if (password_verify($password_input, $hashed_password_db)) {
                $_SESSION['log'] = 'Logged';
                $_SESSION['username'] = $username;
                $_SESSION['roles'] = $role;
                $_SESSION['iduser'] = $iduser;

                $role_paths_login = [ // Digunakan juga untuk redirect setelah login sukses
                    'adminPusat'       => 'admin/',
                    'karyawanPusat'    => 'karyawanPusat/',
                    'owner'            => 'owner/',
                    'adminParahita'    => 'adminParahita/',
                    'adminSerpong'     => 'adminSerpong/',
                    'karyawanParahita' => 'karyawanParahita/',
                    'karyawanSerpong'  => 'karyawanSerpong/'
                ];

                if (array_key_exists($role, $role_paths_login)) {
                    redirect_login($role_paths_login[$role]);
                } elseif ($role == 'admin' && !in_array($username, ['adminPusat', 'adminParahita', 'adminSerpong', 'owner', 'karyawanPusat', 'karyawanParahita', 'karyawanSerpong', 'karyawan'])) {
                    redirect_login('adminBaru/');
                } elseif ($role == 'karyawan' && !in_array($username, ['adminPusat', 'adminParahita', 'adminSerpong', 'owner', 'karyawanPusat', 'karyawanParahita', 'karyawanSerpong', 'admin'])) {
                    redirect_login('karyawanBaru/');
                } else {
                    $login_error = 'Role pengguna tidak valid atau tidak dikenali untuk pengalihan!';
                    session_unset(); // Hapus semua variabel session
                    session_destroy(); // Hancurkan session
                }
            } else {
                $login_error = 'Username atau password salah!';
            }
        } else {
            $login_error = 'Username atau password salah!';
        }
        mysqli_stmt_close($stmt);
    }
}
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>StockMate - Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap"
        rel="stylesheet"
    />
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        rel="stylesheet"
    />

    <style>
        :root {
            --primary-color: #0E3B43;
            --primary-color-darker: #0A2A30;
            --accent-color: #2C6C77;
            --text-light: #ffffff;
            --text-dark: #333333;
            --text-muted: #666666;
            --bg-light: #E8F1F2;
            --border-color: #D0DDE4;
            --error-bg: #f8d7da;
            --error-text: #721c24;
            --error-border: #f5c6cb;
        }

        html {
          box-sizing: border-box;
        }
        *, *:before, *:after {
          box-sizing: inherit;
        }

        body {
            font-family: "Plus Jakarta Sans", sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            overflow-x: hidden; /* Mencegah scroll horizontal, membolehkan vertikal jika konten panjang */
        }

        .background {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .noise {
            position: absolute;
            top: -200%; left: -200%;
            width: 500%; height: 500%;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 250 250' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
            opacity: 0.02;
            animation: noiseAnim 0.5s infinite steps(2);
            pointer-events: none;
        }
        @keyframes noiseAnim { 0% { transform: translate(0,0); } 25% { transform: translate(-2px,2px); } 50% { transform: translate(2px,-2px); } 75% { transform: translate(2px,2px); } 100% { transform: translate(-2px,-2px); }}

        .gradient-sphere {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.35;
            pointer-events: none;
        }
        .sphere-1 {
            width: 550px; height: 550px;
            background: radial-gradient(circle, var(--primary-color) 0%, transparent 65%);
            top: -120px; left: -180px;
            animation: sphereAnim1 22s infinite alternate ease-in-out;
        }
        .sphere-2 {
            width: 450px; height: 450px;
            background: radial-gradient(circle, var(--accent-color) 0%, transparent 65%);
            bottom: -150px; right: -120px;
            animation: sphereAnim2 28s infinite alternate ease-in-out;
        }
        @keyframes sphereAnim1 { 0% { transform: translate(0,0) scale(1); opacity: 0.3; } 100% { transform: translate(60px, 40px) scale(1.25); opacity: 0.4; } }
        @keyframes sphereAnim2 { 0% { transform: translate(0,0) scale(1); opacity: 0.35; } 100% { transform: translate(-50px, -70px) scale(1.15); opacity: 0.25; } }

        .login-page {
            display: flex;
            width: 100%;
            max-width: 1200px;
            min-height: 90vh; /* Bisa disesuaikan agar tidak terlalu besar di layar kecil jika perlu */
            max-height: 700px; /* Batas tinggi untuk layar besar */
            background-color: var(--text-light);
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            margin: 20px; /* Memberi jarak dari tepi viewport pada layar besar */
        }

        .left-panel {
            flex-basis: 55%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            color: var(--text-light);
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .brand { display: flex; align-items: center; margin-bottom: 40px; }
        .logo {
            font-size: 2.5em; font-weight: 700;
            background-color: rgba(255,255,255,0.15);
            color: var(--text-light);
            width: 60px; height: 60px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 12px; margin-right: 15px;
        }
        .logo-text { font-size: 2em; font-weight: 600; }

        .intro-text h1 {
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.3;
        }
        .intro-text p {
            font-size: 1em;
            line-height: 1.7;
            opacity: 0.85;
            margin-bottom: 30px;
        }

        .features .feature {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.08);
            transition: transform 0.3s ease, background-color 0.3s ease;
        }
        .features .feature:hover {
            transform: translateX(10px);
            background-color: rgba(255, 255, 255, 0.15);
        }
        .feature-icon { margin-right: 15px; font-size: 1.5em; width: 30px; text-align: center; }
        .feature-text { font-size: 0.95em; }

        .footer {
            font-size: 0.85em;
            opacity: 0.75;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap; /* Agar footer bisa wrap di layar kecil jika perlu */
            gap: 10px; /* Jarak antar item footer */
        }
        .footer nav ul { list-style: none; padding: 0; margin: 0; display: flex; }
        .footer nav li { margin-left: 15px; }
        .footer nav li:first-child { margin-left: 0; } /* Untuk kasus wrap */
        .footer nav a { color: var(--text-light); text-decoration: none; }
        .footer nav a:hover { text-decoration: underline; }

        .fade-in { opacity: 0; transform: translateY(20px); animation: fadeInAnim 0.6s forwards; }
        .fade-in-1 { animation-delay: 0.2s; }
        .fade-in-2 { animation-delay: 0.4s; }
        .fade-in-3 { animation-delay: 0.6s; }
        .fade-in-4 { animation-delay: 0.8s; }
        @keyframes fadeInAnim { to { opacity: 1; transform: translateY(0); } }

        .right-panel {
            flex-basis: 45%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            background-color: var(--text-light);
        }

        .login-container {
            width: 100%;
            max-width: 400px;
        }

        .login-header { text-align: center; margin-bottom: 30px; }
        .login-header h2 {
            font-size: 1.8em;
            color: var(--text-dark);
            margin-bottom: 8px;
            font-weight: 600;
        }
        .login-header p { color: var(--text-muted); font-size: 0.95em; }

        .error-message {
            background-color: var(--error-bg);
            color: var(--error-text);
            border: 1px solid var(--error-border);
            padding: 0.8rem 1.25rem;
            margin-bottom: 1.5rem;
            border-radius: 0.3rem;
            font-size: 0.9em;
            text-align: center;
        }

        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-size: 0.9em;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text-dark);
        }
        .input-with-icon { position: relative; }
        .form-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.95em;
            pointer-events: none; /* Agar ikon tidak menghalangi klik pada input */
        }
        .input-field {
            width: 100%;
            padding: 12px 15px 12px 45px; /* Padding kiri untuk ikon */
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 1em;
            /* box-sizing: border-box; Sudah dihandle global */
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        .input-field:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(14, 59, 67, 0.25);
        }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            padding: 5px;
            font-size: 1.1em;
        }
        .password-toggle:hover { color: var(--primary-color); }

        .extra-options {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 25px;
            font-size: 0.85em;
        }
        .forgot-password-link {
            color: var(--primary-color);
            text-decoration: none;
        }
        .forgot-password-link:hover { text-decoration: underline; }

        .login-button {
            width: 100%;
            padding: 14px 20px;
            background-color: var(--primary-color);
            color: var(--text-light);
            border: none;
            border-radius: 8px;
            font-size: 1.05em;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
        .login-button:hover {
            background-color: var(--primary-color-darker);
            transform: translateY(-2px);
        }
        .login-button:active { transform: translateY(0); }

        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: var(--primary-color); /* Bisa juga rgba(0,0,0,0.7) jika ingin semi-transparan */
            z-index: 9999;
            display: none; /* Defaultnya tidak tampil */
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .loading-animation {
            display: flex;
            align-items: flex-end;
            height: 60px;
        }
        .loading-animation .bar {
            width: 10px;
            margin: 0 4px;
            background-color: white;
            border-radius: 5px;
            animation: loading-wave 1.2s infinite ease-in-out;
        }
        .loading-animation .bar:nth-child(1) { height: 30px; animation-delay: 0s; }
        .loading-animation .bar:nth-child(2) { height: 50px; animation-delay: 0.1s; }
        .loading-animation .bar:nth-child(3) { height: 20px; animation-delay: 0.2s; }
        .loading-animation .bar:nth-child(4) { height: 50px; animation-delay: 0.3s; }
        .loading-animation .bar:nth-child(5) { height: 30px; animation-delay: 0.4s; }

        @keyframes loading-wave {
            0%, 40%, 100% { transform: scaleY(0.4); height: 20px; }
            20% { transform: scaleY(1.0); height: 60px; }
        }
        #loadingOverlay p {
            color: white;
            margin-top: 20px;
            font-size: 1.2em;
            font-weight: 500;
        }

        /* Tablet dan mode kolom */
        @media (max-width: 992px) {
            body {
                align-items: flex-start; /* Agar konten mulai dari atas jika lebih panjang dari viewport */
            }
            .login-page {
                flex-direction: column;
                max-height: none; /* Hapus batasan tinggi */
                min-height: 100vh; /* Minimal setinggi viewport */
                height: auto; /* Tinggi menyesuaikan konten */
                margin: 0; /* Hapus margin agar full-width */
                border-radius: 0; /* Hapus border-radius untuk tampilan full-screen */
                box-shadow: none; /* Hapus shadow untuk tampilan lebih flat */
            }
            .left-panel {
                flex-basis: auto; /* Tinggi otomatis */
                min-height: auto; /* Tidak perlu min-height spesifik, biarkan konten menentukan */
                padding: 40px 30px; /* Sesuaikan padding */
                justify-content: center; /* Pusatkan konten jika lebih pendek */
                text-align: center; /* Pusatkan teks */
            }
            .left-panel .brand {
                justify-content: center; /* Pusatkan brand */
            }
            .left-panel .intro-text h1 { font-size: 2em; }
            .left-panel .intro-text p { font-size: 0.9em; margin-bottom: 20px; }
            .features { display: none; } /* Fitur tetap disembunyikan */
            .footer {
                justify-content: center; /* Pusatkan footer */
                text-align: center;
                margin-top: 30px;
            }
            .footer nav ul { justify-content: center; }
            .footer nav li { margin: 0 10px; }


            .right-panel {
                flex-basis: auto; /* Lebar otomatis */
                padding: 40px 20px; /* Sesuaikan padding */
                width: 100%; /* Pastikan mengambil lebar penuh */
            }
            .login-container { max-width: 100%; }
        }

        /* Smartphone */
        @media (max-width: 480px) {
            .left-panel {
                padding: 30px 20px;
            }
            .left-panel .brand { margin-bottom: 20px; }
            .left-panel .logo { width: 50px; height: 50px; font-size: 2em; }
            .left-panel .logo-text { font-size: 1.8em; }
            .left-panel .intro-text h1 { font-size: 1.8em; }
            .left-panel .intro-text p { font-size: 0.85em; }

            .login-header h2 { font-size: 1.6em; }
            .login-header p { font-size: 0.9em; }

            .input-field { padding: 12px 12px 12px 40px; } /* Sesuaikan padding input */
            .form-icon { left: 12px; }
            .password-toggle { right: 8px; }

            .footer { font-size: 0.75em; }
            .footer nav li { margin: 0 8px; }
        }
    </style>
</head>
<body>
    <div id="loadingOverlay">
        <div class="loading-animation">
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
            <div class="bar"></div>
        </div>
        <p>Loading ...</p>
    </div>

    <div class="background">
        <div class="noise"></div>
        <div class="gradient-sphere sphere-1"></div>
        <div class="gradient-sphere sphere-2"></div>
    </div>

    <div class="login-page">
        <div class="left-panel">
            <div>
                <div class="brand fade-in fade-in-1">
                    <div class="logo">SM</div>
                    <div class="logo-text">StockMate</div>
                </div>
                <div class="intro-text fade-in fade-in-2">
                    <h1>Solusi Inventaris Cerdas untuk Bisnis Anda</h1>
                    <p>StockMate membantu Anda mengelola stok barang antar toko dengan mudah, efisien, dan akurat.</p>
                </div>
            </div>
            <div>
                <div class="features fade-in fade-in-3">
                    <div class="feature"><div class="feature-icon"><i class="fa-solid fa-box-open"></i></div><div class="feature-text">Manajemen Stok Terpusat</div></div>
                    <div class="feature"><div class="feature-icon"><i class="fa-solid fa-store"></i></div><div class="feature-text">Kelola Banyak Toko</div></div>
                    <div class="feature"><div class="feature-icon"><i class="fa-solid fa-clipboard-list"></i></div><div class="feature-text">Pelaporan Real-time</div></div>
                </div>
                <div class="footer fade-in fade-in-4">
                    <span>© <?php echo date('Y'); ?> StockMate.</span>
                    <nav><ul><li><a href="#">Terms</a></li><li><a href="#">Privacy</a></li><li><a href="#">Help</a></li></ul></nav>
                </div>
            </div>
        </div>

        <div class="right-panel">
            <div class="login-container">
                <div class="login-header">
                    <h2>Welcome Back!</h2>
                    <p>Sign in to your StockMate account.</p>
                </div>

                <?php if (!empty($login_error)): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($login_error); ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="index.php" id="loginForm">
                    <div class="form-group">
                        <label for="inputUsername">Username</label>
                        <div class="input-with-icon">
                            <input name="username" id="inputUsername" type="text" class="input-field" placeholder="Masukkan username Anda" required autocomplete="username"/>
                            <i class="fa-regular fa-user form-icon"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPassword">Password</label>
                        <div class="input-with-icon">
                            <input name="password" type="password" id="inputPassword" class="input-field" placeholder="Masukkan password Anda" required autocomplete="current-password"/>
                            <i class="fa-solid fa-lock form-icon"></i>
                            <button type="button" class="password-toggle" aria-label="Toggle password visibility"><i class="fa-regular fa-eye"></i></button>
                        </div>
                    </div>
                    <div class="extra-options">
                        <a href="#" class="forgot-password-link">Lupa Password?</a>
                    </div>
                    <button type="submit" name="login" class="login-button">Sign In</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById('loginForm');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const passwordToggles = document.querySelectorAll('.password-toggle');

            if (loginForm && loadingOverlay) {
                loginForm.addEventListener('submit', function(event) {
                    const usernameInput = document.getElementById('inputUsername');
                    const passwordInput = document.getElementById('inputPassword');
                    let isValid = true; // Asumsikan valid sampai terbukti tidak

                    // Validasi dasar di sisi klien (PHP sudah melakukan validasi utama)
                    // Anda bisa menambahkan validasi yang lebih spesifik di sini jika perlu
                    if (usernameInput.value.trim() === '' || passwordInput.value.trim() === '') {
                        // Jika ingin mencegah submit jika kosong dari sisi klien juga:
                        // isValid = false;
                        // event.preventDefault(); // Hentikan submit form
                        // Tampilkan pesan error kustom di klien jika perlu
                    }

                    if(isValid) { // Hanya tampilkan loading jika form dianggap valid oleh klien
                        loadingOverlay.style.display = 'flex';
                    }
                });
            }

            // Sembunyikan overlay jika ada error dari PHP (halaman dimuat ulang dengan pesan error)
            <?php if (!empty($login_error)): ?>
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
            }
            <?php endif; ?>

            passwordToggles.forEach(toggle => {
                toggle.addEventListener('click', function () {
                    const passwordInput = this.closest('.input-with-icon').querySelector('input[type="password"], input[type="text"]'); // Lebih robust selector
                    const icon = this.querySelector('i');

                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
        });
    </script>
</body>
</html>