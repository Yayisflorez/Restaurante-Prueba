document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.nav-item[href^="#"]');
    const sections = document.querySelectorAll('.content-section');

    function showSection(sectionId) {
        sections.forEach(sec => sec.classList.add('hidden'));
        const targetSection = document.getElementById(sectionId);
        if (targetSection) targetSection.classList.remove('hidden');

        navItems.forEach(item => {
            item.classList.remove('active');
            if (item.getAttribute('href') === '#' + sectionId) {
                item.classList.add('active');
            }
        });
        
        // Modal center logic for 'pedidos' when no session active
        if (sectionId === 'pedidos' && !sesionPedido) {
            openModal('modal-seleccion-pedido');
            document.getElementById('modal-seleccion-botones').style.display = 'grid';
            document.getElementById('modal-form-con-reserva').style.display = 'none';
            document.getElementById('modal-form-sin-reserva').style.display = 'none';
        } else {
            closeModal('modal-seleccion-pedido');
        }
        
        verificarEstadoSesionPedido();
    }
    window.showSection = showSection;

    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = this.getAttribute('href').substring(1);
            showSection(sectionId);
        });
    });

    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');
    if (menuToggle && sidebar) {
        menuToggle.addEventListener('click', () => sidebar.classList.toggle('active'));
    }

    const menuCatBtns = document.querySelectorAll('.menu-cat-btn:not([data-pedidos-cat])');
    const menuItems = document.querySelectorAll('.menu-item');

    menuCatBtns.forEach(btn => {
        btn.addEventListener('click', function() {
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

    window.openModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('hidden');
    };

    window.closeModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add('hidden');
    };

    // --- LOGICA RESERVAS ---
    window.confirmarReserva = function() {
        const form = document.getElementById('reservaForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        const token = document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('/reservas', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                document.getElementById('conf-fecha').textContent = data.reserva.fecha;
                document.getElementById('conf-hora').textContent = data.reserva.hora;
                document.getElementById('conf-personas').textContent = data.reserva.personas;
                document.getElementById('conf-mesa').textContent = data.reserva.mesa;
                document.getElementById('conf-zona').textContent = data.reserva.zona;
                document.getElementById('conf-codigo').textContent = data.reserva.codigo_referencia;
                openModal('modal-reserva-confirmacion');
                form.reset();
            } else {
                alert('Error al crear reserva: ' + (data.message || ''));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de conexión al guardar reserva.');
        });
    };

    // --- LÓGICA PEDIDOS ---
    let sesionPedido = JSON.parse(sessionStorage.getItem('sesionPedido')) || null;
    let carrito = [];

    function verificarEstadoSesionPedido() {
        const btnConReserva = document.getElementById('btn-con-reserva');
        const btnSinReserva = document.getElementById('btn-sin-reserva');
        const btnFinReserva = document.getElementById('btn-fin-reserva');
        const btnFinServicio = document.getElementById('btn-fin-servicio');
        const vistaNormal = document.getElementById('vista-pedidos-normal');
        
        const headerInfoMesaContainer = document.getElementById('info-mesa-pedido-header');
        const headerInfoMesaTexto = document.getElementById('info-mesa-texto');
        const activeSection = document.querySelector('.nav-item.active')?.getAttribute('href')?.substring(1) || 'menu';

        if (sesionPedido) {
            if(btnConReserva) btnConReserva.style.display = 'none';
            if(btnSinReserva) btnSinReserva.style.display = 'none';
            if (sesionPedido.tipo === 'con_reserva') {
                if(btnFinReserva) btnFinReserva.style.display = 'inline-block';
                if(btnFinServicio) btnFinServicio.style.display = 'none';
            } else {
                if(btnFinReserva) btnFinReserva.style.display = 'none';
                if(btnFinServicio) btnFinServicio.style.display = 'inline-block';
            }
            
            if(vistaNormal) {
                vistaNormal.style.opacity = '1';
                vistaNormal.style.pointerEvents = 'auto';
                vistaNormal.style.filter = 'none';
            }
            if(headerInfoMesaContainer) {
                headerInfoMesaContainer.style.display = 'flex';
                headerInfoMesaTexto.textContent = `Mesa ${sesionPedido.mesa} - ${sesionPedido.zona || 'Local'}`;
            }
            
            closeModal('modal-seleccion-pedido');
        } else {
            if (activeSection === 'pedidos') {
                if(btnConReserva) btnConReserva.style.display = 'none';
                if(btnSinReserva) btnSinReserva.style.display = 'none';
            } else {
                if(btnConReserva) btnConReserva.style.display = 'inline-block';
                if(btnSinReserva) btnSinReserva.style.display = 'inline-block';
            }
            
            if(btnFinReserva) btnFinReserva.style.display = 'none';
            if(btnFinServicio) btnFinServicio.style.display = 'none';
            
            if(vistaNormal) {
                vistaNormal.style.opacity = '0.4';
                vistaNormal.style.pointerEvents = 'none';
                vistaNormal.style.filter = 'blur(3px)';
            }
            if(headerInfoMesaContainer) {
                headerInfoMesaContainer.style.display = 'none';
            }
        }
    }

    window.abrirModalSeleccionPedido = function(tipo) {
        showSection('pedidos');
        mostrarFormularioPedido(tipo);
    };

    window.mostrarFormularioPedido = function(tipo) {
        document.getElementById('modal-seleccion-botones').style.display = 'none';
        document.getElementById('modal-form-con-reserva').style.display = tipo === 'con_reserva' ? 'block' : 'none';
        document.getElementById('modal-form-sin-reserva').style.display = tipo === 'sin_reserva' ? 'block' : 'none';
        
        document.getElementById('auth-error').style.display = 'none';
        document.getElementById('temp-error').style.display = 'none';
    };

    window.volverSeleccionPedido = function() {
        document.getElementById('modal-seleccion-botones').style.display = 'grid';
        document.getElementById('modal-form-con-reserva').style.display = 'none';
        document.getElementById('modal-form-sin-reserva').style.display = 'none';
    };

    window.finalizarSesionPedido = function() {
        if(confirm('¿Estás seguro de finalizar el servicio actual? El carrito se vaciará.')) {
            sessionStorage.removeItem('sesionPedido');
            sesionPedido = null;
            carrito = [];
            renderCarrito();
            verificarEstadoSesionPedido();
        }
    };

    window.verificarReserva = function() {
        const mesa = document.getElementById('auth_mesa').value;
        const codigo = document.getElementById('auth_codigo').value;
        const errTag = document.getElementById('auth-error');
        
        if(!mesa || !codigo) {
            errTag.textContent = 'Llene todos los campos.';
            errTag.style.display = 'block';
            return;
        }

        const token = document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('/pedidos/verificar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ mesa, codigo_referencia: codigo })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                sesionPedido = {
                    tipo: 'con_reserva',
                    mesa: data.reserva.mesa,
                    zona: data.reserva.zona,
                    codigo: data.reserva.codigo_referencia
                };
                sessionStorage.setItem('sesionPedido', JSON.stringify(sesionPedido));
                verificarEstadoSesionPedido();
            } else {
                errTag.textContent = data.message || 'Error de autenticación.';
                errTag.style.display = 'block';
            }
        }).catch(err => {
            console.error(err);
            errTag.textContent = 'Error de conexión.';
            errTag.style.display = 'block';
        });
    };

    window.continuarSinReserva = function() {
        const mesa = document.getElementById('temp_mesa').value;
        const zona = document.getElementById('temp_zona').value;
        const errTag = document.getElementById('temp-error');
        
        if(!mesa || !zona) {
            errTag.textContent = 'Llene todos los campos.';
            errTag.style.display = 'block';
            return;
        }

        sesionPedido = {
            tipo: 'sin_reserva',
            mesa: mesa,
            zona: zona
        };
        sessionStorage.setItem('sesionPedido', JSON.stringify(sesionPedido));
        verificarEstadoSesionPedido();
    };

    // --- CARRITO ---
    window.agregarAlCarrito = function(id, nombre, precio) {
        if(!sesionPedido) {
            alert('Por favor autentica tu pedido primero (Botón Con/Sin Reserva en el panel de arriba).');
            return;
        }
        
        const existe = carrito.find(item => item.id === id);
        if(existe) {
            existe.cantidad++;
        } else {
            carrito.push({ id, nombre, precio, cantidad: 1 });
        }
        renderCarrito();
        
        const btnConf = document.getElementById('btn-confirmar-pedido');
        if(btnConf) {
            btnConf.style.transform = 'scale(1.05)';
            setTimeout(() => btnConf.style.transform = 'scale(1)', 200);
        }
    };

    window.actualizarCantidadCarrito = function(index, delta) {
        carrito[index].cantidad += delta;
        if(carrito[index].cantidad <= 0) {
            carrito.splice(index, 1);
        }
        renderCarrito();
    };

    function renderCarrito() {
        const container = document.getElementById('carrito-items');
        const totalSpan = document.getElementById('carrito-total-precio');
        const vacioText = document.getElementById('carrito-vacio');
        const btnConf = document.getElementById('btn-confirmar-pedido');
        
        if(!container) return;

        container.innerHTML = '';
        let total = 0;

        if(carrito.length === 0) {
            if(vacioText) container.appendChild(vacioText.cloneNode(true));
            if(totalSpan) totalSpan.textContent = '$0.00';
            if(btnConf) btnConf.disabled = true;
            return;
        }

        if(btnConf) btnConf.disabled = false;

        carrito.forEach((item, idx) => {
            total += (item.precio * item.cantidad);
            const div = document.createElement('div');
            div.style.cssText = 'display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid var(--border-color);';
            div.innerHTML = `
                <div style="flex: 1;">
                    <h4 style="margin:0; font-size: 0.95rem;">${item.nombre}</h4>
                    <p style="margin:0; color:var(--primary); font-size: 0.85rem;">$${parseFloat(item.precio).toLocaleString('es-CO', {minimumFractionDigits:2})}</p>
                </div>
                <div class="quantity-control" style="background: rgba(0,0,0,0.2); padding: 2px 5px;">
                    <button class="qty-btn minus" style="width:20px; height:20px; font-size:12px;" onclick="actualizarCantidadCarrito(${idx}, -1)">−</button>
                    <span class="qty-value" style="font-size:12px; margin: 0 5px;">${item.cantidad}</span>
                    <button class="qty-btn plus" style="width:20px; height:20px; font-size:12px;" onclick="actualizarCantidadCarrito(${idx}, 1)">+</button>
                </div>
            `;
            container.appendChild(div);
        });

        if(totalSpan) totalSpan.textContent = '$' + total.toLocaleString('es-CO', {minimumFractionDigits:2});
    }

    window.confirmarPedidoBD = function() {
        if(carrito.length === 0 || !sesionPedido) return;

        const metodoPago = document.getElementById('metodo_pago_pedido').value;
        let total = carrito.reduce((acc, item) => acc + (item.precio * item.cantidad), 0);

        const data = {
            mesa: sesionPedido.mesa,
            zona: sesionPedido.zona,
            tipo_pedido: sesionPedido.tipo,
            total: total,
            notas: 'Metodo Pago ID: ' + metodoPago,
            items: carrito
        };

        const btn = document.getElementById('btn-confirmar-pedido');
        const textOrig = btn.textContent;
        btn.textContent = 'Procesando...';
        btn.disabled = true;

        const token = document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        fetch('/pedidos', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(resData => {
            if(resData.success) {
                alert('Pedido confirmado exitosamente');
                carrito = [];
                renderCarrito();
            } else {
                alert('Error al confirmar pedido');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error de conexión');
        })
        .finally(() => {
            btn.textContent = textOrig;
            btn.disabled = false;
        });
    };

    // --- FILTROS PEDIDOS ---
    window.filtrarPlatosPedido = function() {
        const str = document.getElementById('buscar_plato').value.toLowerCase();
        const platos = document.querySelectorAll('.plato-para-pedir');
        const catActiva = document.querySelector('.menu-cat-btn[data-pedidos-cat].active')?.getAttribute('data-pedidos-cat') || 'all';

        platos.forEach(p => {
            const nombre = p.getAttribute('data-nombre');
            const cat = p.getAttribute('data-cat');
            const coincideNombre = nombre.includes(str);
            const coincideCat = (catActiva === 'all' || cat === catActiva);
            
            p.style.display = (coincideNombre && coincideCat) ? 'flex' : 'none';
        });
    };

    window.filtrarCatPedido = function(cat, btn) {
        document.querySelectorAll('.menu-cat-btn[data-pedidos-cat]').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        filtrarPlatosPedido();
    };

    window.irAPedidos = function(btn) {
        if(!sesionPedido) {
            alert('Primero selecciona Con Reserva o Sin Reserva en el encabezado superior para iniciar un pedido.');
            window.scrollTo({top:0, behavior:'smooth'});
            return;
        }
        
        showSection('pedidos');
        const id = btn.getAttribute('data-plato-id');
        const nombre = btn.getAttribute('data-plato-nombre');
        const precio = parseFloat(btn.getAttribute('data-plato-precio'));
        
        agregarAlCarrito(id, nombre, precio);
    };

    // INIT
    verificarEstadoSesionPedido();
    
    // Flatpickr Init
    if (typeof flatpickr !== 'undefined') {
        flatpickr("#fecha", {
            theme: "dark",
            locale: "es",
            minDate: "today",
            dateFormat: "Y-m-d"
        });
        flatpickr("#hora", {
            theme: "dark",
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true
        });
    }
});
