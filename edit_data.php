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

// Helper upload file (jika ada file baru, simpan ke uploads/, jika tidak gunakan fallback)
function handle_file_upload_edit($file_key, $existing_value = '', $text_fallback = '', $sub_dir = 'uploads/') {
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
            // Hapus file lama jika sebelumnya ada di uploads/
            if (!empty($existing_value) && strpos($existing_value, 'uploads/') === 0 && file_exists(__DIR__ . '/' . $existing_value)) {
                @unlink(__DIR__ . '/' . $existing_value);
            }
            return $sub_dir . $new_name;
        }
    }
    // Jika user menginput teks URL / path baru
    if (!empty($text_fallback) && $text_fallback !== $existing_value) {
        return trim($text_fallback);
    }
    return $existing_value;
}

$table = isset($_POST['table']) ? trim($_POST['table']) : '';
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

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

if (!in_array($table, $allowed_tables) || $id <= 0) {
    header("Location: index.php?status=danger&msg=" . urlencode("Parameter edit tidak valid!"));
    exit;
}

try {
    switch ($table) {
        case 'albums':
            $title = trim($_POST['title'] ?? '');
            $release_year = (int)($_POST['release_year'] ?? date('Y'));
            $old_cover = $_POST['old_cover_image'] ?? '';
            $cover_image = handle_file_upload_edit('cover_image_file', $old_cover, $_POST['cover_image'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($title)) {
                throw new Exception("Judul album tidak boleh kosong!");
            }

            $stmt = mysqli_prepare($conn, "UPDATE albums SET title = ?, release_year = ?, cover_image = ?, description = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sissi", $title, $release_year, $cover_image, $description, $id);
            break;

        case 'band_members':
            $name = trim($_POST['name'] ?? '');
            $role = trim($_POST['role'] ?? '');
            $old_photo = $_POST['old_photo'] ?? '';
            $photo = handle_file_upload_edit('photo_file', $old_photo, $_POST['photo'] ?? '');
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

            $stmt = mysqli_prepare($conn, "UPDATE band_members SET name = ?, role = ?, photo = ?, birth_date = ?, join_year = ?, biography = ?, is_touring_member = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssssisii", $name, $role, $photo, $birth_date, $join_year, $biography, $is_touring_member, $id);
            break;

        case 'community_posts':
            $author_name = trim($_POST['author_name'] ?? '');
            $author_email = !empty($_POST['author_email']) ? trim($_POST['author_email']) : null;
            $message = trim($_POST['message'] ?? '');
            $likes = max(0, (int)($_POST['likes'] ?? 0));

            if (empty($author_name) || empty($message)) {
                throw new Exception("Nama penulis dan isi komentar tidak boleh kosong!");
            }

            $stmt = mysqli_prepare($conn, "UPDATE community_posts SET author_name = ?, author_email = ?, message = ?, likes = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssii", $author_name, $author_email, $message, $likes, $id);
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

            $stmt = mysqli_prepare($conn, "UPDATE contact_messages SET name = ?, email = ?, subject = ?, message = ?, status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssssi", $name, $email, $subject, $message, $status, $id);
            break;

        case 'news':
            $title = trim($_POST['title'] ?? '');
            $slug = trim($_POST['slug'] ?? '');
            if (empty($slug) && !empty($title)) {
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            }
            $summary = trim($_POST['summary'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $old_image = $_POST['old_image_url'] ?? '';
            $image_url = handle_file_upload_edit('image_url_file', $old_image, $_POST['image_url'] ?? '');
            $published_at = !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d');

            if (empty($title) || empty($summary) || empty($content)) {
                throw new Exception("Judul, Ringkasan, dan Konten berita wajib diisi!");
            }

            // Cek keunikan slug kecuali record ini sendiri
            $cek = mysqli_query($conn, "SELECT id FROM news WHERE slug = '" . mysqli_real_escape_string($conn, $slug) . "' AND id != $id");
            if (mysqli_num_rows($cek) > 0) {
                $slug .= '-' . time();
            }

            $stmt = mysqli_prepare($conn, "UPDATE news SET title = ?, slug = ?, summary = ?, content = ?, image_url = ?, published_at = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssssssi", $title, $slug, $summary, $content, $image_url, $published_at, $id);
            break;

        case 'songs':
            $album_id = (int)($_POST['album_id'] ?? 0);
            $title = trim($_POST['title'] ?? '');
            $track_number = max(1, (int)($_POST['track_number'] ?? 1));
            $duration = trim($_POST['duration'] ?? '03:00');
            $old_audio = $_POST['old_audio_file'] ?? '';
            $audio_file = handle_file_upload_edit('audio_file_file', $old_audio, $_POST['audio_file'] ?? '');
            $lyrics = trim($_POST['lyrics'] ?? '');
            $play_count = max(0, (int)($_POST['play_count'] ?? 0));

            if ($album_id <= 0 || empty($title)) {
                throw new Exception("Album dan Judul lagu wajib dipilih dan diisi!");
            }

            $stmt = mysqli_prepare($conn, "UPDATE songs SET album_id = ?, title = ?, track_number = ?, duration = ?, audio_file = ?, lyrics = ?, play_count = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "isisssii", $album_id, $title, $track_number, $duration, $audio_file, $lyrics, $play_count, $id);
            break;

        case 'ticket_bookings':
            if ($auth_role === 'editor') {
                throw new Exception("Akses Ditolak: Editor tidak memiliki wewenang untuk mengubah data transaksi tiket!");
            }
            // Ambil data pemesanan lama sebelum update untuk perbandingan kuota
            $q_old_b = mysqli_query($conn, "SELECT category_id, quantity, payment_status FROM ticket_bookings WHERE id = $id");
            $old_booking = mysqli_fetch_assoc($q_old_b);
            if (!$old_booking) {
                throw new Exception("Data transaksi pemesanan tidak ditemukan!");
            }
            $old_cat_id = (int)$old_booking['category_id'];
            $old_qty = (int)$old_booking['quantity'];
            $old_status = $old_booking['payment_status'];

            $booking_code = trim($_POST['booking_code'] ?? '');
            $tour_id = (int)($_POST['tour_id'] ?? 0);
            $category_id = (int)($_POST['category_id'] ?? 0);
            $buyer_name = trim($_POST['buyer_name'] ?? '');
            $buyer_email = trim($_POST['buyer_email'] ?? '');
            $quantity = (int)($_POST['quantity'] ?? 1);
            $total_price = (float)($_POST['total_price'] ?? 0);
            $payment_status = in_array($_POST['payment_status'] ?? '', ['pending', 'paid', 'cancelled']) ? $_POST['payment_status'] : 'paid';

            if (empty($booking_code) || empty($buyer_name) || empty($buyer_email) || $tour_id <= 0 || $category_id <= 0) {
                throw new Exception("Semua data transaksi pemesanan tiket wajib diisi!");
            }

            if ($quantity <= 0) {
                throw new Exception("Jumlah tiket harus lebih dari 0!");
            }

            // Ambil data kategori tiket
            $q_cat = mysqli_query($conn, "SELECT price, total_quota, available_quota FROM ticket_categories WHERE id = $category_id");
            $r_cat = mysqli_fetch_assoc($q_cat);
            if (!$r_cat) {
                throw new Exception("Kategori tiket yang dipilih tidak ditemukan!");
            }

            // Auto-calculate total price jika kosong / 0
            if ($total_price <= 0) {
                $total_price = $quantity * (float)$r_cat['price'];
            }

            $stmt = mysqli_prepare($conn, "UPDATE ticket_bookings SET booking_code = ?, tour_id = ?, category_id = ?, buyer_name = ?, buyer_email = ?, quantity = ?, total_price = ?, payment_status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "siissidsi", $booking_code, $tour_id, $category_id, $buyer_name, $buyer_email, $quantity, $total_price, $payment_status, $id);
            break;

        case 'ticket_categories':
            if ($auth_role === 'editor') {
                throw new Exception("Akses Ditolak: Editor tidak memiliki wewenang untuk mengubah kategori tiket konser!");
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

            $stmt = mysqli_prepare($conn, "UPDATE ticket_categories SET tour_id = ?, category_name = ?, description = ?, price = ?, total_quota = ?, available_quota = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "issdiii", $tour_id, $category_name, $description, $price, $total_quota, $available_quota, $id);
            break;

        case 'tours':
            if ($auth_role === 'editor') {
                throw new Exception("Akses Ditolak: Editor tidak memiliki wewenang untuk mengubah data tur konser!");
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

            $stmt = mysqli_prepare($conn, "UPDATE tours SET tour_name = ?, event_date = ?, city = ?, country = ?, venue = ?, base_price = ?, status = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "sssssdsi", $tour_name, $event_date, $city, $country, $venue, $base_price, $status, $id);
            break;

        case 'users':
            $target_user_q = mysqli_query($conn, "SELECT * FROM users WHERE id = $id");
            $target_user = mysqli_fetch_assoc($target_user_q);
            if (!$target_user) {
                throw new Exception("Data pengguna yang akan diubah tidak ditemukan!");
            }
            $target_role = $target_user['role'];

            // Logika hak akses perizinan edit user:
            if ($auth_role === 'editor') {
                // Editor hanya bisa mengedit dirinya sendiri
                if ($id !== $auth_user_id) {
                    throw new Exception("Akses Ditolak: Editor hanya dapat mengedit akun profil dirinya sendiri!");
                }
                // Editor tidak boleh mengubah role dirinya
                $role = $target_role;
            } elseif ($auth_role === 'admin') {
                // Admin tidak bisa mengedit akun superadmin
                if ($target_role === 'superadmin') {
                    throw new Exception("Akses Ditolak: Admin tidak diizinkan untuk mengedit akun Superadmin!");
                }
                // Admin hanya bisa delete dan modifikasi editor, atau mengedit akun dirinya sendiri
                if ($target_role !== 'editor' && $id !== $auth_user_id) {
                    throw new Exception("Akses Ditolak: Admin hanya memiliki wewenang untuk memodifikasi akun Editor atau profil sendiri!");
                }
                // Admin tidak dapat mengubah role menjadi superadmin
                $role = $target_role;
            } else {
                // Superadmin: bebas mengubah role
                $role = in_array($_POST['role'] ?? '', ['superadmin', 'admin', 'editor']) ? $_POST['role'] : $target_role;
            }

            $username = trim($_POST['username'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');
            $full_name = trim($_POST['full_name'] ?? '');

            if (empty($username) || empty($email) || empty($full_name)) {
                throw new Exception("Username, Email, dan Nama Lengkap wajib diisi!");
            }

            // Cek duplikasi username / email selain akun ini
            $cek = mysqli_query($conn, "SELECT id FROM users WHERE (username = '" . mysqli_real_escape_string($conn, $username) . "' OR email = '" . mysqli_real_escape_string($conn, $email) . "') AND id != $id");
            if (mysqli_num_rows($cek) > 0) {
                throw new Exception("Username atau Email sudah dipakai akun lain!");
            }

            if (!empty($password)) {
                if (strlen($password) < 4) {
                    throw new Exception("Password baru pengguna minimal harus 4 karakter!");
                }
                // Update dengan password baru yang di-hash
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $stmt = mysqli_prepare($conn, "UPDATE users SET username = ?, email = ?, password = ?, full_name = ?, role = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "sssssi", $username, $email, $hashed_password, $full_name, $role, $id);
            } else {
                // Pertahankan password lama
                $stmt = mysqli_prepare($conn, "UPDATE users SET username = ?, email = ?, full_name = ?, role = ? WHERE id = ?");
                mysqli_stmt_bind_param($stmt, "ssssi", $username, $email, $full_name, $role, $id);
            }

            // Jika mengedit akun yang sedang login, update session
            if ($id === $auth_user_id) {
                $_SESSION['username'] = $username;
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                $_SESSION['role'] = $role;
            }
            break;

        case 'videos':
            $title = trim($_POST['title'] ?? '');
            $category_label = trim($_POST['category_label'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $old_video = $_POST['old_video_source'] ?? '';
            $video_source = handle_file_upload_edit('video_source_file', $old_video, $_POST['video_source'] ?? '');
            $old_poster = $_POST['old_poster_image'] ?? '';
            $poster_image = handle_file_upload_edit('poster_image_file', $old_poster, $_POST['poster_image'] ?? '');
            $duration = trim($_POST['duration'] ?? '');

            if (empty($title) || empty($category_label) || empty($video_source)) {
                throw new Exception("Judul video, Kategori, dan Sumber Video wajib diisi!");
            }

            $stmt = mysqli_prepare($conn, "UPDATE videos SET title = ?, category_label = ?, description = ?, video_source = ?, poster_image = ?, duration = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ssssssi", $title, $category_label, $description, $video_source, $poster_image, $duration, $id);
            break;
    }

    if (isset($stmt) && $stmt) {
        if (mysqli_stmt_execute($stmt)) {
            // Logika bisnis penyesuaian kuota pada perubahan transaksi tiket
            if ($table === 'ticket_bookings') {
                if ($old_status === 'paid' && $payment_status !== 'paid') {
                    // Status dibatalkan / pending: kembalikan kuota ke kategori lama
                    mysqli_query($conn, "UPDATE ticket_categories SET available_quota = LEAST(total_quota, available_quota + $old_qty) WHERE id = $old_cat_id");
                } elseif ($old_status !== 'paid' && $payment_status === 'paid') {
                    // Status berubah menjadi paid: potong kuota dari kategori baru
                    mysqli_query($conn, "UPDATE ticket_categories SET available_quota = GREATEST(0, available_quota - $quantity) WHERE id = $category_id");
                } elseif ($old_status === 'paid' && $payment_status === 'paid') {
                    if ($category_id === $old_cat_id) {
                        $diff = $quantity - $old_qty;
                        if ($diff > 0) {
                            mysqli_query($conn, "UPDATE ticket_categories SET available_quota = GREATEST(0, available_quota - $diff) WHERE id = $category_id");
                        } elseif ($diff < 0) {
                            $refund = abs($diff);
                            mysqli_query($conn, "UPDATE ticket_categories SET available_quota = LEAST(total_quota, available_quota + $refund) WHERE id = $category_id");
                        }
                    } else {
                        // Kategori tiket berpindah
                        mysqli_query($conn, "UPDATE ticket_categories SET available_quota = LEAST(total_quota, available_quota + $old_qty) WHERE id = $old_cat_id");
                        mysqli_query($conn, "UPDATE ticket_categories SET available_quota = GREATEST(0, available_quota - $quantity) WHERE id = $category_id");
                    }
                }
            }

            header("Location: index.php?table=$table&status=success&msg=" . urlencode("Data tabel $table (ID: $id) berhasil diperbarui!"));
        } else {
            throw new Exception("Gagal mengeksekusi update: " . mysqli_stmt_error($stmt));
        }
        mysqli_stmt_close($stmt);
    } else {
        throw new Exception("Gagal menyiapkan statement update database.");
    }
} catch (Exception $e) {
    header("Location: index.php?table=$table&status=danger&msg=" . urlencode($e->getMessage()));
}

mysqli_close($conn);
exit;
?>