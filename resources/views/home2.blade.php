<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - Sabor & Tradición</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css">
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
            <a href="#" class="nav-item logout-btn" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
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
            <div class="header-right" style="display: flex; align-items: center; gap: 20px;">
                <div id="header-order-buttons" style="display: flex; gap: 10px; transition: opacity 0.3s;">
                    <button class="btn-primary" style="padding: 0.5rem 1rem;" id="btn-con-reserva" onclick="abrirModalSeleccionPedido('con_reserva')">Con Reserva</button>
                    <button class="btn-secondary" style="padding: 0.5rem 1rem; border: 1px solid var(--primary); color: var(--primary); background: transparent; border-radius: 8px; cursor: pointer;" id="btn-sin-reserva" onclick="abrirModalSeleccionPedido('sin_reserva')">Sin Reserva</button>
                    
                    <button class="btn-primary" style="padding: 0.5rem 1rem; background: #e74c3c; display: none;" id="btn-fin-reserva" onclick="finalizarSesionPedido()">Finalizar Reserva</button>
                    <button class="btn-primary" style="padding: 0.5rem 1rem; background: #e67e22; display: none;" id="btn-fin-servicio" onclick="finalizarSesionPedido()">Finalizar Servicio</button>
                </div>
                
                <div id="info-mesa-pedido-header" style="display: none; align-items: center; gap: 10px; background: rgba(212,175,55,0.1); border: 1px solid rgba(212,175,55,0.3); padding: 0.4rem 1rem; border-radius: 20px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                    <span id="info-mesa-texto" style="color: var(--primary); font-weight: 600; font-size: 0.9rem;"></span>
                </div>

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
            <div class="form-container">
                <form id="reservaForm" class="styled-form" onsubmit="event.preventDefault(); confirmarReserva()">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha">Fecha</label>
                            <input type="date" id="fecha" name="fecha" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="hora">Hora</label>
                            <input type="time" id="hora" name="hora" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="personas">Número de Personas</label>
                            <div class="quantity-control" style="justify-content: center;">
                                <button type="button" class="qty-btn" onclick="let input = document.getElementById('personas_input'); if(input.value > 1) input.value--">−</button>
                                <input type="number" id="personas_input" name="personas" value="1" min="1" max="20" style="width: 50px; text-align: center; border: none; background: transparent; color: white; font-size: 1.1rem;" readonly>
                                <button type="button" class="qty-btn" onclick="let input = document.getElementById('personas_input'); if(input.value < 20) input.value++">+</button>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="mesa">Mesa (1-20)</label>
                            <input type="number" id="mesa" name="mesa" class="form-control" min="1" max="20" required placeholder="Ej: 5">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Zona</label>
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <label style="flex: 1; min-width: 120px; border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; cursor: pointer; text-align: center; transition: all 0.3s;" class="zona-radio-label">
                                <input type="radio" name="zona" value="interior" required style="display: none;">
                                <span>Interior</span>
                            </label>
                            <label style="flex: 1; min-width: 120px; border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; cursor: pointer; text-align: center; transition: all 0.3s;" class="zona-radio-label">
                                <input type="radio" name="zona" value="terraza" required style="display: none;">
                                <span>Terraza</span>
                            </label>
                            <label style="flex: 1; min-width: 120px; border: 1px solid var(--border-color); padding: 1rem; border-radius: 8px; cursor: pointer; text-align: center; transition: all 0.3s;" class="zona-radio-label">
                                <input type="radio" name="zona" value="privado" required style="display: none;">
                                <span>Salón Privado</span>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notas">Notas adicionales</label>
                        <textarea id="notas" name="notas" class="form-control" rows="3" placeholder="Celebración especial, alergias, etc."></textarea>
                    </div>
                    
                    <button type="submit" class="btn-primary" style="margin-top: 2rem; width: 100%;">Confirmar Reserva</button>
                </form>
            </div>
        </section>

        <!-- Sección Hacer Pedidos -->
        <section class="content-section hidden" id="pedidos" style="position: relative;">
            <div class="section-header">
                <h2 class="section-title">Hacer Pedidos</h2>
            </div>

            <!-- Vista Normal de Pedidos (Bloqueada hasta autenticar) -->
            <div id="vista-pedidos-normal" style="opacity: 0.4; pointer-events: none; display: flex; gap: 2rem; transition: opacity 0.3s ease; filter: blur(3px);">
                <!-- Lista de Platos -->
                <div style="flex: 2; display: flex; flex-direction: column; height: calc(100vh - 200px);">
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <input type="text" id="buscar_plato" class="form-control premium-search" placeholder="Buscar plato por nombre..." onkeyup="filtrarPlatosPedido()">
                    </div>
                    <div class="menu-categories hide-scrollbar" style="margin-bottom: 1.5rem; justify-content: flex-start; overflow-x: auto; white-space: nowrap; padding-bottom: 5px;">
                        <button class="menu-cat-btn active" data-pedidos-cat="all" onclick="filtrarCatPedido('all', this)">Todos</button>
                        @foreach ($categorias as $categoria)
                            <button class="menu-cat-btn" data-pedidos-cat="{{ $categoria->id }}" onclick="filtrarCatPedido('{{ $categoria->id }}', this)">{{ $categoria->nombre }}</button>
                        @endforeach
                    </div>
                    <div class="pedidos-container hide-scrollbar" id="contenedor-platos-pedido" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; overflow-y: auto; padding-right: 10px; flex-grow: 1;">
                        @foreach ($categorias as $categoria)
                            @foreach ($categoria->platos as $plato)
                                <div class="pedido-card plato-para-pedir" data-cat="{{ $categoria->id }}" data-nombre="{{ strtolower($plato->nombre) }}" style="flex-direction: column; text-align: center; padding: 1rem; align-items: center; justify-content: space-between;">
                                    <img src="{{ $plato->imagen ?? 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=400&q=80' }}" alt="{{ $plato->nombre }}" style="width: 100%; height: 120px; object-fit: cover; border-radius: 8px; margin-bottom: 0.5rem;">
                                    <h4 style="margin-bottom: 0.5rem; font-size: 1rem;">{{ $plato->nombre }}</h4>
                                    <p style="color: var(--primary); font-weight: bold; font-size: 1.1rem; margin-bottom: 0.5rem;">${{ number_format($plato->precio, 2) }}</p>
                                    <button class="btn-primary add-to-cart-btn" style="padding: 0.5rem; width: 100%; margin-top: auto;" onclick="agregarAlCarrito({{ $plato->id }}, '{{ addslashes($plato->nombre) }}', {{ $plato->precio }})">Añadir al Pedido</button>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <!-- Carrito Premium -->
                <div class="carrito-panel">
                    <div class="carrito-header">
                        <h3>Tu Pedido</h3>
                    </div>
                    
                    <div id="carrito-items" class="carrito-items hide-scrollbar">
                        <div id="carrito-vacio" class="carrito-vacio">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem; color: rgba(255,255,255,0.2);"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                            <p>El carrito está vacío</p>
                            <p style="font-size: 0.8rem; margin-top: 0.5rem;">Añade platos para comenzar</p>
                        </div>
                    </div>
                    
                    <div class="carrito-footer">
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label style="font-size: 0.85rem;">Método de Pago</label>
                            <select id="metodo_pago_pedido" class="form-control" style="padding: 0.5rem; font-size: 0.9rem;">
                                @foreach ($metodos_pago ?? [] as $metodo)
                                    <option value="{{ $metodo->id }}">{{ $metodo->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="cart-total">
                            <span>Total:</span>
                            <span id="carrito-total-precio" style="color: var(--primary); font-size: 1.5rem;">$0.00</span>
                        </div>
                        <button class="btn-primary btn-confirmar-pedido" onclick="confirmarPedidoBD()" id="btn-confirmar-pedido" disabled>Confirmar Pedido</button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Sección Historial -->
        <section class="content-section hidden" id="historial">
            <div class="section-header">
                <h2 class="section-title">Mi Historial</h2>
            </div>
            <div class="table-container">
                <table class="styled-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tipo</th>
                            <th>Detalle</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>001</td>
                            <td><span class="badge badge-pedido">Pedido</span></td>
                            <td>Hamburguesa Clásica x2, Mojito x1</td>
                            <td>20/05/2026</td>
                            <td><span class="status status-completado">Completado</span></td>
                            <td class="action-cell">
                                <button class="action-btn edit-btn" onclick="openModal('modal-pedido')" title="Editar Pedido">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button class="action-btn download-btn" onclick="downloadPDF()" title="Descargar Comprobante">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>002</td>
                            <td><span class="badge badge-reserva">Reserva</span></td>
                            <td>Mesa para 4 - Terraza</td>
                            <td>18/05/2026</td>
                            <td><span class="status status-completado">Completado</span></td>
                            <td class="action-cell">
                                <button class="action-btn edit-btn" onclick="openModal('modal-reserva')" title="Editar Reserva">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button class="action-btn download-btn" onclick="downloadPDF()" title="Descargar Comprobante">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>003</td>
                            <td><span class="badge badge-pedido">Pedido</span></td>
                            <td>Asado de Tira x1, Risotto x1</td>
                            <td>15/05/2026</td>
                            <td><span class="status status-pendiente">Pendiente</span></td>
                            <td class="action-cell">
                                <button class="action-btn edit-btn" onclick="openModal('modal-pedido')" title="Editar Pedido">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                                <button class="action-btn download-btn" onclick="downloadPDF()" title="Descargar Comprobante">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </button>
                            </td>
                        </tr>
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
                <form class="styled-form">
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
                        <p id="conf-codigo" style="font-size: 1.5rem; font-weight: bold; color: var(--primary); letter-spacing: 2px; margin: 0;"></p>
                    </div>
                </div>
                
                <p style="font-size: 0.85rem; color: #aaa; margin-bottom: 1.5rem;">Guarda este código para realizar pedidos desde tu mesa o en la sección "Con Reserva".</p>
                <button type="button" class="btn-primary" style="width: 100%;" onclick="closeModal('modal-reserva-confirmacion')">Aceptar y Cerrar</button>
            </div>
        </div>

        <div class="modal-overlay hidden" id="modal-reserva">
            <div class="modal-content">
                <button class="close-modal" onclick="closeModal('modal-reserva')">&times;</button>
                <h3 class="modal-title">Editar Reserva</h3>
                <form class="styled-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Fecha</label>
                            <input type="date" class="form-control" value="2026-05-18">
                        </div>
                        <div class="form-group">
                            <label>Hora</label>
                            <input type="time" class="form-control" value="19:30">
                        </div>
                    </div>
                    <button type="button" class="btn-primary" onclick="closeModal('modal-reserva')">Guardar Cambios</button>
                </form>
            </div>
        </div>

        <div class="modal-overlay hidden" id="modal-pedido">
            <div class="modal-content">
                <button class="close-modal" onclick="closeModal('modal-pedido')">&times;</button>
                <h3 class="modal-title">Editar Pedido</h3>
                <div class="pedidos-container" style="display: block; max-height: 250px; overflow-y: auto;">
                    <div class="pedido-card" style="margin-bottom: 1rem; background: var(--bg-dark);">
                        <div class="pedido-info">
                            <h3>Hamburguesa Clásica</h3>
                            <div class="quantity-control">
                                <button type="button" class="qty-btn minus">−</button>
                                <span class="qty-value">2</span>
                                <button type="button" class="qty-btn plus">+</button>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-primary" onclick="closeModal('modal-pedido')" style="margin-top: 1rem;">Actualizar Cantidades</button>
            </div>
        <div class="modal-overlay hidden" id="modal-seleccion-pedido" style="background: rgba(0, 0, 0, 0.8); backdrop-filter: blur(10px);">
            <div class="modal-content" style="max-width: 600px; padding: 3rem;">
                <h3 class="modal-title" style="text-align: center; font-size: 1.8rem; margin-bottom: 2rem;">¿Cómo deseas hacer tu pedido?</h3>
                
                <!-- Botones Iniciales -->
                <div id="modal-seleccion-botones" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="dashboard-card" style="text-align: center; border-color: var(--primary);" onclick="mostrarFormularioPedido('con_reserva')">
                        <div class="card-icon" style="margin: 0 auto 1rem;"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div>
                        <h3>Con Reserva</h3>
                        <p style="font-size: 0.85rem;">Ya tengo una mesa reservada y código de confirmación.</p>
                    </div>
                    <div class="dashboard-card" style="text-align: center;" onclick="mostrarFormularioPedido('sin_reserva')">
                        <div class="card-icon" style="margin: 0 auto 1rem;"><svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg></div>
                        <h3>Sin Reserva</h3>
                        <p style="font-size: 0.85rem;">Estoy en el local y quiero pedir directamente.</p>
                    </div>
                </div>

                <!-- Formulario Con Reserva -->
                <div id="modal-form-con-reserva" style="display: none;">
                    <button class="back-btn" style="margin-bottom: 1.5rem;" onclick="volverSeleccionPedido()">← Volver</button>
                    <p style="color: #aaa; margin-bottom: 1.5rem; text-align: center;">Ingresa los datos de tu reserva para continuar.</p>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>Mesa Asignada</label>
                        <input type="number" id="auth_mesa" class="form-control" style="font-size: 1.2rem; padding: 1rem;" min="1" max="20" placeholder="Ej: 5">
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>Código de Referencia</label>
                        <input type="text" id="auth_codigo" class="form-control" style="font-size: 1.2rem; padding: 1rem; letter-spacing: 2px; text-transform: uppercase;" placeholder="Ej: A1B2C3D4">
                    </div>
                    <button class="btn-primary" style="width: 100%; font-size: 1.1rem; padding: 1rem;" onclick="verificarReserva()">Validar y Continuar</button>
                    <p id="auth-error" style="color: #e74c3c; margin-top: 1rem; font-size: 0.9rem; text-align: center; display: none;"></p>
                </div>

                <!-- Formulario Sin Reserva -->
                <div id="modal-form-sin-reserva" style="display: none;">
                    <button class="back-btn" style="margin-bottom: 1.5rem;" onclick="volverSeleccionPedido()">← Volver</button>
                    <p style="color: #aaa; margin-bottom: 1.5rem; text-align: center;">Selecciona la mesa donde te encuentras.</p>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>Mesa actual</label>
                        <input type="number" id="temp_mesa" class="form-control" style="font-size: 1.2rem; padding: 1rem;" min="1" max="20" placeholder="Ej: 5">
                    </div>
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label>Zona</label>
                        <select id="temp_zona" class="form-control" style="font-size: 1.2rem; padding: 1rem;">
                            <option value="interior">Interior</option>
                            <option value="terraza">Terraza</option>
                            <option value="privado">Salón Privado</option>
                        </select>
                    </div>
                    <button class="btn-primary" style="width: 100%; font-size: 1.1rem; padding: 1rem;" onclick="continuarSinReserva()">Iniciar Pedido</button>
                    <p id="temp-error" style="color: #e74c3c; margin-top: 1rem; font-size: 0.9rem; text-align: center; display: none;"></p>
                </div>
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
    <script src="{{ asset('js/ScriptHome2.js') }}"></script>
</body>
</html>
