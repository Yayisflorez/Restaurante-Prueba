// Script para la página Home
document.addEventListener('DOMContentLoaded', function() {
    // Navegación suave al hacer clic en los enlaces del menú
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Efecto de header al hacer scroll
    const header = document.querySelector('header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.style.background = 'rgba(18, 18, 18, 0.95)';
                header.style.boxShadow = '0 2px 20px rgba(0,0,0,0.5)';
            } else {
                header.style.background = 'rgba(18, 18, 18, 0.9)';
                header.style.boxShadow = 'none';
            }
        });
    }

    // Botones de categoría del menú
    const categoryBtns = document.querySelectorAll('.menu-category-btn');
    categoryBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            categoryBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
