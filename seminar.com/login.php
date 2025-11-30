<?php
// Wajib ada di setiap halaman yang menggunakan session
session_start();

// Sisipkan file koneksi database
include 'koneksi.php';

// Inisialisasi variabel untuk pesan
$error_message = '';
$success_message = '';

// Cek apakah ada pesan sukses dari halaman registrasi
if (isset($_SESSION['register_success'])) {
    $success_message = $_SESSION['register_success'];
    // Hapus session agar pesan tidak muncul lagi saat halaman di-refresh
    unset($_SESSION['register_success']);
}

// Cek apakah form sudah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Ambil username dan password dari form
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = trim($_POST['password']);

    // Validasi dasar
    if (empty($username) || empty($password)) {
        $error_message = "Username dan password wajib diisi!";
    } else {
        // 2. Cari user di database berdasarkan username
        $sql = "SELECT user_id, username, password_hash FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // 3. Cek apakah user ditemukan
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // 4. Verifikasi password yang diinput dengan hash di database
            if (password_verify($password, $user['password_hash'])) {
                // Jika password cocok, login berhasil!
                
                // Simpan informasi user ke dalam session
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                
                // Arahkan ke halaman dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                // Jika password tidak cocok
                $error_message = "Username atau password salah!";
            }
        } else {
            // Jika username tidak ditemukan
            $error_message = "Username atau password salah!";
        }
        $stmt->close();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/login.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* Tambahkan style ini di CSS Anda untuk pesan */
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
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>

<body>
    <div class="background">
        <img src="img/self.png" alt="background">
    </div>

    <div class="wrapper">
        <form action="" method="POST">
            <h1>Login</h1>
            
            <?php if (!empty($error_message)): ?>
                <div class="message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="message success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <div class="input-box">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div class="input-box">
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <div class="remember-forgot">
                <button type="submit" class="btn">Login</button>
                <div class="register-link">
                    <p>Don't have an account? <a href="register.php">Register</a></p>
                </div>
            </div>
        </form>
    </div>

</body>
</html>