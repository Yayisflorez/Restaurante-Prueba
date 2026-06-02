document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.nav-item[href^="#"]');
    const sections = document.querySelectorAll('.content-section');

    // INICIALIZAR SELECTOR DE FECHA PERSONALIZADO
    initDateSelector();

    // INICIALIZAR SELECTOR DE HORA PERSONALIZADO
    initTimeSelector();

    function initDateSelector() {
        const dateGrid = document.getElementById('date-grid');
        const prevMonthBtn = document.getElementById('prev-month');
        const nextMonthBtn = document.getElementById('next-month');
        const currentMonthDisplay = document.getElementById('current-month-display');
        const selectedDateDisplay = document.getElementById('selected-date-display');
        const fechaInput = document.getElementById('fecha');

        let currentDate = new Date();
        let selectedDate = null;

        const months = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        const days = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

        function renderCalendar() {
            dateGrid.innerHTML = '';
            currentMonthDisplay.textContent = `${months[currentDate.getMonth()]} ${currentDate.getFullYear()}`;

            const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
            const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Días vacíos antes del primer día del mes
            for (let i = 0; i < firstDay.getDay(); i++) {
                const emptyDay = document.createElement('button');
                emptyDay.disabled = true;
                dateGrid.appendChild(emptyDay);
            }

            // Días del mes
            for (let day = 1; day <= lastDay.getDate(); day++) {
                const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
                const btn = document.createElement('button');
                btn.textContent = day;
                
                // Deshabilitar días pasados
                if (date < today) {
                    btn.disabled = true;
                }

                // Marcar hoy
                if (date.toDateString() === today.toDateString()) {
                    btn.classList.add('today');
                }

                // Marcar seleccionado
                if (selectedDate && date.toDateString() === selectedDate.toDateString()) {
                    btn.classList.add('selected');
                }

                btn.addEventListener('click', () => {
                    selectedDate = date;
                    fechaInput.value = date.toISOString().split('T')[0];
                    selectedDateDisplay.textContent = date.toLocaleDateString('es-ES', { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    });
                    renderCalendar();
                    
                    // Recargar mesas si hay zona seleccionada
                    const zonaSeleccionada = document.querySelector('input[name="zona"]:checked');
                    if (zonaSeleccionada) {
                        cargarMesasPorZona(zonaSeleccionada.value);
                    }
                });

                dateGrid.appendChild(btn);
            }
        }

        prevMonthBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });

        nextMonthBtn.addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });

        renderCalendar();
    }

    function initTimeSelector() {
        const hourDisplay = document.getElementById('hour-display');
        const minuteDisplay = document.getElementById('minute-display');
        const selectedTimeDisplay = document.getElementById('selected-time-display');
        const horaInput = document.getElementById('hora');
        const periodBtns = document.querySelectorAll('.period-btn');
        const timeAdjustBtns = document.querySelectorAll('.time-adjust-btn');

        let hour = 12;
        let minute = 0;
        let period = 'PM';

        function updateTimeDisplay() {
            hourDisplay.textContent = String(hour).padStart(2, '0');
            minuteDisplay.textContent = String(minute).padStart(2, '0');
            
            // Convertir a formato 24h para el input
            let hour24 = hour;
            if (period === 'AM' && hour24 === 12) {
                hour24 = 0;
            } else if (period === 'PM' && hour24 !== 12) {
                hour24 += 12;
            }
            
            const time24 = `${String(hour24).padStart(2, '0')}:${String(minute).padStart(2, '0')}`;
            horaInput.value = time24;
            selectedTimeDisplay.textContent = time24;
        }

        periodBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                period = btn.dataset.period;
                periodBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                updateTimeDisplay();
                
                // Recargar mesas si hay zona seleccionada
                const zonaSeleccionada = document.querySelector('input[name="zona"]:checked');
                if (zonaSeleccionada) {
                    cargarMesasPorZona(zonaSeleccionada.value);
                }
            });
        });

        timeAdjustBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const unit = btn.dataset.unit;
                
                if (unit === 'hour') {
                    if (btn.classList.contains('time-up')) {
                        hour = hour >= 12 ? 1 : hour + 1;
                    } else {
                        hour = hour <= 1 ? 12 : hour - 1;
                    }
                } else if (unit === 'minute') {
                    if (btn.classList.contains('time-up')) {
                        minute = (minute + 1) % 60;
                    } else {
                        minute = (minute - 1 + 60) % 60;
                    }
                }
                
                updateTimeDisplay();
                
                // Recargar mesas si hay zona seleccionada
                const zonaSeleccionada = document.querySelector('input[name="zona"]:checked');
                if (zonaSeleccionada) {
                    cargarMesasPorZona(zonaSeleccionada.value);
                }
            });
        });

        updateTimeDisplay();
    }

    function formatFecha(dateValue) {
        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(dateValue).toLocaleDateString('es-ES', options);
    }

    function range(start, end) {
        return Array.from({ length: end - start + 1 }, (_, i) => start + i);
    }

    // FUNCIONES PARA RESERVAS
    window.cambiarPersonas = function(delta) {
        const personasNum = document.getElementById('personas-num');
        const personasInput = document.getElementById('personas_input');
        const mesasInput = document.getElementById('mesas');
        
        let currentValue = parseInt(personasNum.textContent);
        currentValue += delta;
        
        // Calcular límite máximo basado en mesas seleccionadas
        let maxPersonas = 20; // Valor por defecto
        if (mesasInput && mesasInput.value) {
            try {
                const mesasSeleccionadas = JSON.parse(mesasInput.value);
                const numMesas = mesasSeleccionadas.length;
                maxPersonas = numMesas * 4; // 4 personas por mesa
            } catch (e) {
                maxPersonas = 20;
            }
        }
        
        if (currentValue < 1) currentValue = 1;
        if (currentValue > maxPersonas) currentValue = maxPersonas;
        
        personasNum.textContent = currentValue;
        personasInput.value = currentValue;
    };

    window.seleccionarMesa = function(mesa, button) {
        const mesaInput = document.getElementById('mesas');
        const mesaBtns = document.querySelectorAll('.mesa-btn');
        
        // Toggle selección múltiple
        button.classList.toggle('selected');
        
        // Obtener todas las mesas seleccionadas
        const mesasSeleccionadas = [];
        mesaBtns.forEach(btn => {
            if (btn.classList.contains('selected')) {
                mesasSeleccionadas.push(btn.textContent);
            }
        });
        
        // Actualizar input hidden con array de mesas
        mesaInput.value = JSON.stringify(mesasSeleccionadas);
        
        // Actualizar información de capacidad
        actualizarInfoMesas(mesasSeleccionadas);
    };

    function actualizarInfoMesas(mesasSeleccionadas) {
        const mesasInfo = document.getElementById('mesas-seleccionadas-info');
        const capacidadInfo = document.getElementById('capacidad-total-info');
        const personasInput = document.getElementById('personas_input');
        const personasNum = document.getElementById('personas-num');
        
        const numMesas = mesasSeleccionadas.length;
        const capacidadTotal = numMesas * 4; // 4 personas por mesa
        
        mesasInfo.textContent = `${numMesas} mesa(s) seleccionada(s)`;
        capacidadInfo.textContent = `Capacidad total: ${capacidadTotal} personas`;
        
        // Actualizar input de personas
        personasInput.value = capacidadTotal;
        personasNum.textContent = capacidadTotal;
    }

    window.cargarMesasPorZona = function(zona) {
        const fechaInput = document.getElementById('fecha');
        const horaInput = document.getElementById('hora');
        const mesaGrid = document.getElementById('mesa-selector-grid');
        
        // Calcular rango de mesas por zona (10 mesas por zona)
        const zonasMesas = {
            'interior': range(1, 10),
            'terraza': range(11, 20),
            'privado': range(21, 30)
        };
        
        const mesasZona = zonasMesas[zona] || range(1, 10);
        
        // Limpiar grid actual
        mesaGrid.innerHTML = '';
        
        // Si no hay fecha o hora seleccionada, mostrar mesas sin disponibilidad
        if (!fechaInput.value || !horaInput.value) {
            mesasZona.forEach(mesa => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'mesa-btn';
                btn.textContent = mesa;
                btn.onclick = function() { seleccionarMesa(mesa, this); };
                mesaGrid.appendChild(btn);
            });
            return;
        }
        
        // Verificar disponibilidad en tiempo real
        const token = document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        fetch('/reservas/disponibilidad', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                fecha: fechaInput.value,
                hora: horaInput.value,
                zona: zona
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Crear mapa de disponibilidad
                const disponibilidadMap = {};
                data.disponibilidad.forEach(item => {
                    disponibilidadMap[item.mesa] = item.disponible;
                });
                
                // Renderizar mesas con estado de disponibilidad
                mesasZona.forEach(mesa => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'mesa-btn';
                    btn.textContent = mesa;
                    
                    // Aplicar clase según disponibilidad
                    if (disponibilidadMap[mesa] === false) {
                        btn.classList.add('mesa-no-disponible');
                    } else {
                        btn.classList.add('mesa-disponible');
                    }
                    
                    btn.onclick = function() { seleccionarMesa(mesa, this); };
                    mesaGrid.appendChild(btn);
                });
            }
        })
        .catch(err => {
            console.error('Error al verificar disponibilidad:', err);
            // En caso de error, mostrar mesas sin estado de disponibilidad
            mesasZona.forEach(mesa => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'mesa-btn';
                btn.textContent = mesa;
                btn.onclick = function() { seleccionarMesa(mesa, this); };
                mesaGrid.appendChild(btn);
            });
        });
    };

    window.confirmarReserva = function() {
        // Verificar si el usuario está autenticado
        const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
        if (!userId) {
            Swal.fire({ 
                icon: 'error', 
                title: 'No autenticado', 
                text: 'Debes iniciar sesión para hacer una reserva.', 
                background: '#1a1a1a', 
                color: '#fff' 
            });
            return;
        }

        // Obtener todos los datos directamente del DOM
        const fechaInput = document.getElementById('fecha');
        const horaInput = document.getElementById('hora');
        const personasInput = document.getElementById('personas_input');
        const mesasInput = document.getElementById('mesas');
        const zonaInput = document.querySelector('input[name="zona"]:checked');
        const notasInput = document.getElementById('notas');

        const data = {
            fecha: fechaInput ? fechaInput.value : null,
            hora: horaInput ? horaInput.value : null,
            personas: personasInput ? personasInput.value : null,
            mesas: mesasInput ? mesasInput.value : null,
            zona: zonaInput ? zonaInput.value : null,
            notas: notasInput ? notasInput.value : ''
        };

        console.log('Datos capturados:', data);

        // Validar que todos los campos requeridos estén presentes
        if (!data.fecha || !data.hora || !data.personas || !data.zona) {
            console.error('Campos incompletos:', data);
            Swal.fire({ 
                icon: 'warning', 
                title: 'Campos incompletos', 
                text: 'Por favor complete todos los campos requeridos: fecha, hora, personas y zona.', 
                background: '#1a1a1a', 
                color: '#fff' 
            });
            return;
        }

        // Validar mesas
        if (!data.mesas) {
            console.error('Mesas no seleccionadas');
            Swal.fire({ 
                icon: 'warning', 
                title: 'Mesas no seleccionadas', 
                text: 'Por favor seleccione al menos una mesa.', 
                background: '#1a1a1a', 
                color: '#fff' 
            });
            return;
        }

        // Parsear mesas desde JSON string a array
        try {
            data.mesas = JSON.parse(data.mesas);
            if (!Array.isArray(data.mesas) || data.mesas.length === 0) {
                console.error('Array de mesas vacío');
                Swal.fire({ 
                    icon: 'warning', 
                    title: 'Mesas no seleccionadas', 
                    text: 'Por favor seleccione al menos una mesa.', 
                    background: '#1a1a1a', 
                    color: '#fff' 
                });
                return;
            }
        } catch (e) {
            console.error('Error al parsear mesas:', e, 'Valor:', data.mesas);
            Swal.fire({ 
                icon: 'error', 
                title: 'Error en mesas', 
                text: 'Error al procesar las mesas seleccionadas. Por favor seleccione las mesas nuevamente.', 
                background: '#1a1a1a', 
                color: '#fff' 
            });
            return;
        }

        // Validar horario del restaurante (8:00 AM - 10:00 PM)
        const horaReserva = parseInt(data.hora.split(':')[0]);
        const minutosReserva = parseInt(data.hora.split(':')[1]);
        const horaTotalReserva = horaReserva + (minutosReserva / 60);
        
        if (horaTotalReserva < 8 || horaTotalReserva >= 22) {
            Swal.fire({ 
                icon: 'error', 
                title: 'Horario no disponible', 
                text: 'El restaurante está cerrado a esta hora, por favor escoger una hora entre las 8:00 AM a 10:00 PM.', 
                background: '#1a1a1a', 
                color: '#fff' 
            });
            return;
        }

        // Validar reserva mínima (5 minutos después de hora actual)
        const ahora = new Date();
        const fechaReserva = new Date(data.fecha + 'T' + data.hora);
        const diferenciaMinutos = (fechaReserva - ahora) / (1000 * 60);
        
        if (diferenciaMinutos < 5) {
            Swal.fire({ 
                icon: 'error', 
                title: 'Reserva muy cercana', 
                text: 'La reserva debe ser al menos 5 minutos después de la hora actual.', 
                background: '#1a1a1a', 
                color: '#fff' 
            });
            return;
        }

        const token = document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        console.log('Enviando datos al servidor:', data);
        console.log('CSRF Token:', token ? 'Presente' : 'No encontrado');

        fetch('/reservas', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify(data)
        })
        .then(res => {
            console.log('Respuesta del servidor:', res.status, res.statusText);
            if (!res.ok) {
                return res.text().then(text => {
                    console.error('Respuesta no OK:', text);
                    throw new Error(`HTTP ${res.status}: ${text}`);
                });
            }
            return res.json();
        })
        .then(data => {
            console.log('Datos recibidos:', data);
            if(data.success) {
                document.getElementById('conf-fecha').textContent = data.reserva.fecha;
                document.getElementById('conf-hora').textContent = data.reserva.hora;
                document.getElementById('conf-personas').textContent = data.reserva.personas;
                // Mostrar mesas separadas por coma
                const mesasArray = data.reserva.mesa.split(',');
                document.getElementById('conf-mesa').textContent = mesasArray.join(', ');
                document.getElementById('conf-zona').textContent = data.reserva.zona;
                document.getElementById('conf-codigo').textContent = data.reserva.codigo_referencia;
                openModal('modal-reserva-confirmacion');
                const form = document.getElementById('reservaForm');
                form.reset();
                // Resetear calendario y selector de hora
                if (document.getElementById('selected-date-display')) {
                    document.getElementById('selected-date-display').textContent = 'Selecciona una fecha';
                }
                if (document.getElementById('selected-time-display')) {
                    document.getElementById('selected-time-display').textContent = 'Selecciona una hora';
                }
                if (document.getElementById('mesas')) {
                    document.getElementById('mesas').value = '';
                }
                document.querySelectorAll('.mesa-btn').forEach(btn => {
                    btn.classList.remove('selected', 'mesa-disponible', 'mesa-no-disponible');
                });
                document.getElementById('mesas-seleccionadas-info').textContent = '0 mesas seleccionadas';
                document.getElementById('capacidad-total-info').textContent = 'Capacidad total: 0 personas';
                // Recargar calendario
                initDateSelector();
            } else {
                console.error('Error del servidor:', data);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al crear reserva: ' + (data.message || ''), background: '#1a1a1a', color: '#fff' });
            }
        })
        .catch(err => {
            console.error('Error de conexión:', err);
            const errorMessage = err.message || 'Error desconocido';
            Swal.fire({ 
                icon: 'error', 
                title: 'Error de conexión', 
                html: `Error al conectar con el servidor:<br><strong>${errorMessage}</strong><br><br>Verifica que:<br>- Estés autenticado<br>- El servidor esté corriendo<br>- Tienes conexión a internet`, 
                background: '#1a1a1a', 
                color: '#fff' 
            });
        });
    };

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

        // Visibilidad botones header
        const orderBtns = document.getElementById('header-order-buttons');
        const sesionActiva = sessionStorage.getItem('sesionPedido');
        if (orderBtns && !sesionActiva) {
            if (sectionId === 'pedidos') {
                orderBtns.style.display = 'none';
                openModal('modal-inicio-pedido');
            } else {
                orderBtns.style.display = 'flex';
            }
        }
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
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al crear reserva: ' + (data.message || ''), background: '#1a1a1a', color: '#fff' });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión al guardar reserva.', background: '#1a1a1a', color: '#fff' });
        });
    };

    // --- LÓGICA PEDIDOS ---
    let sesionPedido = JSON.parse(sessionStorage.getItem('sesionPedido')) || null;
    let carrito = [];

    window.mostrarFormularioPedido = function(tipo) {
        document.getElementById('seleccion-tipo-pedido').style.display = 'none';
        document.getElementById('modal-form-con-reserva').style.display = tipo === 'con_reserva' ? 'block' : 'none';
        document.getElementById('modal-form-sin-reserva').style.display = tipo === 'sin_reserva' ? 'block' : 'none';
    };

    window.volverSeleccionPedido = function() {
        document.getElementById('seleccion-tipo-pedido').style.display = 'flex';
        document.getElementById('modal-form-con-reserva').style.display = 'none';
        document.getElementById('modal-form-sin-reserva').style.display = 'none';
    };

    function verificarEstadoSesionPedido() {
        const btnConReserva = document.getElementById('btn-con-reserva');
        const btnSinReserva = document.getElementById('btn-sin-reserva');
        const btnFinReserva = document.getElementById('btn-fin-reserva');
        const btnFinServicio = document.getElementById('btn-fin-servicio');
        const vistaNormal = document.getElementById('vista-pedidos-normal');
        const infoMesaHeader = document.getElementById('header-info-mesa');
        const orderBtns = document.getElementById('header-order-buttons');

        if (sesionPedido) {
            if(orderBtns) orderBtns.style.display = 'flex';
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
            }
            if(infoMesaHeader) {
                infoMesaHeader.style.display = 'inline-flex';
                infoMesaHeader.textContent = `Mesa ${sesionPedido.mesa} - ${sesionPedido.zona || 'Local'}`;
            }
            
            closeModal('modal-inicio-pedido');
        } else {
            const currentActive = document.querySelector('.nav-item.active')?.getAttribute('href');
            if (orderBtns) {
                if (currentActive === '#pedidos') orderBtns.style.display = 'none';
                else orderBtns.style.display = 'flex';
            }

            if(btnConReserva) btnConReserva.style.display = 'inline-block';
            if(btnSinReserva) btnSinReserva.style.display = 'inline-block';
            if(btnFinReserva) btnFinReserva.style.display = 'none';
            if(btnFinServicio) btnFinServicio.style.display = 'none';
            
            if(vistaNormal) {
                vistaNormal.style.opacity = '0.4';
                vistaNormal.style.pointerEvents = 'none';
            }
            if(infoMesaHeader) infoMesaHeader.style.display = 'none';
        }
    }

    window.iniciarPedido = function(tipo) {
        showSection('pedidos');
        volverSeleccionPedido();
        if(tipo) {
            mostrarFormularioPedido(tipo);
        }
        
        document.getElementById('auth-error').style.display = 'none';
        document.getElementById('temp-error').style.display = 'none';
    };

    window.finalizarSesionPedido = function() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'El carrito se vaciará y finalizarás el servicio actual.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, finalizar',
            cancelButtonText: 'Cancelar',
            background: '#1a1a1a',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                sessionStorage.removeItem('sesionPedido');
                sesionPedido = null;
                carrito = [];
                renderCarrito();
                verificarEstadoSesionPedido();
            }
        });
    };

    window.verificarReserva = function() {
        const mesa = document.getElementById('auth_mesa').value;
        const codigo = document.getElementById('auth_codigo').value;
        const cantidadMesas = document.getElementById('auth_cantidad_mesas').value;
        const errTag = document.getElementById('auth-error');
        
        if(!mesa || !codigo || !cantidadMesas) {
            errTag.textContent = 'Llene todos los campos.';
            errTag.style.display = 'block';
            return;
        }

        // Validar que la cantidad de mesas coincida con las mesas ingresadas
        const mesasArray = mesa.split(',').map(m => m.trim());
        if (mesasArray.length !== parseInt(cantidadMesas)) {
            errTag.textContent = `La cantidad de mesas (${cantidadMesas}) no coincide con las mesas ingresadas (${mesasArray.length}).`;
            errTag.style.display = 'block';
            return;
        }

        const token = document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        // Enviar la primera mesa para verificación (el backend verificará todas)
        fetch('/pedidos/verificar', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ 
                mesa: mesasArray[0], 
                codigo_referencia: codigo,
                mesas: mesasArray 
            })
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
        const mesas = document.getElementById('temp_mesas').value;
        const zonaInput = document.querySelector('input[name="temp_zona"]:checked');
        const errTag = document.getElementById('temp-error');
        
        if(!mesas || !zonaInput) {
            errTag.textContent = 'Seleccione una zona y al menos una mesa.';
            errTag.style.display = 'block';
            return;
        }

        const zona = zonaInput.value;
        
        // Parsear mesas desde JSON
        let mesasArray;
        try {
            mesasArray = JSON.parse(mesas);
        } catch (e) {
            errTag.textContent = 'Error al procesar las mesas seleccionadas.';
            errTag.style.display = 'block';
            return;
        }

        if (mesasArray.length === 0) {
            errTag.textContent = 'Seleccione al menos una mesa.';
            errTag.style.display = 'block';
            return;
        }

        sesionPedido = {
            tipo: 'sin_reserva',
            mesa: mesasArray.join(','),
            zona: zona
        };
        sessionStorage.setItem('sesionPedido', JSON.stringify(sesionPedido));
        verificarEstadoSesionPedido();
    };

    window.cargarMesasSinReserva = function(zona) {
        const mesaGrid = document.getElementById('temp-mesa-selector-grid');
        
        // Calcular rango de mesas por zona (10 mesas por zona)
        const zonasMesas = {
            'interior': range(1, 10),
            'terraza': range(11, 20),
            'privado': range(21, 30)
        };
        
        const mesasZona = zonasMesas[zona] || range(1, 10);
        
        // Limpiar grid actual
        mesaGrid.innerHTML = '';
        
        // Verificar disponibilidad en tiempo real (considerando pedidos sin reserva actuales)
        const token = document.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        fetch('/pedidos/disponibilidad-tiempo-real', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({
                zona: zona
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Crear mapa de disponibilidad
                const disponibilidadMap = {};
                data.disponibilidad.forEach(item => {
                    disponibilidadMap[item.mesa] = item.disponible;
                });
                
                // Renderizar mesas con estado de disponibilidad
                mesasZona.forEach(mesa => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'mesa-btn';
                    btn.textContent = mesa;
                    
                    // Aplicar clase según disponibilidad
                    if (disponibilidadMap[mesa] === false) {
                        btn.classList.add('mesa-no-disponible');
                    } else {
                        btn.classList.add('mesa-disponible');
                    }
                    
                    btn.onclick = function() { seleccionarMesaTemp(mesa, this); };
                    mesaGrid.appendChild(btn);
                });
            }
        })
        .catch(err => {
            console.error('Error al verificar disponibilidad:', err);
            // En caso de error, mostrar mesas sin estado de disponibilidad
            mesasZona.forEach(mesa => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'mesa-btn';
                btn.textContent = mesa;
                btn.onclick = function() { seleccionarMesaTemp(mesa, this); };
                mesaGrid.appendChild(btn);
            });
        });
    };

    window.seleccionarMesaTemp = function(mesa, button) {
        const mesaInput = document.getElementById('temp_mesas');
        const mesaBtns = document.querySelectorAll('#temp-mesa-selector-grid .mesa-btn');
        
        // Toggle selección múltiple
        button.classList.toggle('selected');
        
        // Obtener todas las mesas seleccionadas
        const mesasSeleccionadas = [];
        mesaBtns.forEach(btn => {
            if (btn.classList.contains('selected')) {
                mesasSeleccionadas.push(btn.textContent);
            }
        });
        
        // Actualizar input hidden con array de mesas
        mesaInput.value = JSON.stringify(mesasSeleccionadas);
        
        // Actualizar información de capacidad
        actualizarInfoMesasTemp(mesasSeleccionadas);
    };

    function actualizarInfoMesasTemp(mesasSeleccionadas) {
        const mesasInfo = document.getElementById('temp-mesas-seleccionadas-info');
        const capacidadInfo = document.getElementById('temp-capacidad-total-info');
        
        const numMesas = mesasSeleccionadas.length;
        const capacidadTotal = numMesas * 4; // 4 personas por mesa
        
        mesasInfo.textContent = `${numMesas} mesa(s) seleccionada(s)`;
        capacidadInfo.textContent = `Capacidad total: ${capacidadTotal} personas`;
    }

    window.actualizarInputMesas = function() {
        const cantidadMesas = document.getElementById('auth_cantidad_mesas').value;
        const mesaInput = document.getElementById('auth_mesa');
        const labelMesa = document.getElementById('label_mesa');
        const mesaHint = document.getElementById('mesa_hint');
        
        if (parseInt(cantidadMesas) > 2) {
            // Cambiar a input de texto para permitir múltiples mesas separadas por comas
            mesaInput.type = 'text';
            mesaInput.removeAttribute('min');
            mesaInput.removeAttribute('max');
            labelMesa.textContent = 'Mesas (separadas por coma)';
            mesaHint.style.display = 'block';
        } else {
            // Mantener como input numérico
            mesaInput.type = 'number';
            mesaInput.setAttribute('min', '1');
            mesaInput.setAttribute('max', '20');
            labelMesa.textContent = 'Mesa (1-20)';
            mesaHint.style.display = 'none';
        }
    };

    // --- CARRITO ---
    window.agregarAlCarrito = function(id, nombre, precio) {
        if(!sesionPedido) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor autentica tu pedido primero (Botón Con/Sin Reserva).',
                background: '#1a1a1a',
                color: '#fff'
            });
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
                Swal.fire({ icon: 'success', title: '¡Éxito!', text: 'Pedido confirmado exitosamente.', background: '#1a1a1a', color: '#fff' });
                carrito = [];
                renderCarrito();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Error al confirmar pedido.', background: '#1a1a1a', color: '#fff' });
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión.', background: '#1a1a1a', color: '#fff' });
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
        const catSelect = document.getElementById('categoria_plato');
        const catActiva = catSelect ? catSelect.value : 'all';

        platos.forEach(p => {
            const nombre = p.getAttribute('data-nombre');
            const cat = p.getAttribute('data-cat');
            const coincideNombre = nombre.includes(str);
            const coincideCat = (catActiva === 'all' || cat === catActiva);
            
            p.style.display = (coincideNombre && coincideCat) ? 'flex' : 'none';
        });
    };

    window.filtrarCatPedidoDropdown = function(select) {
        filtrarPlatosPedido();
    };

    window.irAPedidos = function(btn) {
        if(!sesionPedido) {
            Swal.fire({
                icon: 'info',
                title: 'Autenticación Requerida',
                text: 'Primero selecciona Con Reserva o Sin Reserva en la sección de Hacer Pedidos.',
                background: '#1a1a1a',
                color: '#fff'
            });
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
});
