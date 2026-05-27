document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.nav-item[href^="#"]');
    const sections = document.querySelectorAll('.content-section');

    function showSection(sectionId) {
        // Ocultar todas las secciones
        sections.forEach(sec => {
            sec.classList.add('hidden');
        });

        // Mostrar la seccion seleccionada
        const targetSection = document.getElementById(sectionId);
        if (targetSection) {
            targetSection.classList.remove('hidden');
        }

        // Actualizar clase active en sidebar
        navItems.forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('href') === '#' + sectionId) {
                item.classList.add('active');
            }
        });
    }

    // Agregar evento a los items del sidebar
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = this.getAttribute('href').substring(1);
            showSection(sectionId);
        });
    });

    // Manejar el toggle del menu en moviles
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }

    // LÓGICA DE FILTRADO DEL MENÚ
    const menuCatBtns = document.querySelectorAll('.menu-cat-btn');
    const menuItems = document.querySelectorAll('.menu-item');

    menuCatBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Quitar active de todos
            menuCatBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const categoryId = this.getAttribute('data-category-id');

            menuItems.forEach(item => {
                if (categoryId === 'all' || item.getAttribute('data-category-id') === categoryId) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // LÓGICA DE RESERVAS: Validación de Zonas y Personas
    const zonaSelect = document.getElementById('zona');
    const personasSelect = document.getElementById('personas');

    if (zonaSelect && personasSelect) {
        zonaSelect.addEventListener('change', function() {
            const zona = this.value;
            let maxPersonas = 20; // Default

            if (zona === 'interior') maxPersonas = 10;
            else if (zona === 'terraza') maxPersonas = 6;
            else if (zona === 'privado') maxPersonas = 15;

            // Re-generar opciones de personas
            personasSelect.innerHTML = '<option value="">Seleccione...</option>';
            for (let i = 1; i <= maxPersonas; i++) {
                personasSelect.innerHTML += `<option value="${i}">${i} persona${i > 1 ? 's' : ''}</option>`;
            }
            if(maxPersonas > 1) {
                 personasSelect.innerHTML += `<option value="${maxPersonas + 1}">${maxPersonas + 1}+ personas (Contactar restaurante)</option>`;
            }
        });
    }

    // LÓGICA DE PRE-ORDEN Y PEDIDOS: Botones +/-
    const qtyControls = document.querySelectorAll('.quantity-control');
    qtyControls.forEach(control => {
        const minusBtn = control.querySelector('.minus');
        const plusBtn = control.querySelector('.plus');
        const valueSpan = control.querySelector('.qty-value');

        if (minusBtn && plusBtn && valueSpan) {
            minusBtn.addEventListener('click', () => {
                let val = parseInt(valueSpan.textContent);
                if (val > 0) valueSpan.textContent = val - 1;
            });

            plusBtn.addEventListener('click', () => {
                let val = parseInt(valueSpan.textContent);
                valueSpan.textContent = val + 1;
            });
        }
    });

    // LÓGICA DE MODALES
    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('hidden');
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('hidden');
    };

    // LÓGICA DE DESCARGA (Placeholder)
    window.downloadPDF = function() {
        // En un futuro esto llamará al backend para generar un PDF.
        // Por ahora, usamos print del navegador.
        window.print();
    };

    // Exportar funcion global por si algun boton la necesita
    window.showSection = showSection;
});
