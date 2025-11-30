<?php
require_once 'koneksi.php';

// Inisialisasi variabel pesan
$pesan_sukses = "";
$pesan_error = "";

// 2. Cek apakah form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 3. Ambil dan bersihkan data dari form
    $nama_lengkap = $conn->real_escape_string($_POST['nama_lengkap']);
    $email = $conn->real_escape_string($_POST['alamat_email']);
    $nomor_instansi = $conn->real_escape_string($_POST['nomor_instansi']);
    $jabatan_studi = $conn->real_escape_string($_POST['jabatan']);
    // Mengambil jenis tiket yang dipilih, bukan lagi jumlah
    $jenis_tiket = $conn->real_escape_string($_POST['jenis_tiket']);

    // 4. Proses upload bukti pembayaran
    $bukti_pembayaran_nama = "";
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == 0) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $nama_file_asli = basename($_FILES["bukti_pembayaran"]["name"]);
        $ekstensi_file = strtolower(pathinfo($nama_file_asli, PATHINFO_EXTENSION));
        // Membuat nama file unik untuk menghindari duplikasi
        $bukti_pembayaran_nama = uniqid('bukti_', true) . '.' . $ekstensi_file;
        $target_file = $target_dir . $bukti_pembayaran_nama;

        $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
        // Validasi tipe file dan ukuran (maksimal 2MB)
        if (in_array($ekstensi_file, $allowed_types) && $_FILES["bukti_pembayaran"]["size"] <= 2000000) {
            if (!move_uploaded_file($_FILES["bukti_pembayaran"]["tmp_name"], $target_file)) {
                $pesan_error = "Maaf, terjadi kesalahan saat mengupload file.";
            }
        } else {
            $pesan_error = "File harus berformat JPG, JPEG, PNG, atau PDF dan ukuran maksimal 2MB.";
        }
    } else {
        $pesan_error = "Bukti pembayaran wajib diupload.";
    }

    // 5. Simpan ke database jika tidak ada error upload
    if (empty($pesan_error)) {
        // Query SQL diubah untuk menyimpan `jenis_tiket`
        $sql = "INSERT INTO pendaftaran (nama_lengkap, email, nomor_instansi, jabatan_studi, jenis_tiket, bukti_pembayaran)
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            // Binding parameter disesuaikan: `i` (integer) menjadi `s` (string) untuk jenis tiket
            $stmt->bind_param("ssssss", $nama_lengkap, $email, $nomor_instansi, $jabatan_studi, $jenis_tiket, $bukti_pembayaran_nama);
            
            if ($stmt->execute()) {
                $pesan_sukses = "Pendaftaran berhasil! Terima kasih, " . htmlspecialchars($nama_lengkap) . ". Kami akan segera memverifikasi pembayaran Anda.";
            } else {
                $pesan_error = "Terjadi kesalahan saat menyimpan data: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $pesan_error = "Terjadi kesalahan pada query database: " . $conn->error;
        }
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Pemilihan Tiket</title>
    <!-- Ganti 'reguler.css' jika nama file CSS Anda berbeda -->
    <link rel="stylesheet" href="css/reguler.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

    <header>
        <div class="header-container">
            <a href="#" class="logo">j. sulaiman</a>
            <nav>
                <ul>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                    <li><a href="tiket.php" class="active">Tiket</a></li>
                    <li><a href="#">Log Out</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <div class="breadcrumb">
            <a href="#">Home</a> > <a href="#">Tiket</a> > <span>Pendaftaran</span>
        </div>

        <h1>Pendaftaran Pemilihan Tiket</h1>
        <p class="subtitle">Lengkapi data diri Anda untuk menyelesaikan pembelian.</p>

        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert success"><?= htmlspecialchars($pesan_sukses) ?></div>
        <?php elseif (!empty($pesan_error)): ?>
            <div class="alert error"><?= htmlspecialchars($pesan_error) ?></div>
        <?php endif; ?>

        <div class="content-wrapper">
            <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data" class="registration-form">
                
                <section class="form-section">
                    <h2>1. Informasi Peserta</h2>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nama_lengkap">Nama Lengkap*</label>
                            <input type="text" id="nama_lengkap" name="nama_lengkap" required>
                        </div>
                        <div class="form-group">
                            <label for="alamat_email">Alamat Email*</label>
                            <input type="email" id="alamat_email" name="alamat_email" required>
                        </div>
                        <div class="form-group">
                            <label for="nomor_instansi">Nama Instansi/Alamat/nama sekolah</label>
                            <input type="text" id="nomor_instansi" name="nomor_instansi">
                        </div>
                        <div class="form-group">
                            <label for="jabatan">/Program Studi</label>
                            <input type="text" id="jabatan" name="jabatan">
                        </div>
                    </div>
                </section>

                <!-- ================= PERUBAHAN DIMULAI DI SINI ================= -->
                <section class="form-section">
                    <h2>2. Pilihan Tiket</h2>
                    <div class="ticket-options">
                        <div class="radio-group">
                            <input type="radio" id="reguler" name="jenis_tiket" value="Reguler" data-price="100000" checked>
                            <label for="reguler">Reguler (Rp 100.000)</label>
                        </div>
                        <div class="radio-group">
                            <input type="radio" id="mahasigma" name="jenis_tiket" value="Mahasigma" data-price="75000">
                            <label for="mahasigma">Mahasigma (Rp 75.000)</label>
                        </div>
                        <div class="radio-group">
                            <input type="radio" id="pelajar" name="jenis_tiket" value="Pelajar" data-price="50000">
                            <label for="pelajar">Pelajar (Rp 50.000)</label>
                        </div>
                    </div>
                </section>
                <!-- ================= PERUBAHAN SELESAI DI SINI ================= -->


                <section class="form-section">
                    <h2>3. Pembayaran</h2>
                    <div class="payment-method">
                        <input type="radio" id="bca" name="metode_pembayaran" value="bca" checked>
                        <label for="bca">Transfer Bank (BCA: 987-644-321 a.n. J. Sulaiman)</label>
                    </div>
                    <div class="file-upload-wrapper">
                        <label for="bukti_pembayaran" class="file-upload-btn">Pilih File</label>
                        <input type="file" id="bukti_pembayaran" name="bukti_pembayaran" required>
                        <span id="file-name-display">Belum ada file dipilih</span>
                    </div>
                    <div class="form-group-checkbox">
                        <input type="checkbox" id="terms" name="terms" required>
                        <label for="terms">Saya menyetujui Syarat & Ketentuan* <a href="#">(Lihat S&K)</a></label>
                    </div>
                </section>
                
                <button type="submit" class="submit-btn">Bayar Sekarang</button>
            </form>

            <aside class="order-summary">
                <h3>Ringkasan Pesanan</h3>
                <div class="summary-item">
                    <!-- ID ditambahkan untuk JavaScript -->
                    <span id="summary-ticket-name">Tiket Reguler</span>
                    <span id="summary-ticket-price">Rp 100.000</span>
                </div>
                <div class="summary-item">
                    <span>Biaya Admin</span>
                    <!-- Data value ditambahkan untuk perhitungan -->
                    <span id="admin-fee" data-value="5000">Rp 5.000</span>
                </div>
                <hr>
                <div class="summary-total">
                    <span>Total Pembayaran</span>
                    <!-- ID ditambahkan untuk JavaScript -->
                    <strong id="summary-total">Rp 105.000</strong>
                </div>
            </aside>
        </div>
    </main>

    <!-- ================= JAVASCRIPT UNTUK RINGKASAN DINAMIS ================= -->
    <script>
        // Menampilkan nama file yang di-upload
        const fileInput = document.getElementById('bukti_pembayaran');
        const fileNameDisplay = document.getElementById('file-name-display');
        fileInput.onchange = function () {
            if (this.files.length > 0) {
                fileNameDisplay.textContent = this.files[0].name;
            } else {
                fileNameDisplay.textContent = 'Belum ada file dipilih';
            }
        };

        // Mengelola ringkasan pesanan dinamis
        document.addEventListener('DOMContentLoaded', function() {
            const ticketOptions = document.querySelectorAll('input[name="jenis_tiket"]');
            const summaryTicketName = document.getElementById('summary-ticket-name');
            const summaryTicketPrice = document.getElementById('summary-ticket-price');
            const summaryTotal = document.getElementById('summary-total');
            const adminFeeElement = document.getElementById('admin-fee');
            
            // Mengambil nilai biaya admin dari atribut data-value
            const adminFee = parseInt(adminFeeElement.getAttribute('data-value'));

            // Fungsi untuk memformat angka menjadi Rupiah
            function formatRupiah(number) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(number).replace('IDR', 'Rp');
            }

            // Fungsi untuk memperbarui ringkasan pesanan
            function updateSummary() {
                const selectedTicket = document.querySelector('input[name="jenis_tiket"]:checked');
                const ticketPrice = parseInt(selectedTicket.getAttribute('data-price'));
                const ticketName = selectedTicket.value;

                const totalPayment = ticketPrice + adminFee;

                summaryTicketName.textContent = `Tiket ${ticketName}`;
                summaryTicketPrice.textContent = formatRupiah(ticketPrice);
                summaryTotal.textContent = formatRupiah(totalPayment);
            }

            // Tambahkan event listener untuk setiap pilihan tiket
            ticketOptions.forEach(option => {
                option.addEventListener('change', updateSummary);
            });

            // Panggil fungsi sekali saat halaman dimuat untuk mengatur nilai awal
            updateSummary();
        });
    </script>
</body>
</html>