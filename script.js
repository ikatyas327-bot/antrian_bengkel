document.addEventListener('DOMContentLoaded', function() {
    const antrianForm = document.getElementById('antrianForm');
    const successMessage = document.getElementById('successMessage');

    // 1. Logika Pemrosesan Form Antrian
    if (antrianForm) {
        antrianForm.addEventListener('submit', function(event) {
             // Mencegah form di-submit secara default

            // --- SIMULASI PENGIRIMAN DATA ---
            
            // Mengambil data dari form
            const formData = new FormData(this);
            const nama = formData.get('nama');
            const telepon = formData.get('telepon');
            
            console.log(`Pemesanan antrian dari: ${nama}, Telp: ${telepon}`);
            
            // Sembunyikan form dan tampilkan pesan sukses
            antrianForm.style.display = 'none';
            successMessage.style.display = 'block';

            // Opsional: Kembali ke tampilan form setelah 8 detik
            setTimeout(() => {
                 antrianForm.style.display = 'block';
                 successMessage.style.display = 'none';
                 this.reset(); // Mengosongkan form
            }, 8000); 
        });
    }

    // 2. Efek Smooth Scroll untuk link navigasi
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            // Kecualikan tombol Pesan Antrian di navbar agar tidak terpengaruh smooth scroll
            if (this.classList.contains('btn-nav')) return; 

            e.preventDefault();

            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
});
// --- Smooth Scroll for Navbar Links ---
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute('href'));
    if (target) {
      target.scrollIntoView({
        behavior: 'smooth'
      });
    }
  });
});

// --- Navbar hover animation ---
const navLinks = document.querySelectorAll('nav ul li a');
navLinks.forEach(link => {
  link.addEventListener('mouseenter', () => {
    link.style.color = '#00ffc6';
    link.style.transition = 'color 0.3s ease';
  });
  link.addEventListener('mouseleave', () => {
    link.style.color = '';
  });
});

// --- Navbar scroll effect ---
window.addEventListener('scroll', () => {
  const nav = document.querySelector('nav');
  if (window.scrollY > 80) {
    nav.style.background = 'rgba(0,0,0,0.85)';
    nav.style.backdropFilter = 'blur(6px)';
    nav.style.transition = '0.3s ease';
  } else {
    nav.style.background = 'transparent';
  }
});
