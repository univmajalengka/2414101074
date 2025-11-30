<?php
// Mulai sesi untuk menangani pesan
session_start();

// Sisipkan file koneksi.php
include 'koneksi.php';

// Inisialisasi variabel untuk pesan error
$error_message = '';
$success_message = '';

// Cek apakah form sudah di-submit dengan metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Ambil data dari form dan bersihkan
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // 2. Validasi Input
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error_message = "Semua kolom wajib diisi!";
    } elseif ($password !== $confirm_password) {
        $error_message = "Konfirmasi password tidak cocok KOCAK!";
    } elseif (strlen($password) < 6) {
        $error_message = "Password minimal harus 6 karakter!";
    } else {
        // 3. Cek apakah username sudah ada di database
        $sql_check = "SELECT user_id FROM users WHERE username = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error_message = "Username sudah digunakan, silakan pilih yang lain.";
        } else {
            // 4. Jika semua validasi lolos, hash password
            // Ini sangat penting untuk keamanan! Jangan simpan password plain text.
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // 5. Masukkan data ke dalam tabel 'users'
            $sql_insert = "INSERT INTO users (username, password_hash) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            
            // Bind parameter ke statement
            $stmt_insert->bind_param("ss", $username, $password_hash);

            // Eksekusi statement dan cek hasilnya
            if ($stmt_insert->execute()) {
                // Jika berhasil, redirect ke halaman login
                $_SESSION['register_success'] = "Registrasi berhasil! Silakan login.";
                header("Location: login.php");
                exit();
            } else {
                $error_message = "Terjadi kesalahan saat registrasi: " . $conn->error;
            }
            
            // Tutup statement insert
            $stmt_insert->close();
        }

        // Tutup statement check
        $stmt_check->close();
    }
}

// Tutup koneksi database
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/register.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        /* Tambahkan style ini di CSS Anda untuk pesan error */
        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>
    
    <div class="background">
        <img src="img/self.png" alt="background">
    </div>

    <div class="wrapper">
        <form action="" method="POST">
            <h1>Register</h1>

            <?php if (!empty($error_message)): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="input-row">
                <div class="input-box">
                    <input type="text" name="username" placeholder="Username" required>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="input-box">
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                </div>
            </div>

            <button type="submit" class="btn">Register</button>

            <div class="register-link">
                <p>Have an account? <a href="login.php">Log In</a></p>
            </div>
        </form>
    </div>

</body>
</html>