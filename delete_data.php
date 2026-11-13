<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Koneksi database
require_once __DIR__ . "/connect.php";

// Cek autentikasi login
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php?status=danger&msg=" . urlencode("Sesi login telah berakhir! Silakan masuk kembali."));
    exit;
}
$auth_user_id = (int)$_SESSION['user_id'];
$auth_role = $_SESSION['role'] ?? 'editor';

// Daftar tabel yang diizinkan
$allowed_tables = [
    'albums',
    'band_members',
    'community_posts',
    'contact_messages',
    'news',
    'songs',
    'ticket_bookings',
    'ticket_categories',
    'tours',
    'users',
    'videos'
];

// Ambil parameter tabel dan id
$table = isset($_POST['table']) ? trim($_POST['table']) : (isset($_GET['table']) ? trim($_GET['table']) : '');
$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if (!in_array($table, $allowed_tables) || $id <= 0) {
    header("Location: index.php?status=danger&msg=" . urlencode("Permintaan hapus tidak valid."));
    exit;
}

try {
    // Cek keberadaan data & ambil data untuk penanganan relasi dan file
    $check_res = mysqli_query($conn, "SELECT * FROM `$table` WHERE id = $id");
    if (!$check_res || mysqli_num_rows($check_res) === 0) {
        header("Location: index.php?table=$table&status=warning&msg=" . urlencode("Data dengan ID $id tidak ditemukan atau sudah dihapus."));
        exit;
    }
    $existing_row = mysqli_fetch_assoc($check_res);

    // Simpan data khusus untuk logika bisnis pemesanan tiket
    $old_category_id = isset($existing_row['category_id']) ? (int)$existing_row['category_id'] : 0;
    $old_quantity = isset($existing_row['quantity']) ? (int)$existing_row['quantity'] : 0;
    $old_payment_status = isset($existing_row['payment_status']) ? $existing_row['payment_status'] : '';

    // Kumpulkan file yang akan dihapus jika SQL DELETE berhasil
    $file_columns = [
        'albums' => ['cover_image'],
        'band_members' => ['photo'],
        'news' => ['image_url'],
        'songs' => ['audio_file'],
        'videos' => ['video_source', 'poster_image']
    ];

    $files_to_delete = [];
    if (isset($file_columns[$table])) {
        foreach ($file_columns[$table] as $col_name) {
            if (!empty($existing_row[$col_name]) && strpos($existing_row[$col_name], 'uploads/') === 0) {
                $files_to_delete[] = __DIR__ . '/' . $existing_row[$col_name];
            }
        }
    }

    // Cek perizinan role hapus data
    if ($auth_role === 'editor' && in_array($table, ['tours', 'ticket_categories', 'ticket_bookings'])) {
        throw new Exception("Akses Ditolak: Editor tidak memiliki wewenang untuk menghapus data tur konser atau tiket!");
    }

    if ($table === 'users') {
        $target_user_role = $existing_row['role'] ?? 'editor';

        // 1. Tidak dapat menghapus akun diri sendiri yang sedang login
        if ($id === $auth_user_id) {
            throw new Exception("Akses Ditolak: Anda tidak dapat menghapus akun Anda sendiri saat sedang login!");
        }

        // 2. Editor tidak bisa delete akun siapapun
        if ($auth_role === 'editor') {
            throw new Exception("Akses Ditolak: Editor tidak memiliki hak akses untuk menghapus akun pengguna!");
        }

        // 3. Admin tidak bisa delete akun superadmin, admin hanya bisa delete editor
        if ($auth_role === 'admin') {
            if ($target_user_role === 'superadmin') {
                throw new Exception("Akses Ditolak: Admin tidak diizinkan untuk menghapus akun Superadmin!");
            }
            if ($target_user_role !== 'editor') {
                throw new Exception("Akses Ditolak: Admin hanya memiliki wewenang untuk menghapus akun Editor!");
            }
        }

        // 4. Superadmin: cegah hapus superadmin terakhir
        if ($target_user_role === 'superadmin') {
            $count_super = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role = 'superadmin'");
            $num_super = (int)mysqli_fetch_assoc($count_super)['total'];
            if ($num_super <= 1) {
                throw new Exception("Akses Ditolak: Tidak dapat menghapus akun Superadmin terakhir pada sistem!");
            }
        }
    }

    // Eksekusi Hapus Data
    $stmt = mysqli_prepare($conn, "DELETE FROM `$table` WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Gagal menyiapkan statement database: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "i", $id);
    $exec_ok = mysqli_stmt_execute($stmt);

    if ($exec_ok) {
        // Hapus file fisik HANYA setelah query DELETE berhasil di database
        foreach ($files_to_delete as $full_path) {
            if (file_exists($full_path)) {
                @unlink($full_path);
            }
        }

        // Logika bisnis: Jika transaksi tiket berstatus 'paid' dihapus, kembalikan kuota ke ticket_categories
        if ($table === 'ticket_bookings' && $old_payment_status === 'paid' && $old_category_id > 0 && $old_quantity > 0) {
            mysqli_query($conn, "UPDATE ticket_categories SET available_quota = LEAST(total_quota, available_quota + $old_quantity) WHERE id = $old_category_id");
        }

        mysqli_stmt_close($stmt);
        header("Location: index.php?table=$table&status=success&msg=" . urlencode("Data dengan ID $id berhasil dihapus!"));
        exit;
    } else {
        throw new Exception("Gagal menghapus data: " . mysqli_stmt_error($stmt));
    }
} catch (mysqli_sql_exception $e) {
    $err_code = $e->getCode();
    $err_msg = $e->getMessage();

    // Penanganan error Foreign Key Constraint (MySQL Error 1451)
    if ($err_code === 1451 || strpos($err_msg, 'foreign key constraint') !== false) {
        $msg = "Data tidak dapat dihapus karena masih digunakan atau terhubung dengan data lain pada sistem!";
        if ($table === 'albums') {
            $msg = "Album ini tidak dapat dihapus karena masih memiliki relasi lagu (songs). Silakan hapus atau pindahkan lagu terlebih dahulu!";
        } elseif ($table === 'tours') {
            $msg = "Jadwal tur konser tidak dapat dihapus karena masih terhubung dengan kategori tiket atau transaksi pemesanan tiket!";
        } elseif ($table === 'ticket_categories') {
            $msg = "Kategori tiket ini tidak dapat dihapus karena telah memiliki data transaksi pemesanan tiket (ticket_bookings)!";
        }
        header("Location: index.php?table=$table&status=warning&msg=" . urlencode($msg));
    } else {
        header("Location: index.php?table=$table&status=danger&msg=" . urlencode("Gagal menghapus data: " . $err_msg));
    }
} catch (Exception $e) {
    header("Location: index.php?table=$table&status=danger&msg=" . urlencode($e->getMessage()));
}

mysqli_close($conn);
exit;
?>