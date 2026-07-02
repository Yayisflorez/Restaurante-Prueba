<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    <title>Mi Panel - Sabor & Tradición</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="{{ asset('css/home2.css') }}">
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('img/LogoRestaurant.png') }}" alt="Logo" class="sidebar-logo">
            <h2>Sabor & Tradición</h2>
        </div>

        <nav class="sidebar-nav">

            <a href="#menu" class="nav-item active">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                <span>Ver Menú</span>
            </a>
            <a href="#reservar" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                <span>Reservar Mesa</span>
            </a>
            <a href="#pedidos" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                <span>Hacer Pedidos</span>
            </a>
            <a href="#historial" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                <span>Ver Historial</span>
            </a>
            <a href="#perfil" class="nav-item">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span>Mi Perfil</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: none;">
                @csrf
            </form>
            <a href="#" class="nav-item logout-btn" onclick="event.preventDefault(); mostrarCargaUsuario();">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </aside>

    <!-- Contenido Principal -->
    <main class="main-content">
        <!-- Header Superior -->
        <header class="top-header">
            <div class="header-left">
                <button class="menu-toggle" id="menuToggle">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
                </button>
                <h1>Bienvenido, <span class="user-name">{{ Auth::user()->name }}</span></h1>
            </div>
            <div class="header-right" style="display: flex; align-items: center; gap: 15px;">
                <div id="header-order-buttons" style="display: flex; gap: 10px;">
                    <button class="btn-primary" style="padding: 0.5rem 1.2rem; border: 1px solid var(--primary); height: 42px; display: flex; align-items: center; justify-content: center; box-sizing: border-box; white-space: nowrap;" id="btn-con-reserva" onclick="iniciarPedido('con_reserva')">Con Reserva</button>
                    <button class="btn-secondary" style="padding: 0.5rem 1.2rem; border: 1px solid var(--primary); color: var(--primary); background: transparent; border-radius: 8px; cursor: pointer; transition: background 0.3s; height: 42px; display: flex; align-items: center; justify-content: center; box-sizing: border-box; white-space: nowrap;" id="btn-sin-reserva" onclick="iniciarPedido('sin_reserva')" onmouseover="this.style.background='rgba(194,149,69,0.1)'" onmouseout="this.style.background='transparent'">Sin Reserva</button>
                    
                    <button class="btn-primary" style="padding: 0.5rem 1.2rem; background: #e74c3c; border: 1px solid #e74c3c; display: none; height: 42px; display: flex; align-items: center; justify-content: center; box-sizing: border-box; white-space: nowrap;" id="btn-fin-reserva" onclick="finalizarSesionPedido()">Finalizar Reserva</button>
                    <button class="btn-primary" style="padding: 0.5rem 1.2rem; background: #e67e22; border: 1px solid #e67e22; display: none; height: 42px; display: flex; align-items: center; justify-content: center; box-sizing: border-box; white-space: nowrap;" id="btn-fin-servicio" onclick="finalizarSesionPedido()">Finalizar Servicio</button>
                </div>
                
                <span id="header-info-mesa" style="font-size: 0.9rem; color: #000; font-weight: bold; background: var(--primary); padding: 0 1.2rem; height: 42px; border-radius: 20px; display: none; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(194, 149, 69, 0.3); box-sizing: border-box; white-space: nowrap;"></span>

                <div class="user-avatar">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
            </div>
        </header>

        <!-- Sección Ver Menú -->
        <section class="content-section" id="menu">
            <div class="section-header">
                <h2 class="section-title">Nuestro Menú</h2>
            </div>
            <div class="menu-categories">
                <button class="menu-cat-btn active" data-category-id="all">Todos</button>
                @forelse ($categorias as $index => $categoria)
                    <button class="menu-cat-btn" data-category-id="{{ $categoria->id }}">{{ $categoria->nombre }}</button>
                @empty
                    <p style="color: #aaa; text-align: center; width: 100%;">No hay categorías disponibles.</p>
                @endforelse
            </div>
            <div class="menu-grid">
                @foreach ($categorias as $categoria)
                    @foreach ($categoria->platos as $plato)
                        <div class="menu-item" data-category-id="{{ $categoria->id }}">
                            <img src="{{ $plato->imagen ?? 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=400&q=80' }}" alt="{{ $plato->nombre }}">
                            <div class="menu-item-info">
                                <h3>{{ $plato->nombre }}</h3>
                                <p>{{ $plato->descripcion }}</p>
                                <span class="price">${{ number_format($plato->precio, 2) }}</span>
                                <button
                                    class="btn btn-primary add-to-cart-btn"
                                    style="margin-top: 10px; width: 100%;"
                                    data-plato-id="{{ $plato->id }}"
                                    data-plato-nombre="{{ $plato->nombre }}"
                                    data-plato-precio="{{ $plato->precio }}"
                                    onclick="irAPedidos(this)"
                                >Hacer Pedido</button>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </section>

        <!-- Sección Reservar Mesa -->
        <section class="content-section hidden" id="reservar">
            <div class="section-header">
                <h2 class="section-title">Reservar Mesa</h2>
            </div>

            <div class="reserva-layout">

                <!-- Columna Izquierda: Calendario y Hora Interactiva -->
                <div class="reserva-col-calendar">
                    <!-- Selector de Fecha Personalizado -->
                    <div class="date-selector-container">
                        <div class="date-selector-header">
                            <h3 class="date-selector-title">Selecciona la Fecha</h3>
                            <div class="date-selector-value" id="selected-date-display">Selecciona una fecha</div>
                        </div>
                        <div class="date-selector-body">
                            <div class="date-nav">
                                <button type="button" class="date-nav-btn" id="prev-month">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                                </button>
                                <span class="date-nav-month" id="current-month-display">Enero 2026</span>
                                <button type="button" class="date-nav-btn" id="next-month">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                                </button>
                            </div>
                            <div class="date-grid-header">
                                <span>Dom</span><span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span>
                            </div>
                            <div class="date-grid" id="date-grid"></div>
                        </div>
                        <input type="hidden" id="fecha" name="fecha" required>
                    </div>

                    <!-- Selector de Hora Personalizado -->
                    <div class="time-selector-container">
                        <div class="time-selector-header">
                            <h3 class="time-selector-title">Selecciona la Hora</h3>
                            <div class="time-selector-value" id="selected-time-display">Selecciona una hora</div>
                        </div>
                        <div class="time-selector-body">
                            <div class="time-display">
                                <div class="time-unit">
                                    <button type="button" class="time-adjust-btn time-up" data-unit="hour">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                                    </button>
                                    <input type="number" class="time-value" id="hour-display" value="12" min="1" max="12" style="background:transparent; border:none; outline:none; width:4rem; padding:0; margin:0; -moz-appearance:textfield;" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                    <button type="button" class="time-adjust-btn time-down" data-unit="hour">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                    </button>
                                </div>
                                <span class="time-separator">:</span>
                                <div class="time-unit">
                                    <button type="button" class="time-adjust-btn time-up" data-unit="minute">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                                    </button>
                                    <input type="number" class="time-value" id="minute-display" value="00" min="0" max="59" style="background:transparent; border:none; outline:none; width:4rem; padding:0; margin:0; -moz-appearance:textfield;" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                    <button type="button" class="time-adjust-btn time-down" data-unit="minute">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                    </button>
                                </div>
                                <div class="time-period">
                                    <button type="button" class="period-btn" data-period="AM">AM</button>
                                    <button type="button" class="period-btn active" data-period="PM">PM</button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="hora" name="hora" required>
                    </div>
                </div>

                <!-- Columna Derecha: Datos -->
                <div class="reserva-col-data">
                    <form id="reservaForm" class="styled-form" onsubmit="event.preventDefault(); confirmarReserva()">

                        <!-- Personas -->
                        <div class="form-group">
                            <label>Número de Personas</label>
                            <div class="personas-control" id="personas-control-wrapper" style="opacity: 0.5; pointer-events: none;" title="Primero selecciona una mesa">
                                <button type="button" class="personas-btn" onclick="cambiarPersonas(-1)">&#8722;</button>
                                <div class="personas-display">
                                    <span id="personas-num" class="personas-num">1</span>
                                    <span class="personas-lbl">persona(s)</span>
                                </div>
                                <button type="button" class="personas-btn" onclick="cambiarPersonas(1)">&#43;</button>
                                <input type="hidden" id="personas_input" name="personas" value="1">
                            </div>
                            <p id="personas-hint" style="color: #aaa; font-size: 0.82rem; margin-top: 0.4rem;">Selecciona primero una mesa para habilitar este campo.</p>
                        </div>

                        <!-- Zona -->
                        <div class="form-group">
                            <label>Zona</label>
                            <div class="zona-cards">
                                <label class="zona-card">
                                    <input type="radio" name="zona" value="interior" required style="display:none;" onchange="cargarMesasPorZona('interior')">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                    <span>Interior</span>
                                </label>
                                <label class="zona-card">
                                    <input type="radio" name="zona" value="terraza" required style="display:none;" onchange="cargarMesasPorZona('terraza')">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                                    <span>Terraza</span>
                                </label>
                                <label class="zona-card">
                                    <input type="radio" name="zona" value="privado" required style="display:none;" onchange="cargarMesasPorZona('privado')">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                    <span>Salón Privado</span>
                                </label>
                            </div>
                        </div>

                        <!-- Mesa -->
                        <div class="form-group">
                            <label>Mesas (selección múltiple - 4 personas por mesa)</label>
                            <div class="mesa-info">
                                <span id="mesas-seleccionadas-info">0 mesas seleccionadas</span>
                                <span id="capacidad-total-info">Capacidad total: 0 personas</span>
                            </div>
                            <div class="mesa-selector-grid" id="mesa-selector-grid">
                                <!-- Las mesas se cargan dinámicamente según la zona -->
                            </div>
                            <input type="hidden" id="mesas" name="mesas" required>
                        </div>

                        <!-- Notas -->
                        <div class="form-group">
                            <label for="notas">Notas adicionales</label>
                            <textarea id="notas" name="notas" class="form-control" rows="3" placeholder="Celebración especial, alergias, etc."></textarea>
                        </div>

                        <button type="submit" class="btn-primary" id="btn-confirmar-reserva" style="margin-top: 1.5rem; width: 100%; padding: 1rem; font-size: 1.1rem; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 0.6rem;">Confirmar Reserva</button>
                    </form>
                </div>

            </div>
        </section>

        <!-- Sección Hacer Pedidos -->
        <section class="content-section hidden" id="pedidos" style="position: relative; overflow-x: hidden; height: calc(100vh - 85px); display: flex; flex-direction: column;">
            <!-- Vista Normal de Pedidos (Bloqueada hasta autenticar) -->
            <div id="vista-pedidos-normal" style="opacity: 0.4; pointer-events: none; display: flex; gap: 2rem; transition: opacity 0.3s ease; flex: 1; min-height: 0;">
                <!-- Lista de Platos -->
                <div style="flex: 2; overflow: hidden; display: flex; flex-direction: column; min-height: 0;">
                    <div class="section-header" style="flex-shrink: 0; margin-bottom: 1rem;">
                        <h2 class="section-title" style="margin-bottom: 0;">Hacer Pedidos</h2>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1rem; flex-shrink: 0; display: flex; gap: 1rem;">
                        <input type="text" id="buscar_plato" class="form-control" placeholder="Buscar plato por nombre..." onkeyup="filtrarPlatosPedido()" style="flex: 2; border-radius: 20px; padding-left: 1.5rem; background: rgba(0,0,0,0.5);">
                        <select id="categoria_plato" class="form-control" onchange="filtrarCatPedidoDropdown(this)" style="flex: 1; border-radius: 20px; background: rgba(0,0,0,0.5);">
                            <option value="all">Todas las Categorías</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Contenedor Grid -->
                    <div class="pedidos-grid-container" id="contenedor-platos-pedido">
                        @foreach ($categorias as $categoria)
                            @foreach ($categoria->platos as $plato)
                                <div class="pedido-card-modern plato-para-pedir {{ $plato->estado === 'agotado' ? 'agotado' : '' }}" data-cat="{{ $categoria->id }}" data-nombre="{{ strtolower($plato->nombre) }}" style="{{ $plato->estado === 'agotado' ? 'opacity: 0.5; pointer-events: none; position: relative;' : 'position: relative;' }}">
                                    @if($plato->estado === 'agotado')
                                        <div class="tag-agotado-float" style="position: absolute; top: 10px; right: 10px; background: #e74c3c; color: #fff; font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 4px; z-index: 5;">AGOTADO</div>
                                    @elseif($plato->estado === 'disponible')
                                        <div class="tag-disponible-float" style="position: absolute; top: 10px; right: 10px; background: rgba(46,204,113,0.2); color: #2ecc71; font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.6rem; border-radius: 4px; z-index: 5;">Disponible</div>
                                    @endif
                                    <div class="img-wrapper">
                                        <img src="{{ $plato->imagen ?? 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=400&q=80' }}" alt="{{ $plato->nombre }}">
                                    </div>
                                    <div class="card-content">
                                        <h4>{{ $plato->nombre }}</h4>
                                        <p class="price">${{ number_format($plato->precio, 2) }}</p>
                                        <button class="btn-add-modern" onclick="agregarAlCarrito({{ $plato->id }}, '{{ addslashes($plato->nombre) }}', {{ $plato->precio }})" title="Añadir al carrito" {{ $plato->estado === 'agotado' ? 'disabled' : '' }}>
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <!-- Carrito Rediseñado -->
                <div class="modern-cart" style="flex: 1; min-width: 320px; display: flex; flex-direction: column; height: 100%;">
                    <div class="cart-header" style="flex-shrink: 0;">
                        <h3>Tu Pedido</h3>
                    </div>
                    
                    <div id="carrito-items" class="cart-items-wrapper">
                        <div id="carrito-vacio" class="empty-cart-msg">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="2" style="margin-bottom: 10px;"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                            <p>El carrito está vacío</p>
                        </div>
                    </div>
                    
                    <div class="cart-footer" style="flex-shrink: 0;">
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label style="font-size: 0.85rem; color: #aaa;">Método de Pago</label>
                            <select id="metodo_pago_pedido" class="form-control" style="background: rgba(0,0,0,0.5);">
                                @foreach ($metodos_pago ?? [] as $metodo)
                                    <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="cart-total-row">
                            <span>Total</span>
                            <span id="carrito-total-precio" class="total-amount">$0.00</span>
                        </div>
                        <button class="btn-primary" style="width: 100%; border-radius: 12px; font-size: 1.1rem; padding: 1rem;" onclick="confirmarPedidoBD()" id="btn-confirmar-pedido" disabled>Confirmar Pedido</button>
                    </div>
                </div>
            </div>

            <!-- Modal Central de Inicio de Pedido -->
            <div id="modal-inicio-pedido" class="hidden" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 50; display: flex; align-items: center; justify-content: center;">
                <div style="width: 450px; background: var(--bg-sidebar); border: 1px solid var(--border-color); border-radius: 16px; padding: 2.5rem; position: relative; box-shadow: 0 15px 50px rgba(0,0,0,0.8); display: flex; flex-direction: column; text-align: center;">
                    <button onclick="closeModal('modal-inicio-pedido')" style="position: absolute; top: 15px; right: 20px; background: none; border: none; color: #aaa; font-size: 1.5rem; cursor: pointer;">&times;</button>
                    <h3 class="modal-title" style="margin-bottom: 2rem;">¿Cómo desea iniciar su pedido?</h3>
                    
                    <div id="seleccion-tipo-pedido" style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                        <button class="btn-primary" style="flex: 1; padding: 1.5rem 1rem; display: flex; flex-direction: column; align-items: center; gap: 10px;" onclick="mostrarFormularioPedido('con_reserva')">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                            <span>Con Reserva</span>
                        </button>
                        <button class="btn-secondary" style="flex: 1; padding: 1.5rem 1rem; border: 1px solid var(--primary); color: var(--primary); background: transparent; border-radius: 8px; display: flex; flex-direction: column; align-items: center; gap: 10px; transition: all 0.3s;" onclick="mostrarFormularioPedido('sin_reserva')" onmouseover="this.style.background='rgba(194,149,69,0.1)'" onmouseout="this.style.background='transparent'">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            <span>Sin Reserva</span>
                        </button>
                    </div>

                    <div id="modal-form-con-reserva" style="display: none; text-align: left;">
                        <button class="back-btn" style="margin-bottom: 1rem; padding: 0.2rem 0.5rem; font-size: 0.8rem;" onclick="volverSeleccionPedido()">← Volver</button>
                        <p style="color: #aaa; margin-bottom: 1rem; font-size: 0.9rem;">Ingrese los datos de su reserva.</p>
                        <div class="form-group">
                            <label>Cantidad de Mesas Reservadas</label>
                            <input type="number" id="auth_cantidad_mesas" class="form-control" min="1" max="10" placeholder="Ej: 2" onchange="actualizarInputMesas()">
                        </div>
                        <div class="form-group">
                            <label id="label_mesa">Mesa (1-20)</label>
                            <input type="number" id="auth_mesa" class="form-control" min="1" max="20" placeholder="Ej: 5">
                            <p id="mesa_hint" style="color: #aaa; font-size: 0.8rem; margin-top: 0.3rem; display: none;">Separe las mesas con comas (ej: 1,2,3)</p>
                        </div>
                        <div class="form-group">
                            <label>Código de Referencia</label>
                            <div style="display: flex; gap: 0.5rem; align-items: center;">
                                <input type="text" id="auth_codigo" class="form-control" placeholder="Ej: A1B2C3D4" style="flex: 1;">
                                <button type="button" onclick="pegarReferencia()" title="Pegar referencia del portapapeles" style="background: rgba(194,149,69,0.15); border: 1px solid var(--primary); color: var(--primary); border-radius: 8px; padding: 0.5rem 0.75rem; cursor: pointer; font-size: 0.85rem; transition: all 0.2s; white-space: nowrap; display: flex; align-items: center; gap: 0.4rem; flex-shrink: 0;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>
                                    Pegar
                                </button>
                            </div>
                        </div>
                        <button class="btn-primary" style="width: 100%; margin-top: 1rem;" onclick="verificarReserva()">Verificar Reserva</button>
                        <p style="text-align: center; margin-top: 1rem; font-size: 0.88rem; color: #aaa;">
                            ¿No tienes reserva? 
                            <a href="#reservar" onclick="event.preventDefault(); showSection('reservar')" style="color: var(--primary); text-decoration: none; font-weight: 600; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                Haz una reserva aquí
                            </a>
                        </p>
                        <p id="auth-error" style="color: #e74c3c; margin-top: 1rem; font-size: 0.9rem; display: none; text-align: center;"></p>
                    </div>

                    <div id="modal-form-sin-reserva" style="display: none; text-align: left;">
                        <button class="back-btn" style="margin-bottom: 1rem; padding: 0.2rem 0.5rem; font-size: 0.8rem;" onclick="volverSeleccionPedido()">← Volver</button>
                        <p style="color: #aaa; margin-bottom: 1rem; font-size: 0.9rem;">Seleccione las mesas donde se encuentra.</p>
                        
                        <!-- Zona -->
                        <div class="form-group">
                            <label>Zona</label>
                            <div class="zona-cards">
                                <label class="zona-card">
                                    <input type="radio" name="temp_zona" value="interior" required style="display:none;" onchange="cargarMesasSinReserva('interior')">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                    <span>Interior</span>
                                </label>
                                <label class="zona-card">
                                    <input type="radio" name="temp_zona" value="terraza" required style="display:none;" onchange="cargarMesasSinReserva('terraza')">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                                    <span>Terraza</span>
                                </label>
                                <label class="zona-card">
                                    <input type="radio" name="temp_zona" value="privado" required style="display:none;" onchange="cargarMesasSinReserva('privado')">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                    <span>Salón Privado</span>
                                </label>
                            </div>
                        </div>

                        <!-- Mesas -->
                        <div class="form-group">
                            <label>Mesas (selección múltiple - 4 personas por mesa)</label>
                            <div class="mesa-info">
                                <span id="temp-mesas-seleccionadas-info">0 mesas seleccionadas</span>
                                <span id="temp-capacidad-total-info">Capacidad total: 0 personas</span>
                            </div>
                            <div class="mesa-selector-grid" id="temp-mesa-selector-grid">
                                <!-- Las mesas se cargan dinámicamente según la zona -->
                            </div>
                            <input type="hidden" id="temp_mesas" name="temp_mesas" required>
                        </div>
                        
                        <button class="btn-primary" style="width: 100%; margin-top: 1rem;" onclick="continuarSinReserva()">Comenzar Pedido</button>
                        <p id="temp-error" style="color: #e74c3c; margin-top: 1rem; font-size: 0.9rem; display: none; text-align: center;"></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección Historial -->
        <section class="content-section hidden" id="historial">
            <div class="section-header">
                <h2 class="section-title">Mi Historial</h2>
            </div>
            <div class="filter-buttons" style="display: flex; gap: 1rem; margin-bottom: 1.5rem; justify-content: space-between; align-items: center;">
                <div style="display: flex; gap: 1rem;">
                    <button class="btn-primary filter-btn active" onclick="cargarHistorial('todos')" data-filter="todos">Todos</button>
                    <button class="btn-primary filter-btn" onclick="cargarHistorial('reservas')" data-filter="reservas">Reservas</button>
                    <button class="btn-primary filter-btn" onclick="cargarHistorial('pedidos')" data-filter="pedidos">Pedidos</button>
                </div>
                <div id="historial-right-button">
                    <button class="btn-secondary" onclick="descargarHistorialExcel()" style="display: flex; align-items: center; gap: 0.5rem; border: 1px solid #27ae60; color: #27ae60; background: transparent; border-radius: 8px; padding: 0.5rem 1rem; cursor: pointer; transition: background 0.3s;" onmouseover="this.style.background='rgba(39, 174, 96, 0.1)'" onmouseout="this.style.background='transparent'">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        Descargar Excel
                    </button>
                </div>
            </div>
            <div class="table-container">
                <div id="historial-loading" style="text-align: center; padding: 2rem; display: none;">
                    <p>Cargando historial...</p>
                </div>
                <div id="historial-vacio" style="text-align: center; padding: 2rem; display: none;">
                    <p id="historial-vacio-mensaje" style="color: #aaa; font-size: 1.1rem;"></p>
                </div>
                <table class="styled-table" id="historial-table">
                    <thead id="historial-thead">
                        <tr>
                            <th>#</th>
                            <th>Tipo</th>
                            <th>Detalle</th>
                            <th>Fecha Creación</th>
                            <th>Fecha Reserva</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="historial-body">
                        <!-- Los datos se cargarán dinámicamente desde la base de datos -->
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Sección Perfil -->
        <section class="content-section hidden" id="perfil">
            <div class="section-header">
                <h2 class="section-title">Mi Perfil</h2>
            </div>
            <div class="profile-container">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <h3>{{ Auth::user()->name }} {{ Auth::user()->lastname }}</h3>
                    <p class="profile-email">{{ Auth::user()->email }}</p>
                    <div class="profile-details">
                        <div class="detail-row">
                            <span class="detail-label">Documento:</span>
                            <span class="detail-value">{{ Auth::user()->numero_documento ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Teléfono:</span>
                            <span class="detail-value">{{ Auth::user()->telefono ?? 'N/A' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Miembro desde:</span>
                            <span class="detail-value">{{ Auth::user()->created_at ? Auth::user()->created_at->format('M Y') : 'N/A' }}</span>
                        </div>
                    </div>
                    <a href="#editar-perfil" class="btn-primary" onclick="showSection('editar-perfil')" style="display:inline-block; margin-top:1.5rem; text-decoration:none; text-align:center;">Editar Perfil</a>
                </div>
            </div>
        </section>

        <!-- Sección Editar Perfil -->
        <section class="content-section hidden" id="editar-perfil">
            <div class="section-header">
                <h2 class="section-title">Editar Perfil</h2>
                <button class="back-btn" onclick="showSection('perfil')">← Volver a Perfil</button>
            </div>
            <div class="form-container">
                <form class="styled-form" id="form-editar-perfil" onsubmit="event.preventDefault(); guardarPerfil()">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-name">Nombre</label>
                            <input type="text" id="edit-name" class="form-control" value="{{ Auth::user()->name }}">
                        </div>
                        <div class="form-group">
                            <label for="edit-lastname">Apellido</label>
                            <input type="text" id="edit-lastname" class="form-control" value="{{ Auth::user()->lastname }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="edit-phone">Teléfono</label>
                            <input type="tel" id="edit-phone" class="form-control" value="{{ Auth::user()->telefono }}">
                        </div>
                        <div class="form-group">
                            <label for="edit-email">Correo</label>
                            <input type="email" id="edit-email" class="form-control" value="{{ Auth::user()->email }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit-password">Nueva Contraseña</label>
                        <input type="password" id="edit-password" class="form-control" placeholder="Dejar en blanco para no cambiar">
                    </div>
                    <button type="submit" class="btn-primary">Guardar Cambios</button>
                </form>
            </div>
        </section>

        <!-- Modales de Edición y Confirmación -->
        <div class="modal-overlay hidden" id="modal-reserva-confirmacion">
            <div class="modal-content" style="text-align: center; max-width: 400px;">
                <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(212,175,55,0.2); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                </div>
                <h3 class="modal-title" style="margin-bottom: 0.5rem;">Reserva Confirmada</h3>
                <p style="color: #aaa; margin-bottom: 1.5rem;">Tu reserva ha sido registrada exitosamente.</p>
                
                <div style="background: var(--bg-dark); padding: 1.5rem; border-radius: 8px; border: 1px dashed var(--primary); margin-bottom: 1.5rem; text-align: left;">
                    <p style="margin-bottom: 0.5rem;"><strong>Fecha:</strong> <span id="conf-fecha"></span></p>
                    <p style="margin-bottom: 0.5rem;"><strong>Hora:</strong> <span id="conf-hora"></span></p>
                    <p style="margin-bottom: 0.5rem;"><strong>Personas:</strong> <span id="conf-personas"></span></p>
                    <p style="margin-bottom: 0.5rem;"><strong>Mesa:</strong> <span id="conf-mesa"></span></p>
                    <p style="margin-bottom: 0.5rem;"><strong>Zona:</strong> <span id="conf-zona" style="text-transform: capitalize;"></span></p>
                    <div style="margin-top: 1rem; text-align: center;">
                        <p style="font-size: 0.9rem; color: #aaa; margin-bottom: 0.3rem;">Código de Referencia:</p>
                        <div style="display: flex; align-items: center; justify-content: center; gap: 0.75rem;">
                            <p id="conf-codigo" style="font-size: 1.5rem; font-weight: bold; color: var(--primary); letter-spacing: 2px; margin: 0;"></p>
                            <button type="button" id="btn-copiar-referencia" onclick="copiarReferencia()" title="Copiar referencia" style="background: rgba(194,149,69,0.15); border: 1px solid var(--primary); color: var(--primary); border-radius: 8px; padding: 0.45rem 0.75rem; cursor: pointer; font-size: 0.85rem; transition: all 0.2s; display: flex; align-items: center; gap: 0.4rem;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                                Copiar
                            </button>
                        </div>
                    </div>
                </div>
                
                <p style="font-size: 0.85rem; color: #aaa; margin-bottom: 1.5rem;">Guarda este código para realizar pedidos desde tu mesa o en la sección "Con Reserva".</p>
                <button type="button" class="btn-primary" style="width: 100%;" onclick="closeModal('modal-reserva-confirmacion')">Aceptar y Cerrar</button>
            </div>
        </div>

        <div class="modal-overlay hidden" id="modal-editar-reserva">
            <div class="modal-content" style="max-width: 1000px; max-height: 90vh; overflow-y: auto;">
                <button class="close-modal" onclick="closeModal('modal-editar-reserva')">&times;</button>
                <h3 class="modal-title">Editar Reserva</h3>
                <form class="styled-form" id="form-editar-reserva">
                    <input type="hidden" id="edit-reserva-id">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">
                        <!-- Columna Izquierda: Calendario + Hora -->
                        <div>
                            <!-- Selector de Fecha Personalizado -->
                            <div class="date-selector-container">
                                <div class="date-selector-header">
                                    <h3 class="date-selector-title">Selecciona la Fecha</h3>
                                    <div class="date-selector-value" id="edit-selected-date-display">Selecciona una fecha</div>
                                </div>
                                <div class="date-selector-body">
                                    <div class="date-nav">
                                        <button type="button" class="date-nav-btn" id="edit-prev-month">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                                        </button>
                                        <span class="date-nav-month" id="edit-current-month-display">Junio 2026</span>
                                        <button type="button" class="date-nav-btn" id="edit-next-month">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                                        </button>
                                    </div>
                                    <div class="date-grid-header">
                                        <span>Dom</span><span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span>
                                    </div>
                                    <div class="date-grid" id="edit-date-grid"></div>
                                </div>
                                <input type="hidden" id="edit-reserva-fecha" name="fecha" required>
                            </div>

                            <!-- Selector de Hora Personalizado -->
                            <div class="time-selector-container">
                                <div class="time-selector-header">
                                    <h3 class="time-selector-title">Selecciona la Hora</h3>
                                    <div class="time-selector-value" id="edit-selected-time-display">12:00</div>
                                </div>
                                <div class="time-selector-body">
                                    <div class="time-display">
                                        <div class="time-unit">
                                            <button type="button" class="time-adjust-btn time-up" data-unit="hour" data-ctx="edit">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                                            </button>
                                            <span class="time-value" id="edit-hour-display">12</span>
                                            <button type="button" class="time-adjust-btn time-down" data-unit="hour" data-ctx="edit">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                            </button>
                                        </div>
                                        <span class="time-separator">:</span>
                                        <div class="time-unit">
                                            <button type="button" class="time-adjust-btn time-up" data-unit="minute" data-ctx="edit">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                                            </button>
                                            <span class="time-value" id="edit-minute-display">00</span>
                                            <button type="button" class="time-adjust-btn time-down" data-unit="minute" data-ctx="edit">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                            </button>
                                        </div>
                                        <div class="time-period">
                                            <button type="button" class="period-btn" data-period="AM" data-ctx="edit">AM</button>
                                            <button type="button" class="period-btn active" data-period="PM" data-ctx="edit">PM</button>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="edit-reserva-hora" name="hora" required>
                            </div>
                        </div>

                        <!-- Columna Derecha: Datos -->
                        <div>
                            <!-- Zona -->
                            <div class="form-group" style="margin-bottom: 1rem;">
                                <label>Zona</label>
                                <div class="zona-cards" style="gap: 0.5rem;">
                                    <label class="zona-card" style="padding: 0.6rem 0.8rem; font-size: 0.85rem;">
                                        <input type="radio" name="edit-reserva-zona" value="interior" required style="display:none;" onchange="cargarMesasEdit('interior')">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                        <span>Interior</span>
                                    </label>
                                    <label class="zona-card" style="padding: 0.6rem 0.8rem; font-size: 0.85rem;">
                                        <input type="radio" name="edit-reserva-zona" value="terraza" required style="display:none;" onchange="cargarMesasEdit('terraza')">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                                        <span>Terraza</span>
                                    </label>
                                    <label class="zona-card" style="padding: 0.6rem 0.8rem; font-size: 0.85rem;">
                                        <input type="radio" name="edit-reserva-zona" value="privado" required style="display:none;" onchange="cargarMesasEdit('privado')">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                        <span>Salón Privado</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Personas -->
                            <div class="form-group" style="margin-bottom: 1rem;">
                                <label>Número de Personas</label>
                                <input type="number" id="edit-reserva-personas" class="form-control" min="1" required>
                            </div>

                            <!-- Mesas -->
                            <div class="form-group" style="margin-bottom: 1rem;">
                                <label>Mesas (selección múltiple)</label>
                                <div class="mesa-info">
                                    <span id="edit-mesas-seleccionadas-info">0 mesas seleccionadas</span>
                                </div>
                                <div class="mesa-selector-grid" id="edit-mesa-selector-grid"></div>
                                <input type="hidden" id="edit-reserva-mesas" name="mesas" required>
                            </div>

                            <!-- Notas -->
                            <div class="form-group" style="margin-bottom: 1rem;">
                                <label>Notas (opcional)</label>
                                <textarea id="edit-reserva-notas" class="form-control" rows="2"></textarea>
                            </div>

                            <button type="button" class="btn-primary" style="width: 100%;" onclick="guardarEdicionReserva()">Guardar Cambios</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal-overlay hidden" id="modal-editar-pedido">
            <div class="modal-content" style="max-width: 1200px; max-height: 90vh; overflow: hidden; display: flex; flex-direction: column; padding: 0;">
                <div style="padding: 1.5rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                    <h3 class="modal-title" style="margin: 0;">Editar Pedido</h3>
                    <button class="close-modal" onclick="closeModal('modal-editar-pedido')" style="position: static; font-size: 2rem;">&times;</button>
                </div>
                <input type="hidden" id="edit-pedido-id">
                
                <div style="display: flex; flex: 1; min-height: 0; overflow: hidden;">
                    <!-- Columna Izquierda: Menú -->
                    <div style="flex: 2; padding: 1.5rem; display: flex; flex-direction: column; overflow-y: auto; border-right: 1px solid var(--border-color);">
                        <div class="form-group" style="margin-bottom: 1rem; display: flex; gap: 1rem; flex-shrink: 0;">
                            <input type="text" id="edit-buscar_plato" class="form-control" placeholder="Buscar plato por nombre..." onkeyup="filtrarPlatosEditPedido()" style="flex: 2; border-radius: 20px; background: rgba(0,0,0,0.5); padding-left: 1.5rem;">
                            <select id="edit-categoria_plato" class="form-control" onchange="filtrarPlatosEditPedido()" style="flex: 1; border-radius: 20px; background: rgba(0,0,0,0.5);">
                                <option value="all">Todas las Categorías</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="pedidos-grid-container" id="edit-pedido-platos-grid" style="padding-bottom: 1rem;">
                            <!-- Los platos se cargarán dinámicamente -->
                        </div>
                    </div>

                    <!-- Columna Derecha: Voucher / Carrito -->
                    <div class="modern-cart" style="flex: 1; min-width: 350px; padding: 1.5rem; display: flex; flex-direction: column; background: var(--bg-sidebar);">
                        <div class="cart-header" style="flex-shrink: 0; margin-bottom: 1rem;">
                            <h3 style="margin: 0; color: var(--primary);">Platos en tu pedido</h3>
                        </div>
                        
                        <div id="edit-pedido-detalles" class="cart-items-wrapper" style="flex: 1; overflow-y: auto; padding-right: 0.5rem; margin-bottom: 1rem;">
                            <!-- Los detalles se cargarán dinámicamente -->
                        </div>
                        
                        <div class="cart-footer" style="flex-shrink: 0;">
                            <div class="form-group" style="margin-bottom: 1rem;">
                                <label style="font-size: 0.85rem; color: #aaa;">Método de Pago</label>
                                <select id="edit_metodo_pago_pedido" class="form-control" style="background: rgba(0,0,0,0.5);">
                                    @foreach ($metodos_pago ?? [] as $metodo)
                                        <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="cart-total-row" style="display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.05); padding: 1rem; border-radius: 8px; margin-bottom: 1rem;">
                                <span style="font-size: 1.1rem;">Total</span>
                                <span id="edit-pedido-total" class="total-amount" style="font-size: 1.3rem; font-weight: bold; color: var(--primary);">$0.00</span>
                            </div>
                            <button type="button" class="btn-primary" style="width: 100%; border-radius: 12px; font-size: 1.1rem; padding: 1rem;" onclick="guardarEdicionPedido()">Guardar Cambios</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/loader.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/ScriptHome2.js') }}?v={{ time() }}"></script>
    <script>
        // Polling para Actualizaciones en Tiempo Real (Usuario)
        let lastUpdateCheck = new Date().toISOString();
        setInterval(() => {
            const openModals = document.querySelectorAll('.modal-overlay:not(.hidden)');
            if (openModals.length > 0) return;
            
            if (document.activeElement && (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA')) return;

            fetch(`/api/updates/check?last_check=${lastUpdateCheck}`)
                .then(res => res.json())
                .then(data => {
                    if (data.has_updates) {
                        lastUpdateCheck = data.timestamp;
                        location.reload(); 
                    } else if (data.timestamp) {
                        lastUpdateCheck = data.timestamp;
                    }
                })
                .catch(err => console.error("Error checking updates:", err));
        }, 15000);
    </script>
</body>
</html>
