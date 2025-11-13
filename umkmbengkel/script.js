// === SMOOTH SCROLL UNTUK NAVIGASI ===
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();

    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      window.scrollTo({
        top: target.offsetTop - 80, // jarak dari atas (biar nggak ketutupan header)
        behavior: 'smooth'
      });
    }
  });
});

// === FORM ANTRIAN ===
const form = document.getElementById('antrianForm');
const successMessage = document.getElementById('successMessage');

if (form) {
  form.addEventListener('submit', function (e) {
    e.preventDefault();

    // Ambil data form
    const nama = document.getElementById('nama').value.trim();
    const telepon = document.getElementById('telepon').value.trim();
    const tanggal = document.getElementById('tanggal').value.trim();
    const jenis = document.getElementById('jenis_perbaikan').value;
    const keluhan = document.getElementById('keluhan').value.trim();

    if (!nama || !telepon || !tanggal || !jenis || !keluhan) {
      alert("⚠️ Harap isi semua kolom terlebih dahulu.");
      return;
    }

    // Tampilkan pesan sukses
    successMessage.style.display = 'block';
    successMessage.style.opacity = '0';
    successMessage.scrollIntoView({ behavior: 'smooth' });

    // Efek fade-in
    setTimeout(() => {
      successMessage.style.transition = 'opacity 0.8s ease';
      successMessage.style.opacity = '1';
    }, 100);

    // Reset form
    form.reset();

    // Hilangkan pesan otomatis setelah 8 detik
    setTimeout(() => {
      successMessage.style.opacity = '0';
      setTimeout(() => {
        successMessage.style.display = 'none';
      }, 800);
    }, 8000);
  });
}

// === EFEK HEADER SCROLL ===
const header = document.querySelector('header');
window.addEventListener('scroll', () => {
  if (window.scrollY > 80) {
    header.style.background = "#fff";
    header.style.boxShadow = "0 2px 12px rgba(0,0,0,0.06)";
  } else {
    header.style.background = "transparent";
    header.style.boxShadow = "none";
  }
});
