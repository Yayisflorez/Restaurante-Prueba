     // Lógica simple para cambiar estilo de categoría activa
        const categoryBtns = document.querySelectorAll('.menu-category-btn');
        categoryBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                categoryBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                // Aquí iría la lógica para filtrar platos
            });
        });