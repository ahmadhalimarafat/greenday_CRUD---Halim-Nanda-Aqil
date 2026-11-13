<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Koneksi database greenday
require_once __DIR__ . "/connect.php";

// ---------------------------------------------------------
// 1. Handler Logout
// ---------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: index.php?status=success&msg=" . urlencode("Anda telah berhasil keluar (logout) dari sistem Green Day Admin."));
    exit;
}

// ---------------------------------------------------------
// 2. Handler Login Form Submit
// ---------------------------------------------------------
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $username_or_email = trim($_POST['username_or_email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username_or_email) || empty($password)) {
        $login_error = "Harap masukkan username/email dan password Anda!";
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, username, email, password, full_name, role FROM users WHERE username = ? OR email = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $username_or_email, $username_or_email);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($res && $user = mysqli_fetch_assoc($res)) {
                if (password_verify($password, $user['password'])) {
                    // Set Session
                    $_SESSION['user_id'] = (int)$user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];

                    // Update timestamp login terakhir
                    mysqli_query($conn, "UPDATE users SET last_login = NOW() WHERE id = " . (int)$user['id']);

                    header("Location: index.php?status=success&msg=" . urlencode("Selamat datang kembali, " . $user['full_name'] . " (" . strtoupper($user['role']) . ")!"));
                    exit;
                } else {
                    $login_error = "Password yang Anda masukkan salah. Silakan coba lagi!";
                }
            } else {
                $login_error = "Akun dengan username atau email tersebut tidak terdaftar di sistem!";
            }
            mysqli_stmt_close($stmt);
        } else {
            $login_error = "Terjadi kegagalan koneksi database saat validasi login.";
        }
    }
}

// ---------------------------------------------------------
// 3. Tampilan Halaman Login (Jika Belum Login)
// ---------------------------------------------------------
if (!isset($_SESSION['user_id'])) {
    ?>
    <!doctype html>
    <html lang="id" data-bs-theme="dark">
      <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Login Administrator - Green Day CMS Portal</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
        <link rel="stylesheet" href="./css/adminlte.css" />
        <style>
          body.login-bg {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 50% 20%, #153222 0%, #0b0f12 75%);
            font-family: system-ui, -apple-system, sans-serif;
            padding: 24px 12px;
          }
          .login-container {
            width: 100%;
            max-width: 460px;
          }
          .login-box-card {
            background: rgba(18, 26, 23, 0.94);
            border: 1px solid rgba(0, 188, 140, 0.4);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.7), 0 0 25px rgba(0, 188, 140, 0.2);
            overflow: hidden;
            backdrop-filter: blur(12px);
          }
          .login-brand-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            filter: drop-shadow(0 0 14px rgba(0, 188, 140, 0.65));
          }
          .btn-login-greenday {
            background: linear-gradient(135deg, #00bc8c, #008f68);
            border: none;
            color: #ffffff;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 12px;
            transition: all 0.3s ease;
          }
          .btn-login-greenday:hover {
            background: linear-gradient(135deg, #05d6a0, #00a87d);
            box-shadow: 0 0 20px rgba(0, 188, 140, 0.6);
            color: #ffffff;
            transform: translateY(-2px);
          }
          .demo-role-card {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            transition: all 0.2s ease;
            cursor: pointer;
          }
          .demo-role-card:hover {
            background: rgba(0, 188, 140, 0.12);
            border-color: rgba(0, 188, 140, 0.4);
            transform: translateY(-2px);
          }
        </style>
      </head>
      <body class="login-bg">
        <div class="login-container">
          <div class="text-center mb-4">
            <img src="./assets/img/logo.png" alt="Green Day" class="login-brand-logo mb-2" />
            <h2 class="fw-black text-white tracking-wide mb-1" style="letter-spacing: 2px;">GREEN DAY</h2>
            <p class="text-success small fw-semibold text-uppercase mb-0" style="letter-spacing: 1px;">Admin & Content Management Portal</p>
          </div>

          <div class="login-box-card p-4 p-md-5">
            <h4 class="fw-bold text-white text-center mb-1">Masuk Administrator</h4>
            <p class="text-secondary small text-center mb-4">Gunakan akun resmi untuk mengakses dashboard sistem</p>

            <?php if (!empty($login_error)): ?>
              <div class="alert alert-danger py-2 px-3 small d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2 flex-shrink-0"></i>
                <div><?php echo htmlspecialchars($login_error); ?></div>
              </div>
            <?php endif; ?>

            <?php if (isset($_GET['msg'])): ?>
              <div class="alert alert-info py-2 px-3 small d-flex align-items-center mb-3" role="alert">
                <i class="bi bi-info-circle-fill fs-5 me-2 flex-shrink-0"></i>
                <div><?php echo htmlspecialchars($_GET['msg']); ?></div>
              </div>
            <?php endif; ?>

            <form action="index.php" method="POST">
              <input type="hidden" name="login_submit" value="1" />
              <div class="mb-3">
                <label for="login_user" class="form-label text-light small fw-semibold">Username atau Email</label>
                <div class="input-group">
                  <span class="input-group-text bg-dark border-secondary text-secondary">
                    <i class="bi bi-person-fill"></i>
                  </span>
                  <input
                    type="text"
                    name="username_or_email"
                    id="login_user"
                    class="form-control bg-dark text-white border-secondary"
                    placeholder="Contoh: aimcleverley / admin@greenday.com"
                    value="<?php echo htmlspecialchars($_POST['username_or_email'] ?? ''); ?>"
                    required
                    autofocus
                  />
                </div>
              </div>

              <div class="mb-4">
                <label for="login_pass" class="form-label text-light small fw-semibold">Password</label>
                <div class="input-group">
                  <span class="input-group-text bg-dark border-secondary text-secondary">
                    <i class="bi bi-shield-lock-fill"></i>
                  </span>
                  <input
                    type="password"
                    name="password"
                    id="login_pass"
                    class="form-control bg-dark text-white border-secondary"
                    placeholder="Masukkan password akun"
                    required
                  />
                  <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" title="Lihat/Sembunyikan password">
                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                  </button>
                </div>
              </div>

              <button type="submit" class="btn btn-login-greenday w-100 rounded-3 mb-3">
                <i class="bi bi-box-arrow-in-right me-2"></i> MASUK KE DASHBOARD
              </button>
            </form>

            <hr class="border-secondary my-4" />

            <!-- Card Bantuan Akun Login Demo -->
            <div>
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-semibold">PILIHAN AKUN (KLIK UNTUK MENGISI):</span>
                <span class="badge text-bg-dark border border-secondary" style="font-size: 10px;">Role Matrix</span>
              </div>

              <!-- Superadmin -->
              <div class="demo-role-card p-2 mb-2" onclick="fillLogin('aimcleverley', 'admin123')">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <span class="badge text-bg-danger me-1">SUPERADMIN</span>
                    <strong class="text-white small">aimcleverley</strong>
                  </div>
                  <small class="text-success font-monospace">admin123</small>
                </div>
                <div class="text-secondary" style="font-size: 11px;">Akses Penuh: semua data & kelola semua user</div>
              </div>

              <!-- Admin -->
              <div class="demo-role-card p-2 mb-2" onclick="fillLogin('admin_tour', 'admin123')">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <span class="badge text-bg-primary me-1">ADMIN</span>
                    <strong class="text-white small">admin_tour</strong>
                  </div>
                  <small class="text-success font-monospace">admin123</small>
                </div>
                <div class="text-secondary" style="font-size: 11px;">Kelola Konser, Tiket, Konten & modifikasi/delete Editor</div>
              </div>

              <!-- Editor -->
              <div class="demo-role-card p-2" onclick="fillLogin('editor_content', 'editor123')">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <span class="badge text-bg-success me-1">EDITOR</span>
                    <strong class="text-white small">editor_content</strong>
                  </div>
                  <small class="text-success font-monospace">editor123</small>
                </div>
                <div class="text-secondary" style="font-size: 11px;">Katalog Musik, Video, Berita & hanya edit profil sendiri</div>
              </div>
            </div>
          </div>

          <div class="text-center mt-3 text-secondary" style="font-size: 12px;">
            &copy; 2026 Green Day Portal &bull; Sistem CRUD & Hak Akses Berjenjang
          </div>
        </div>

        <script>
          function fillLogin(user, pass) {
            document.getElementById('login_user').value = user;
            document.getElementById('login_pass').value = pass;
          }

          const toggleBtn = document.getElementById('togglePasswordBtn');
          const passInput = document.getElementById('login_pass');
          const passIcon = document.getElementById('togglePasswordIcon');
          if (toggleBtn && passInput) {
            toggleBtn.addEventListener('click', function () {
              const isPassword = passInput.getAttribute('type') === 'password';
              passInput.setAttribute('type', isPassword ? 'text' : 'password');
              passIcon.classList.toggle('bi-eye', !isPassword);
              passIcon.classList.toggle('bi-eye-slash', isPassword);
            });
          }
        </script>
      </body>
    </html>
    <?php
    exit;
}

// Informasi Pengguna yang Sedang Login
$auth_user_id = (int)$_SESSION['user_id'];
$auth_username = $_SESSION['username'];
$auth_name = $_SESSION['full_name'];
$auth_role = $_SESSION['role'] ?? 'editor';
$auth_email = $_SESSION['email'] ?? '';

// Konfigurasi 11 Tabel Database Greenday
$tables_config = [
    'albums' => [
        'label' => 'Albums',
        'sublabel' => 'Master Data Album Musik',
        'icon' => 'bi-disc',
        'description' => 'Menyimpan master data album musik resmi Green Day.',
        'title_col' => 'title',
        'sort_col_idx' => 2
    ],
    'band_members' => [
        'label' => 'Band Members',
        'sublabel' => 'Profil Personel Green Day',
        'icon' => 'bi-people',
        'description' => 'Menyimpan profil resmi, instrumen musik, dan biografi personel band.',
        'title_col' => 'name',
        'sort_col_idx' => 2
    ],
    'community_posts' => [
        'label' => 'Community Posts',
        'sublabel' => 'Obrolan Komunitas Fans',
        'icon' => 'bi-chat-quote',
        'description' => 'Menyimpan kiriman obrolan dan ulasan dari para penggemar.',
        'title_col' => 'author_name',
        'sort_col_idx' => 1
    ],
    'contact_messages' => [
        'label' => 'Contact Messages',
        'sublabel' => 'Pesan Masuk Form Kontak',
        'icon' => 'bi-envelope',
        'description' => 'Menampung pesan saran, kritik, dan pertanyaan fans.',
        'title_col' => 'name',
        'sort_col_idx' => 1
    ],
    'news' => [
        'label' => 'News',
        'sublabel' => 'Artikel Berita & Pengumuman',
        'icon' => 'bi-newspaper',
        'description' => 'Menyimpan artikel pengumuman resmi dan berita aktivitas Green Day.',
        'title_col' => 'title',
        'sort_col_idx' => 2
    ],
    'songs' => [
        'label' => 'Songs',
        'sublabel' => 'Daftar Lagu (Tracks)',
        'icon' => 'bi-music-note-beamed',
        'description' => 'Menyimpan data trek lagu, nomor urut, durasi, dan file audio.',
        'title_col' => 'title',
        'sort_col_idx' => 1
    ],
    'ticket_bookings' => [
        'label' => 'Ticket Bookings',
        'sublabel' => 'Transaksi Pemesanan Tiket',
        'icon' => 'bi-ticket-perforated',
        'description' => 'Mencatat transaksi pemesanan tiket konser resmi dan status pembayaran.',
        'title_col' => 'booking_code',
        'sort_col_idx' => 1
    ],
    'ticket_categories' => [
        'label' => 'Ticket Categories',
        'sublabel' => 'Kategori Kelas Tiket Tur',
        'icon' => 'bi-tags',
        'description' => 'Menyimpan kelas tiket untuk setiap tur konser beserta kuota dan harga.',
        'title_col' => 'category_name',
        'sort_col_idx' => 2
    ],
    'tours' => [
        'label' => 'Tours',
        'sublabel' => 'Jadwal Tur Konser Dunia',
        'icon' => 'bi-globe-americas',
        'description' => 'Menyimpan master agenda tur konser dunia, tanggal, kota, venue, dan status.',
        'title_col' => 'tour_name',
        'sort_col_idx' => 1
    ],
    'users' => [
        'label' => 'Users',
        'sublabel' => 'Pengguna & Administrator CMS',
        'icon' => 'bi-person-gear',
        'description' => 'Menyimpan akun pengguna dan administrator pengelola sistem web dinamis.',
        'title_col' => 'full_name',
        'sort_col_idx' => 2
    ],
    'videos' => [
        'label' => 'Videos',
        'sublabel' => 'Koleksi Video Musik & Live Konser',
        'icon' => 'bi-camera-video',
        'description' => 'Menyimpan koleksi video musik resmi dan rekaman konser live.',
        'title_col' => 'title',
        'sort_col_idx' => 2
    ]
];

// Ambil tabel aktif (default 'albums')
$current_table = isset($_GET['table']) ? trim($_GET['table']) : 'albums';
if (!array_key_exists($current_table, $tables_config)) {
    $current_table = 'albums';
}
$table_info = $tables_config[$current_table];

// Hitung total record untuk masing-masing tabel (ditampilkan pada badge sidebar)
$table_counts = [];
foreach ($tables_config as $tbl => $cfg) {
    $res_cnt = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `$tbl`");
    $cnt_row = $res_cnt ? mysqli_fetch_assoc($res_cnt) : ['total' => 0];
    $table_counts[$tbl] = (int)$cnt_row['total'];
}

// Helper fungsi pemformatan tanggal yang aman dari bug 01-01-1970
if (!function_exists('safe_format_datetime')) {
    function safe_format_datetime($val, $format = 'd-m-Y H:i') {
        if (empty($val) || $val === '0000-00-00' || $val === '0000-00-00 00:00:00') {
            return '-';
        }
        $ts = strtotime($val);
        return $ts ? date($format, $ts) : '-';
    }
}
if (!function_exists('safe_format_date')) {
    function safe_format_date($val, $format = 'd-m-Y') {
        if (empty($val) || $val === '0000-00-00' || $val === '0000-00-00 00:00:00') {
            return '-';
        }
        $ts = strtotime($val);
        return $ts ? date($format, $ts) : '-';
    }
}

// Relasi Foreign Keys untuk tampilan & form dropdown
$albums_list = [];
$res_a = mysqli_query($conn, "SELECT id, title, release_year FROM albums ORDER BY release_year DESC, title ASC");
if ($res_a) {
    while ($ra = mysqli_fetch_assoc($res_a)) {
        $albums_list[$ra['id']] = $ra['title'] . ' (' . $ra['release_year'] . ')';
    }
}

$tours_list = [];
$res_t = mysqli_query($conn, "SELECT id, venue, city, country, event_date FROM tours ORDER BY event_date ASC");
if ($res_t) {
    while ($rt = mysqli_fetch_assoc($res_t)) {
        $tours_list[$rt['id']] = $rt['venue'] . ' - ' . $rt['city'] . ' (' . safe_format_date($rt['event_date'], 'd M Y') . ')';
    }
}

$categories_list = [];
$res_c = mysqli_query($conn, "SELECT c.id, c.category_name, c.price, t.city FROM ticket_categories c LEFT JOIN tours t ON c.tour_id = t.id ORDER BY c.id ASC");
if ($res_c) {
    while ($rc = mysqli_fetch_assoc($res_c)) {
        $categories_list[$rc['id']] = $rc['category_name'] . ' (Rp ' . number_format($rc['price'], 0, ',', '.') . ' - ' . ($rc['city'] ?? '') . ')';
    }
}

// Kolom yang dapat dicari di server-side untuk setiap tabel
$searchable_columns = [
    'albums' => ['title', 'release_year', 'description'],
    'band_members' => ['name', 'role', 'biography'],
    'community_posts' => ['author_name', 'author_email', 'message'],
    'contact_messages' => ['name', 'email', 'subject', 'message'],
    'news' => ['title', 'slug', 'summary', 'content'],
    'songs' => ['title', 'duration', 'lyrics'],
    'ticket_bookings' => ['booking_code', 'buyer_name', 'buyer_email', 'payment_status'],
    'ticket_categories' => ['category_name', 'description'],
    'tours' => ['tour_name', 'city', 'country', 'venue', 'status'],
    'users' => ['username', 'email', 'full_name', 'role'],
    'videos' => ['title', 'category_label', 'description']
];

// Logika Limit Record (Dapat menampilkan 5, 10, 25, 50, 100, 250, hingga 500 / Semua data)
$allowed_limits = [5, 10, 25, 50, 100, 250, 500];
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
if (!in_array($limit, $allowed_limits)) {
    $limit = 10;
}

// Logika Pencarian Server-side
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$where_sql = '';
if ($search_query !== '') {
    $escaped_q = mysqli_real_escape_string($conn, $search_query);
    $cols = $searchable_columns[$current_table] ?? ['id'];
    $clauses = [];
    foreach ($cols as $col) {
        $clauses[] = "`$col` LIKE '%$escaped_q%'";
    }
    if (!empty($clauses)) {
        $where_sql = " WHERE (" . implode(" OR ", $clauses) . ")";
    }
}

// Hitung total record (dengan filter jika pencarian aktif)
if ($where_sql !== '') {
    $res_search_cnt = mysqli_query($conn, "SELECT COUNT(*) AS total FROM `$current_table` $where_sql");
    $total_data = $res_search_cnt ? (int)mysqli_fetch_assoc($res_search_cnt)['total'] : 0;
} else {
    $total_data = $table_counts[$current_table];
}

$total_pages = ceil($total_data / $limit);
if ($total_pages < 1) { $total_pages = 1; }

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1);
if ($page > $total_pages) {
    $page = $total_pages;
}
$offset = ($page - 1) * $limit;

// Ambil data tabel aktif
$data_query = "SELECT * FROM `$current_table` $where_sql ORDER BY id DESC LIMIT $limit OFFSET $offset";
$data_result = mysqli_query($conn, $data_query);

// Parameter query string untuk mempertahankan state pencarian & limit pada pagination
$pagination_params = "&limit=" . $limit . ($search_query !== '' ? "&q=" . urlencode($search_query) : "");

// Helper fungsi resolusi path media gambar/file
if (!function_exists('resolve_media')) {
    function resolve_media($path) {
        if (empty($path)) return '';
        $path = trim($path);
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            return $path;
        }
        if (strpos($path, 'Web Green Day/') === 0) {
            return 'Greenday/' . substr($path, strlen('Web Green Day/'));
        }
        if (strpos($path, '../assets/') === 0) {
            return 'Greenday/assets/' . substr($path, strlen('../assets/'));
        }
        if (strpos($path, 'assets/') === 0) {
            if (file_exists(__DIR__ . '/Greenday/' . $path)) {
                return 'Greenday/' . $path;
            }
            return './' . $path;
        }
        if (strpos($path, 'uploads/') === 0) {
            return './' . $path;
        }
        return $path;
    }
}
?>
<!doctype html>
<html lang="en">
  <!--begin::Head-->
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Data <?php echo htmlspecialchars($table_info['label']); ?> - Green Day Admin</title>

    <!--begin::Theme Init (prevents flash of incorrect theme on load, #6043)-->
    <script>
      (() => {
        'use strict';
        const root = document.documentElement;

        if (root.getAttribute('data-lte-color-mode') === 'off') {
          return;
        }

        const STORAGE_KEY = 'lte-theme';
        let stored = null;
        try {
          stored = localStorage.getItem(STORAGE_KEY);
        } catch {
          // localStorage may be unavailable
        }

        const authored = root.getAttribute('data-bs-theme');
        let resolved = 'light';
        if (stored === 'dark' || stored === 'light') {
          resolved = stored;
        } else if (authored === 'dark' || authored === 'light') {
          resolved = authored;
        } else if (globalThis.matchMedia('(prefers-color-scheme: dark)').matches) {
          resolved = 'dark';
        }
        root.setAttribute('data-bs-theme', resolved);
        root.style.colorScheme = resolved;
        if (resolved !== authored) {
          root.setAttribute('data-lte-theme-resolved', '');
        }
      })();
    </script>
    <!--end::Theme Init-->

    <!--begin::Accessibility Meta Tags-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#007bff" media="(prefers-color-scheme: light)" />
    <meta name="theme-color" content="#1a1a1a" media="(prefers-color-scheme: dark)" />
    <!--end::Accessibility Meta Tags-->

    <!--begin::Primary Meta Tags-->
    <meta name="title" content="Data <?php echo htmlspecialchars($table_info['label']); ?>" />
    <meta name="author" content="Green Day Admin" />
    <meta name="description" content="Green Day CRUD Application based on AdminLTE 4" />
    <!--end::Primary Meta Tags-->

    <!--begin::Accessibility Features-->
    <meta name="supported-color-schemes" content="light dark" />
    <link rel="preload" href="./css/adminlte.css" as="style" />
    <!--end::Accessibility Features-->

    <!--begin::Fonts-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/@fontsource/source-sans-3@5.0.12/index.css"
      integrity="sha256-tXJfXfp6Ewt1ilPzLDtQnJV4hclT9XuaZUKyUvmyr+Q="
      crossorigin="anonymous"
      media="print"
      onload="this.media = 'all'"
    />
    <!--end::Fonts-->

    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/styles/overlayscrollbars.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(OverlayScrollbars)-->

    <!--begin::Third Party Plugin(Bootstrap Icons)-->
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css"
      crossorigin="anonymous"
    />
    <!--end::Third Party Plugin(Bootstrap Icons)-->

    <!--begin::Required Plugin(AdminLTE)-->
    <link rel="stylesheet" href="./css/adminlte.css" />
    <!--end::Required Plugin(AdminLTE)-->
  </head>
  <!--end::Head-->

  <!--begin::Body-->
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <!--begin::App Wrapper-->
    <div class="app-wrapper">

      <!--begin::Top Navbar (Header Dashboard)-->
      <nav class="app-header navbar navbar-expand bg-body shadow-sm">
        <div class="container-fluid">
          <ul class="navbar-nav align-items-center">
            <li class="nav-item">
              <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" title="Buka/Tutup Sidebar">
                <i class="bi bi-list fs-4"></i>
              </a>
            </li>
            <li class="nav-item d-none d-sm-inline-block ms-2">
              <span class="nav-link text-secondary fw-semibold">
                <i class="bi bi-disc-fill text-success me-1"></i> Green Day Admin Portal
              </span>
            </li>
          </ul>

          <ul class="navbar-nav ms-auto align-items-center gap-2">
            <!-- Active Role Badge -->
            <li class="nav-item">
              <?php if ($auth_role === 'superadmin'): ?>
                <span class="badge text-bg-danger px-3 py-2 fw-bold shadow-sm">
                  <i class="bi bi-shield-fill-check me-1"></i> SUPERADMIN
                </span>
              <?php elseif ($auth_role === 'admin'): ?>
                <span class="badge text-bg-primary px-3 py-2 fw-bold shadow-sm">
                  <i class="bi bi-shield-lock me-1"></i> ADMIN
                </span>
              <?php else: ?>
                <span class="badge text-bg-success px-3 py-2 fw-bold shadow-sm">
                  <i class="bi bi-pencil-square me-1"></i> EDITOR
                </span>
              <?php endif; ?>
            </li>

            <!-- User Menu Dropdown -->
            <li class="nav-item dropdown">
              <a href="#" class="nav-link dropdown-toggle d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="rounded-circle bg-secondary text-white fw-bold d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 14px;">
                  <?php echo strtoupper(substr($auth_name, 0, 1)); ?>
                </div>
                <span class="d-none d-md-inline fw-semibold"><?php echo htmlspecialchars($auth_name); ?></span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end shadow">
                <li class="px-3 py-2">
                  <div class="fw-bold"><?php echo htmlspecialchars($auth_name); ?></div>
                  <div class="small text-muted">@<?php echo htmlspecialchars($auth_username); ?></div>
                  <div class="small text-secondary"><?php echo htmlspecialchars($auth_email); ?></div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item" href="index.php?table=users">
                    <i class="bi bi-people me-2"></i> Manajemen Users
                  </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <a class="dropdown-item text-danger fw-bold" href="index.php?action=logout" onclick="return confirm('Apakah Anda yakin ingin keluar dari sistem?');">
                    <i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </nav>
      <!--end::Top Navbar-->

      <!--begin::Sidebar (Menggunakan Template Bawaan AdminLTE 4 Sesuai users.php)-->
      <aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
        <!--begin::Sidebar Brand-->
        <div class="sidebar-brand">
          <!--begin::Brand Link-->
          <a href="index.php" class="brand-link">
            <!--begin::Brand Image-->
            <img
              src="./assets/img/logo.png"
              alt="Green Day Logo"
              class="brand-image opacity-75 shadow"
            />
            <!--end::Brand Image-->
            <!--begin::Brand Text-->
            <span class="brand-text fw-bold">Green Day</span>
            <!--end::Brand Text-->
          </a>
          <!--end::Brand Link-->
        </div>
        <!--end::Sidebar Brand-->

        <!--begin::Sidebar User Info Widget-->
        <div class="px-3 py-3 border-bottom border-secondary border-opacity-25 d-flex align-items-center gap-2">
          <div class="rounded-circle bg-success text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; font-size: 15px;">
            <?php echo strtoupper(substr($auth_name, 0, 1)); ?>
          </div>
          <div class="overflow-hidden">
            <div class="fw-bold text-white text-truncate" style="font-size: 13px;" title="<?php echo htmlspecialchars($auth_name); ?>">
              <?php echo htmlspecialchars($auth_name); ?>
            </div>
            <div class="d-flex align-items-center gap-1 mt-1">
              <span class="badge text-bg-<?php echo ($auth_role === 'superadmin' ? 'danger' : ($auth_role === 'admin' ? 'primary' : 'success')); ?>" style="font-size: 10px;">
                <?php echo strtoupper($auth_role); ?>
              </span>
              <a href="index.php?action=logout" class="text-danger small ms-1" title="Keluar (Logout)" onclick="return confirm('Yakin ingin logout?');">
                <i class="bi bi-box-arrow-right"></i>
              </a>
            </div>
          </div>
        </div>
        <!--end::Sidebar User Info Widget-->

        <!--begin::Sidebar Wrapper-->
        <div class="sidebar-wrapper">
          <nav class="mt-2" aria-label="Main navigation">
            <!--begin::Sidebar Menu-->
            <ul
              class="nav sidebar-menu flex-column"
              data-lte-toggle="treeview"
              data-accordion="false"
              id="navigation"
            >
              <li class="nav-header fw-bold text-uppercase">DAFTAR TABEL</li>

              <?php foreach ($tables_config as $table_key => $config): ?>
                <?php
                  $is_active = ($current_table === $table_key);
                  $is_view_only = ($auth_role === 'editor' && in_array($table_key, ['tours', 'ticket_categories', 'ticket_bookings']));
                ?>
                <li class="nav-item">
                  <a href="index.php?table=<?php echo $table_key; ?>" class="nav-link <?php echo $is_active ? 'active fw-bold' : ''; ?>">
                    <i class="nav-icon bi <?php echo $config['icon']; ?>"></i>
                    <p>
                      <?php echo htmlspecialchars($config['label']); ?>
                      <?php if ($is_view_only): ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle py-0 px-1 me-1" style="font-size: 9px;">View</span>
                      <?php endif; ?>
                      <span class="nav-badge badge <?php echo $is_active ? 'text-bg-light fw-bold' : 'text-bg-secondary'; ?> me-3"><?php echo $table_counts[$table_key] ?? 0; ?></span>
                    </p>
                  </a>
                </li>
              <?php endforeach; ?>
              <li class="nav-header fw-bold text-uppercase mt-3">AKSES & SESI</li>
              <li class="nav-item">
                <a href="index.php?action=logout" class="nav-link text-danger" onclick="return confirm('Apakah Anda yakin ingin logout?');">
                  <i class="nav-icon bi bi-box-arrow-right"></i>
                  <p>Keluar (Logout)</p>
                </a>
              </li>
            </ul>
            <!--end::Sidebar Menu-->
          </nav>
        </div>
        <!--end::Sidebar Wrapper-->
      </aside>
      <!--end::Sidebar-->

      <!--begin::App Main-->
      <main class="app-main">
        <!--begin::App Content Header (Persis seperti contoh users.php)-->
        <div class="app-content-header">
          <!--begin::Container-->
          <div class="container-fluid">
            <!--begin::Row-->
            <div class="row">
              <div class="col-sm-6">
                <h1 class="mb-0 fs-3 fw-bold">Data <?php echo htmlspecialchars($table_info['label']); ?></h1>
              </div>
              <div class="col-sm-6">
                <nav aria-label="breadcrumb">
                  <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item fw-bold"><a href="index.php" class="text-decoration-none">DAFTAR TABEL</a></li>
                    <li class="breadcrumb-item active fw-bold" aria-current="page"><?php echo htmlspecialchars($table_info['label']); ?></li>
                  </ol>
                </nav>
              </div>
            </div>
            <!--end::Row-->
          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content Header-->

        <!--begin::App Content-->
        <div class="app-content">
          <!--begin::Container-->
          <div class="container-fluid">

            <!-- Alert Notifikasi Flash Message -->
            <?php if (isset($_GET['msg'])): ?>
              <?php
                $status_alert = $_GET['status'] ?? 'info';
                $alert_icons = [
                    'success' => 'bi-check-circle-fill',
                    'danger' => 'bi-exclamation-triangle-fill',
                    'warning' => 'bi-exclamation-circle-fill',
                    'info' => 'bi-info-circle-fill'
                ];
                $alert_icon = $alert_icons[$status_alert] ?? 'bi-info-circle-fill';
              ?>
              <div class="alert alert-<?php echo htmlspecialchars($status_alert); ?> alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="bi <?php echo $alert_icon; ?> fs-5 me-2"></i>
                <div><?php echo htmlspecialchars($_GET['msg']); ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <!--begin::Row-->
            <div class="row">
              <div class="col-12">
                <!--begin::Card (Persis seperti Card di users.php)-->
                <div class="card mb-4">
                  <!--begin::Card Header-->
                  <div class="card-header">
                    <div class="row g-2 align-items-center">
                      <div class="col-12 col-md-4">
                        <h3 class="card-title fw-bold">Direktori Data <?php echo htmlspecialchars($table_info['label']); ?></h3>
                      </div>
                      <div class="col-12 col-md-8">
                        <div class="d-flex flex-wrap justify-content-md-end gap-2">
                          <form method="GET" action="index.php" class="d-flex align-items-center">
                            <input type="hidden" name="table" value="<?php echo htmlspecialchars($current_table); ?>">
                            <input type="hidden" name="limit" value="<?php echo $limit; ?>">
                            <div class="input-group input-group-sm w-auto">
                              <span class="input-group-text">
                                <i class="bi bi-search" aria-hidden="true"></i>
                              </span>
                              <input
                                type="search"
                                name="q"
                                id="user-search"
                                class="form-control"
                                placeholder="Cari data..."
                                value="<?php echo htmlspecialchars($search_query); ?>"
                                aria-label="Cari data"
                                style="width: 180px"
                              />
                              <?php if (!empty($search_query)): ?>
                                <a href="index.php?table=<?php echo htmlspecialchars($current_table); ?>&limit=<?php echo $limit; ?>" class="btn btn-outline-secondary" title="Hapus filter pencarian">
                                  <i class="bi bi-x-lg"></i>
                                </a>
                              <?php endif; ?>
                              <button type="submit" class="btn btn-outline-secondary" title="Cari di seluruh database">
                                <i class="bi bi-arrow-right"></i>
                              </button>
                            </div>
                          </form>
                          <?php
                            $can_add = true;
                            $add_restriction_msg = '';
                            if ($current_table === 'users') {
                                if ($auth_role !== 'superadmin') {
                                    $can_add = false;
                                    $add_restriction_msg = 'Hanya Superadmin yang dapat menambah akun pengguna baru';
                                }
                            } elseif ($auth_role === 'editor' && in_array($current_table, ['tours', 'ticket_categories', 'ticket_bookings'])) {
                                $can_add = false;
                                $add_restriction_msg = 'Editor dalam mode Lihat Saja pada data tur & tiket';
                            }
                          ?>
                          <?php if ($can_add): ?>
                            <button
                              type="button"
                              class="btn btn-sm btn-primary fw-bold"
                              data-bs-toggle="modal"
                              data-bs-target="#modal-add"
                            >
                              <i class="bi bi-plus-circle me-1" aria-hidden="true"></i>
                              Tambah Data <?php echo htmlspecialchars($table_info['label']); ?>
                            </button>
                          <?php else: ?>
                            <span class="badge text-bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 small d-inline-flex align-items-center">
                              <i class="bi bi-shield-lock-fill me-1"></i> <?php echo htmlspecialchars($add_restriction_msg); ?>
                            </span>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </div>
                  <!--end::Card Header-->

                  <!--begin::Card Body-->
                  <?php if (!empty($search_query)): ?>
                    <div class="alert alert-light border mx-3 mt-3 mb-0 py-2 px-3 d-flex justify-content-between align-items-center">
                      <span class="small">
                        <i class="bi bi-funnel-fill text-primary me-1"></i>
                        Menampilkan hasil pencarian untuk: <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong> (<strong><?php echo $total_data; ?></strong> data ditemukan)
                      </span>
                      <a href="index.php?table=<?php echo htmlspecialchars($current_table); ?>&limit=<?php echo $limit; ?>" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 12px;">
                        <i class="bi bi-x-circle me-1"></i>Reset
                      </a>
                    </div>
                  <?php endif; ?>
                  <div class="card-body p-0">
                    <div class="table-responsive">
                      <?php if ($data_result && mysqli_num_rows($data_result) > 0) { ?>
                        <table class="table table-hover align-middle m-0">
                          <thead>
                            <tr>
                              <?php if ($current_table === 'albums'): ?>
                                <th>ID</th>
                                <th>Cover</th>
                                <th id="sort-nama" style="cursor: pointer;" title="Klik untuk mengurutkan A-Z / Z-A" data-col="2">
                                  Judul Album <i class="bi bi-arrow-down-up fs-7 text-secondary"></i>
                                </th>
                                <th>Tahun Rilis</th>
                                <th>Deskripsi</th>
                                <th>Tanggal Dibuat</th>

                              <?php elseif ($current_table === 'band_members'): ?>
                                <th>ID</th>
                                <th>Foto</th>
                                <th id="sort-nama" style="cursor: pointer;" title="Klik untuk mengurutkan A-Z / Z-A" data-col="2">
                                  Nama Personel <i class="bi bi-arrow-down-up fs-7 text-secondary"></i>
                                </th>
                                <th>Peran</th>
                                <th>Tanggal Lahir</th>
                                <th>Tahun Bergabung</th>
                                <th>Status</th>

                              <?php elseif ($current_table === 'community_posts'): ?>
                                <th>ID</th>
                                <th id="sort-nama" style="cursor: pointer;" title="Klik untuk mengurutkan A-Z / Z-A" data-col="1">
                                  Nama Pengirim <i class="bi bi-arrow-down-up fs-7 text-secondary"></i>
                                </th>
                                <th>Email</th>
                                <th>Pesan</th>
                                <th>Likes</th>
                                <th>Tanggal Dibuat</th>

                              <?php elseif ($current_table === 'contact_messages'): ?>
                                <th>ID</th>
                                <th id="sort-nama" style="cursor: pointer;" title="Klik untuk mengurutkan A-Z / Z-A" data-col="1">
                                  Nama Pengirim <i class="bi bi-arrow-down-up fs-7 text-secondary"></i>
                                </th>
                                <th>Email</th>
                                <th>Subjek</th>
                                <th>Pesan</th>
                                <th>Status</th>
                                <th>Tanggal Dibuat</th>

                              <?php elseif ($current_table === 'news'): ?>
                                <th>ID</th>
                                <th>Gambar</th>
                                <th id="sort-nama" style="cursor: pointer;" title="Klik untuk mengurutkan A-Z / Z-A" data-col="2">
                                  Judul Berita <i class="bi bi-arrow-down-up fs-7 text-secondary"></i>
                                </th>
                                <th>Slug</th>
                                <th>Ringkasan</th>
                                <th>Tanggal Publikasi</th>
                                <th>Tanggal Dibuat</th>

                              <?php elseif ($current_table === 'songs'): ?>
                                <th>ID</th>
                                <th id="sort-nama" style="cursor: pointer;" title="Klik untuk mengurutkan A-Z / Z-A" data-col="1">
                                  Judul Lagu <i class="bi bi-arrow-down-up fs-7 text-secondary"></i>
                                </th>
                                <th>Album</th>
                                <th>Nomor Trek</th>
                                <th>Durasi</th>
                                <th>File Audio</th>
                                <th>Jumlah Putar</th>
                                <th>Tanggal Dibuat</th>

                              <?php elseif ($current_table === 'ticket_bookings'): ?>
                                <th>ID</th>
                                <th id="sort-nama" style="cursor: pointer;" title="Klik untuk mengurutkan A-Z / Z-A" data-col="1">
                                  Kode Booking <i class="bi bi-arrow-down-up fs-7 text-secondary"></i>
                                </th>
                                <th>Jadwal Konser</th>
                                <th>Kategori</th>
                                <th>Nama Pembeli</th>
                                <th>Jumlah</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                                <th>Tanggal Dibuat</th>

                              <?php elseif ($current_table === 'ticket_categories'): ?>
                                <th>ID</th>
                                <th>Jadwal Konser</th>
                                <th id="sort-nama" style="cursor: pointer;" title="Klik untuk mengurutkan A-Z / Z-A" data-col="2">
                                  Kategori Tiket <i class="bi bi-arrow-down-up fs-7 text-secondary"></i>
                                </th>
                                <th>Fasilitas</th>
                                <th>Harga</th>
                                <th>Total Kuota</th>
                                <th>Kuota Tersedia</th>

                              <?php elseif ($current_table === 'tours'): ?>
                                <th>ID</th>
                                <th id="sort-nama" style="cursor: pointer;" title="Klik untuk mengurutkan A-Z / Z-A" data-col="1">
                                  Nama Tur <i class="bi bi-arrow-down-up fs-7 text-secondary"></i>
                                </th>
                                <th>Tanggal Konser</th>
                                <th>Kota & Negara</th>
                                <th>Venue</th>
                                <th>Harga Tiket</th>
                                <th>Status</th>
                                <th>Tanggal Dibuat</th>

                              <?php elseif ($current_table === 'users'): ?>
                                <th>ID</th>
                                <th>Username</th>
                                <th id="sort-nama" style="cursor: pointer;" title="Klik untuk mengurutkan A-Z / Z-A" data-col="2">
                                  Nama Lengkap <i class="bi bi-arrow-down-up fs-7 text-secondary"></i>
                                </th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Login Terakhir</th>
                                <th>Tanggal Dibuat</th>

                              <?php elseif ($current_table === 'videos'): ?>
                                <th>ID</th>
                                <th>Poster</th>
                                <th id="sort-nama" style="cursor: pointer;" title="Klik untuk mengurutkan A-Z / Z-A" data-col="2">
                                  Judul Video <i class="bi bi-arrow-down-up fs-7 text-secondary"></i>
                                </th>
                                <th>Kategori</th>
                                <th>Durasi</th>
                                <th>Sumber Video</th>
                                <th>Tanggal Dibuat</th>
                              <?php endif; ?>

                              <th class="text-end">Aksi</th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php while ($row = mysqli_fetch_assoc($data_result)): ?>
                              <tr>
                                <?php if ($current_table === 'albums'): ?>
                                  <td><span class="fw-medium">#<?php echo $row['id']; ?></span></td>
                                  <td>
                                    <?php $cov = resolve_media($row['cover_image']); ?>
                                    <?php if (!empty($cov)): ?>
                                      <img src="<?php echo htmlspecialchars($cov); ?>" alt="Cover" class="rounded" style="width: 32px; height: 32px; object-fit: cover;">
                                    <?php else: ?>
                                      <div class="rounded d-flex align-items-center justify-content-center bg-secondary text-white fw-bold" style="width: 32px; height: 32px; font-size: 14px;">
                                        <?php echo strtoupper(substr($row['title'], 0, 1)); ?>
                                      </div>
                                    <?php endif; ?>
                                  </td>
                                  <td><span class="fw-medium"><?php echo htmlspecialchars($row['title']); ?></span></td>
                                  <td><span class="badge text-bg-primary"><?php echo $row['release_year']; ?></span></td>
                                  <td><span class="text-secondary small"><?php echo htmlspecialchars(mb_strimwidth($row['description'] ?? '', 0, 60, '...')); ?></span></td>
                                  <td><?php echo safe_format_datetime($row['created_at']); ?></td>

                                <?php elseif ($current_table === 'band_members'): ?>
                                  <td><span class="fw-medium">#<?php echo $row['id']; ?></span></td>
                                  <td>
                                    <?php $pic = resolve_media($row['photo']); ?>
                                    <?php if (!empty($pic)): ?>
                                      <img src="<?php echo htmlspecialchars($pic); ?>" alt="Foto" class="rounded-circle" style="width: 32px; height: 32px; object-fit: cover;">
                                    <?php else: ?>
                                      <div class="rounded-circle d-flex align-items-center justify-content-center bg-secondary text-white fw-bold" style="width: 32px; height: 32px; font-size: 14px;">
                                        <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                                      </div>
                                    <?php endif; ?>
                                  </td>
                                  <td><span class="fw-medium"><?php echo htmlspecialchars($row['name']); ?></span></td>
                                  <td><span class="badge text-bg-info"><?php echo htmlspecialchars($row['role']); ?></span></td>
                                  <td><?php echo safe_format_date($row['birth_date']); ?></td>
                                  <td><?php echo $row['join_year']; ?></td>
                                  <td>
                                    <?php if (!empty($row['is_touring_member'])): ?>
                                      <span class="badge text-bg-warning">Touring</span>
                                    <?php else: ?>
                                      <span class="badge text-bg-success">Official</span>
                                    <?php endif; ?>
                                  </td>

                                <?php elseif ($current_table === 'community_posts'): ?>
                                  <td><span class="fw-medium">#<?php echo $row['id']; ?></span></td>
                                  <td>
                                    <div class="d-flex align-items-center">
                                      <div class="rounded-circle me-2 d-flex align-items-center justify-content-center bg-secondary text-white fw-bold" style="width: 32px; height: 32px; font-size: 14px;">
                                        <?php echo strtoupper(substr($row['author_name'], 0, 1)); ?>
                                      </div>
                                      <span class="fw-medium"><?php echo htmlspecialchars($row['author_name']); ?></span>
                                    </div>
                                  </td>
                                  <td><span class="text-secondary small"><?php echo htmlspecialchars($row['author_email'] ?? '-'); ?></span></td>
                                  <td><?php echo htmlspecialchars(mb_strimwidth($row['message'], 0, 70, '...')); ?></td>
                                  <td><span class="badge text-bg-danger"><i class="bi bi-heart-fill me-1"></i><?php echo $row['likes']; ?></span></td>
                                  <td><?php echo safe_format_datetime($row['created_at']); ?></td>

                                <?php elseif ($current_table === 'contact_messages'): ?>
                                  <td><span class="fw-medium">#<?php echo $row['id']; ?></span></td>
                                  <td>
                                    <div class="d-flex align-items-center">
                                      <div class="rounded-circle me-2 d-flex align-items-center justify-content-center bg-secondary text-white fw-bold" style="width: 32px; height: 32px; font-size: 14px;">
                                        <?php echo strtoupper(substr($row['name'], 0, 1)); ?>
                                      </div>
                                      <span class="fw-medium"><?php echo htmlspecialchars($row['name']); ?></span>
                                    </div>
                                  </td>
                                  <td><span class="text-secondary small"><?php echo htmlspecialchars($row['email']); ?></span></td>
                                  <td><span class="fw-medium"><?php echo htmlspecialchars($row['subject']); ?></span></td>
                                  <td><?php echo htmlspecialchars(mb_strimwidth($row['message'], 0, 60, '...')); ?></td>
                                  <td>
                                    <?php if ($row['status'] === 'replied'): ?>
                                      <span class="badge text-bg-success">Replied</span>
                                    <?php elseif ($row['status'] === 'read'): ?>
                                      <span class="badge text-bg-info">Read</span>
                                    <?php else: ?>
                                      <span class="badge text-bg-warning">Unread</span>
                                    <?php endif; ?>
                                  </td>
                                  <td><?php echo safe_format_datetime($row['created_at']); ?></td>

                                <?php elseif ($current_table === 'news'): ?>
                                  <td><span class="fw-medium">#<?php echo $row['id']; ?></span></td>
                                  <td>
                                    <?php $img = resolve_media($row['image_url']); ?>
                                    <?php if (!empty($img)): ?>
                                      <img src="<?php echo htmlspecialchars($img); ?>" alt="Gambar" class="rounded" style="width: 32px; height: 32px; object-fit: cover;">
                                    <?php else: ?>
                                      <div class="rounded d-flex align-items-center justify-content-center bg-secondary text-white fw-bold" style="width: 32px; height: 32px; font-size: 14px;">
                                        <?php echo strtoupper(substr($row['title'], 0, 1)); ?>
                                      </div>
                                    <?php endif; ?>
                                  </td>
                                  <td><span class="fw-medium"><?php echo htmlspecialchars($row['title']); ?></span></td>
                                  <td><code><?php echo htmlspecialchars($row['slug']); ?></code></td>
                                  <td><span class="text-secondary small"><?php echo htmlspecialchars(mb_strimwidth($row['summary'], 0, 60, '...')); ?></span></td>
                                  <td><?php echo safe_format_date($row['published_at']); ?></td>
                                  <td><?php echo safe_format_datetime($row['created_at']); ?></td>

                                <?php elseif ($current_table === 'songs'): ?>
                                  <td><span class="fw-medium">#<?php echo $row['id']; ?></span></td>
                                  <td><span class="fw-medium"><?php echo htmlspecialchars($row['title']); ?></span></td>
                                  <td><span class="badge text-bg-secondary"><?php echo htmlspecialchars($albums_list[$row['album_id']] ?? 'Album #' . $row['album_id']); ?></span></td>
                                  <td><span class="badge text-bg-light border"><?php echo $row['track_number']; ?></span></td>
                                  <td><?php echo htmlspecialchars($row['duration']); ?></td>
                                  <td><small class="text-secondary"><code><?php echo htmlspecialchars(basename($row['audio_file'])); ?></code></small></td>
                                  <td><span class="badge text-bg-success"><?php echo number_format($row['play_count']); ?></span></td>
                                  <td><?php echo safe_format_datetime($row['created_at']); ?></td>

                                <?php elseif ($current_table === 'ticket_bookings'): ?>
                                  <td><span class="fw-medium">#<?php echo $row['id']; ?></span></td>
                                  <td><span class="fw-bold text-primary"><?php echo htmlspecialchars($row['booking_code']); ?></span></td>
                                  <td><small><?php echo htmlspecialchars($tours_list[$row['tour_id']] ?? 'Tur #' . $row['tour_id']); ?></small></td>
                                  <td><span class="badge text-bg-secondary"><?php echo htmlspecialchars($categories_list[$row['category_id']] ?? 'Kat #' . $row['category_id']); ?></span></td>
                                  <td><?php echo htmlspecialchars($row['buyer_name']); ?></td>
                                  <td><?php echo $row['quantity']; ?> tiket</td>
                                  <td><span class="fw-bold text-success">Rp <?php echo number_format($row['total_price'], 0, ',', '.'); ?></span></td>
                                  <td>
                                    <?php if ($row['payment_status'] === 'paid'): ?>
                                      <span class="badge text-bg-success">Paid</span>
                                    <?php elseif ($row['payment_status'] === 'pending'): ?>
                                      <span class="badge text-bg-warning">Pending</span>
                                    <?php else: ?>
                                      <span class="badge text-bg-danger">Cancelled</span>
                                    <?php endif; ?>
                                  </td>
                                  <td><?php echo safe_format_datetime($row['booking_date']); ?></td>

                                <?php elseif ($current_table === 'ticket_categories'): ?>
                                  <td><span class="fw-medium">#<?php echo $row['id']; ?></span></td>
                                  <td><small><?php echo htmlspecialchars($tours_list[$row['tour_id']] ?? 'Tur #' . $row['tour_id']); ?></small></td>
                                  <td><span class="fw-medium"><?php echo htmlspecialchars($row['category_name']); ?></span></td>
                                  <td><span class="text-secondary small"><?php echo htmlspecialchars($row['description'] ?? '-'); ?></span></td>
                                  <td><span class="fw-bold text-success">Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></span></td>
                                  <td><?php echo $row['total_quota']; ?></td>
                                  <td><span class="badge text-bg-primary"><?php echo $row['available_quota']; ?></span></td>

                                <?php elseif ($current_table === 'tours'): ?>
                                  <td><span class="fw-medium">#<?php echo $row['id']; ?></span></td>
                                  <td><span class="fw-medium"><?php echo htmlspecialchars($row['tour_name']); ?></span></td>
                                  <td><?php echo safe_format_date($row['event_date']); ?></td>
                                  <td><?php echo htmlspecialchars($row['city'] . ', ' . $row['country']); ?></td>
                                  <td><?php echo htmlspecialchars($row['venue']); ?></td>
                                  <td><span class="fw-bold text-success">Rp <?php echo number_format($row['base_price'], 0, ',', '.'); ?></span></td>
                                  <td>
                                    <?php if ($row['status'] === 'available'): ?>
                                      <span class="badge text-bg-success">Available</span>
                                    <?php elseif ($row['status'] === 'sold-out'): ?>
                                      <span class="badge text-bg-danger">Sold Out</span>
                                    <?php else: ?>
                                      <span class="badge text-bg-secondary">Completed</span>
                                    <?php endif; ?>
                                  </td>
                                  <td><?php echo safe_format_datetime($row['created_at']); ?></td>

                                <?php elseif ($current_table === 'users'): ?>
                                  <td><span class="fw-medium">#<?php echo $row['id']; ?></span></td>
                                  <td><code><?php echo htmlspecialchars($row['username']); ?></code></td>
                                  <td>
                                    <div class="d-flex align-items-center">
                                      <div class="rounded-circle me-2 d-flex align-items-center justify-content-center bg-secondary text-white fw-bold" style="width: 32px; height: 32px; font-size: 14px;">
                                        <?php echo strtoupper(substr($row['full_name'], 0, 1)); ?>
                                      </div>
                                      <span class="fw-medium"><?php echo htmlspecialchars($row['full_name']); ?></span>
                                    </div>
                                  </td>
                                  <td><?php echo htmlspecialchars($row['email']); ?></td>
                                  <td>
                                    <?php if ($row['role'] === 'superadmin'): ?>
                                      <span class="badge text-bg-danger fw-bold"><i class="bi bi-shield-fill-check me-1"></i>Superadmin</span>
                                    <?php elseif ($row['role'] === 'admin'): ?>
                                      <span class="badge text-bg-primary fw-bold"><i class="bi bi-shield-lock me-1"></i>Admin</span>
                                    <?php else: ?>
                                      <span class="badge text-bg-success fw-bold"><i class="bi bi-pencil-square me-1"></i>Editor</span>
                                    <?php endif; ?>
                                  </td>
                                  <td><?php echo safe_format_datetime($row['last_login']); ?></td>
                                  <td><?php echo safe_format_datetime($row['created_at']); ?></td>

                                <?php elseif ($current_table === 'videos'): ?>
                                  <td><span class="fw-medium">#<?php echo $row['id']; ?></span></td>
                                  <td>
                                    <?php $post = resolve_media($row['poster_image']); ?>
                                    <?php if (!empty($post)): ?>
                                      <img src="<?php echo htmlspecialchars($post); ?>" alt="Poster" class="rounded" style="width: 32px; height: 32px; object-fit: cover;">
                                    <?php else: ?>
                                      <div class="rounded d-flex align-items-center justify-content-center bg-secondary text-white fw-bold" style="width: 32px; height: 32px; font-size: 14px;">
                                        <?php echo strtoupper(substr($row['title'], 0, 1)); ?>
                                      </div>
                                    <?php endif; ?>
                                  </td>
                                  <td><span class="fw-medium"><?php echo htmlspecialchars($row['title']); ?></span></td>
                                  <td><span class="badge text-bg-secondary"><?php echo htmlspecialchars($row['category_label']); ?></span></td>
                                  <td><?php echo htmlspecialchars($row['duration'] ?? '-'); ?></td>
                                  <td><small class="text-secondary"><code><?php echo htmlspecialchars(mb_strimwidth($row['video_source'], 0, 35, '...')); ?></code></small></td>
                                  <td><?php echo safe_format_datetime($row['created_at']); ?></td>
                                <?php endif; ?>

                                <!-- Tombol Aksi Sesuai Perizinan Role (RBAC) -->
                                <td class="text-end">
                                  <?php
                                    $can_edit = true;
                                    $can_delete = true;
                                    $lock_badge = '';

                                    if ($current_table === 'users') {
                                        $row_role = $row['role'];
                                        $row_id = (int)$row['id'];

                                        if ($auth_role === 'superadmin') {
                                            $can_edit = true;
                                            if ($row_id === $auth_user_id) {
                                                $can_delete = false;
                                                $lock_badge = '<span class="badge text-bg-light border text-muted small me-1"><i class="bi bi-person-check me-1"></i>Akun Anda</span>';
                                            }
                                        } elseif ($auth_role === 'admin') {
                                            if ($row_role === 'superadmin') {
                                                // Admin tidak bisa edit dan delete superadmin
                                                $can_edit = false;
                                                $can_delete = false;
                                                $lock_badge = '<span class="badge text-bg-danger-subtle text-danger-emphasis border border-danger-subtle small"><i class="bi bi-shield-fill me-1"></i>Protected (Superadmin)</span>';
                                            } elseif ($row_role === 'admin') {
                                                if ($row_id === $auth_user_id) {
                                                    // Akun admin sendiri: bisa edit profil sendiri, tidak bisa delete
                                                    $can_edit = true;
                                                    $can_delete = false;
                                                    $lock_badge = '<span class="badge text-bg-primary-subtle text-primary-emphasis border border-primary-subtle small me-1"><i class="bi bi-person-check me-1"></i>Akun Anda</span>';
                                                } else {
                                                    // Admin lain: terkunci
                                                    $can_edit = false;
                                                    $can_delete = false;
                                                    $lock_badge = '<span class="badge text-bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle small"><i class="bi bi-lock-fill me-1"></i>Admin Lain</span>';
                                                }
                                            } elseif ($row_role === 'editor') {
                                                // Admin hanya bisa delete dan modifikasi editor
                                                $can_edit = true;
                                                $can_delete = true;
                                            }
                                        } elseif ($auth_role === 'editor') {
                                            // Editor hanya bisa mengedit dirinya sendiri
                                            if ($row_id === $auth_user_id) {
                                                $can_edit = true;
                                                $can_delete = false; // Editor tidak bisa delete akun
                                                $lock_badge = '<span class="badge text-bg-success-subtle text-success-emphasis border border-success-subtle small me-1"><i class="bi bi-person-check me-1"></i>Akun Anda</span>';
                                            } else {
                                                // Editor tidak bisa mengedit admin dan superadmin serta user lain
                                                $can_edit = false;
                                                $can_delete = false;
                                                $lock_badge = '<span class="badge text-bg-light border text-muted small"><i class="bi bi-lock-fill me-1"></i>Terkunci</span>';
                                            }
                                        }
                                    } elseif ($auth_role === 'editor' && in_array($current_table, ['tours', 'ticket_categories', 'ticket_bookings'])) {
                                        $can_edit = false;
                                        $can_delete = false;
                                        $lock_badge = '<span class="badge text-bg-light border text-muted small"><i class="bi bi-eye me-1"></i>Lihat Saja</span>';
                                    }
                                  ?>

                                  <?php if (!$can_edit && !$can_delete): ?>
                                    <?php echo $lock_badge; ?>
                                  <?php else: ?>
                                    <div class="btn-group btn-group-sm">
                                      <?php if (!empty($lock_badge)): ?>
                                        <?php echo $lock_badge; ?>
                                      <?php endif; ?>
                                      <?php if ($can_edit): ?>
                                        <button
                                          type="button"
                                          class="btn btn-outline-secondary"
                                          data-bs-toggle="modal"
                                          data-bs-target="#modal-edit"
                                          data-row='<?php echo htmlspecialchars(json_encode($row), ENT_QUOTES, "UTF-8"); ?>'
                                          title="Edit Data"
                                        >
                                          <i class="bi bi-pencil" aria-hidden="true"></i>
                                        </button>
                                      <?php endif; ?>
                                      <?php if ($can_delete): ?>
                                        <button
                                          type="button"
                                          class="btn btn-outline-danger"
                                          data-bs-toggle="modal"
                                          data-bs-target="#modal-delete"
                                          data-id="<?php echo $row['id']; ?>"
                                          data-title="<?php echo htmlspecialchars($row[$table_info['title_col']] ?? ('ID ' . $row['id'])); ?>"
                                          title="Hapus Data"
                                        >
                                          <i class="bi bi-trash" aria-hidden="true"></i>
                                        </button>
                                      <?php endif; ?>
                                    </div>
                                  <?php endif; ?>
                                </td>
                              </tr>
                            <?php endwhile; ?>
                          </tbody>
                        </table>
                      <?php } else { ?>
                        <div class="p-4 text-center text-secondary">0 results - Data Not Found!</div>
                      <?php } ?>
                    </div>
                  </div>
                  <!--end::Card Body-->

                  <!--begin::Card Footer (Persis seperti Card Footer users.php)-->
                  <div class="card-footer clearfix d-flex flex-wrap justify-content-between align-items-center">
                    <!-- Dropdown Pilihan Limit Record per Halaman -->
                    <form method="GET" action="" class="d-flex align-items-center gap-2 mb-2 mb-md-0">
                      <input type="hidden" name="table" value="<?php echo htmlspecialchars($current_table); ?>">
                      <?php if (!empty($search_query)): ?>
                        <input type="hidden" name="q" value="<?php echo htmlspecialchars($search_query); ?>">
                      <?php endif; ?>
                      <label for="limit-select" class="fs-7 text-body-secondary mb-0">Tampilkan:</label>
                      <select name="limit" id="limit-select" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="5" <?php echo ($limit == 5) ? 'selected' : ''; ?>>5</option>
                        <option value="10" <?php echo ($limit == 10) ? 'selected' : ''; ?>>10</option>
                        <option value="25" <?php echo ($limit == 25) ? 'selected' : ''; ?>>25</option>
                        <option value="50" <?php echo ($limit == 50) ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo ($limit == 100) ? 'selected' : ''; ?>>100</option>
                        <option value="250" <?php echo ($limit == 250) ? 'selected' : ''; ?>>250</option>
                        <option value="500" <?php echo ($limit == 500) ? 'selected' : ''; ?>>500 (Semua)</option>
                      </select>
                      <span class="fs-7 text-body-secondary">
                        record dari total <strong class="text-dark fw-bold"><?php echo $total_data; ?></strong> data
                        <?php if (!empty($search_query)): ?>
                          <span class="badge text-bg-light border text-secondary ms-1">hasil pencarian</span>
                        <?php endif; ?>
                      </span>
                    </form>

                    <!-- Navigasi Pagination -->
                    <ul class="pagination pagination-sm m-0">
                      <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?table=<?php echo $current_table; ?>&page=1<?php echo $pagination_params; ?>" aria-label="First">&laquo;&laquo; First</a>
                      </li>
                      <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?table=<?php echo $current_table; ?>&page=<?php echo max(1, $page - 1); ?><?php echo $pagination_params; ?>" aria-label="Previous">&laquo; Prev</a>
                      </li>
                      <li class="page-item active">
                        <span class="page-link fw-bold"><?php echo $page; ?> / <?php echo $total_pages; ?></span>
                      </li>
                      <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?table=<?php echo $current_table; ?>&page=<?php echo min($total_pages, $page + 1); ?><?php echo $pagination_params; ?>" aria-label="Next">Next &raquo;</a>
                      </li>
                      <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?table=<?php echo $current_table; ?>&page=<?php echo $total_pages; ?><?php echo $pagination_params; ?>" aria-label="Last">Last &raquo;&raquo;</a>
                      </li>
                    </ul>
                  </div>
                  <!--end::Card Footer-->

                </div>
                <!--end::Card-->
              </div>
            </div>
            <!--end::Row-->

            <!-- ========================================== -->
            <!-- MODAL ADD (TAMBAH DATA)                   -->
            <!-- ========================================== -->
            <div class="modal fade" id="modal-add" tabindex="-1" aria-labelledby="modal-add-label" aria-hidden="true">
              <div class="modal-dialog <?php echo in_array($current_table, ['albums', 'band_members', 'news', 'ticket_bookings', 'tours', 'ticket_categories']) ? 'modal-lg' : ''; ?>">
                <div class="modal-content">
                  <form action="insert_data.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="table" value="<?php echo htmlspecialchars($current_table); ?>">

                    <div class="modal-header">
                      <h5 class="modal-title fw-bold" id="modal-add-label">Tambah Data <?php echo htmlspecialchars($table_info['label']); ?></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                      <?php if ($current_table === 'albums'): ?>
                        <div class="mb-3">
                          <label class="form-label">Judul Album</label>
                          <input type="text" name="title" class="form-control" placeholder="Contoh: Saviors" required>
                          <div class="form-text">Judul album wajib diisi</div>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun Rilis</label>
                            <input type="number" name="release_year" class="form-control" value="<?php echo date('Y'); ?>" min="1980" max="2099" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Upload Cover Album</label>
                            <input type="file" name="cover_image_file" class="form-control" accept="image/*">
                            <input type="text" name="cover_image" class="form-control form-control-sm mt-1" placeholder="Atau isi URL/Path gambar">
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Deskripsi Album</label>
                          <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi latar belakang rilis album..."></textarea>
                        </div>

                      <?php elseif ($current_table === 'band_members'): ?>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Personel</label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Billie Joe Armstrong" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Peran / Instrumen</label>
                            <input type="text" name="role" class="form-control" placeholder="Lead Vocals, Lead Guitar" required>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Foto Personel</label>
                            <input type="file" name="photo_file" class="form-control" accept="image/*">
                            <input type="text" name="photo" class="form-control form-control-sm mt-1" placeholder="Atau isi URL/Path foto">
                          </div>
                          <div class="col-md-3 mb-3">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="birth_date" class="form-control">
                          </div>
                          <div class="col-md-3 mb-3">
                            <label class="form-label">Tahun Bergabung</label>
                            <input type="number" name="join_year" class="form-control" value="1987" min="1980" max="2099" required>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Status Anggota</label>
                          <select name="is_touring_member" class="form-select">
                            <option value="0">Official Band Member</option>
                            <option value="1">Touring / Additional Member</option>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Biografi Personel</label>
                          <textarea name="biography" class="form-control" rows="3" placeholder="Profil lengkap personel..."></textarea>
                        </div>

                      <?php elseif ($current_table === 'community_posts'): ?>
                        <div class="mb-3">
                          <label class="form-label">Nama Pengirim</label>
                          <input type="text" name="author_name" class="form-control" placeholder="Nama fans" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Email Pengirim</label>
                          <input type="email" name="author_email" class="form-control" placeholder="nama@email.com">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Pesan / Obrolan Komunitas</label>
                          <textarea name="message" class="form-control" rows="4" placeholder="Tuliskan pesan..." required></textarea>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Jumlah Likes Awal</label>
                          <input type="number" name="likes" class="form-control" value="0" min="0">
                        </div>

                      <?php elseif ($current_table === 'contact_messages'): ?>
                        <div class="mb-3">
                          <label class="form-label">Nama Lengkap</label>
                          <input type="text" name="name" class="form-control" placeholder="Nama pengirim" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Alamat Email</label>
                          <input type="email" name="email" class="form-control" placeholder="email@domain.com" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Subjek Pesan</label>
                          <input type="text" name="subject" class="form-control" placeholder="Subjek pertanyaan/saran" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Isi Pesan</label>
                          <textarea name="message" class="form-control" rows="4" placeholder="Detail pesan masuk..." required></textarea>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Status Pesan</label>
                          <select name="status" class="form-select">
                            <option value="unread" selected>Unread</option>
                            <option value="read">Read</option>
                            <option value="replied">Replied</option>
                          </select>
                        </div>

                      <?php elseif ($current_table === 'news'): ?>
                        <div class="mb-3">
                          <label class="form-label">Judul Berita</label>
                          <input type="text" name="title" class="form-control" placeholder="Judul artikel berita" required>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Slug URL</label>
                            <input type="text" name="slug" class="form-control" placeholder="Otomatis dibuat jika dikosongkan">
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Publikasi</label>
                            <input type="date" name="published_at" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Upload Gambar Berita</label>
                          <input type="file" name="image_url_file" class="form-control" accept="image/*">
                          <input type="text" name="image_url" class="form-control form-control-sm mt-1" placeholder="Atau isi URL/Path gambar">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Ringkasan Berita</label>
                          <textarea name="summary" class="form-control" rows="2" placeholder="Ringkasan singkat..."></textarea>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Isi Berita Lengkap</label>
                          <textarea name="content" class="form-control" rows="5" placeholder="Konten artikel berita lengkap..." required></textarea>
                        </div>

                      <?php elseif ($current_table === 'songs'): ?>
                        <div class="mb-3">
                          <label class="form-label">Album Terkait</label>
                          <select name="album_id" class="form-select" required>
                            <option value="">-- Pilih Album --</option>
                            <?php foreach ($albums_list as $aid => $aname): ?>
                              <option value="<?php echo $aid; ?>"><?php echo htmlspecialchars($aname); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Judul Lagu</label>
                          <input type="text" name="title" class="form-control" placeholder="Contoh: Boulevard of Broken Dreams" required>
                        </div>
                        <div class="row">
                          <div class="col-md-4 mb-3">
                            <label class="form-label">Nomor Trek</label>
                            <input type="number" name="track_number" class="form-control" value="1" min="1" required>
                          </div>
                          <div class="col-md-4 mb-3">
                            <label class="form-label">Durasi (MM:SS)</label>
                            <input type="text" name="duration" class="form-control" value="03:30" placeholder="03:45" required>
                          </div>
                          <div class="col-md-4 mb-3">
                            <label class="form-label">Play Count Awal</label>
                            <input type="number" name="play_count" class="form-control" value="0" min="0">
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Upload File Audio MP3</label>
                          <input type="file" name="audio_file_file" class="form-control" accept="audio/*">
                          <input type="text" name="audio_file" class="form-control form-control-sm mt-1" placeholder="Atau isi path file: Web Green Day/assets/audio/contoh.mp3">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Lirik Lagu</label>
                          <textarea name="lyrics" class="form-control" rows="4" placeholder="Tuliskan lirik lagu lengkap..."></textarea>
                        </div>

                      <?php elseif ($current_table === 'ticket_bookings'): ?>
                        <div class="mb-3">
                          <label class="form-label">Kode Booking</label>
                          <input type="text" name="booking_code" class="form-control" value="GD-TIX-<?php echo date('Ymd'); ?>-<?php echo rand(1000, 9999); ?>" required>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Jadwal Tur Konser</label>
                            <select name="tour_id" class="form-select" required>
                              <option value="">-- Pilih Jadwal Konser --</option>
                              <?php foreach ($tours_list as $tid => $tinfo): ?>
                                <option value="<?php echo $tid; ?>"><?php echo htmlspecialchars($tinfo); ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori Tiket</label>
                            <select name="category_id" class="form-select" required>
                              <option value="">-- Pilih Kategori Tiket --</option>
                              <?php foreach ($categories_list as $cid => $cinfo): ?>
                                <option value="<?php echo $cid; ?>"><?php echo htmlspecialchars($cinfo); ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Pembeli</label>
                            <input type="text" name="buyer_name" class="form-control" placeholder="Nama lengkap pembeli" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Email Pembeli</label>
                            <input type="email" name="buyer_email" class="form-control" placeholder="pembeli@domain.com" required>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah Tiket</label>
                            <input type="number" name="quantity" class="form-control" value="1" min="1" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Total Harga (Rp)</label>
                            <input type="number" step="1000" name="total_price" class="form-control" placeholder="Contoh: 750000" value="0">
                            <div class="form-text">Biarkan 0 untuk hitung otomatis (Jumlah &times; Harga Kategori)</div>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Status Pembayaran</label>
                          <select name="payment_status" class="form-select">
                            <option value="paid" selected>Paid (Lunas)</option>
                            <option value="pending">Pending (Menunggu Bayar)</option>
                            <option value="cancelled">Cancelled (Dibatalkan)</option>
                          </select>
                        </div>

                      <?php elseif ($current_table === 'ticket_categories'): ?>
                        <div class="mb-3">
                          <label class="form-label">Jadwal Tur Konser</label>
                          <select name="tour_id" class="form-select" required>
                            <option value="">-- Pilih Konser Terkait --</option>
                            <?php foreach ($tours_list as $tid => $tinfo): ?>
                              <option value="<?php echo $tid; ?>"><?php echo htmlspecialchars($tinfo); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Nama Kategori Tiket</label>
                          <input type="text" name="category_name" class="form-control" placeholder="Contoh: VIP Standing / Cat 1" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Fasilitas & Deskripsi</label>
                          <input type="text" name="description" class="form-control" placeholder="Akses soundcheck, barisan depan...">
                        </div>
                        <div class="row">
                          <div class="col-md-4 mb-3">
                            <label class="form-label">Harga Tiket (Rp)</label>
                            <input type="number" step="1000" name="price" class="form-control" placeholder="Contoh: 500000" value="500000" required>
                          </div>
                          <div class="col-md-4 mb-3">
                            <label class="form-label">Total Kuota</label>
                            <input type="number" name="total_quota" class="form-control" value="200" min="1" required>
                          </div>
                          <div class="col-md-4 mb-3">
                            <label class="form-label">Kuota Tersedia</label>
                            <input type="number" name="available_quota" class="form-control" value="200" min="0" required>
                          </div>
                        </div>

                      <?php elseif ($current_table === 'tours'): ?>
                        <div class="mb-3">
                          <label class="form-label">Nama Agenda Tur</label>
                          <input type="text" name="tour_name" class="form-control" value="The Saviors World Tour" required>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Konser</label>
                            <input type="date" name="event_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Harga Tiket Mulai (Rp)</label>
                            <input type="number" step="1000" name="base_price" class="form-control" placeholder="Contoh: 500000" value="500000" required>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Kota</label>
                            <input type="text" name="city" class="form-control" placeholder="Jakarta" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Negara</label>
                            <input type="text" name="country" class="form-control" placeholder="Indonesia" required>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Venue / Stadion</label>
                          <input type="text" name="venue" class="form-control" placeholder="Gelora Bung Karno Stadium" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Status Tiket Konser</label>
                          <select name="status" class="form-select">
                            <option value="available" selected>Available (Tersedia)</option>
                            <option value="sold-out">Sold Out (Habis)</option>
                            <option value="completed">Completed (Selesai)</option>
                          </select>
                        </div>

                      <?php elseif ($current_table === 'users'): ?>
                        <div class="mb-3">
                          <label class="form-label">Username</label>
                          <input type="text" name="username" class="form-control" placeholder="username_admin" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Nama Lengkap</label>
                          <input type="text" name="full_name" class="form-control" placeholder="Nama Lengkap Administrator" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Email</label>
                          <input type="email" name="email" class="form-control" placeholder="admin@greenday.com" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Password</label>
                          <input type="password" name="password" class="form-control" placeholder="Password akun" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Role Akses</label>
                          <select name="role" class="form-select">
                            <option value="admin" selected>Admin</option>
                            <option value="superadmin">Superadmin</option>
                            <option value="editor">Editor</option>
                          </select>
                        </div>

                      <?php elseif ($current_table === 'videos'): ?>
                        <div class="mb-3">
                          <label class="form-label">Judul Video</label>
                          <input type="text" name="title" class="form-control" placeholder="Contoh: Dilemma (Official Music Video)" required>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori / Label Video</label>
                            <input type="text" name="category_label" class="form-control" placeholder="Official Video / Live" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Durasi (MM:SS)</label>
                            <input type="text" name="duration" class="form-control" value="03:45" placeholder="03:45">
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Upload Video File</label>
                          <input type="file" name="video_source_file" class="form-control" accept="video/*">
                          <input type="text" name="video_source" class="form-control form-control-sm mt-1" placeholder="Atau isi URL/Path video: Web Green Day/assets/videos/contoh.mp4">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Upload Poster Thumbnail</label>
                          <input type="file" name="poster_image_file" class="form-control" accept="image/*">
                          <input type="text" name="poster_image" class="form-control form-control-sm mt-1" placeholder="Atau isi URL/Path gambar poster">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Deskripsi Video</label>
                          <textarea name="description" class="form-control" rows="3" placeholder="Keterangan video..."></textarea>
                        </div>
                      <?php endif; ?>
                    </div>

                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                      <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!--end::Add Modal-->

            <!-- ========================================== -->
            <!-- MODAL EDIT (UBAH DATA)                    -->
            <!-- ========================================== -->
            <div class="modal fade" id="modal-edit" tabindex="-1" aria-labelledby="modal-edit-label" aria-hidden="true">
              <div class="modal-dialog <?php echo in_array($current_table, ['albums', 'band_members', 'news', 'ticket_bookings', 'tours', 'ticket_categories']) ? 'modal-lg' : ''; ?>">
                <div class="modal-content">
                  <form action="edit_data.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="table" value="<?php echo htmlspecialchars($current_table); ?>">
                    <input type="hidden" name="id" id="edit-id">

                    <div class="modal-header">
                      <h5 class="modal-title fw-bold" id="modal-edit-label">Edit Data <?php echo htmlspecialchars($table_info['label']); ?></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                      <?php if ($current_table === 'albums'): ?>
                        <input type="hidden" name="old_cover_image" id="edit-old-cover-image">
                        <div class="mb-3">
                          <label class="form-label">Judul Album</label>
                          <input type="text" name="title" id="edit-title" class="form-control" required>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun Rilis</label>
                            <input type="number" name="release_year" id="edit-release-year" class="form-control" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Ganti Cover Album</label>
                            <input type="file" name="cover_image_file" class="form-control" accept="image/*">
                            <input type="text" name="cover_image" id="edit-cover-image" class="form-control form-control-sm mt-1">
                            <div class="form-text">Biarkan kosong jika tidak ingin mengubah cover.</div>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Deskripsi Album</label>
                          <textarea name="description" id="edit-description" class="form-control" rows="3"></textarea>
                        </div>

                      <?php elseif ($current_table === 'band_members'): ?>
                        <input type="hidden" name="old_photo" id="edit-old-photo">
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Personel</label>
                            <input type="text" name="name" id="edit-name" class="form-control" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Peran / Instrumen</label>
                            <input type="text" name="role" id="edit-role" class="form-control" required>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Ganti Foto Personel</label>
                            <input type="file" name="photo_file" class="form-control" accept="image/*">
                            <input type="text" name="photo" id="edit-photo" class="form-control form-control-sm mt-1">
                            <div class="form-text">Biarkan kosong jika tidak ingin mengubah foto.</div>
                          </div>
                          <div class="col-md-3 mb-3">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="birth_date" id="edit-birth-date" class="form-control">
                          </div>
                          <div class="col-md-3 mb-3">
                            <label class="form-label">Tahun Bergabung</label>
                            <input type="number" name="join_year" id="edit-join-year" class="form-control" required>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Status Anggota</label>
                          <select name="is_touring_member" id="edit-is-touring-member" class="form-select">
                            <option value="0">Official Band Member</option>
                            <option value="1">Touring / Additional Member</option>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Biografi Personel</label>
                          <textarea name="biography" id="edit-biography" class="form-control" rows="3"></textarea>
                        </div>

                      <?php elseif ($current_table === 'community_posts'): ?>
                        <div class="mb-3">
                          <label class="form-label">Nama Pengirim</label>
                          <input type="text" name="author_name" id="edit-author-name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Email Pengirim</label>
                          <input type="email" name="author_email" id="edit-author-email" class="form-control">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Pesan / Obrolan Komunitas</label>
                          <textarea name="message" id="edit-message" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Jumlah Likes</label>
                          <input type="number" name="likes" id="edit-likes" class="form-control" min="0">
                        </div>

                      <?php elseif ($current_table === 'contact_messages'): ?>
                        <div class="mb-3">
                          <label class="form-label">Nama Lengkap</label>
                          <input type="text" name="name" id="edit-name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Alamat Email</label>
                          <input type="email" name="email" id="edit-email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Subjek Pesan</label>
                          <input type="text" name="subject" id="edit-subject" class="form-control" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Isi Pesan</label>
                          <textarea name="message" id="edit-message" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Status Pesan</label>
                          <select name="status" id="edit-status" class="form-select">
                            <option value="unread">Unread</option>
                            <option value="read">Read</option>
                            <option value="replied">Replied</option>
                          </select>
                        </div>

                      <?php elseif ($current_table === 'news'): ?>
                        <input type="hidden" name="old_image_url" id="edit-old-image-url">
                        <div class="mb-3">
                          <label class="form-label">Judul Berita</label>
                          <input type="text" name="title" id="edit-title" class="form-control" required>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Slug URL</label>
                            <input type="text" name="slug" id="edit-slug" class="form-control" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Publikasi</label>
                            <input type="date" name="published_at" id="edit-published-at" class="form-control" required>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Ganti Gambar Berita</label>
                          <input type="file" name="image_url_file" class="form-control" accept="image/*">
                          <input type="text" name="image_url" id="edit-image-url" class="form-control form-control-sm mt-1">
                          <div class="form-text">Biarkan kosong jika tidak ingin mengubah gambar.</div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Ringkasan Berita</label>
                          <textarea name="summary" id="edit-summary" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Isi Berita Lengkap</label>
                          <textarea name="content" id="edit-content" class="form-control" rows="5" required></textarea>
                        </div>

                      <?php elseif ($current_table === 'songs'): ?>
                        <input type="hidden" name="old_audio_file" id="edit-old-audio-file">
                        <div class="mb-3">
                          <label class="form-label">Album Terkait</label>
                          <select name="album_id" id="edit-album-id" class="form-select" required>
                            <option value="">-- Pilih Album --</option>
                            <?php foreach ($albums_list as $aid => $aname): ?>
                              <option value="<?php echo $aid; ?>"><?php echo htmlspecialchars($aname); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Judul Lagu</label>
                          <input type="text" name="title" id="edit-title" class="form-control" required>
                        </div>
                        <div class="row">
                          <div class="col-md-4 mb-3">
                            <label class="form-label">Nomor Trek</label>
                            <input type="number" name="track_number" id="edit-track-number" class="form-control" required>
                          </div>
                          <div class="col-md-4 mb-3">
                            <label class="form-label">Durasi (MM:SS)</label>
                            <input type="text" name="duration" id="edit-duration" class="form-control" required>
                          </div>
                          <div class="col-md-4 mb-3">
                            <label class="form-label">Play Count</label>
                            <input type="number" name="play_count" id="edit-play-count" class="form-control" min="0">
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Ganti File Audio MP3</label>
                          <input type="file" name="audio_file_file" class="form-control" accept="audio/*">
                          <input type="text" name="audio_file" id="edit-audio-file" class="form-control form-control-sm mt-1">
                          <div class="form-text">Biarkan kosong jika tidak ingin mengubah audio.</div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Lirik Lagu</label>
                          <textarea name="lyrics" id="edit-lyrics" class="form-control" rows="4"></textarea>
                        </div>

                      <?php elseif ($current_table === 'ticket_bookings'): ?>
                        <div class="mb-3">
                          <label class="form-label">Kode Booking</label>
                          <input type="text" name="booking_code" id="edit-booking-code" class="form-control" readonly required>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Jadwal Tur Konser</label>
                            <select name="tour_id" id="edit-tour-id" class="form-select" required>
                              <option value="">-- Pilih Jadwal Konser --</option>
                              <?php foreach ($tours_list as $tid => $tinfo): ?>
                                <option value="<?php echo $tid; ?>"><?php echo htmlspecialchars($tinfo); ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori Tiket</label>
                            <select name="category_id" id="edit-category-id" class="form-select" required>
                              <option value="">-- Pilih Kategori Tiket --</option>
                              <?php foreach ($categories_list as $cid => $cinfo): ?>
                                <option value="<?php echo $cid; ?>"><?php echo htmlspecialchars($cinfo); ?></option>
                              <?php endforeach; ?>
                            </select>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Pembeli</label>
                            <input type="text" name="buyer_name" id="edit-buyer-name" class="form-control" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Email Pembeli</label>
                            <input type="email" name="buyer_email" id="edit-buyer-email" class="form-control" required>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah Tiket</label>
                            <input type="number" name="quantity" id="edit-quantity" class="form-control" min="1" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Total Harga (Rp)</label>
                            <input type="number" step="1000" name="total_price" id="edit-total-price" class="form-control" required>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Status Pembayaran</label>
                          <select name="payment_status" id="edit-payment-status" class="form-select">
                            <option value="paid">Paid (Lunas)</option>
                            <option value="pending">Pending (Menunggu Bayar)</option>
                            <option value="cancelled">Cancelled (Dibatalkan)</option>
                          </select>
                        </div>

                      <?php elseif ($current_table === 'ticket_categories'): ?>
                        <div class="mb-3">
                          <label class="form-label">Jadwal Tur Konser</label>
                          <select name="tour_id" id="edit-tour-id" class="form-select" required>
                            <option value="">-- Pilih Konser Terkait --</option>
                            <?php foreach ($tours_list as $tid => $tinfo): ?>
                              <option value="<?php echo $tid; ?>"><?php echo htmlspecialchars($tinfo); ?></option>
                            <?php endforeach; ?>
                          </select>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Nama Kategori Tiket</label>
                          <input type="text" name="category_name" id="edit-category-name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Fasilitas & Deskripsi</label>
                          <input type="text" name="description" id="edit-description" class="form-control">
                        </div>
                        <div class="row">
                          <div class="col-md-4 mb-3">
                            <label class="form-label">Harga Tiket (Rp)</label>
                            <input type="number" step="1000" name="price" id="edit-price" class="form-control" required>
                          </div>
                          <div class="col-md-4 mb-3">
                            <label class="form-label">Total Kuota</label>
                            <input type="number" name="total_quota" id="edit-total-quota" class="form-control" required>
                          </div>
                          <div class="col-md-4 mb-3">
                            <label class="form-label">Kuota Tersedia</label>
                            <input type="number" name="available_quota" id="edit-available-quota" class="form-control" required>
                          </div>
                        </div>

                      <?php elseif ($current_table === 'tours'): ?>
                        <div class="mb-3">
                          <label class="form-label">Nama Agenda Tur</label>
                          <input type="text" name="tour_name" id="edit-tour-name" class="form-control" required>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Konser</label>
                            <input type="date" name="event_date" id="edit-event-date" class="form-control" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Harga Tiket Mulai (Rp)</label>
                            <input type="number" step="1000" name="base_price" id="edit-base-price" class="form-control" required>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Kota</label>
                            <input type="text" name="city" id="edit-city" class="form-control" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Negara</label>
                            <input type="text" name="country" id="edit-country" class="form-control" required>
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Venue / Stadion</label>
                          <input type="text" name="venue" id="edit-venue" class="form-control" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Status Tiket Konser</label>
                          <select name="status" id="edit-status" class="form-select">
                            <option value="available">Available (Tersedia)</option>
                            <option value="sold-out">Sold Out (Habis)</option>
                            <option value="completed">Completed (Selesai)</option>
                          </select>
                        </div>

                      <?php elseif ($current_table === 'users'): ?>
                        <div class="mb-3">
                          <label class="form-label">Username</label>
                          <input type="text" name="username" id="edit-username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Nama Lengkap</label>
                          <input type="text" name="full_name" id="edit-full-name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Email</label>
                          <input type="email" name="email" id="edit-email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Password Baru (Opsional)</label>
                          <input type="password" name="password" id="edit-password" class="form-control" placeholder="Biarkan kosong jika tidak ingin mengubah password">
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Role Akses</label>
                          <?php if ($auth_role === 'superadmin'): ?>
                            <select name="role" id="edit-role" class="form-select">
                              <option value="admin">Admin</option>
                              <option value="superadmin">Superadmin</option>
                              <option value="editor">Editor</option>
                            </select>
                            <small class="text-muted"><i class="bi bi-shield-check text-success me-1"></i>Sebagai Superadmin, Anda dapat menentukan role akun ini.</small>
                          <?php else: ?>
                            <input type="text" id="edit-role-display" class="form-control bg-light" readonly>
                            <input type="hidden" name="role" id="edit-role">
                            <small class="text-muted"><i class="bi bi-shield-lock text-warning me-1"></i>Hanya Superadmin yang berwenang mengubah Role Akses.</small>
                          <?php endif; ?>
                        </div>

                      <?php elseif ($current_table === 'videos'): ?>
                        <input type="hidden" name="old_video_source" id="edit-old-video-source">
                        <input type="hidden" name="old_poster_image" id="edit-old-poster-image">
                        <div class="mb-3">
                          <label class="form-label">Judul Video</label>
                          <input type="text" name="title" id="edit-title" class="form-control" required>
                        </div>
                        <div class="row">
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori / Label Video</label>
                            <input type="text" name="category_label" id="edit-category-label" class="form-control" required>
                          </div>
                          <div class="col-md-6 mb-3">
                            <label class="form-label">Durasi (MM:SS)</label>
                            <input type="text" name="duration" id="edit-duration" class="form-control">
                          </div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Ganti File Video</label>
                          <input type="file" name="video_source_file" class="form-control" accept="video/*">
                          <input type="text" name="video_source" id="edit-video-source" class="form-control form-control-sm mt-1">
                          <div class="form-text">Biarkan kosong jika tidak ingin mengubah video.</div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Ganti Poster Thumbnail</label>
                          <input type="file" name="poster_image_file" class="form-control" accept="image/*">
                          <input type="text" name="poster_image" id="edit-poster-image" class="form-control form-control-sm mt-1">
                          <div class="form-text">Biarkan kosong jika tidak ingin mengubah poster.</div>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Deskripsi Video</label>
                          <textarea name="description" id="edit-description" class="form-control" rows="3"></textarea>
                        </div>
                      <?php endif; ?>
                    </div>

                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                      <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!--end::Edit Modal-->

            <!-- ========================================== -->
            <!-- MODAL DELETE (HAPUS DATA)                 -->
            <!-- ========================================== -->
            <div class="modal fade" id="modal-delete" tabindex="-1" aria-labelledby="modal-delete-label" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form action="delete_data.php" method="post">
                    <input type="hidden" name="table" value="<?php echo htmlspecialchars($current_table); ?>">
                    <input type="hidden" name="id" id="delete-id">

                    <div class="modal-header">
                      <h5 class="modal-title fw-bold" id="modal-delete-label">Hapus Data <?php echo htmlspecialchars($table_info['label']); ?></h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                      <p class="mb-0">
                        Apakah kamu yakin ingin menghapus data <strong id="delete-item-title">ini</strong>?
                        Data yang dihapus tidak dapat dikembalikan.
                      </p>
                    </div>

                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                      <button type="submit" class="btn btn-danger">Hapus Data</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <!--end::Delete Modal-->

          </div>
          <!--end::Container-->
        </div>
        <!--end::App Content-->
      </main>
      <!--end::App Main-->

      <!--begin::Footer (Persis seperti Footer users.php)-->
      <footer class="app-footer">
        <!--begin::To the end-->
        <div class="float-end d-none d-sm-inline">Green Day Web Programming</div>
        <!--end::To the end-->
        <!--begin::Copyright-->
        <strong>
          Copyright &copy; 2014-2026&nbsp;
          <a href="https://adminlte.io" class="text-decoration-none">AdminLTE.io</a>.
        </strong>
        All rights reserved.
        <!--end::Copyright-->
      </footer>
      <!--end::Footer-->

    </div>
    <!--end::App Wrapper-->

    <!--begin::Script-->
    <!--begin::Third Party Plugin(OverlayScrollbars)-->
    <script
      src="https://cdn.jsdelivr.net/npm/overlayscrollbars@2.11.0/browser/overlayscrollbars.browser.es6.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Third Party Plugin(OverlayScrollbars)-->
    <!--begin::Required Plugin(popperjs for Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(popperjs for Bootstrap 5)-->
    <!--begin::Required Plugin(Bootstrap 5)-->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"
      crossorigin="anonymous"
    ></script>
    <!--end::Required Plugin(Bootstrap 5)-->
    <!--begin::Required Plugin(AdminLTE)-->
    <script src="./js/adminlte.js"></script>
    <!--end::Required Plugin(AdminLTE)-->

    <!--begin::OverlayScrollbars Configure-->
    <script>
      const SELECTOR_SIDEBAR_WRAPPER = '.sidebar-wrapper';
      const Default = {
        scrollbarTheme: 'os-theme-light',
        scrollbarAutoHide: 'leave',
        scrollbarClickScroll: true,
      };
      document.addEventListener('DOMContentLoaded', function () {
        const sidebarWrapper = document.querySelector(SELECTOR_SIDEBAR_WRAPPER);
        const isMobile = window.innerWidth <= 992;
        if (
          sidebarWrapper &&
          OverlayScrollbarsGlobal?.OverlayScrollbars !== undefined &&
          !isMobile
        ) {
          OverlayScrollbarsGlobal.OverlayScrollbars(sidebarWrapper, {
            scrollbars: {
              theme: Default.scrollbarTheme,
              autoHide: Default.scrollbarAutoHide,
              clickScroll: Default.scrollbarClickScroll,
            },
          });
        }
      });
    </script>
    <!--end::OverlayScrollbars Configure-->

    <!-- JavaScript Handlers (Delete, Edit, Search, Sort persis seperti di users.php) -->
    <script>
      // 1. Modal Delete Handler (Sesuai users.php)
      const modalDelete = document.getElementById('modal-delete');
      if (modalDelete) {
        modalDelete.addEventListener('show.bs.modal', function (event) {
          const button = event.relatedTarget;
          const id = button.getAttribute('data-id');
          const title = button.getAttribute('data-title');

          const deleteInput = document.getElementById('delete-id');
          if (deleteInput) deleteInput.value = id;

          const titleEl = document.getElementById('delete-item-title');
          if (titleEl && title) {
            titleEl.textContent = '"' + title + '"';
          }
        });
      }

      // 2. Modal Edit Handler (Populasi data otomatis dari data-row JSON)
      const modalEdit = document.getElementById('modal-edit');
      if (modalEdit) {
        modalEdit.addEventListener('show.bs.modal', function (event) {
          const button = event.relatedTarget;
          const rawData = button.getAttribute('data-row');
          if (!rawData) return;
          const data = JSON.parse(rawData);

          const editId = document.getElementById('edit-id');
          if (editId) editId.value = data.id || '';

          for (const key in data) {
            const kebabKey = key.replace(/_/g, '-');
            const el = document.getElementById('edit-' + kebabKey);
            if (el) {
              el.value = data[key] !== null ? data[key] : '';
            }
            const oldEl = document.getElementById('edit-old-' + kebabKey);
            if (oldEl) {
              oldEl.value = data[key] !== null ? data[key] : '';
            }
          }

          const roleDisp = document.getElementById('edit-role-display');
          if (roleDisp && data.role) {
            roleDisp.value = data.role.toUpperCase();
          }

          const editPwd = document.getElementById('edit-password');
          if (editPwd) editPwd.value = '';
        });
      }

      // 3. Real-time Search Filter (Persis seperti script search di users.php)
      const searchInput = document.getElementById('user-search');
      if (searchInput) {
        searchInput.addEventListener('input', function () {
          const keyword = this.value.toLowerCase().trim();
          const rows = document.querySelectorAll('.table tbody tr');

          rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(keyword) ? '' : 'none';
          });
        });
      }

      // 4. Sort Table (Persis seperti script sort-nama di users.php)
      let sortOrder = 'asc';
      const sortBtn = document.getElementById('sort-nama');
      if (sortBtn) {
        sortBtn.addEventListener('click', function () {
          const tbody = document.querySelector('.table tbody');
          if (!tbody) return;
          const rows = Array.from(tbody.querySelectorAll('tr'));
          const colIndex = parseInt(this.getAttribute('data-col') || '2');

          rows.sort((a, b) => {
            const valA = a.cells[colIndex] ? a.cells[colIndex].textContent.trim().toLowerCase() : '';
            const valB = b.cells[colIndex] ? b.cells[colIndex].textContent.trim().toLowerCase() : '';

            if (valA < valB) {
              return sortOrder === 'asc' ? -1 : 1;
            }
            if (valA > valB) {
              return sortOrder === 'asc' ? 1 : -1;
            }
            return 0;
          });

          rows.forEach(row => tbody.appendChild(row));

          const icon = this.querySelector('i');
          if (sortOrder === 'asc') {
            sortOrder = 'desc';
            if (icon) icon.className = 'bi bi-arrow-up fs-7 text-primary';
            this.title = 'Terurut A-Z (Klik untuk ubah ke Z-A)';
          } else {
            sortOrder = 'asc';
            if (icon) icon.className = 'bi bi-arrow-down fs-7 text-primary';
            this.title = 'Terurut Z-A (Klik untuk ubah ke A-Z)';
          }
        });
      }
    </script>
    <!--end::Script-->
  </body>
  <!--end::Body-->
</html>
<?php mysqli_close($conn); ?>
