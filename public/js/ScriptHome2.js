document.addEventListener("DOMContentLoaded", function () {
    const navItems = document.querySelectorAll('.nav-item[href^="#"]');
    const sections = document.querySelectorAll(".content-section");

    // INICIALIZAR SELECTOR DE FECHA PERSONALIZADO
    initDateSelector();

    // INICIALIZAR SELECTOR DE HORA PERSONALIZADO
    initTimeSelector();

    function initDateSelector() {
        const dateGrid = document.getElementById("date-grid");
        const prevMonthBtn = document.getElementById("prev-month");
        const nextMonthBtn = document.getElementById("next-month");
        const currentMonthDisplay = document.getElementById(
            "current-month-display",
        );
        const selectedDateDisplay = document.getElementById(
            "selected-date-display",
        );
        const fechaInput = document.getElementById("fecha");

        let currentDate = new Date();
        let selectedDate = null;

        const months = [
            "Enero",
            "Febrero",
            "Marzo",
            "Abril",
            "Mayo",
            "Junio",
            "Julio",
            "Agosto",
            "Septiembre",
            "Octubre",
            "Noviembre",
            "Diciembre",
        ];
        const days = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];

        function renderCalendar() {
            dateGrid.innerHTML = "";
            currentMonthDisplay.textContent = `${months[currentDate.getMonth()]} ${currentDate.getFullYear()}`;

            const firstDay = new Date(
                currentDate.getFullYear(),
                currentDate.getMonth(),
                1,
            );
            const lastDay = new Date(
                currentDate.getFullYear(),
                currentDate.getMonth() + 1,
                0,
            );
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            // Días vacíos antes del primer día del mes
            for (let i = 0; i < firstDay.getDay(); i++) {
                const emptyDay = document.createElement("button");
                emptyDay.disabled = true;
                dateGrid.appendChild(emptyDay);
            }

            // Días del mes
            for (let day = 1; day <= lastDay.getDate(); day++) {
                const date = new Date(
                    currentDate.getFullYear(),
                    currentDate.getMonth(),
                    day,
                );
                const btn = document.createElement("button");
                btn.textContent = day;

                // Deshabilitar días pasados
                if (date < today) {
                    btn.disabled = true;
                }

                // Marcar hoy
                if (date.toDateString() === today.toDateString()) {
                    btn.classList.add("today");
                }

                // Marcar seleccionado
                if (
                    selectedDate &&
                    date.toDateString() === selectedDate.toDateString()
                ) {
                    btn.classList.add("selected");
                }

                btn.addEventListener("click", () => {
                    selectedDate = date;
                    fechaInput.value = date.toISOString().split("T")[0];
                    selectedDateDisplay.textContent = date.toLocaleDateString(
                        "es-ES",
                        {
                            weekday: "long",
                            year: "numeric",
                            month: "long",
                            day: "numeric",
                        },
                    );
                    renderCalendar();

                    // Recargar mesas si hay zona seleccionada
                    const zonaSeleccionada = document.querySelector(
                        'input[name="zona"]:checked',
                    );
                    if (zonaSeleccionada) {
                        cargarMesasPorZona(zonaSeleccionada.value);
                    }
                });

                dateGrid.appendChild(btn);
            }
        }

        prevMonthBtn.addEventListener("click", () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            renderCalendar();
        });

        nextMonthBtn.addEventListener("click", () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            renderCalendar();
        });

        renderCalendar();
    }

    function initTimeSelector() {
        const hourDisplay = document.getElementById("hour-display");
        const minuteDisplay = document.getElementById("minute-display");
        const selectedTimeDisplay = document.getElementById(
            "selected-time-display",
        );
        const horaInput = document.getElementById("hora");
        const periodBtns = document.querySelectorAll(".period-btn");
        const timeAdjustBtns = document.querySelectorAll(".time-adjust-btn");

        let hour = 12;
        let minute = 0;
        let period = "PM";

        function updateTimeDisplay() {
            hourDisplay.value = String(hour).padStart(2, "0");
            minuteDisplay.value = String(minute).padStart(2, "0");

            // Convertir a formato 24h para el input
            let hour24 = hour;
            if (period === "AM" && hour24 === 12) {
                hour24 = 0;
            } else if (period === "PM" && hour24 !== 12) {
                hour24 += 12;
            }

            const time24 = `${String(hour24).padStart(2, "0")}:${String(minute).padStart(2, "0")}`;
            horaInput.value = time24;
            selectedTimeDisplay.textContent = time24;
        }

        periodBtns.forEach((btn) => {
            btn.addEventListener("click", () => {
                period = btn.dataset.period;
                periodBtns.forEach((b) => b.classList.remove("active"));
                btn.classList.add("active");
                updateTimeDisplay();

                // Recargar mesas si hay zona seleccionada
                const zonaSeleccionada = document.querySelector(
                    'input[name="zona"]:checked',
                );
                if (zonaSeleccionada) {
                    cargarMesasPorZona(zonaSeleccionada.value);
                }
            });
        });

        // Event listeners for manual input typing
        [hourDisplay, minuteDisplay].forEach((input) => {
            input.addEventListener("change", () => {
                let h = parseInt(hourDisplay.value) || 12;
                let m = parseInt(minuteDisplay.value) || 0;

                if (h < 1) h = 12;
                if (h > 12) h = 1;
                if (m < 0) m = 59;
                if (m > 59) m = 0;

                hour = h;
                minute = m;
                updateTimeDisplay();

                const zonaSeleccionada = document.querySelector(
                    'input[name="zona"]:checked',
                );
                if (zonaSeleccionada) {
                    cargarMesasPorZona(zonaSeleccionada.value);
                }
            });
        });

        timeAdjustBtns.forEach((btn) => {
            btn.addEventListener("click", () => {
                const unit = btn.dataset.unit;

                if (unit === "hour") {
                    if (btn.classList.contains("time-up")) {
                        hour = hour >= 12 ? 1 : hour + 1;
                    } else {
                        hour = hour <= 1 ? 12 : hour - 1;
                    }
                } else if (unit === "minute") {
                    if (btn.classList.contains("time-up")) {
                        minute = (minute + 1) % 60;
                    } else {
                        minute = (minute - 1 + 60) % 60;
                    }
                }

                updateTimeDisplay();

                // Recargar mesas si hay zona seleccionada
                const zonaSeleccionada = document.querySelector(
                    'input[name="zona"]:checked',
                );
                if (zonaSeleccionada) {
                    cargarMesasPorZona(zonaSeleccionada.value);
                }
            });
        });

        updateTimeDisplay();
    }

    function formatFecha(dateValue) {
        const options = {
            weekday: "long",
            year: "numeric",
            month: "long",
            day: "numeric",
        };
        return new Date(dateValue).toLocaleDateString("es-ES", options);
    }

    function range(start, end) {
        return Array.from({ length: end - start + 1 }, (_, i) => start + i);
    }

    // FUNCIONES PARA RESERVAS
    window.cambiarPersonas = function (delta) {
        const personasNum = document.getElementById("personas-num");
        const personasInput = document.getElementById("personas_input");
        const mesasInput = document.getElementById("mesas");

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

    window.seleccionarMesa = function (mesa, button) {
        // Verificar si la mesa está ocupada (no disponible)
        if (button.classList.contains("mesa-no-disponible")) {
            Swal.fire({
                icon: "warning",
                title: "Mesa ocupada",
                text: "Esta mesa ya está reservada u ocupada. Por favor selecciona otra mesa.",
                background: "#1a1a1a",
                color: "#fff",
            });
            return;
        }

        const mesaInput = document.getElementById("mesas");
        const mesaBtns = document.querySelectorAll(".mesa-btn");

        // Toggle selección múltiple
        button.classList.toggle("selected");

        // Obtener todas las mesas seleccionadas
        const mesasSeleccionadas = [];
        mesaBtns.forEach((btn) => {
            if (btn.classList.contains("selected")) {
                mesasSeleccionadas.push(btn.textContent);
            }
        });

        // Actualizar input hidden con array de mesas
        mesaInput.value = JSON.stringify(mesasSeleccionadas);

        // Actualizar información de capacidad
        actualizarInfoMesas(mesasSeleccionadas);
    };

    function actualizarInfoMesas(mesasSeleccionadas) {
        const mesasInfo = document.getElementById("mesas-seleccionadas-info");
        const capacidadInfo = document.getElementById("capacidad-total-info");
        const personasInput = document.getElementById("personas_input");
        const personasNum = document.getElementById("personas-num");
        const personasWrapper = document.getElementById(
            "personas-control-wrapper",
        );
        const personasHint = document.getElementById("personas-hint");

        const numMesas = mesasSeleccionadas.length;
        const capacidadTotal = numMesas * 4; // 4 personas por mesa

        mesasInfo.textContent = `${numMesas} mesa(s) seleccionada(s)`;
        capacidadInfo.textContent = `Capacidad total: ${capacidadTotal} personas`;

        // Habilitar/deshabilitar control de personas según si hay mesas
        if (numMesas > 0) {
            if (personasWrapper) {
                personasWrapper.style.opacity = "1";
                personasWrapper.style.pointerEvents = "auto";
            }
            if (personasHint) personasHint.style.display = "none";
        } else {
            if (personasWrapper) {
                personasWrapper.style.opacity = "0.5";
                personasWrapper.style.pointerEvents = "none";
            }
            if (personasHint) personasHint.style.display = "block";
        }

        // Actualizar input de personas
        personasInput.value = capacidadTotal;
        personasNum.textContent = capacidadTotal;
    }

    window.cargarMesasPorZona = function (zona) {
        const fechaInput = document.getElementById("fecha");
        const horaInput = document.getElementById("hora");
        const mesaGrid = document.getElementById("mesa-selector-grid");

        // Calcular rango de mesas por zona (10 mesas por zona)
        const zonasMesas = {
            interior: range(1, 10),
            terraza: range(11, 20),
            privado: range(21, 30),
        };

        const mesasZona = zonasMesas[zona] || range(1, 10);

        // Limpiar grid actual
        mesaGrid.innerHTML = "";

        // Si no hay fecha o hora seleccionada, mostrar mesas sin disponibilidad
        if (!fechaInput.value || !horaInput.value) {
            mesasZona.forEach((mesa) => {
                const btn = document.createElement("button");
                btn.type = "button";
                btn.className = "mesa-btn";
                btn.textContent = mesa;
                btn.onclick = function () {
                    seleccionarMesa(mesa, this);
                };
                mesaGrid.appendChild(btn);
            });
            return;
        }

        // Verificar disponibilidad en tiempo real
        const token =
            document.querySelector('input[name="_token"]')?.value ||
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

        fetch("/reservas/disponibilidad", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": token,
            },
            body: JSON.stringify({
                fecha: fechaInput.value,
                hora: horaInput.value,
                zona: zona,
            }),
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.success) {
                    // Crear mapa de disponibilidad
                    const disponibilidadMap = {};
                    data.disponibilidad.forEach((item) => {
                        disponibilidadMap[item.mesa] = item.disponible;
                    });

                    // Renderizar mesas con estado de disponibilidad
                    mesasZona.forEach((mesa) => {
                        const btn = document.createElement("button");
                        btn.type = "button";
                        btn.className = "mesa-btn";
                        btn.textContent = mesa;

                        // Aplicar clase según disponibilidad
                        if (disponibilidadMap[mesa] === false) {
                            btn.classList.add("mesa-no-disponible");
                        } else {
                            btn.classList.add("mesa-disponible");
                        }

                        btn.onclick = function () {
                            seleccionarMesa(mesa, this);
                        };
                        mesaGrid.appendChild(btn);
                    });
                }
            })
            .catch((err) => {
                console.error("Error al verificar disponibilidad:", err);
                // En caso de error, mostrar mesas sin estado de disponibilidad
                mesasZona.forEach((mesa) => {
                    const btn = document.createElement("button");
                    btn.type = "button";
                    btn.className = "mesa-btn";
                    btn.textContent = mesa;
                    btn.onclick = function () {
                        seleccionarMesa(mesa, this);
                    };
                    mesaGrid.appendChild(btn);
                });
            });
    };

    window.confirmarReserva = function () {
        // Verificar si el usuario está autenticado
        const userId = document
            .querySelector('meta[name="user-id"]')
            ?.getAttribute("content");
        if (!userId) {
            Swal.fire({
                icon: "error",
                title: "No autenticado",
                text: "Debes iniciar sesión para hacer una reserva.",
                background: "#1a1a1a",
                color: "#fff",
            });
            return;
        }

        // Obtener todos los datos directamente del DOM
        const fechaInput = document.getElementById("fecha");
        const horaInput = document.getElementById("hora");
        const personasInput = document.getElementById("personas_input");
        const mesasInput = document.getElementById("mesas");
        const zonaInput = document.querySelector('input[name="zona"]:checked');
        const notasInput = document.getElementById("notas");

        const data = {
            fecha: fechaInput ? fechaInput.value : null,
            hora: horaInput ? horaInput.value : null,
            personas: personasInput ? personasInput.value : null,
            mesas: mesasInput ? mesasInput.value : null,
            zona: zonaInput ? zonaInput.value : null,
            notas: notasInput ? notasInput.value : "",
        };

        console.log("Datos capturados:", data);

        // Validar que todos los campos requeridos estén presentes
        if (!data.fecha || !data.hora || !data.personas || !data.zona) {
            console.error("Campos incompletos:", data);
            Swal.fire({
                icon: "warning",
                title: "Campos incompletos",
                text: "Por favor complete todos los campos requeridos: fecha, hora, personas y zona.",
                background: "#1a1a1a",
                color: "#fff",
            });
            return;
        }

        // Validar mesas
        if (!data.mesas) {
            console.error("Mesas no seleccionadas");
            Swal.fire({
                icon: "warning",
                title: "Mesas no seleccionadas",
                text: "Por favor seleccione al menos una mesa.",
                background: "#1a1a1a",
                color: "#fff",
            });
            return;
        }

        // Parsear mesas desde JSON string a array
        try {
            data.mesas = JSON.parse(data.mesas);
            if (!Array.isArray(data.mesas) || data.mesas.length === 0) {
                console.error("Array de mesas vacío");
                Swal.fire({
                    icon: "warning",
                    title: "Mesas no seleccionadas",
                    text: "Por favor seleccione al menos una mesa.",
                    background: "#1a1a1a",
                    color: "#fff",
                });
                return;
            }

            // Validar que ninguna de las mesas seleccionadas esté ocupada
            const mesaBtns = document.querySelectorAll(".mesa-btn");
            const mesasOcupadas = [];
            data.mesas.forEach((mesa) => {
                const btn = Array.from(mesaBtns).find(
                    (b) => b.textContent === String(mesa),
                );
                if (btn && btn.classList.contains("mesa-no-disponible")) {
                    mesasOcupadas.push(mesa);
                }
            });

            if (mesasOcupadas.length > 0) {
                Swal.fire({
                    icon: "error",
                    title: "Mesas ocupadas",
                    text: `Las mesas ${mesasOcupadas.join(", ")} están ocupadas. Por favor selecciona otras mesas.`,
                    background: "#1a1a1a",
                    color: "#fff",
                });
                return;
            }
        } catch (e) {
            console.error("Error al parsear mesas:", e, "Valor:", data.mesas);
            Swal.fire({
                icon: "error",
                title: "Error en mesas",
                text: "Error al procesar las mesas seleccionadas. Por favor seleccione las mesas nuevamente.",
                background: "#1a1a1a",
                color: "#fff",
            });
            return;
        }

        // Validar horario del restaurante (8:00 AM - 10:00 PM) - DESACTIVADO PARA PRUEBAS
        /*
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
        */

        const token =
            document.querySelector('input[name="_token"]')?.value ||
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

        console.log("Enviando datos al servidor:", data);
        console.log("CSRF Token:", token ? "Presente" : "No encontrado");

        // Mostrar pantalla de carga general
        if (typeof window.mostrarCarga === "function") {
            window.mostrarCarga("Confirmando reserva...");
        }

        fetch("/reservas", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": token,
            },
            body: JSON.stringify(data),
        })
            .then((res) => {
                console.log(
                    "Respuesta del servidor:",
                    res.status,
                    res.statusText,
                );
                if (!res.ok) {
                    return res.text().then((text) => {
                        console.error("Respuesta no OK:", text);
                        throw new Error(`HTTP ${res.status}: ${text}`);
                    });
                }
                return res.json();
            })
            .then((data) => {
                console.log("Datos recibidos:", data);
                if (data.success) {
                    document.getElementById("conf-fecha").textContent =
                        data.reserva.fecha;
                    document.getElementById("conf-hora").textContent =
                        data.reserva.hora;
                    document.getElementById("conf-personas").textContent =
                        data.reserva.personas;
                    // Mostrar mesas separadas por coma
                    const mesasArray = data.reserva.mesa.split(",");
                    document.getElementById("conf-mesa").textContent =
                        mesasArray.join(", ");
                    document.getElementById("conf-zona").textContent =
                        data.reserva.zona;
                    document.getElementById("conf-codigo").textContent =
                        data.reserva.codigo_referencia;
                    openModal("modal-reserva-confirmacion");
                    const form = document.getElementById("reservaForm");
                    form.reset();
                    // Resetear calendario y selector de hora
                    if (document.getElementById("selected-date-display")) {
                        document.getElementById(
                            "selected-date-display",
                        ).textContent = "Selecciona una fecha";
                    }
                    if (document.getElementById("selected-time-display")) {
                        document.getElementById(
                            "selected-time-display",
                        ).textContent = "Selecciona una hora";
                    }
                    if (document.getElementById("mesas")) {
                        document.getElementById("mesas").value = "";
                    }
                    document.querySelectorAll(".mesa-btn").forEach((btn) => {
                        btn.classList.remove(
                            "selected",
                            "mesa-disponible",
                            "mesa-no-disponible",
                        );
                    });
                    document.getElementById(
                        "mesas-seleccionadas-info",
                    ).textContent = "0 mesas seleccionadas";
                    document.getElementById(
                        "capacidad-total-info",
                    ).textContent = "Capacidad total: 0 personas";
                    // Volver a deshabilitar el control de personas
                    const personasWrapper = document.getElementById(
                        "personas-control-wrapper",
                    );
                    const personasHint =
                        document.getElementById("personas-hint");
                    if (personasWrapper) {
                        personasWrapper.style.opacity = "0.5";
                        personasWrapper.style.pointerEvents = "none";
                    }
                    if (personasHint) personasHint.style.display = "block";
                    // Recargar calendario
                    initDateSelector();
                } else {
                    console.error("Error del servidor:", data);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Error al crear reserva: " + (data.message || ""),
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                }
            })
            .catch((err) => {
                console.error("Error de conexión:", err);
                const errorMessage = err.message || "Error desconocido";
                Swal.fire({
                    icon: "error",
                    title: "Error de conexión",
                    html: `Error al conectar con el servidor:<br><strong>${errorMessage}</strong><br><br>Verifica que:<br>- Estés autenticado<br>- El servidor esté corriendo<br>- Tienes conexión a internet`,
                    background: "#1a1a1a",
                    color: "#fff",
                });
            })
            .finally(() => {
                if (typeof Swal !== "undefined") Swal.close();
            });
    };

    function showSection(sectionId) {
        sections.forEach((sec) => sec.classList.add("hidden"));
        const targetSection = document.getElementById(sectionId);
        if (targetSection) targetSection.classList.remove("hidden");

        // Cargar historial cuando se muestra la sección de historial
        if (sectionId === "historial") {
            cargarHistorial("todos");
        }

        navItems.forEach((item) => {
            item.classList.remove("active");
            if (item.getAttribute("href") === "#" + sectionId) {
                item.classList.add("active");
            }
        });

        // Visibilidad botones header
        const orderBtns = document.getElementById("header-order-buttons");
        const sesionActiva = sessionStorage.getItem("sesionPedido");
        if (orderBtns && !sesionActiva) {
            if (sectionId === "pedidos") {
                orderBtns.style.display = "none";
                openModal("modal-inicio-pedido");
            } else {
                orderBtns.style.display = "flex";
            }
        }
    }
    window.showSection = showSection;

    navItems.forEach((item) => {
        item.addEventListener("click", function (e) {
            e.preventDefault();
            const sectionId = this.getAttribute("href").substring(1);
            showSection(sectionId);
        });
    });

    const menuToggle = document.getElementById("menuToggle");
    const sidebar = document.querySelector(".sidebar");
    if (menuToggle && sidebar) {
        menuToggle.addEventListener("click", () =>
            sidebar.classList.toggle("active"),
        );
    }

    const menuCatBtns = document.querySelectorAll(
        ".menu-cat-btn:not([data-pedidos-cat])",
    );
    const menuItems = document.querySelectorAll(".menu-item");

    menuCatBtns.forEach((btn) => {
        btn.addEventListener("click", function () {
            menuCatBtns.forEach((b) => b.classList.remove("active"));
            this.classList.add("active");
            const categoryId = this.getAttribute("data-category-id");
            menuItems.forEach((item) => {
                if (
                    categoryId === "all" ||
                    item.getAttribute("data-category-id") === categoryId
                ) {
                    item.style.display = "block";
                } else {
                    item.style.display = "none";
                }
            });
        });
    });

    window.openModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove("hidden");
    };

    window.closeModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.add("hidden");
    };

    // --- LÓGICA HISTORIAL ---
    window.descargarHistorialExcel = function () {
        const filtroActivo =
            document.querySelector(".filter-btn.active")?.dataset.filter ||
            "todos";
        window.location.href = `/historial/exportar?filtro=${filtroActivo}`;
    };

    // --- COPIAR / PEGAR REFERENCIA ---
    window.copiarReferencia = function () {
        const codigo = document
            .getElementById("conf-codigo")
            ?.textContent?.trim();
        if (!codigo) return;
        navigator.clipboard
            .writeText(codigo)
            .then(() => {
                const btn = document.getElementById("btn-copiar-referencia");
                if (btn) {
                    const original = btn.innerHTML;
                    btn.innerHTML =
                        '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> ¡Copiado!';
                    btn.style.background = "rgba(46,204,113,0.2)";
                    btn.style.borderColor = "#2ecc71";
                    btn.style.color = "#2ecc71";
                    setTimeout(() => {
                        btn.innerHTML = original;
                        btn.style.background = "";
                        btn.style.borderColor = "";
                        btn.style.color = "";
                    }, 2000);
                }
                // Guardar en sessionStorage para pegar luego
                sessionStorage.setItem("referenciaCopiada", codigo);
            })
            .catch(() => {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo copiar al portapapeles.",
                    background: "#1a1a1a",
                    color: "#fff",
                });
            });
    };

    window.pegarReferencia = function () {
        const input = document.getElementById("auth_codigo");
        if (!input) return;
        // Intentar primero desde portapapeles
        if (navigator.clipboard && navigator.clipboard.readText) {
            navigator.clipboard
                .readText()
                .then((text) => {
                    if (text && text.trim()) {
                        input.value = text.trim();
                        input.style.borderColor = "var(--primary)";
                        setTimeout(() => (input.style.borderColor = ""), 1500);
                    } else {
                        pegarDesdeSession(input);
                    }
                })
                .catch(() => pegarDesdeSession(input));
        } else {
            pegarDesdeSession(input);
        }
    };

    function pegarDesdeSession(input) {
        const guardada = sessionStorage.getItem("referenciaCopiada");
        if (guardada) {
            input.value = guardada;
            input.style.borderColor = "var(--primary)";
            setTimeout(() => (input.style.borderColor = ""), 1500);
        } else {
            Swal.fire({
                icon: "info",
                title: "Portapapeles vacío",
                text: "No hay referencia copiada. Primero copia la referencia desde el ticket de tu reserva.",
                background: "#1a1a1a",
                color: "#fff",
            });
        }
    }

    // --- LOGOUT CON PANTALLA DE CARGA ---
    window.mostrarCargaUsuario = function () {
        mostrarCarga("Cerrando sesión...");
        setTimeout(() => {
            document.getElementById("logout-form").submit();
        }, 900);
    };

    window.guardarPerfil = function () {
        const nombre = document.getElementById("edit-name").value;
        const apellido = document.getElementById("edit-lastname").value;
        const telefono = document.getElementById("edit-phone").value;
        const correo = document.getElementById("edit-email").value;
        const password = document.getElementById("edit-password").value;

        if (!nombre || !apellido || !correo) {
            Swal.fire({
                icon: "warning",
                title: "Campos incompletos",
                text: "El nombre, apellido y correo son obligatorios.",
                background: "#1a1a1a",
                color: "#fff",
            });
            return;
        }

        const token =
            document.querySelector('input[name="_token"]')?.value ||
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

        fetch("/perfil/actualizar", {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": token,
            },
            body: JSON.stringify({
                name: nombre,
                lastname: apellido,
                telefono: telefono,
                email: correo,
                password: password,
            }),
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.success) {
                    Swal.fire({
                        icon: "success",
                        title: "¡Perfil actualizado!",
                        text: data.message,
                        background: "#1a1a1a",
                        color: "#fff",
                    }).then(() => {
                        window.location.reload(); // Recargar para mostrar los nuevos datos
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text:
                            data.message || "No se pudo actualizar el perfil.",
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                }
            })
            .catch((err) => {
                console.error("Error al actualizar perfil:", err);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Error de conexión al actualizar el perfil",
                    background: "#1a1a1a",
                    color: "#fff",
                });
            });
    };

    window.descargarPDF = function (tipo, id) {
        if (tipo === "reserva") {
            window.location.href = `/reservas/${id}/pdf`;
        } else if (tipo === "pedido") {
            window.location.href = `/pedidos/${id}/pdf`;
        }
    };

    window.eliminarItem = function (tipo, id) {
        Swal.fire({
            icon: "warning",
            title: "¿Estás seguro?",
            text: `¿Deseas eliminar este ${tipo}? Esta acción no se puede deshacer.`,
            background: "#1a1a1a",
            color: "#fff",
            showCancelButton: true,
            confirmButtonColor: "#e74c3c",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar",
        }).then((result) => {
            if (result.isConfirmed) {
                const token =
                    document.querySelector('input[name="_token"]')?.value ||
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute("content");
                const url =
                    tipo === "reserva" ? `/reservas/${id}` : `/pedidos/${id}`;

                fetch(url, {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": token,
                    },
                })
                    .then((res) => res.json())
                    .then((data) => {
                        if (data.success) {
                            Swal.fire({
                                icon: "success",
                                title: "Eliminado",
                                text: data.message,
                                background: "#1a1a1a",
                                color: "#fff",
                            });
                            // Recargar historial
                            cargarHistorial("todos");
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: data.message || "Error al eliminar",
                                background: "#1a1a1a",
                                color: "#fff",
                            });
                        }
                    })
                    .catch((err) => {
                        console.error("Error al eliminar:", err);
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Error de conexión al eliminar",
                            background: "#1a1a1a",
                            color: "#fff",
                        });
                    });
            }
        });
    };

    window.modificarItem = function (tipo, id) {
        if (tipo === "reserva") {
            cargarReservaParaEditar(id);
        } else if (tipo === "pedido") {
            cargarPedidoParaEditar(id);
        }
    };

    let editPedidoDetalles = [];

    // ========== CALENDARIO Y HORA PARA EDITAR RESERVA ==========
    let editReservaCalendar = {
        currentDate: new Date(),
        selectedDate: null,
    };

    let editReservaTime = {
        hour: 12,
        minute: 0,
        period: "PM",
    };

    function initEditDateSelector() {
        const dateGrid = document.getElementById("edit-date-grid");
        const prevMonthBtn = document.getElementById("edit-prev-month");
        const nextMonthBtn = document.getElementById("edit-next-month");
        const currentMonthDisplay = document.getElementById(
            "edit-current-month-display",
        );
        const selectedDateDisplay = document.getElementById(
            "edit-selected-date-display",
        );
        const fechaInput = document.getElementById("edit-reserva-fecha");

        const months = [
            "Enero",
            "Febrero",
            "Marzo",
            "Abril",
            "Mayo",
            "Junio",
            "Julio",
            "Agosto",
            "Septiembre",
            "Octubre",
            "Noviembre",
            "Diciembre",
        ];

        function renderCalendar() {
            if (!dateGrid) return;
            dateGrid.innerHTML = "";
            currentMonthDisplay.textContent = `${months[editReservaCalendar.currentDate.getMonth()]} ${editReservaCalendar.currentDate.getFullYear()}`;

            const firstDay = new Date(
                editReservaCalendar.currentDate.getFullYear(),
                editReservaCalendar.currentDate.getMonth(),
                1,
            );
            const lastDay = new Date(
                editReservaCalendar.currentDate.getFullYear(),
                editReservaCalendar.currentDate.getMonth() + 1,
                0,
            );
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            for (let i = 0; i < firstDay.getDay(); i++) {
                const emptyDay = document.createElement("button");
                emptyDay.disabled = true;
                dateGrid.appendChild(emptyDay);
            }

            for (let day = 1; day <= lastDay.getDate(); day++) {
                const date = new Date(
                    editReservaCalendar.currentDate.getFullYear(),
                    editReservaCalendar.currentDate.getMonth(),
                    day,
                );
                const btn = document.createElement("button");
                btn.type = "button";
                btn.textContent = day;

                if (date < today) {
                    btn.disabled = true;
                }

                if (date.toDateString() === today.toDateString()) {
                    btn.classList.add("today");
                }

                if (
                    editReservaCalendar.selectedDate &&
                    date.toDateString() ===
                        editReservaCalendar.selectedDate.toDateString()
                ) {
                    btn.classList.add("selected");
                }

                btn.addEventListener("click", () => {
                    editReservaCalendar.selectedDate = date;
                    fechaInput.value = date.toISOString().split("T")[0];
                    selectedDateDisplay.textContent = date.toLocaleDateString(
                        "es-ES",
                        {
                            weekday: "long",
                            year: "numeric",
                            month: "long",
                            day: "numeric",
                        },
                    );
                    renderCalendar();

                    // Recargar mesas si hay zona seleccionada
                    const zonaSeleccionada = document.querySelector(
                        'input[name="edit-reserva-zona"]:checked',
                    );
                    if (zonaSeleccionada) {
                        cargarMesasEdit(zonaSeleccionada.value);
                    }
                });

                dateGrid.appendChild(btn);
            }
        }

        prevMonthBtn?.addEventListener("click", () => {
            editReservaCalendar.currentDate.setMonth(
                editReservaCalendar.currentDate.getMonth() - 1,
            );
            renderCalendar();
        });

        nextMonthBtn?.addEventListener("click", () => {
            editReservaCalendar.currentDate.setMonth(
                editReservaCalendar.currentDate.getMonth() + 1,
            );
            renderCalendar();
        });

        renderCalendar();
    }

    function initEditTimeSelector() {
        const hourDisplay = document.getElementById("edit-hour-display");
        const minuteDisplay = document.getElementById("edit-minute-display");
        const horaInput = document.getElementById("edit-reserva-hora");
        const selectedTimeDisplay = document.getElementById(
            "edit-selected-time-display",
        );

        const buttons = document.querySelectorAll('[data-ctx="edit"]');

        function updateTimeDisplay() {
            let h24 = editReservaTime.hour;
            if (editReservaTime.period === "AM" && h24 === 12) h24 = 0;
            else if (editReservaTime.period === "PM" && h24 !== 12) h24 += 12;

            const time24 =
                String(h24).padStart(2, "0") +
                ":" +
                String(editReservaTime.minute).padStart(2, "0");
            horaInput.value = time24;
            hourDisplay.textContent = String(editReservaTime.hour).padStart(
                2,
                "0",
            );
            minuteDisplay.textContent = String(editReservaTime.minute).padStart(
                2,
                "0",
            );
            selectedTimeDisplay.textContent = time24;
        }

        buttons.forEach((btn) => {
            btn.addEventListener("click", (e) => {
                e.preventDefault();
                const unit = btn.dataset.unit;
                const period = btn.dataset.period;

                if (period) {
                    document
                        .querySelectorAll('.period-btn[data-ctx="edit"]')
                        .forEach((b) => b.classList.remove("active"));
                    btn.classList.add("active");
                    editReservaTime.period = period;
                } else if (unit === "hour") {
                    if (btn.classList.contains("time-up")) {
                        editReservaTime.hour =
                            editReservaTime.hour >= 12
                                ? 1
                                : editReservaTime.hour + 1;
                    } else {
                        editReservaTime.hour =
                            editReservaTime.hour <= 1
                                ? 12
                                : editReservaTime.hour - 1;
                    }
                } else if (unit === "minute") {
                    if (btn.classList.contains("time-up")) {
                        editReservaTime.minute =
                            (editReservaTime.minute + 1) % 60;
                    } else {
                        editReservaTime.minute =
                            (editReservaTime.minute - 1 + 60) % 60;
                    }
                }

                updateTimeDisplay();

                const zonaEl = document.querySelector(
                    'input[name="edit-reserva-zona"]:checked',
                );
                if (zonaEl) {
                    cargarMesasEdit(zonaEl.value);
                }
            });
        });

        updateTimeDisplay();
    }

    window.cargarMesasEdit = function (zona) {
        const mesaGrid = document.getElementById("edit-mesa-selector-grid");
        if (!mesaGrid) return;

        const zonasMesas = {
            interior: Array.from({ length: 10 }, (_, i) => i + 1),
            terraza: Array.from({ length: 10 }, (_, i) => i + 11),
            privado: Array.from({ length: 10 }, (_, i) => i + 21),
        };

        const mesasZona =
            zonasMesas[zona] || Array.from({ length: 10 }, (_, i) => i + 1);
        mesaGrid.innerHTML =
            '<p style="color:#aaa; font-size:0.85rem;">Verificando disponibilidad...</p>';

        const fecha = document.getElementById("edit-reserva-fecha").value;
        const hora = document.getElementById("edit-reserva-hora").value;
        const reservaId = document.getElementById("edit-reserva-id").value;
        const currentMesas = (
            document.getElementById("edit-reserva-mesas").value || ""
        )
            .split(",")
            .filter(Boolean)
            .map((m) => m.trim());

        if (!fecha || !hora) {
            renderMesasEditGrid(mesasZona, {}, currentMesas);
            return;
        }

        const token = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        fetch("/reservas/disponibilidad", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": token,
            },
            body: JSON.stringify({
                fecha: fecha,
                hora: hora,
                zona: zona,
                excluir_reserva_id: reservaId,
            }),
        })
            .then((res) => res.json())
            .then((data) => {
                const map = {};
                if (data.success) {
                    data.disponibilidad.forEach((item) => {
                        map[item.mesa] = item.disponible;
                    });
                }
                renderMesasEditGrid(mesasZona, map, currentMesas);
            })
            .catch(() => renderMesasEditGrid(mesasZona, {}, currentMesas));
    };

    function renderMesasEditGrid(mesasZona, map, currentMesas) {
        const grid = document.getElementById("edit-mesa-selector-grid");
        grid.innerHTML = "";

        mesasZona.forEach((mesa) => {
            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "mesa-btn";
            btn.textContent = mesa;

            if (currentMesas.includes(String(mesa))) {
                btn.classList.add("selected");
            } else if (map[mesa] === false) {
                btn.classList.add("mesa-no-disponible");
                btn.title = "Mesa ocupada";
            } else {
                btn.classList.add("mesa-disponible");
            }

            btn.onclick = function () {
                if (this.classList.contains("mesa-no-disponible")) {
                    Swal.fire({
                        icon: "warning",
                        title: "Mesa ocupada",
                        text: "Esta mesa ya está reservada.",
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                    return;
                }
                this.classList.toggle("selected");
                actualizarMesasSeleccionadasEdit();
            };

            grid.appendChild(btn);
        });

        actualizarMesasSeleccionadasEdit();
    }

    function actualizarMesasSeleccionadasEdit() {
        const selected = [];
        document
            .querySelectorAll("#edit-mesa-selector-grid .mesa-btn.selected")
            .forEach((b) => {
                selected.push(b.textContent);
            });
        document.getElementById("edit-reserva-mesas").value =
            selected.join(",");
        document.getElementById("edit-mesas-seleccionadas-info").textContent =
            selected.length + " mesa(s) seleccionada(s)";
    }

    function cargarReservaParaEditar(id) {
        const token =
            document.querySelector('input[name="_token"]')?.value ||
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

        fetch(`/reservas/${id}`, {
            method: "GET",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": token,
            },
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.success) {
                    const reserva = data.reserva;
                    document.getElementById("edit-reserva-id").value =
                        reserva.id;
                    document.getElementById("edit-reserva-personas").value =
                        reserva.personas;
                    document.getElementById("edit-reserva-notas").value =
                        reserva.notas || "";

                    // Parsear mesas desde string separado por comas
                    const mesasArray = reserva.mesa
                        .split(",")
                        .map((m) => m.trim());

                    // Seleccionar zona
                    const zonaRadio = document.querySelector(
                        `input[name="edit-reserva-zona"][value="${reserva.zona}"]`,
                    );
                    if (zonaRadio) zonaRadio.checked = true;

                    // Reinicializar selectores interactivos
                    editReservaCalendar.currentDate = new Date(reserva.fecha);
                    editReservaCalendar.selectedDate = new Date(reserva.fecha);

                    // Parsear hora
                    const [horaStr, minutoStr] = reserva.hora.split(":");
                    const horaNum = parseInt(horaStr);
                    const minutoNum = parseInt(minutoStr);

                    if (horaNum >= 12) {
                        editReservaTime.period = "PM";
                        editReservaTime.hour =
                            horaNum === 12 ? 12 : horaNum - 12;
                    } else {
                        editReservaTime.period = "AM";
                        editReservaTime.hour = horaNum === 0 ? 12 : horaNum;
                    }
                    editReservaTime.minute = minutoNum;

                    // Inicializar selectores
                    initEditDateSelector();
                    initEditTimeSelector();

                    // Cargar mesas de la zona
                    document.getElementById("edit-reserva-mesas").value =
                        mesasArray.join(",");
                    setTimeout(() => {
                        cargarMesasEdit(reserva.zona);
                        openModal("modal-editar-reserva");
                    }, 200);
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: data.message || "Error al cargar la reserva",
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                }
            })
            .catch((err) => {
                console.error("Error al cargar reserva:", err);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Error de conexión al cargar la reserva",
                    background: "#1a1a1a",
                    color: "#fff",
                });
            });
    }

    function cargarMesasParaEditar(zona, mesasSeleccionadas) {
        const mesaGrid = document.getElementById("edit-mesa-selector-grid");
        const zonasMesas = {
            interior: range(1, 10),
            terraza: range(11, 20),
            privado: range(21, 30),
        };
        const mesasZona = zonasMesas[zona] || range(1, 10);

        mesaGrid.innerHTML = "";

        mesasZona.forEach((mesa) => {
            const btn = document.createElement("button");
            btn.type = "button";
            btn.className = "mesa-btn";
            btn.textContent = mesa;

            if (mesasSeleccionadas.includes(String(mesa))) {
                btn.classList.add("selected");
            }

            btn.onclick = function () {
                seleccionarMesaEditar(mesa, this);
            };
            mesaGrid.appendChild(btn);
        });

        actualizarInfoMesasEditar(mesasSeleccionadas);
    }

    window.guardarEdicionReserva = function () {
        const id = document.getElementById("edit-reserva-id").value;
        const fecha = document.getElementById("edit-reserva-fecha").value;
        const hora = document.getElementById("edit-reserva-hora").value;
        const personas = document.getElementById("edit-reserva-personas").value;
        const zona = document.querySelector(
            'input[name="edit-reserva-zona"]:checked',
        )?.value;
        const mesasStr = document.getElementById("edit-reserva-mesas").value;
        const notas = document.getElementById("edit-reserva-notas").value;

        if (!fecha || !hora || !personas || !zona || !mesasStr) {
            Swal.fire({
                icon: "warning",
                title: "Campos incompletos",
                text: "Por favor complete todos los campos requeridos.",
                background: "#1a1a1a",
                color: "#fff",
            });
            return;
        }

        try {
            // Mesas pueden ser string separado por comas o JSON array
            let mesas;
            if (mesasStr.startsWith("[")) {
                mesas = JSON.parse(mesasStr);
            } else {
                mesas = mesasStr
                    .split(",")
                    .map((m) => m.trim())
                    .filter((m) => m);
            }

            if (!Array.isArray(mesas) || mesas.length === 0) {
                Swal.fire({
                    icon: "warning",
                    title: "Mesas no seleccionadas",
                    text: "Por favor seleccione al menos una mesa.",
                    background: "#1a1a1a",
                    color: "#fff",
                });
                return;
            }

            const token =
                document.querySelector('input[name="_token"]')?.value ||
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content");

            fetch(`/reservas/${id}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": token,
                },
                body: JSON.stringify({
                    fecha: fecha,
                    hora: hora,
                    zona: zona,
                    personas: parseInt(personas),
                    mesas: mesas,
                    notas: notas,
                }),
            })
                .then((res) => res.json())
                .then((data) => {
                    if (data.success) {
                        Swal.fire({
                            icon: "success",
                            title: "Actualizado",
                            text: data.message,
                            background: "#1a1a1a",
                            color: "#fff",
                        });
                        closeModal("modal-editar-reserva");
                        cargarHistorial("todos");
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text:
                                data.message ||
                                "Error al actualizar la reserva",
                            background: "#1a1a1a",
                            color: "#fff",
                        });
                    }
                })
                .catch((err) => {
                    console.error("Error al actualizar reserva:", err);
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Error de conexión al actualizar la reserva",
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                });
        } catch (e) {
            console.error("Error al parsear mesas:", e);
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Error al procesar las mesas seleccionadas.",
                background: "#1a1a1a",
                color: "#fff",
            });
        }
    };

    function cargarPedidoParaEditar(id) {
        const token =
            document.querySelector('input[name="_token"]')?.value ||
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

        // Cargar platos disponibles primero
        cargarPlatosDisponibles();

        fetch(`/pedidos/${id}`, {
            method: "GET",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": token,
            },
        })
            .then((res) => {
                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                }
                return res.json();
            })
            .then((data) => {
                if (data.success) {
                    const pedido = data.pedido;
                    document.getElementById("edit-pedido-id").value = pedido.id;

                    // Asegurar que los detalles tengan la imagen del plato
                    editPedidoDetalles = pedido.detalles.map((detalle) => ({
                        ...detalle,
                        plato_imagen:
                            detalle.plato_imagen ||
                            detalle.imagen ||
                            "https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=400&q=80",
                        plato_precio: parseFloat(
                            detalle.plato_precio || detalle.precio || 0,
                        ),
                        subtotal: parseFloat(detalle.subtotal || 0),
                    }));

                    renderizarDetallesPedidoEditar();
                    actualizarTotalPedidoEditar();

                    openModal("modal-editar-pedido");
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: data.message || "Error al cargar el pedido",
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                }
            })
            .catch((err) => {
                console.error("Error al cargar pedido:", err);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text:
                        "Error de conexión al cargar el pedido: " + err.message,
                    background: "#1a1a1a",
                    color: "#fff",
                });
            });
    }

    function cargarPlatosDisponibles() {
        const token =
            document.querySelector('input[name="_token"]')?.value ||
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");
        const grid = document.getElementById("edit-pedido-platos-grid");

        fetch("/platos", {
            method: "GET",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": token,
            },
        })
            .then((res) => {
                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                }
                return res.json();
            })
            .then((data) => {
                if (data.success && data.platos) {
                    // Cargar grid visual
                    if (grid) {
                        grid.innerHTML = "";
                        data.platos.forEach((plato) => {
                            const div = document.createElement("div");
                            div.className =
                                "pedido-card-modern plato-edit-grid";
                            div.setAttribute("data-cat", plato.categoria_id);
                            div.setAttribute(
                                "data-nombre",
                                plato.nombre.toLowerCase(),
                            );
                            div.innerHTML = `
                            <div class="img-wrapper">
                                <img src="${plato.imagen || "https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=400&q=80"}" alt="${plato.nombre}">
                            </div>
                            <div class="card-content">
                                <h4>${plato.nombre}</h4>
                                <p class="price">$${parseFloat(plato.precio).toFixed(2)}</p>
                                <button class="btn-add-modern" onclick="agregarPlatoAPedidoDesdeGrid(${plato.id}, '${plato.nombre.replace(/'/g, "\\'")}', ${plato.precio}, '${plato.imagen || "https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=400&q=80"}')" title="Añadir al pedido">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                </button>
                            </div>
                        `;
                            grid.appendChild(div);
                        });
                    }
                } else {
                    console.error("Error en respuesta:", data);
                }
            })
            .catch((err) => {
                console.error("Error al cargar platos:", err);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Error al cargar los platos disponibles",
                    background: "#1a1a1a",
                    color: "#fff",
                });
            });
    }

    window.agregarPlatoAPedido = function () {
        const select = document.getElementById("edit-pedido-plato-select");
        const platoId = select.value;

        if (!platoId) {
            Swal.fire({
                icon: "warning",
                title: "Selecciona un plato",
                text: "Por favor selecciona un plato del menú.",
                background: "#1a1a1a",
                color: "#fff",
            });
            return;
        }

        const option = select.options[select.selectedIndex];
        const precio = parseFloat(option.dataset.precio);
        const nombre = option.dataset.nombre;
        const imagen =
            option.dataset.imagen ||
            "https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=400&q=80";

        // Verificar si el plato ya existe en el pedido
        const existe = editPedidoDetalles.find((d) => d.plato_id == platoId);
        if (existe) {
            existe.cantidad += 1;
            existe.subtotal = existe.cantidad * precio;
        } else {
            editPedidoDetalles.push({
                plato_id: parseInt(platoId),
                plato_nombre: nombre,
                plato_precio: precio,
                plato_imagen: imagen,
                cantidad: 1,
                subtotal: precio,
            });
        }

        renderizarDetallesPedidoEditar();
        actualizarTotalPedidoEditar();
        select.value = "";
    };

    window.agregarPlatoAPedidoDesdeGrid = function (
        platoId,
        nombre,
        precio,
        imagen,
    ) {
        // Verificar si el plato ya existe en el pedido
        const existe = editPedidoDetalles.find((d) => d.plato_id == platoId);
        if (existe) {
            existe.cantidad += 1;
            existe.subtotal = existe.cantidad * precio;
        } else {
            editPedidoDetalles.push({
                plato_id: parseInt(platoId),
                plato_nombre: nombre,
                plato_precio: parseFloat(precio),
                plato_imagen: imagen,
                cantidad: 1,
                subtotal: parseFloat(precio),
            });
        }

        renderizarDetallesPedidoEditar();
        actualizarTotalPedidoEditar();
    };

    function renderizarDetallesPedidoEditar() {
        const container = document.getElementById("edit-pedido-detalles");
        container.innerHTML = "";

        editPedidoDetalles.forEach((detalle, index) => {
            const div = document.createElement("div");
            div.style.cssText =
                "display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid var(--border-color);";
            div.innerHTML = `
                <div style="flex: 1;">
                    <h4 style="margin:0; font-size: 0.95rem;">${detalle.plato_nombre}</h4>
                    <p style="margin:0; color:var(--primary); font-size: 0.85rem;">$${parseFloat(detalle.plato_precio).toLocaleString("es-CO", { minimumFractionDigits: 2 })}</p>
                </div>
                <div class="quantity-control" style="background: rgba(0,0,0,0.2); padding: 2px 5px; display: flex; align-items: center; gap: 0.5rem; border-radius: 4px;">
                    <button class="qty-btn minus" style="width:20px; height:20px; font-size:12px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; border-radius: 4px; cursor: pointer;" onclick="cambiarCantidadDetalle(${index}, -1)">−</button>
                    <span class="qty-value" style="font-size:12px; margin: 0 5px;">${detalle.cantidad}</span>
                    <button class="qty-btn plus" style="width:20px; height:20px; font-size:12px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white; border-radius: 4px; cursor: pointer;" onclick="cambiarCantidadDetalle(${index}, 1)">+</button>
                </div>
                <button type="button" onclick="eliminarDetalle(${index})" style="margin-left: 1rem; background: transparent; border: none; color: #e74c3c; cursor: pointer;" title="Eliminar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                </button>
            `;
            container.appendChild(div);
        });
    }

    window.cambiarCantidadDetalle = function (index, cambio) {
        const detalle = editPedidoDetalles[index];
        detalle.cantidad += cambio;
        if (detalle.cantidad < 1) detalle.cantidad = 1;
        detalle.subtotal = detalle.plato_precio * detalle.cantidad;

        renderizarDetallesPedidoEditar();
        actualizarTotalPedidoEditar();
    };

    window.eliminarDetalle = function (index) {
        editPedidoDetalles.splice(index, 1);
        renderizarDetallesPedidoEditar();
        actualizarTotalPedidoEditar();
    };

    function actualizarTotalPedidoEditar() {
        const total = editPedidoDetalles.reduce(
            (sum, detalle) => sum + parseFloat(detalle.subtotal || 0),
            0,
        );
        document.getElementById("edit-pedido-total").textContent =
            total.toFixed(2);
    }

    window.guardarEdicionPedido = function () {
        if (editPedidoDetalles.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "Pedido vacío",
                text: "El pedido debe tener al menos un plato.",
                background: "#1a1a1a",
                color: "#fff",
            });
            return;
        }

        const id = document.getElementById("edit-pedido-id").value;
        const token =
            document.querySelector('input[name="_token"]')?.value ||
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

        const detalles = editPedidoDetalles.map((d) => ({
            plato_id: d.plato_id,
            cantidad: d.cantidad,
        }));

        fetch(`/pedidos/${id}`, {
            method: "PUT",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": token,
            },
            body: JSON.stringify({
                detalles: detalles,
            }),
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.success) {
                    Swal.fire({
                        icon: "success",
                        title: "Actualizado",
                        text: data.message,
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                    closeModal("modal-editar-pedido");
                    cargarHistorial("todos");
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: data.message || "Error al actualizar el pedido",
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                }
            })
            .catch((err) => {
                console.error("Error al actualizar pedido:", err);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Error de conexión al actualizar el pedido",
                    background: "#1a1a1a",
                    color: "#fff",
                });
            });
    };

    window.cargarHistorial = function (filtro = "todos") {
        const historialBody = document.getElementById("historial-body");
        const historialLoading = document.getElementById("historial-loading");
        const historialVacio = document.getElementById("historial-vacio");
        const historialVacioMensaje = document.getElementById(
            "historial-vacio-mensaje",
        );
        const historialTable = document.getElementById("historial-table");

        // Actualizar botones activos
        document.querySelectorAll(".filter-btn").forEach((btn) => {
            btn.classList.remove("active");
            if (btn.dataset.filter === filtro) {
                btn.classList.add("active");
            }
        });

        // Mostrar loading
        historialLoading.style.display = "block";
        historialVacio.style.display = "none";
        historialTable.style.display = "none";
        historialBody.innerHTML = "";

        const token =
            document.querySelector('input[name="_token"]')?.value ||
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

        // Actualizar thead según filtro
        const thead = document.getElementById("historial-thead");
        if (thead) {
            if (filtro === "reservas") {
                thead.innerHTML = `
                    <tr>
                        <th>#</th>
                        <th>Tipo</th>
                        <th>Detalle</th>
                        <th>Fecha Creación</th>
                        <th>Fecha Reserva</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                `;
            } else {
                thead.innerHTML = `
                    <tr>
                        <th>#</th>
                        <th>Tipo</th>
                        <th>Detalle</th>
                        <th>Fecha Creación</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                `;
            }
        }

        let url = "/historial";
        if (filtro === "reservas") {
            url = "/historial/reservas";
        } else if (filtro === "pedidos") {
            url = "/historial/pedidos";
        }

        fetch(url, {
            method: "GET",
            headers: {
                Accept: "application/json",
                "X-CSRF-TOKEN": token,
            },
        })
            .then((res) => {
                console.log(
                    "Respuesta del servidor:",
                    res.status,
                    res.statusText,
                );
                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}: ${res.statusText}`);
                }
                return res.json();
            })
            .then((data) => {
                console.log("Datos recibidos:", data);
                historialLoading.style.display = "none";

                if (
                    data.success &&
                    data.historial &&
                    data.historial.length > 0
                ) {
                    historialTable.style.display = "table";
                    data.historial.forEach((item, index) => {
                        const row = document.createElement("tr");
                        const badgeClass =
                            item.tipo === "reserva"
                                ? "badge-reserva"
                                : "badge-pedido";
                        const statusClass =
                            item.estado === "completado" ||
                            item.estado === "confirmado" ||
                            item.estado === "confirmada"
                                ? "status-confirmado"
                                : item.estado === "pendiente"
                                  ? "status-pendiente"
                                  : item.estado === "cancelada" ||
                                      item.estado === "cancelado"
                                    ? "status-cancelada"
                                    : "status-pendiente";

                        const fechaReservaHtml =
                            filtro === "reservas"
                                ? item.fecha_reserva
                                    ? `<td><span style="background: rgba(194,149,69,0.15); color: var(--primary); padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600;">${item.fecha_reserva}</span></td>`
                                    : `<td><span style="color: #555; font-size: 0.85rem;">—</span></td>`
                                : "";

                        row.innerHTML = `
                        <td>${String(index + 1).padStart(3, "0")}</td>
                        <td><span class="badge ${badgeClass}">${item.tipo === "reserva" ? "Reserva" : "Pedido"}</span></td>
                        <td>${item.detalle}</td>
                        <td>${item.fecha_creacion || item.fecha}</td>
                        ${fechaReservaHtml}
                        <td><span class="status ${statusClass}">${item.estado}</span></td>
                        <td class="action-cell">
                            <button class="action-btn download-btn" onclick="descargarPDF('${item.tipo}', ${item.id})" title="Descargar PDF">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            </button>
                            <button class="action-btn edit-btn" onclick="modificarItem('${item.tipo}', ${item.id})" title="Modificar">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <button class="action-btn delete-btn" onclick="eliminarItem('${item.tipo}', ${item.id})" title="Eliminar">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                            </button>
                        </td>
                    `;
                        historialBody.appendChild(row);
                    });
                } else {
                    // Mostrar mensaje de vacío
                    console.log(
                        "Mostrando mensaje de vacío para filtro:",
                        filtro,
                    );
                    historialVacio.style.display = "block";
                    if (filtro === "reservas") {
                        historialVacioMensaje.textContent =
                            "Aún no has reservado.";
                    } else if (filtro === "pedidos") {
                        historialVacioMensaje.textContent =
                            "Aún no has hecho ningún pedido.";
                    } else {
                        historialVacioMensaje.textContent =
                            "Aún no tienes un historial.";
                    }
                }
            })
            .catch((err) => {
                console.error("Error al cargar historial:", err);
                historialLoading.style.display = "none";
                historialVacio.style.display = "block";
                historialVacioMensaje.textContent =
                    "Error al cargar el historial: " + err.message;
            });
    };

    // --- LÓGICA PEDIDOS ---
    let sesionPedido =
        JSON.parse(sessionStorage.getItem("sesionPedido")) || null;
    let carrito = [];

    window.mostrarFormularioPedido = function (tipo) {
        document.getElementById("seleccion-tipo-pedido").style.display = "none";
        document.getElementById("modal-form-con-reserva").style.display =
            tipo === "con_reserva" ? "block" : "none";
        document.getElementById("modal-form-sin-reserva").style.display =
            tipo === "sin_reserva" ? "block" : "none";
    };

    window.volverSeleccionPedido = function () {
        document.getElementById("seleccion-tipo-pedido").style.display = "flex";
        document.getElementById("modal-form-con-reserva").style.display =
            "none";
        document.getElementById("modal-form-sin-reserva").style.display =
            "none";
    };

    function verificarEstadoSesionPedido() {
        const btnConReserva = document.getElementById("btn-con-reserva");
        const btnSinReserva = document.getElementById("btn-sin-reserva");
        const btnFinReserva = document.getElementById("btn-fin-reserva");
        const btnFinServicio = document.getElementById("btn-fin-servicio");
        const vistaNormal = document.getElementById("vista-pedidos-normal");
        const infoMesaHeader = document.getElementById("header-info-mesa");
        const orderBtns = document.getElementById("header-order-buttons");

        if (sesionPedido) {
            if (orderBtns) orderBtns.style.display = "flex";
            if (btnConReserva) btnConReserva.style.display = "none";
            if (btnSinReserva) btnSinReserva.style.display = "none";
            if (sesionPedido.tipo === "con_reserva") {
                if (btnFinReserva) btnFinReserva.style.display = "inline-block";
                if (btnFinServicio) btnFinServicio.style.display = "none";
            } else {
                if (btnFinReserva) btnFinReserva.style.display = "none";
                if (btnFinServicio)
                    btnFinServicio.style.display = "inline-block";
            }

            if (vistaNormal) {
                vistaNormal.style.opacity = "1";
                vistaNormal.style.pointerEvents = "auto";
            }
            if (infoMesaHeader) {
                infoMesaHeader.style.display = "inline-flex";
                infoMesaHeader.textContent = `Mesa ${sesionPedido.mesa} - ${sesionPedido.zona || "Local"}`;
            }

            closeModal("modal-inicio-pedido");
        } else {
            const currentActive = document
                .querySelector(".nav-item.active")
                ?.getAttribute("href");
            if (orderBtns) {
                if (currentActive === "#pedidos")
                    orderBtns.style.display = "none";
                else orderBtns.style.display = "flex";
            }

            if (btnConReserva) btnConReserva.style.display = "inline-block";
            if (btnSinReserva) btnSinReserva.style.display = "inline-block";
            if (btnFinReserva) btnFinReserva.style.display = "none";
            if (btnFinServicio) btnFinServicio.style.display = "none";

            if (vistaNormal) {
                vistaNormal.style.opacity = "0.4";
                vistaNormal.style.pointerEvents = "none";
            }
            if (infoMesaHeader) infoMesaHeader.style.display = "none";
        }
    }

    window.iniciarPedido = function (tipo) {
        showSection("pedidos");
        volverSeleccionPedido();
        if (tipo) {
            mostrarFormularioPedido(tipo);
        }

        document.getElementById("auth-error").style.display = "none";
        document.getElementById("temp-error").style.display = "none";
    };

    window.finalizarSesionPedido = function () {
        Swal.fire({
            title: "¿Estás seguro?",
            text: "El carrito se vaciará y finalizarás el servicio actual.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, finalizar",
            cancelButtonText: "Cancelar",
            background: "#1a1a1a",
            color: "#fff",
        }).then((result) => {
            if (result.isConfirmed) {
                sessionStorage.removeItem("sesionPedido");
                sesionPedido = null;
                carrito = [];
                renderCarrito();
                verificarEstadoSesionPedido();
            }
        });
    };

    window.verificarReserva = function () {
        const mesa = document.getElementById("auth_mesa").value;
        const codigo = document.getElementById("auth_codigo").value;
        const cantidadMesas = document.getElementById(
            "auth_cantidad_mesas",
        ).value;
        const errTag = document.getElementById("auth-error");

        if (!mesa || !codigo || !cantidadMesas) {
            errTag.textContent = "Llene todos los campos.";
            errTag.style.display = "block";
            return;
        }

        // Validar que la cantidad de mesas coincida con las mesas ingresadas
        const mesasArray = mesa.split(",").map((m) => m.trim());
        if (mesasArray.length !== parseInt(cantidadMesas)) {
            errTag.textContent = `La cantidad de mesas (${cantidadMesas}) no coincide con las mesas ingresadas (${mesasArray.length}).`;
            errTag.style.display = "block";
            return;
        }

        const token =
            document.querySelector('input[name="_token"]')?.value ||
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

        // Enviar la primera mesa para verificación (el backend verificará todas)
        fetch("/pedidos/verificar", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": token,
            },
            body: JSON.stringify({
                mesa: mesasArray[0],
                codigo_referencia: codigo,
                mesas: mesasArray,
            }),
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.success) {
                    sesionPedido = {
                        tipo: "con_reserva",
                        mesa: data.reserva.mesa,
                        zona: data.reserva.zona,
                        codigo: data.reserva.codigo_referencia,
                    };
                    sessionStorage.setItem(
                        "sesionPedido",
                        JSON.stringify(sesionPedido),
                    );
                    verificarEstadoSesionPedido();
                } else {
                    errTag.textContent =
                        data.message || "Error de autenticación.";
                    errTag.style.display = "block";
                }
            })
            .catch((err) => {
                console.error(err);
                errTag.textContent = "Error de conexión.";
                errTag.style.display = "block";
            });
    };

    window.continuarSinReserva = function () {
        const mesas = document.getElementById("temp_mesas").value;
        const zonaInput = document.querySelector(
            'input[name="temp_zona"]:checked',
        );
        const errTag = document.getElementById("temp-error");

        if (!mesas || !zonaInput) {
            errTag.textContent = "Seleccione una zona y al menos una mesa.";
            errTag.style.display = "block";
            return;
        }

        const zona = zonaInput.value;

        // Parsear mesas desde JSON
        let mesasArray;
        try {
            mesasArray = JSON.parse(mesas);
        } catch (e) {
            errTag.textContent = "Error al procesar las mesas seleccionadas.";
            errTag.style.display = "block";
            return;
        }

        if (mesasArray.length === 0) {
            errTag.textContent = "Seleccione al menos una mesa.";
            errTag.style.display = "block";
            return;
        }

        sesionPedido = {
            tipo: "sin_reserva",
            mesa: mesasArray.join(","),
            zona: zona,
        };
        sessionStorage.setItem("sesionPedido", JSON.stringify(sesionPedido));
        verificarEstadoSesionPedido();
    };

    window.cargarMesasSinReserva = function (zona) {
        const mesaGrid = document.getElementById("temp-mesa-selector-grid");

        // Calcular rango de mesas por zona (10 mesas por zona)
        const zonasMesas = {
            interior: range(1, 10),
            terraza: range(11, 20),
            privado: range(21, 30),
        };

        const mesasZona = zonasMesas[zona] || range(1, 10);

        // Limpiar grid actual
        mesaGrid.innerHTML = "";

        // Verificar disponibilidad en tiempo real (considerando pedidos sin reserva actuales)
        const token =
            document.querySelector('input[name="_token"]')?.value ||
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

        // Calcular hora local correctamente para evitar problemas de zona horaria
        const ahora = new Date();
        const horaLocal = `${String(ahora.getHours()).padStart(2, "0")}:${String(ahora.getMinutes()).padStart(2, "0")}`;
        const yyyyMMdd = `${ahora.getFullYear()}-${String(ahora.getMonth() + 1).padStart(2, "0")}-${String(ahora.getDate()).padStart(2, "0")}`;

        fetch("/pedidos/disponibilidad-tiempo-real", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": token,
            },
            body: JSON.stringify({
                zona: zona,
                fecha: yyyyMMdd,
                hora: horaLocal,
            }),
        })
            .then((res) => res.json())
            .then((data) => {
                if (data.success) {
                    // Crear mapa de disponibilidad
                    const disponibilidadMap = {};
                    data.disponibilidad.forEach((item) => {
                        disponibilidadMap[item.mesa] = item.disponible;
                    });

                    // Renderizar mesas con estado de disponibilidad
                    mesasZona.forEach((mesa) => {
                        const btn = document.createElement("button");
                        btn.type = "button";
                        btn.className = "mesa-btn";
                        btn.textContent = mesa;

                        // Aplicar clase según disponibilidad
                        if (disponibilidadMap[mesa] === false) {
                            btn.classList.add("mesa-no-disponible");
                        } else {
                            btn.classList.add("mesa-disponible");
                        }

                        btn.onclick = function () {
                            seleccionarMesaTemp(mesa, this);
                        };
                        mesaGrid.appendChild(btn);
                    });
                }
            })
            .catch((err) => {
                console.error("Error al verificar disponibilidad:", err);
                // En caso de error, mostrar mesas sin estado de disponibilidad
                mesasZona.forEach((mesa) => {
                    const btn = document.createElement("button");
                    btn.type = "button";
                    btn.className = "mesa-btn";
                    btn.textContent = mesa;
                    btn.onclick = function () {
                        seleccionarMesaTemp(mesa, this);
                    };
                    mesaGrid.appendChild(btn);
                });
            });
    };

    window.seleccionarMesaTemp = function (mesa, button) {
        const mesaInput = document.getElementById("temp_mesas");
        const mesaBtns = document.querySelectorAll(
            "#temp-mesa-selector-grid .mesa-btn",
        );

        // Toggle selección múltiple
        button.classList.toggle("selected");

        // Obtener todas las mesas seleccionadas
        const mesasSeleccionadas = [];
        mesaBtns.forEach((btn) => {
            if (btn.classList.contains("selected")) {
                mesasSeleccionadas.push(btn.textContent);
            }
        });

        // Actualizar input hidden con array de mesas
        mesaInput.value = JSON.stringify(mesasSeleccionadas);

        // Actualizar información de capacidad
        actualizarInfoMesasTemp(mesasSeleccionadas);
    };

    function actualizarInfoMesasTemp(mesasSeleccionadas) {
        const mesasInfo = document.getElementById(
            "temp-mesas-seleccionadas-info",
        );
        const capacidadInfo = document.getElementById(
            "temp-capacidad-total-info",
        );

        const numMesas = mesasSeleccionadas.length;
        const capacidadTotal = numMesas * 4; // 4 personas por mesa

        mesasInfo.textContent = `${numMesas} mesa(s) seleccionada(s)`;
        capacidadInfo.textContent = `Capacidad total: ${capacidadTotal} personas`;
    }

    window.actualizarInputMesas = function () {
        const cantidadMesas = document.getElementById(
            "auth_cantidad_mesas",
        ).value;
        const mesaInput = document.getElementById("auth_mesa");
        const labelMesa = document.getElementById("label_mesa");
        const mesaHint = document.getElementById("mesa_hint");

        if (parseInt(cantidadMesas) >= 2) {
            // Cambiar a input de texto para permitir múltiples mesas separadas por comas
            mesaInput.type = "text";
            mesaInput.removeAttribute("min");
            mesaInput.removeAttribute("max");
            labelMesa.textContent = "Mesas (separadas por coma)";
            mesaHint.style.display = "block";
            mesaInput.placeholder = "Ej: 1,2,3";
        } else {
            // Mantener como input numérico
            mesaInput.type = "number";
            mesaInput.setAttribute("min", "1");
            mesaInput.setAttribute("max", "20");
            labelMesa.textContent = "Mesa (1-20)";
            mesaHint.style.display = "none";
            mesaInput.placeholder = "Ej: 5";
        }
    };

    // Validación de comas en el input de mesas
    document.addEventListener("DOMContentLoaded", function () {
        const authMesaInput = document.getElementById("auth_mesa");
        if (authMesaInput) {
            authMesaInput.addEventListener("keypress", function (e) {
                if (e.key === ",") {
                    const cantidadMesas = document.getElementById(
                        "auth_cantidad_mesas",
                    ).value;
                    if (parseInt(cantidadMesas) < 2) {
                        e.preventDefault();
                        Swal.fire({
                            icon: "warning",
                            title: "Cantidad insuficiente",
                            text: "Debes seleccionar al menos 2 mesas para usar múltiples mesas con comas.",
                            background: "#1a1a1a",
                            color: "#fff",
                            timer: 2000,
                        });
                    }
                }
            });

            // También validar paste (copiar y pegar)
            authMesaInput.addEventListener("paste", function (e) {
                e.preventDefault();
                const pastedText = (
                    e.clipboardData || window.clipboardData
                ).getData("text");
                const cantidadMesas = document.getElementById(
                    "auth_cantidad_mesas",
                ).value;

                if (parseInt(cantidadMesas) < 2 && pastedText.includes(",")) {
                    Swal.fire({
                        icon: "warning",
                        title: "Cantidad insuficiente",
                        text: "Debes seleccionar al menos 2 mesas para usar múltiples mesas con comas.",
                        background: "#1a1a1a",
                        color: "#fff",
                        timer: 2000,
                    });
                    return;
                }

                this.value += pastedText;
            });
        }
    });

    // --- CARRITO ---
    window.agregarAlCarrito = function (id, nombre, precio) {
        if (!sesionPedido) {
            Swal.fire({
                icon: "warning",
                title: "Atención",
                text: "Por favor autentica tu pedido primero (Botón Con/Sin Reserva).",
                background: "#1a1a1a",
                color: "#fff",
            });
            return;
        }

        const existe = carrito.find((item) => item.id === id);
        if (existe) {
            existe.cantidad++;
        } else {
            carrito.push({ id, nombre, precio, cantidad: 1 });
        }
        renderCarrito();

        const btnConf = document.getElementById("btn-confirmar-pedido");
        if (btnConf) {
            btnConf.style.transform = "scale(1.05)";
            setTimeout(() => (btnConf.style.transform = "scale(1)"), 200);
        }
    };

    window.actualizarCantidadCarrito = function (index, delta) {
        carrito[index].cantidad += delta;
        if (carrito[index].cantidad <= 0) {
            carrito.splice(index, 1);
        }
        renderCarrito();
    };

    function renderCarrito() {
        const container = document.getElementById("carrito-items");
        const totalSpan = document.getElementById("carrito-total-precio");
        const vacioText = document.getElementById("carrito-vacio");
        const btnConf = document.getElementById("btn-confirmar-pedido");

        if (!container) return;

        container.innerHTML = "";
        let total = 0;

        if (carrito.length === 0) {
            if (vacioText) container.appendChild(vacioText.cloneNode(true));
            if (totalSpan) totalSpan.textContent = "$0.00";
            if (btnConf) btnConf.disabled = true;
            return;
        }

        if (btnConf) btnConf.disabled = false;

        carrito.forEach((item, idx) => {
            total += item.precio * item.cantidad;
            const div = document.createElement("div");
            div.style.cssText =
                "display:flex; justify-content:space-between; align-items:center; margin-bottom:10px; padding-bottom:10px; border-bottom:1px solid var(--border-color);";
            div.innerHTML = `
                <div style="flex: 1;">
                    <h4 style="margin:0; font-size: 0.95rem;">${item.nombre}</h4>
                    <p style="margin:0; color:var(--primary); font-size: 0.85rem;">$${parseFloat(item.precio).toLocaleString("es-CO", { minimumFractionDigits: 2 })}</p>
                </div>
                <div class="quantity-control" style="background: rgba(0,0,0,0.2); padding: 2px 5px;">
                    <button class="qty-btn minus" style="width:20px; height:20px; font-size:12px;" onclick="actualizarCantidadCarrito(${idx}, -1)">−</button>
                    <span class="qty-value" style="font-size:12px; margin: 0 5px;">${item.cantidad}</span>
                    <button class="qty-btn plus" style="width:20px; height:20px; font-size:12px;" onclick="actualizarCantidadCarrito(${idx}, 1)">+</button>
                </div>
            `;
            container.appendChild(div);
        });

        if (totalSpan)
            totalSpan.textContent =
                "$" +
                total.toLocaleString("es-CO", { minimumFractionDigits: 2 });
    }

    window.confirmarPedidoBD = function () {
        if (carrito.length === 0 || !sesionPedido) return;

        const metodoPago = document.getElementById("metodo_pago_pedido").value;
        let total = carrito.reduce(
            (acc, item) => acc + item.precio * item.cantidad,
            0,
        );

        const data = {
            mesa: sesionPedido.mesa,
            zona: sesionPedido.zona,
            tipo_pedido: sesionPedido.tipo,
            total: total,
            notas: "Metodo Pago ID: " + metodoPago,
            items: carrito,
        };

        if (typeof window.mostrarCarga === "function") {
            window.mostrarCarga("Confirmando pedido...");
        }

        const token =
            document.querySelector('input[name="_token"]')?.value ||
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content");

        fetch("/pedidos", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": token,
            },
            body: JSON.stringify(data),
        })
            .then((res) => res.json())
            .then((resData) => {
                if (resData.success) {
                    Swal.fire({
                        icon: "success",
                        title: "¡Éxito!",
                        text: "Pedido confirmado exitosamente.",
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                    carrito = [];
                    renderCarrito();
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Error al confirmar pedido.",
                        background: "#1a1a1a",
                        color: "#fff",
                    });
                }
            })
            .catch((err) => {
                console.error(err);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Error de conexión.",
                    background: "#1a1a1a",
                    color: "#fff",
                });
            })
            .finally(() => {
                if (typeof Swal !== "undefined") Swal.close();
            });
    };

    // --- FILTROS PEDIDOS ---
    window.filtrarPlatosPedido = function () {
        const str = document.getElementById("buscar_plato").value.toLowerCase();
        const platos = document.querySelectorAll(".plato-para-pedir");
        const catSelect = document.getElementById("categoria_plato");
        const catActiva = catSelect ? catSelect.value : "all";

        platos.forEach((p) => {
            const nombre = p.getAttribute("data-nombre");
            const cat = p.getAttribute("data-cat");
            const coincideNombre = nombre.includes(str);
            const coincideCat = catActiva === "all" || cat === catActiva;

            p.style.display = coincideNombre && coincideCat ? "flex" : "none";
        });
    };

    window.filtrarCatPedidoDropdown = function (select) {
        filtrarPlatosPedido();
    };

    window.filtrarPlatosEditPedido = function () {
        const str = document
            .getElementById("edit-buscar_plato")
            .value.toLowerCase();
        const platos = document.querySelectorAll(
            "#edit-pedido-platos-grid .plato-edit-grid",
        );
        const catSelect = document.getElementById("edit-categoria_plato");
        const catActiva = catSelect ? catSelect.value : "all";

        platos.forEach((p) => {
            const nombre = p.getAttribute("data-nombre") || "";
            const cat = p.getAttribute("data-cat") || "";
            const coincideNombre = nombre.includes(str);
            const coincideCat = catActiva === "all" || cat == catActiva;

            p.style.display = coincideNombre && coincideCat ? "flex" : "none";
        });
    };

    window.irAPedidos = function (btn) {
        if (!sesionPedido) {
            Swal.fire({
                icon: "info",
                title: "Autenticación Requerida",
                text: "Primero selecciona Con Reserva o Sin Reserva en la sección de Hacer Pedidos.",
                background: "#1a1a1a",
                color: "#fff",
            });
            window.scrollTo({ top: 0, behavior: "smooth" });
            return;
        }

        showSection("pedidos");
        const id = btn.getAttribute("data-plato-id");
        const nombre = btn.getAttribute("data-plato-nombre");
        const precio = parseFloat(btn.getAttribute("data-plato-precio"));

        agregarAlCarrito(id, nombre, precio);
    };

    // INIT
    verificarEstadoSesionPedido();
});
