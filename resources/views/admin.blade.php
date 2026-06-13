<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - Sabor & Tradición</title>
    <link rel="stylesheet" href="{{ asset('css/home2.css') }}">
    <style>
        body { background: var(--bg-dark); color: var(--text-light); }
        .admin-container { display: flex; min-height: 100vh; }
        .admin-sidebar {
            width: 260px;
            background: var(--bg-sidebar);
            color: var(--text-light);
            padding: 2rem 1.5rem;
            position: fixed;
            height: 100%;
            box-shadow: 2px 0 30px rgba(0,0,0,0.25);
        }
        .admin-sidebar h2 {
            margin-bottom: 1.5rem;
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: var(--primary);
        }
        .admin-sidebar nav a {
            display: block;
            color: var(--text-muted);
            text-decoration: none;
            margin: 12px 0;
            padding: 12px 16px;
            border-radius: 14px;
            transition: all .25s ease;
            font-size: 0.95rem;
        }
        .admin-sidebar nav a:hover,
        .admin-sidebar nav a.active {
            background: rgba(194,149,69,0.12);
            color: var(--text-light);
            border-left: 3px solid var(--primary);
        }
        .admin-sidebar .sidebar-footer {
            position: absolute;
            bottom: 30px;
            left: 20px;
            right: 20px;
        }
        .admin-main {
            margin-left: 260px;
            padding: 2.5rem 3rem;
            width: calc(100% - 260px);
        }
        .admin-section {
            background: var(--bg-card);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.25);
        }
        .admin-section h2 {
            font-family: 'Playfair Display', serif;
            color: var(--text-light);
            margin-bottom: 0.5rem;
            font-size: 1.8rem;
        }
        .admin-section p {
            color: var(--text-muted);
            margin-bottom: 1.5rem;
            line-height: 1.7;
        }
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(180px, 1fr));
            gap: 1.2rem;
            margin-bottom: 1rem;
        }
        .metric-card {
            background: rgba(255,255,255,0.05);
            color: var(--text-light);
            border-radius: 22px;
            padding: 1.6rem;
            border: 1px solid rgba(255,255,255,0.08);
        }
        .metric-card h3 { margin: 0 0 0.8rem; font-size: 0.95rem; color: var(--text-muted); }
        .metric-card strong { font-size: 2rem; display: block; }
        .admin-alert { margin-bottom: 1.5rem; padding: 1rem 1.2rem; border-radius: 16px; }
        .admin-alert.success { background: rgba(46,204,113,0.15); color: var(--success); }
        .admin-alert.error { background: rgba(231,76,60,0.15); color: var(--danger); }
        .table-container { overflow-x: auto; }
        .styled-table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255,255,255,0.03);
            border-radius: 18px;
            overflow: hidden;
        }
        .styled-table thead { background: rgba(194,149,69,0.12); }
        .styled-table th,
        .styled-table td { padding: 1rem 1.2rem; color: var(--text-light); }
        .styled-table th { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.08em; color: var(--primary); }
        .styled-table tbody tr { border-bottom: 1px solid rgba(255,255,255,0.05); }
        .styled-table tbody tr:hover { background: rgba(194,149,69,0.08); }
        .form-control {
            width: 100%;
            padding: 0.95rem 1rem;
            border-radius: 14px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            color: var(--text-light);
            font-size: 0.95rem;
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(194,149,69,0.12);
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.9rem 1.2rem;
            border-radius: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
            border: 1px solid transparent;
            text-decoration: none;
            color: #000;
            min-height: 46px;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary { background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); color: #000; border-color: transparent; }
        .btn-secondary { background: rgba(255,255,255,0.08); color: var(--text-light); border-color: rgba(255,255,255,0.1); }
        .btn-secondary:hover { background: rgba(255,255,255,0.12); }
        .btn-danger { background: rgba(232,76,61,0.15); color: #f5f5f5; border-color: rgba(232,76,61,0.25); }
        .btn-danger:hover { background: rgba(232,76,61,0.22); }
        .small-form { display: flex; flex-wrap: wrap; gap: 0.8rem; align-items: center; }
        .small-form .form-control { flex: 1; min-width: 150px; }
        .tag-status { display: inline-flex; align-items: center; padding: 0.35rem 0.75rem; border-radius: 999px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.04em; }
        .tag-pendiente { background: rgba(243,156,18,0.15); color: var(--warning); }
        .tag-confirmada { background: rgba(46,204,113,0.15); color: var(--success); }
        .tag-cancelada { background: rgba(231,76,60,0.15); color: var(--danger); }
        .tag-en_preparacion { background: rgba(52,152,219,0.15); color: #2a4365; }
        .tag-completado { background: rgba(46,204,113,0.15); color: var(--success); }
        .tag-disponible { background: rgba(46,204,113,0.12); color: var(--success); }
        .tag-agotado { background: rgba(231,76,60,0.12); color: var(--danger); }
        @media (max-width: 1100px) {
            .admin-main { margin-left: 0; width: 100%; padding: 1.5rem; }
            .admin-container { flex-direction: column; }
            .admin-sidebar { position: relative; width: 100%; height: auto; }
            .metrics-grid { grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
            .small-form { flex-direction: column; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h2>Panel Administrador</h2>
            <nav>
                <a href="#dashboard" class="active">Inicio</a>
                <a href="#usuarios">Usuarios</a>
                <a href="#platos">Menú</a>
                <a href="#reservas">Reservas</a>
                <a href="#pedidos">Pedidos</a>
            </nav>
            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                    @csrf
                    <button type="submit" class="btn btn-secondary" style="width:100%; margin-top:1rem;">Cerrar Sesión</button>
                </form>
            </div>
        </aside>

        <main class="admin-main">
            @if(session('success'))
                <div class="admin-alert success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="admin-alert error">{{ session('error') }}</div>
            @endif

            <section id="dashboard" class="admin-section">
                <div style="display:flex; justify-content:space-between; flex-wrap:wrap; gap:12px; align-items:center;">
                    <div>
                        <h2>Bienvenido, {{ Auth::user()->name }}</h2>
                        <p>Desde aquí puedes administrar clientes, platos, reservas y pedidos.</p>
                    </div>
                </div>
                <div class="metrics-grid">
                    <div class="metric-card">
                        <h3>Clientes registrados</h3>
                        <strong>{{ $usuarios->count() }}</strong>
                    </div>
                    <div class="metric-card">
                        <h3>Platos en el menú</h3>
                        <strong>{{ $platos->count() }}</strong>
                    </div>
                    <div class="metric-card">
                        <h3>Reservas activas</h3>
                        <strong>{{ $reservas->count() }}</strong>
                    </div>
                    <div class="metric-card">
                        <h3>Pedidos totales</h3>
                        <strong>{{ $pedidos->count() }}</strong>
                    </div>
                </div>
            </section>

            <section id="usuarios" class="admin-section">
                <h2>Clientes</h2>
                <p>Elimina clientes o envía correos directos desde aquí.</p>
                <div class="table-container">
                    <table class="styled-table admin-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Registrado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($usuarios as $usuario)
                            <tr>
                                <td>{{ $usuario->name }} {{ $usuario->lastname }}</td>
                                <td>{{ $usuario->email }}</td>
                                <td>{{ $usuario->telefono ?? 'N/A' }}</td>
                                <td>{{ $usuario->created_at?->format('d/m/Y') ?? 'N/A' }}</td>
                                <td>
                                    <form action="{{ route('admin.usuarios.destroy', $usuario->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No hay clientes registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>

                <div style="margin-top: 24px;">
                    <h3>Enviar correo a un cliente</h3>
                    <form action="{{ route('admin.usuarios.enviarCorreo', $usuarios->first()?->id ?? 0) }}" method="POST" id="send-email-form" class="admin-form">
                        @csrf
                        <div class="small-form">
                            <select class="form-control" name="usuario_id" id="usuario_id" required>
                                <option value="">Selecciona un cliente</option>
                                @foreach($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}">{{ $usuario->name }} {{ $usuario->lastname }} - {{ $usuario->email }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input class="form-control" type="text" name="asunto" placeholder="Asunto" required>
                        <textarea class="form-control" name="mensaje" placeholder="Mensaje para el cliente" required></textarea>
                        <button type="submit" class="btn btn-primary">Enviar correo</button>
                    </form>
                </div>
            </section>

            <section id="platos" class="admin-section">
                <h2>Menú</h2>
                <p>Agrega, edita precios o elimina platos del menú.</p>
                <div class="table-container">
                    <table class="styled-table admin-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($platos as $plato)
                            <tr>
                                <td>{{ $plato->nombre }}</td>
                                <td>{{ $plato->categoria?->nombre ?? 'Sin categoría' }}</td>
                                <td>${{ number_format($plato->precio, 2) }}</td>
                                <td><span class="tag-status tag-{{ $plato->estado }}">{{ ucfirst(str_replace('_', ' ', $plato->estado)) }}</span></td>
                                <td>
                                    <form action="{{ route('admin.platos.update', $plato->id) }}" method="POST" class="small-form" style="gap:8px;">
                                        @csrf
                                        @method('PUT')
                                        <input class="form-control" type="text" name="nombre" value="{{ $plato->nombre }}" required placeholder="Nombre" style="width: 160px;">
                                        <input class="form-control" type="number" step="0.01" name="precio" value="{{ $plato->precio }}" required placeholder="Precio" style="width: 120px;">
                                        <select class="form-control" name="estado" style="width: 160px;">
                                            @foreach($platoEstados as $estado)
                                                <option value="{{ $estado }}" {{ $plato->estado === $estado ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $estado)) }}</option>
                                            @endforeach
                                        </select>
                                        <select class="form-control" name="categoria_id" style="width: 160px;">
                                            @foreach($categorias as $categoria)
                                                <option value="{{ $categoria->id }}" {{ $plato->categoria_id === $categoria->id ? 'selected' : '' }}>{{ $categoria->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-secondary">Guardar</button>
                                    </form>
                                    <form action="{{ route('admin.platos.destroy', $plato->id) }}" method="POST" style="display:inline-block; margin-top:8px;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No hay platos disponibles.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>

                <div style="margin-top: 24px;">
                    <h3>Agregar nuevo plato</h3>
                    <form action="{{ route('admin.platos.store') }}" method="POST" class="admin-form">
                        @csrf
                        <input class="form-control" type="text" name="nombre" placeholder="Nombre del plato" required>
                        <textarea class="form-control" name="descripcion" placeholder="Descripción del plato"></textarea>
                        <input class="form-control" type="number" step="0.01" name="precio" placeholder="Precio" required>
                        <select class="form-control" name="categoria_id" required>
                            <option value="">Selecciona categoría</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                        <select class="form-control" name="estado" required>
                            <option value="disponible">Disponible</option>
                            <option value="agotado">Agotado</option>
                        </select>
                        <input class="form-control" type="text" name="imagen" placeholder="URL de imagen (opcional)">
                        <button type="submit" class="btn btn-primary">Agregar plato</button>
                    </form>
                </div>
            </section>

            <section id="reservas" class="admin-section">
                <h2>Reservas</h2>
                <p>Controla los estados de las reservas y elimínalas si es necesario.</p>
                <div class="table-container">
                    <table class="styled-table admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Mesa</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservas as $reserva)
                            <tr>
                                <td>{{ $reserva->id }}</td>
                                <td>{{ $reserva->user?->name ?? 'Cliente eliminado' }}</td>
                                <td>{{ $reserva->mesa }} / {{ $reserva->zona }}</td>
                                <td>{{ $reserva->fecha }} {{ $reserva->hora ?? '' }}</td>
                                <td><span class="tag-status tag-{{ $reserva->estado }}">{{ ucfirst(str_replace('_', ' ', $reserva->estado)) }}</span></td>
                                <td>
                                    <form action="{{ route('admin.reservas.update', $reserva->id) }}" method="POST" class="small-form">
                                        @csrf
                                        @method('PUT')
                                        <select class="form-control" name="estado" style="min-width: 160px;">
                                            @foreach($reservaEstados as $estado)
                                                <option value="{{ $estado }}" {{ $reserva->estado === $estado ? 'selected' : '' }}>{{ ucfirst($estado) }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-secondary">Actualizar</button>
                                    </form>
                                    <form action="{{ route('admin.reservas.destroy', $reserva->id) }}" method="POST" style="margin-top:8px;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6">No hay reservas registradas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </section>

            <section id="pedidos" class="admin-section">
                <h2>Pedidos</h2>
                <p>Administra los pedidos, cambia estados y elimina pedidos completados o cancelados.</p>
                <div class="table-container">
                    <table class="styled-table admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Detalle</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pedidos as $pedido)
                            <tr>
                                <td>{{ $pedido->id }}</td>
                                <td>{{ $pedido->user?->name ?? 'Cliente eliminado' }}</td>
                                <td>{{ $pedido->detalles->map(fn($detalle) => $detalle->plato?->nombre . ' x' . $detalle->cantidad)->join(', ') }}</td>
                                <td>${{ number_format($pedido->total, 2) }}</td>
                                <td><span class="tag-status tag-{{ $pedido->estado }}">{{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}</span></td>
                                <td>
                                    <form action="{{ route('admin.pedidos.update', $pedido->id) }}" method="POST" class="small-form">
                                        @csrf
                                        @method('PUT')
                                        <select class="form-control" name="estado" style="min-width: 160px;">
                                            @foreach($pedidoEstados as $estado)
                                                <option value="{{ $estado }}" {{ $pedido->estado === $estado ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $estado)) }}</option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-secondary">Actualizar</button>
                                    </form>
                                    <form action="{{ route('admin.pedidos.destroy', $pedido->id) }}" method="POST" style="margin-top:8px;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6">No hay pedidos registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
            </section>
        </main>
    </div>

    <script>
        document.querySelectorAll('.admin-sidebar nav a').forEach(function(link) {
            link.addEventListener('click', function() {
                document.querySelectorAll('.admin-sidebar nav a').forEach(function(item) {
                    item.classList.remove('active');
                });
                link.classList.add('active');
            });
        });

        const sendEmailForm = document.getElementById('send-email-form');
        if (sendEmailForm) {
            sendEmailForm.addEventListener('submit', function(event) {
                const selectedUsuario = document.getElementById('usuario_id').value;
                if (!selectedUsuario) {
                    event.preventDefault();
                    alert('Selecciona un cliente para enviar el correo.');
                    return;
                }
                sendEmailForm.action = '{{ route('admin.usuarios.enviarCorreo', 0) }}'.replace('/0', '/' + selectedUsuario);
            });
        }
    </script>
</body>
</html>
