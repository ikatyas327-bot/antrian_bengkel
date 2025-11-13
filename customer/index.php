<?php
include '../koneksi.php'; // pastikan ini menunjuk ke koneksi.php utama

if (!isset($conn) || !$conn) {
    die("❌ Koneksi database gagal: variabel \$conn tidak terdefinisi.");
}
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// Ambil data layanan
$query  = "SELECT * FROM menu ORDER BY name ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-SPEED Bengkel</title>
  <style>
   /* ==== RESET & GLOBAL ==== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
}

body {
    background-color: #fafafa;
    color: #2b2b2b;
    line-height: 1.6;
}

/* ==== WARNA UTAMA ==== */
:root {
    --primary-blue: #2f5da8;   /* biru utama */
    --primary-navy: #1a2f58;   /* navy doff elegan */
    --soft-gray: #f2f3f5;
    --text-dark: #2b2b2b;
    --text-muted: #666;
    --deep-blue: #163665;
}

/* ==== HEADER ==== */
header {
    background-color: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    position: sticky;
    top: 0;
    z-index: 1000;
}

header .container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px 60px;
}

/* ==== LOGO ==== */
.navbar-logo {
    height: 55px;
    transition: transform 0.3s ease;
}

.navbar-logo:hover {
    transform: scale(1.04);
}

/* ==== NAVIGATION ==== */
nav ul {
    list-style: none;
    display: flex;
    gap: 30px;
}

nav ul li a {
    position: relative;
    text-decoration: none;
    color: var(--text-dark);
    font-weight: 500;
    padding-bottom: 5px;
    transition: color 0.3s ease;
}

nav ul li a::after {
    content: "";
    position: absolute;
    left: 0;
    bottom: 0;
    width: 0%;
    height: 2px;
    background: var(--primary-blue);
    border-radius: 1px;
    transition: width 0.3s ease;
}

nav ul li a:hover {
    color: var(--primary-blue);
}

nav ul li a:hover::after {
    width: 100%;
}

/* ==== TOMBOL NAV ==== */
.btn-nav {
    background: linear-gradient(135deg, var(--primary-blue), var(--primary-navy));
    color: #fff;
    padding: 10px 22px;
    border-radius: 25px;
    font-weight: 600;
    letter-spacing: 0.5px;
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.btn-nav:hover {
    transform: translateY(-2px);
    opacity: 0.9;
}

/* ==== HERO SECTION ==== */
.hero-section-with-bg {
    background: linear-gradient(rgba(255, 255, 255, 0.65), rgba(255, 255, 255, 0.65)),
                url("../image/Galeri Bengkel 2.jpg") center/cover no-repeat;
    padding: 180px 20px;
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.hero-section-with-bg h2 {
    font-family: 'Playfair Display', serif;
    font-size: 3.6em;
    font-weight: 700;
    letter-spacing: 1px;
    margin-bottom: 15px;
    background: linear-gradient(90deg, #004e92, #000428);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.15);
}

.hero-section-with-bg p {
    font-family: 'Poppins', sans-serif;
    color: #333;
    font-size: 1.15em;
    max-width: 800px;
    margin: 0 auto;
    line-height: 1.7;
}

.hero-btn {
    margin-top: 30px;
    background: linear-gradient(135deg, var(--primary-blue), var(--primary-navy));
    color: #fff;
    padding: 14px 34px;
    border-radius: 30px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.hero-btn:hover {
    transform: translateY(-3px);
    opacity: 0.9;
}


/* ==== SERVICES ==== */
.services-section {
    padding: 80px 50px;
    background-color: #ffffff;
    text-align: center;
}

.services-section h2 {
    font-size: 2.2em;
    color: var(--deep-blue);
    margin-bottom: 10px;
}

.section-subtitle {
    color: #777;
    margin-bottom: 45px;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
    gap: 30px;
}

.service-card {
    background-color: var(--soft-gray);
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.service-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 4px 14px rgba(26, 47, 88, 0.25);
}

.service-image {
    width: 100%;
    height: 200px;
    object-fit: cover;
}

.service-card h3 {
    margin-top: 15px;
    color: var(--primary-blue);
}

.description {
    padding: 12px 20px;
    color: var(--text-muted);
}

.price {
    display: inline-block;
    background: linear-gradient(90deg, var(--primary-navy), var(--primary-blue));
    color: #fff;
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 600;
}

/* ==== ABOUT ==== */
.about-story {
  max-width: 850px;
  margin: 0 auto 60px auto;
  background: #ffffff;
  border-left: 6px solid var(--primary-blue);
  border-radius: 10px;
  padding: 25px 30px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.06);
  text-align: left;
}

.about-story h4 {
  font-size: 1.4em;
  color: #0a3d62;
  margin-bottom: 10px;
  font-weight: 600;
}

.about-story p {
  color: #444;
  font-size: 1.05em;
  line-height: 1.8;
}

.about-section {
  background: #f9fafc;
  padding: 100px 20px;
  text-align: center;
}

.about-title {
  font-family: 'Poppins', sans-serif;
  font-size: 2.2em;
  color: #0a3d62;
  margin-bottom: 10px;
  font-weight: 700;
}

.about-title span {
  color: var(--primary-blue);
}

.about-desc {
  font-size: 1.1em;
  color: #555;
  max-width: 800px;
  margin: 0 auto 50px auto;
  line-height: 1.7;
}

.about-features {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 30px;
}

.feature-box {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.08);
  padding: 30px 25px;
  max-width: 320px;
  transition: all 0.3s ease;
}

.feature-box:hover {
  transform: translateY(-6px);
  box-shadow: 0 10px 18px rgba(0,0,0,0.12);
}

.feature-box i {
  font-size: 2.5em;
  color: var(--primary-blue);
  margin-bottom: 15px;
}

.feature-box h4 {
  font-size: 1.3em;
  margin-bottom: 10px;
  color: #0a3d62;
  font-weight: 600;
}

.feature-box p {
  font-size: 0.95em;
  color: #555;
  line-height: 1.6;
}


/* ==== GALERI ==== */
.gallery {
    background-color: #fafafa;
    padding: 80px 40px;
    text-align: center;
}

.gallery h2 {
    font-size: 2.2em;
    color: var(--deep-blue);
    font-weight: 700;
    position: relative;
    display: inline-block;
    margin-bottom: 50px;
}

.gallery h2::after {
    content: "";
    position: absolute;
    bottom: -12px;
    left: 50%;
    transform: translateX(-50%);
    width: 90px;
    height: 3px;
    border-radius: 2px;
    background: var(--primary-blue);
}

.gallery-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    justify-items: center;
    align-items: center;
}

.gallery-item {
    overflow: hidden;
    border-radius: 14px;
    background-color: #ffffff;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.gallery-item:hover {
    transform: translateY(-6px);
    box-shadow: 0 6px 18px rgba(26, 47, 88, 0.2);
}

.gallery-item img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    border-radius: 14px;
    transition: transform 0.4s ease;
}

.gallery-item:hover img {
    transform: scale(1.05);
}

/* ==== KONTAK ==== */
/* ==== KONTAK ==== */
#kontak {
    background-color: #f9f9f9;
    padding: 50px 20px;
    text-align: center;
}

#kontak h2 {
    font-size: 1.9em;
    color: var(--deep-blue);
    font-weight: 700;
    margin-bottom: 10px;
}

#kontak p {
    color: #555;
    margin-bottom: 35px;
    font-size: 0.95em;
    max-width: 700px;
    margin-left: auto;
    margin-right: auto;
}

/* ==== FLEX SEJAJAR ==== */
.contact-container {
    display: flex;
    justify-content: center;
    align-items: stretch;
    flex-wrap: wrap;
    gap: 18px;
}

/* ==== KOTAK KONTAK ==== */
.contact-box {
    flex: 1 1 150px;
    max-width: 160px;
    background-color: #ffffff;
    border-radius: 14px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    padding: 20px 10px;
    text-align: center;
    transition: all 0.3s ease;
}

.contact-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 12px rgba(26, 47, 88, 0.15);
}

/* ==== IKON ==== */
.contact-box .icon {
    background: linear-gradient(135deg, var(--primary-blue), var(--primary-navy));
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #fff;
    font-size: 16px;
    margin: 0 auto 10px;
}

/* ==== TEKS ==== */
.contact-box h4 {
    font-size: 0.9em;
    color: var(--deep-blue);
    font-weight: 600;
    margin-bottom: 5px;
}

.contact-box p,
.contact-box a {
    color: #444;
    font-size: 0.8em;
    text-decoration: none;
    display: block;
}

.contact-box a:hover {
    color: var(--primary-blue);
}

/* ==== RESPONSIVE ==== */
@media (max-width: 900px) {
    .contact-container {
        gap: 15px;
    }

    .contact-box {
        flex: 1 1 130px;
        max-width: 140px;
        padding: 16px 8px;
    }

    .contact-box .icon {
        width: 36px;
        height: 36px;
        font-size: 14px;
    }
}

@media (max-width: 600px) {
    .contact-container {
        flex-wrap: wrap;
        justify-content: center;
    }

    .contact-box {
        flex: 1 1 45%;
        max-width: 45%;
    }
}

@media (max-width: 420px) {
    .contact-box {
        flex: 1 1 100%;
        max-width: 100%;
    }
}

/* ==== PETA LOKASI ==== */
#lokasi {
    background-color: #ffffff;
    padding: 80px 40px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

#lokasi h2 {
    font-size: 2em;
    color: var(--deep-blue);
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 12px;
}

#lokasi p {
    color: #555;
    font-size: 0.95em;
    margin-bottom: 40px;
    max-width: 700px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

/* ==== MAP BOX ==== */
.map-container {
    width: 100%;
    max-width: 950px;
    margin: 0 auto;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 18px rgba(26, 47, 88, 0.1);
    padding: 12px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.map-container:hover {
    transform: translateY(-4px);
    box-shadow: 0 6px 20px rgba(26, 47, 88, 0.15);
}

.map-container iframe {
    width: 100%;
    height: 450px;
    border-radius: 12px;
    border: none;
}

/* ==== ANIMASI RINGAN UNTUK IKON ==== */
#lokasi h2 i {
    color: var(--primary-blue);
    animation: bounce 1.8s infinite;
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-4px);
    }
}

/* ==== RESPONSIVE ==== */
@media (max-width: 768px) {
    #lokasi {
        padding: 60px 20px;
    }

    #lokasi h2 {
        font-size: 1.6em;
    }

    .map-container iframe {
        height: 350px;
    }
}

@media (max-width: 480px) {
    #lokasi h2 {
        flex-direction: column;
        gap: 4px;
    }

    #lokasi p {
        font-size: 0.9em;
    }

    .map-container iframe {
        height: 300px;
    }
}


/* ==== RESPONSIVE ==== */
@media (max-width: 768px) {
    #kontak {
        padding: 60px 20px;
    }

    .contact-grid {
        grid-template-columns: 1fr;
    }

    .contact-box {
        max-width: 320px;
        min-height: 220px;
    }
}


/* ==== FOOTER ==== */
footer {
    background-color: #ffffff;
    text-align: center;
    padding: 30px 10px;
    color: #666;
    font-size: 0.9em;
    border-top: 1px solid #e3e3e3;
}

/* ==== RESPONSIVE ==== */
@media (max-width: 768px) {
    header .container {
        flex-direction: column;
        gap: 10px;
    }

    nav ul {
        flex-wrap: wrap;
        justify-content: center;
        gap: 18px;
    }

    .hero-section-with-bg {
        padding: 120px 15px;
    }

    .hero-section-with-bg h2 {
        font-size: 2.4em;
    }

    .hero-section-with-bg p {
        font-size: 1em;
    }

    .services-section {
        padding: 50px 20px;
    }

    .booking-form {
        padding: 25px;
    }

    .contact-container {
        flex-direction: column;
        align-items: center;
    }

    .contact-card {
        width: 80%;
    }
}
  </style>
</head>
<body>

 <!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-SPEED Bengkel - Layanan Servis Cepat & Tepat</title>
  <link rel="stylesheet" href="style.css">

  <!-- Font Awesome untuk ikon -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>

  <header>
    <div class="container">
      <div class="logo">
        <img src="../image/logoumkm.png" alt="Logo E-SPEED Bengkel" class="navbar-logo">
      </div>
      <nav>
        <ul>
          <li><a href="#hero">Beranda</a></li>
          <li><a href="#layanan">Layanan</a></li>
          <li><a href="#about">Tentang Kami</a></li>
          <li><a href="#kontak">Kontak</a></li>
          <li><a href="#lokasi">Peta Lokasi</a></li>
          <li><a href="ambil_antrian.php" class="btn-nav">Pesan Antrian</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main>

    <!-- HERO -->
    <section id="hero" class="hero-section-with-bg">
      <div class="container">
        <h2>Selamat Datang di <span class="text-neon">E-SPEED</span></h2>
        <p>Spesialis Perbaikan Mesin dan Kelistrikan Motor/Mobil Anda. Cepat, Tepat, dan Bergaransi!</p>
      </div>
    </section>

    <!-- LAYANAN -->
    <section id="layanan" class="services-section">
      <h2>Layanan Jasa Kami</h2>
      <p class="section-subtitle">Pilih layanan servis kendaraan Anda dengan harga transparan dan estimasi waktu pengerjaan yang jelas.</p>

      <div class="services-grid">
        <div class="service-card">
          <img src="../image/menu1.jpg" alt="Gambar Layanan Bubut" class="service-image">
          <h3>Bubut poros</h3>
          <p class="description">Pengerjaan poros menggunakan mesin bubut untuk hasil presisi tinggi.</p>
          <div class="details">
            <span class="price">Rp 1.500.000</span>
          </div>
          <p class="work-time">Waktu: 1 - 2 Hari</p>
        </div>

        <div class="service-card">
          <img src="../image/menu2.jpg" alt="Gambar Layanan Bubut Cakram Rem" class="service-image">
          <h3>Bubut cakram rem</h3>
          <p class="description">Perataan dan pembubutan cakram rem kendaraan agar kembali halus dan aman.</p>
          <div class="details">
            <span class="price">Rp 350.000</span>
          </div>
          <p class="work-time">Waktu: 1 - 2 Jam (per cakram)</p>
        </div>

        <div class="service-card">
          <img src="../image/menu3.jpg" alt="Gambar Layanan Frais Permukaan" class="service-image">
          <h3>Frais permukaan</h3>
          <p class="description">Pemotongan dan perataan permukaan logam menggunakan mesin frais presisi.</p>
          <div class="details">
            <span class="price">Rp 2.000.000</span>
          </div>
          <p class="work-time">Waktu: 1 - 3 Hari</p>
        </div>
      </div>
    </section>

    <!-- TENTANG -->
    <section id="about" class="section-padding about-section">
  <div class="container">
    <h3 class="about-title">Mengapa Memilih <span>E-SPEED?</span></h3>
    <p class="about-desc">
      E-SPEED Bengkel hadir untuk memberikan pengalaman servis terbaik bagi kendaraan Anda. 
      Kami menggabungkan kecepatan, ketepatan, dan teknologi modern dalam setiap layanan yang kami berikan.
    </p>

    <div class="about-story">
      <h4>Kisah Kami</h4>
      <p>
        Didirikan pada tahun 2015 di jantung Desa Mentaos, E-SPEED bermula dari sebuah bengkel kecil 
        dengan peralatan sederhana dan semangat besar untuk membantu para pengendara di sekitar Gudo, Jombang. 
        Seiring waktu, dedikasi terhadap kualitas dan kepuasan pelanggan membuat E-SPEED berkembang pesat 
        menjadi bengkel modern dengan layanan profesional.  
        Kini kami menjadi tempat andalan bagi masyarakat yang menginginkan servis cepat, transparan, dan bergaransi.
      </p>
    </div>

    <div class="about-features">
      <div class="feature-box">
        <i class="fas fa-tools"></i>
        <h4>Mekanik Profesional</h4>
        <p>Tim kami terdiri dari teknisi berpengalaman dan bersertifikat yang siap menangani segala jenis perbaikan mesin dan kelistrikan.</p>
      </div>

      <div class="feature-box">
        <i class="fas fa-tachometer-alt"></i>
        <h4>Servis Cepat & Efisien</h4>
        <p>Kami memahami waktu Anda berharga — perbaikan dilakukan cepat tanpa mengorbankan kualitas.</p>
      </div>

      <div class="feature-box">
        <i class="fas fa-shield-alt"></i>
        <h4>Bergaransi & Transparan</h4>
        <p>Setiap layanan dilengkapi garansi dan laporan hasil servis yang transparan untuk kenyamanan Anda.</p>
      </div>
    </div>
  </div>
</section>



    <!-- GALERI -->
    <section class="gallery">
      <h2>Galeri Bengkel Kami</h2>
      <div class="gallery-container">
        <div class="gallery-item"><img src="../image/Galeri Bengkel 1.jpg" alt="Bengkel 1"></div>
        <div class="gallery-item"><img src="../image/Galeri Bengkel 2.jpg" alt="Bengkel 2"></div>
        <div class="gallery-item"><img src="../image/Galeri Bengkel 3.jpg" alt="Bengkel 3"></div>
        <div class="gallery-item"><img src="../image/Galeri Bengkel 4.jpg" alt="Bengkel 4"></div>
      </div>
    </section>

    <!-- KONTAK -->
    <section id="kontak">
      <h2>Hubungi Kami</h2>
      <p>Anda dapat menghubungi E-SPEED Bengkel melalui berbagai saluran berikut untuk informasi layanan, kerja sama, atau pemesanan servis.</p>
      
      <div class="contact-container">
        <div class="contact-box">
          <div class="icon"><i class="fas fa-phone"></i></div>
          <h4>Telepon</h4>
          <p>+62 341 555 789</p>
        </div>
        <div class="contact-box">
          <div class="icon"><i class="fab fa-whatsapp"></i></div>
          <h4>WhatsApp</h4>
          <a href="https://wa.me/628123456789">Chat Sekarang</a>
        </div>
        <div class="contact-box">
          <div class="icon"><i class="fas fa-envelope"></i></div>
          <h4>Email</h4>
          <a href="mailto:info@espeedbengkel.id">info@espeedbengkel.id</a>
        </div>
        <div class="contact-box">
          <div class="icon"><i class="fab fa-facebook-f"></i></div>
          <h4>Facebook</h4>
          <a href="#">E-SPEED Bengkel</a>
        </div>
        <div class="contact-box">
          <div class="icon"><i class="fab fa-instagram"></i></div>
          <h4>Instagram</h4>
          <a href="#">@espeed_bengkel</a>
        </div>
      </div>
    </section>

    <!-- PETA LOKASI -->
    <section id="lokasi" class="map-section">
      <h2><i class="fas fa-map-marker-alt"></i> Lokasi Kami</h2>
      <p>Kunjungi bengkel kami langsung di lokasi berikut atau gunakan peta interaktif untuk petunjuk arah.</p>
      
      <div class="map-container">
        <iframe src="https://www.google.com/maps?q=Dsn.+Dermo,+Ds.+Mentaos,+Kec.+Gudo,+Kab.+Jombang,+Jawa+Timur&output=embed" 
        width="100%" 
        height="350" 
        style="border:0;" 
        allowfullscreen="" 
        loading="lazy" 
        referrerpolicy="no-referrer-when-downgrade"> 
        </iframe>

</section>

  </main>

  <footer>
    <div class="container">
      <p>&copy; 2025 E-SPEED Bengkel. All Rights Reserved.</p>
      <p>Dsn. Dermo, Ds. Mentaos, Kec. Gudo, Kab. Jombang, Jawa Timur</p>
    </div>
  </footer>

  <!-- Script -->
  <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
  <script src="script.js"></script>

</body>
</html
