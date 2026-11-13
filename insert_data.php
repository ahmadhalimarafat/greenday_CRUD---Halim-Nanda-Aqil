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

// Helper upload file dengan validasi tipe ekstensi aman
function handle_file_upload($file_key, $text_fallback = '', $sub_dir = 'uploads/') {
    if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/' . $sub_dir;
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $ext = strtolower(pathinfo($_FILES[$file_key]['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'mp3', 'wav', 'ogg', 'm4a', 'flac', 'mp4', 'webm', 'mkv', 'mov'];
        if (!in_array($ext, $allowed_exts)) {
            throw new Exception("Tipe file (.$ext) tidak diizinkan untuk diunggah!");
        }
        $clean_basename = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', pathinfo($_FILES[$file_key]['name'], PATHINFO_FILENAME));
        $new_name = time() . '_' . $clean_basename . ($ext ? '.' . $ext : '');
        $dest = $upload_dir . $new_name;
        if (move_uploaded_file($_FILES[$file_key]['tmp_name'], $dest)) {
            return $sub_dir . $new_name;
        }
    }
    return !empty($text_fallback) ? trim($text_fallback) : '';
}

// Ambil nama tabel
$table = isset($_POST['table']) ? trim($_POST['table']) : '';

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

if (!in_array($table, $allowed_tables)) {
    header("Location: index.php?status=danger&msg=" . urlencode("Tabel tidak valid!"));
    exit;
}

try {
    switch ($table) {
        case 'albums':
            $title = trim($_POST['title'] ?? '');
            $release_year = (int)($_POST['release_year'] ?? date('Y'));
            $cover_image = handle_file_upload('cover_image_file', $_POST['cover_image'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($title)) {
                throw new Exception("Judul album tidak boleh kosong!");
            }

            $stmt = mysqli_prepare($conn, "INSERT INTO albums (title, release_year, cover_image, description) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "siss", $title, $release_year, $cover_image, $description);
            break;

        case 'band_members':
            $name = trim($_POST['name'] ?? '');
            $role = trim($_POST['role'] ?? '');
            $photo = handle_file_upload('photo_file', $_POST['photo'] ?? '');
            $birth_date = !empty($_POST['birth_date']) ? $_POST['birth_date'] : null;
            $join_year = (int)($_POST['join_year'] ?? 1990);
            $biography = trim($_POST['biography'] ?? '');
            $is_touring_member = isset($_POST['is_touring_member']) ? (int)$_POST['is_touring_member'] : 0;

            if (empty($name) || empty($role)) {
                throw new Exception("Nama dan Role personel tidak boleh kosong!");
            }

            if ($join_year < 1980 || $join_year > (int)date('Y') + 1) {
                throw new Exception("Tahun bergabung harus antara 1980 hingga " . (date('Y') + 1) . "!");
            }

            $stmt = mysqli_prepare($conn, "INSERT INTO band_members (name, role, photo, birth_date, join_year, biography, is_touring_member) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssssisi", $name, $role, $photo, $birth_date, $join_year, $biography, $is_touring_member);
            break;

        case 'community_posts':
            $author_name = trim($_POST['author_name'] ?? '');
            $author_email = !empty($_POST['author_email']) ? trim($_POST['author_email']) : null;
            $message = trim($_POST['message'] ?? '');
            $likes = max(0, (int)($_POST['likes'] ?? 0));

            if (empty($author_name) || empty($message)) {
                throw new Exception("Nama penulis dan pesan komunitas tidak boleh kosong!");
            }

            $stmt = mysqli_prepare($conn, "INSERT INTO community_posts (author_name, author_email, message, likes) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssi", $author_name, $author_email, $message, $likes);
            break;

        case 'contact_messages':
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $subject = trim($_POST['subject'] ?? '');
            $message = trim($_POST['message'] ?? '');
            $status = in_array($_POST['status'] ?? '', ['unread', 'read', 'replied']) ? $_POST['status'] : 'unread';

            if (empty($name) || empty($email) || empty($subject) || empty($message)) {
                throw new Exception("Semua field kontak wajib diisi!");
            }

            $stmt = mysqli_prepare($conn, "INSERT INTO contact_messages (name, email, subject, message, status) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssss", $name, $email, $subject, $message, $status);
            break;

        case 'news':
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            if (empty($slug) && !empty($title)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            }
            $summary = trim($_POST['summary'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $image_url = handle_file_upload('image_url_file', $_POST['image_url'] ?? '');
            $published_at = !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d');

            if (empty($title) || empty($summary) || empty($content)) {
                throw new Exception("Judul, Ringkasan, dan Konten berita wajib diisi!");
            }

            // Cek keunikan slug
            $cek = mysqli_query($conn, "SELECT id FROM news WHERE slug = '" . mysqli_real_escape_string($conn, $slug) . "'");
            if (mysqli_num_rows($cek) > 0) {
                $slug .= '-' . time();
            }

            $stmt = mysqli_prepare($conn, "INSERT INTO news (title, slug, summary, content, image_url, published_at) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssssss", $title, $slug, $summary, $content, $image_url, $published_at);
            break;

        case 'songs':
            $album_id = (int)($_POST['album_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $track_number = max(1, (int)($_POST['track_number'] ?? 1));
            $duration = trim($_POST['duration'] ?? '03:00');
            $audio_file = handle_file_upload('audio_file_file', $_POST['audio_file'] ?? '');
            $lyrics = trim($_POST['lyrics'] ?? '');
            $play_count = max(0, (int)($_POST['play_count'] ?? 0));

            if ($album_id <= 0 || empty($title)) {
                throw new Exception("Album dan Judul lagu wajib dipilih dan diisi!");
            }

            $stmt = mysqli_prepare($conn, "INSERT INTO songs (album_id, title, track_number, duration, audio_file, lyrics, play_count) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "isisssi", $album_id, $title, $track_number, $duration, $audio_file, $lyrics, $play_count);
            break;

        case 'ticket_bookings':
            if ($auth_role === 'editor') {
                throw new Exception("Akses Ditolak: Editor tidak memiliki wewenang untuk menambah data transaksi tiket!");
            }
            $booking_code = trim($_POST['booking_code'] ?? '');
            if (empty($booking_code)) {
                $booking_code = 'GD-TIX-' . date('Ymd') . '-' . rand(1000, 9999);
            }
            $tour_id = (int)($_POST['tour_id'] ?? 0);
            $category_id = (int)($_POST['category_id'] ?? 0);
            $buyer_name = trim($_POST['buyer_name'] ?? '');
            $buyer_email = trim($_POST['buyer_email'] ?? '');
            $quantity = (int)($_POST['quantity'] ?? 1);
            $total_price = (float)($_POST['total_price'] ?? 0);
            $payment_status = in_array($_POST['payment_status'] ?? '', ['pending', 'paid', 'cancelled']) ? $_POST['payment_status'] : 'paid';

            if (empty($buyer_name) || empty($buyer_email) || $tour_id <= 0 || $category_id <= 0) {
                throw new Exception("Data pemesan, tur konser, dan kategori tiket wajib diisi!");
            }

            if ($quantity <= 0) {
                throw new Exception("Jumlah tiket harus lebih dari 0!");
            }

            // Ambil data kategori tiket untuk validasi kuota & harga
            $q_cat = mysqli_query($conn, "SELECT price, total_quota, available_quota FROM ticket_categories WHERE id = $category_id");
            $r_cat = mysqli_fetch_assoc($q_cat);
            if (!$r_cat) {
                throw new Exception("Kategori tiket yang dipilih tidak ditemukan!");
            }

            // Validasi kuota tiket jika status pembayaran paid
            if ($payment_status === 'paid' && $quantity > (int)$r_cat['available_quota']) {
                throw new Exception("Jumlah tiket dipesan ($quantity) melebihi sisa kuota yang tersedia (" . $r_cat['available_quota'] . ")!");
            }

            // Auto-calculate total price jika 0 atau belum terisi
            if ($total_price <= 0) {
                $total_price = $quantity * (float)$r_cat['price'];
            }

            $stmt = mysqli_prepare($conn, "INSERT INTO ticket_bookings (booking_code, tour_id, category_id, buyer_name, buyer_email, quantity, total_price, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "siissids", $booking_code, $tour_id, $category_id, $buyer_name, $buyer_email, $quantity, $total_price, $payment_status);
            break;

        case 'ticket_categories':
            if ($auth_role === 'editor') {
                throw new Exception("Akses Ditolak: Editor tidak memiliki wewenang untuk menambah kategori tiket konser!");
            }
            $tour_id = (int)($_POST['tour_id'] ?? 0);
            $category_name = trim($_POST['category_name'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $price = max(0, (float)($_POST['price'] ?? 0));
            $total_quota = max(1, (int)($_POST['total_quota'] ?? 100));
            $available_quota = isset($_POST['available_quota']) ? (int)$_POST['available_quota'] : $total_quota;

            if ($tour_id <= 0 || empty($category_name)) {
                throw new Exception("Jadwal tur konser dan nama kategori tiket wajib diisi!");
            }

            if ($available_quota > $total_quota) {
                $available_quota = $total_quota;
            }
            if ($available_quota < 0) {
                $available_quota = 0;
            }

            $stmt = mysqli_prepare($conn, "INSERT INTO ticket_categories (tour_id, category_name, description, price, total_quota, available_quota) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "issdii", $tour_id, $category_name, $description, $price, $total_quota, $available_quota);
            break;

        case 'tours':
            if ($auth_role === 'editor') {
                throw new Exception("Akses Ditolak: Editor tidak memiliki wewenang untuk menambah jadwal tur konser!");
            }
            $tour_name = trim($_POST['tour_name'] ?? 'The Saviors World Tour');
            $event_date = !empty($_POST['event_date']) ? $_POST['event_date'] : date('Y-m-d');
            $city = trim($_POST['city'] ?? '');
            $country = trim($_POST['country'] ?? '');
            $venue = trim($_POST['venue'] ?? '');
            $base_price = max(0, (float)($_POST['base_price'] ?? 500000.00));
            $status = in_array($_POST['status'] ?? '', ['available', 'sold-out', 'completed']) ? $_POST['status'] : 'available';

            if (empty($city) || empty($country) || empty($venue)) {
                throw new Exception("Kota, Negara, dan Venue konser wajib diisi!");
            }

            $stmt = mysqli_prepare($conn, "INSERT INTO tours (tour_name, event_date, city, country, venue, base_price, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssssds", $tour_name, $event_date, $city, $country, $venue, $base_price, $status);
            break;

        case 'users':
            if ($auth_role !== 'superadmin') {
                throw new Exception("Akses Ditolak: Hanya Superadmin yang berwenang menambahkan user baru!");
            }
            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');
            $role = in_array($_POST['role'] ?? '', ['superadmin', 'admin', 'editor']) ? $_POST['role'] : 'admin';

            if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
                throw new Exception("Semua field pengguna wajib diisi!");
            }

            if (strlen($password) < 4) {
                throw new Exception("Password pengguna minimal harus 4 karakter!");
            }

            // Cek username atau email duplikat
            $cek = mysqli_query($conn, "SELECT id FROM users WHERE username = '" . mysqli_real_escape_string($conn, $username) . "' OR email = '" . mysqli_real_escape_string($conn, $email) . "'");
            if (mysqli_num_rows($cek) > 0) {
                throw new Exception("Username atau Email sudah terdaftar! Gunakan yang lain.");
            }

            // Hash password BCRYPT
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssss", $username, $email, $hashed_password, $full_name, $role);
            break;

        case 'videos':
            $title = trim($_POST['title'] ?? '');
            $category_label = trim($_POST['category_label'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $video_source = handle_file_upload('video_source_file', $_POST['video_source'] ?? '');
            $poster_image = handle_file_upload('poster_image_file', $_POST['poster_image'] ?? '');
            $duration = trim($_POST['duration'] ?? '');

            if (empty($title) || empty($category_label) || empty($video_source)) {
                throw new Exception("Judul video, Kategori, dan Sumber Video wajib diisi!");
            }

            $stmt = mysqli_prepare($conn, "INSERT INTO videos (title, category_label, description, video_source, poster_image, duration) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssssss", $title, $category_label, $description, $video_source, $poster_image, $duration);
            break;
    }

    if (isset($stmt) && $stmt) {
        if (mysqli_stmt_execute($stmt)) {
            $inserted_id = mysqli_insert_id($conn);

            // Logika bisnis: Kurangi kuota tiket jika pemesanan langsung status 'paid'
            if ($table === 'ticket_bookings' && $payment_status === 'paid' && $category_id > 0 && $quantity > 0) {
                mysqli_query($conn, "UPDATE ticket_categories SET available_quota = GREATEST(0, available_quota - $quantity) WHERE id = $category_id");
            }

            header("Location: index.php?table=$table&status=success&msg=" . urlencode("Data baru berhasil ditambahkan ke tabel $table (ID: $inserted_id)!"));
        } else {
            throw new Exception("Gagal mengeksekusi query: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    } else {
        throw new Exception("Gagal menyiapkan statement database.");
    }
} catch (Exception $e) {
    header("Location: index.php?table=$table&status=danger&msg=" . urlencode($e->getMessage()));
}

mysqli_close($conn);
exit;
?>