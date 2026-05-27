<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Panel - Sabor & Tradición</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
            <div class="header-right">
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
                                <button class="btn btn-primary add-to-cart-btn" style="margin-top: 10px; width: 100%;">Agregar al carrito</button>
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
                <form id="reservaForm" class="styled-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha">Fecha</label>
                            <input type="date" id="fecha" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="hora">Hora</label>
                            <input type="time" id="hora" class="form-control">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="personas">Número de Personas</label>
                            <select id="personas" class="form-control">
                                <option value="">Seleccione...</option>
                                <option value="1">1 persona</option>
                                <option value="2">2 personas</option>
                                <option value="3">3 personas</option>
                                <option value="4">4 personas</option>
                                <option value="5">5 personas</option>
                                <option value="6">6+ personas</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="zona">Zona</label>
                            <select id="zona" class="form-control">
                                <option value="">Seleccione...</option>
                                <option value="interior">Interior</option>
                                <option value="terraza">Terraza</option>
                                <option value="privado">Salón Privado</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="notas">Notas adicionales</label>
                        <textarea id="notas" class="form-control" rows="3" placeholder="Celebración especial, alergias, etc."></textarea>
                    </div>
                    
                    <div class="preorder-section" style="margin-top: 2rem;">
                        <h3 style="margin-bottom: 1rem; color: var(--primary);">Pre-ordenar Platos (Opcional)</h3>
                        <div class="preorder-grid">
                            @foreach ($categorias as $categoria)
                                @foreach ($categoria->platos as $plato)
                                    <div class="preorder-item">
                                        <div class="preorder-info">
                                            <h4>{{ $plato->nombre }}</h4>
                                            <span style="color: var(--primary);">${{ number_format($plato->precio, 2) }}</span>
                                        </div>
                                        <div class="quantity-control">
                                            <button type="button" class="qty-btn minus">−</button>
                                            <span class="qty-value">0</span>
                                            <button type="button" class="qty-btn plus">+</button>
                                        </div>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" class="btn-primary" style="margin-top: 2rem;">Confirmar Reserva</button>
                </form>
            </div>
        </section>

        <!-- Sección Hacer Pedidos -->
        <section class="content-section hidden" id="pedidos">
            <div class="section-header">
                <h2 class="section-title">Hacer Pedidos</h2>
            </div>
            <div class="pedidos-container">
                <div class="pedido-card">
                    <img src="https://images.unsplash.com/photo-1550547660-d9450f859349?w=300&q=80" alt="Hamburguesa">
                    <div class="pedido-info">
                        <h3>Hamburguesa Clásica</h3>
                        <p class="pedido-price">$14.00</p>
                        <div class="quantity-control">
                            <button class="qty-btn">−</button>
                            <span class="qty-value">0</span>
                            <button class="qty-btn">+</button>
                        </div>
                    </div>
                </div>
                <div class="pedido-card">
                    <img src="https://images.unsplash.com/photo-1544025162-811114215b3e?w=300&q=80" alt="Asado">
                    <div class="pedido-info">
                        <h3>Asado de Tira</h3>
                        <p class="pedido-price">$32.00</p>
                        <div class="quantity-control">
                            <button class="qty-btn">−</button>
                            <span class="qty-value">0</span>
                            <button class="qty-btn">+</button>
                        </div>
                    </div>
                </div>
                <div class="pedido-card">
                    <img src="https://images.unsplash.com/photo-1563379926898-05f4575a45d8?w=300&q=80" alt="Risotto">
                    <div class="pedido-info">
                        <h3>Risotto del Mar</h3>
                        <p class="pedido-price">$28.00</p>
                        <div class="quantity-control">
                            <button class="qty-btn">−</button>
                            <span class="qty-value">0</span>
                            <button class="qty-btn">+</button>
                        </div>
                    </div>
                </div>
                <div class="pedido-card">
                    <img src="https://images.unsplash.com/photo-1624353365286-3f8d62daad51?w=300&q=80" alt="Postre">
                    <div class="pedido-info">
                        <h3>Volcán de Cacao</h3>
                        <p class="pedido-price">$10.00</p>
                        <div class="quantity-control">
                            <button class="qty-btn">−</button>
                            <span class="qty-value">0</span>
                            <button class="qty-btn">+</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cart-summary">
                <div class="cart-total">
                    <span>Total:</span>
                    <span class="total-price">$0.00</span>
                </div>
                <button class="btn-primary">Confirmar Pedido</button>
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

        <!-- Modales de Edición -->
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
        </div>

    </main>

    <script src="{{ asset('js/ScriptHome2.js') }}"></script>
</body>
</html>
