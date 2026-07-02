@extends('admin.layout')

@section('title', 'Pedidos')

@section('content')
    <div class="summary-grid">
        <div class="summary-card">
            <span class="summary-label">Pedidos totales</span>
            <strong>{{ $pedidoCount }}</strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Última semana</span>
            <strong>{{ $weeklyTotal }}</strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Último mes</span>
            <strong>{{ $monthlyTotal }}</strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Clientes únicos</span>
            <strong>{{ $uniqueClients }}</strong>
        </div>
    </div>

    <section class="admin-section">
        <h2>Resumen de pedidos</h2>
        <p>Visualiza la evolución semanal y mensual de los pedidos.</p>
        <div class="dashboard-grid">
            <div class="chart-card">
                <h3>Pedidos por semana</h3>
                <canvas id="weeklyPedidoChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Pedidos por mes</h3>
                <canvas id="monthlyPedidoChart"></canvas>
            </div>
        </div>
    </section>

    <section class="admin-section">
        <h2>Pedidos recientes</h2>
        <p>Edita, cambia el estado o elimina pedidos directamente desde aquí.</p>
        <div class="table-container">
            <table class="styled-table admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pedidos as $pedido)
                        <tr>
                            <td>#{{ $pedido->id }}</td>
                            <td>{{ $pedido->user?->name ?? 'Cliente eliminado' }}</td>
                            <td>{{ $pedido->created_at->format('d/m/Y H:i') }}</td>
                            <td>${{ number_format($pedido->total, 2) }}</td>
                            <td><span class="tag-status tag-{{ $pedido->estado }}">{{ ucfirst(str_replace('_', ' ', $pedido->estado)) }}</span></td>
                            <td class="table-actions">
                                <button type="button" class="btn btn-secondary btn-icon-only edit-pedido-btn" data-pedido-id="{{ $pedido->id }}" title="Editar pedido">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <form action="{{ route('admin.pedidos.destroy', $pedido->id) }}" method="POST" style="display:inline-block;" class="form-delete-pedido" data-nombre="Pedido #{{ $pedido->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-icon-only btn-confirm-delete" title="Eliminar pedido">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No hay pedidos registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $pedidos->links('admin.partials.pagination') }}
        </div>
    </section>

    <div class="modal-overlay hidden" id="modal-editar-pedido-admin">
        <div class="modal-content" style="max-width: 1200px; width: 95%; max-height: 95vh; overflow-y: hidden; padding: 0;">
            <div style="display: flex; height: 85vh; overflow: hidden; border-radius: 16px;">
                <!-- Columna Izquierda: Menú -->
                <div style="flex: 1; padding: 2rem; overflow-y: auto; background: var(--bg-sidebar);">
                    <button class="close-modal" onclick="closeModalAdmin('modal-editar-pedido-admin')" style="z-index: 10;">&times;</button>
                    <h3 class="modal-title" style="margin-bottom: 1rem;">Menú</h3>
                    
                    <div class="menu-filters-modern" style="display: flex; gap: 1rem; margin-bottom: 1.5rem; align-items: center; flex-wrap: wrap;">
                        <div class="search-bar-modern" style="position: relative; flex: 1; min-width: 250px;">
                            <svg style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #aaa;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                            <input type="text" id="admin-buscador-platos" placeholder="Buscar platos..." onkeyup="renderizarPlatosGrid()" style="width: 100%; padding: 0.8rem 1rem 0.8rem 2.8rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 25px; color: #f5f5f5; outline: none; transition: border-color 0.3s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
                        </div>
                        <div class="custom-select-wrapper" style="flex: 2; min-width: 300px; position: relative;">
                            <select id="edit-pedido-categorias" onchange="cambiarCategoriaAdmin(this.value)" style="width: 100%; padding: 0.8rem 1rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 25px; color: #f5f5f5; outline: none; appearance: none; cursor: pointer; transition: border-color 0.3s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
                                <!-- Opciones dinámicas -->
                            </select>
                            <svg style="position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: #aaa;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                    </div>
                    
                    <div class="pedidos-grid-container" id="edit-pedido-platos-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem;">
                        <!-- Los platos se cargarán dinámicamente -->
                    </div>
                </div>
                
                <!-- Columna Derecha: Voucher / Carrito -->
                <div style="width: 350px; background: rgba(10, 10, 10, 0.95); border-left: 1px solid rgba(255,255,255,0.05); padding: 2rem; display: flex; flex-direction: column;">
                    <h3 class="modal-title" style="margin-bottom: 1.5rem; font-size: 1.4rem;">Pedido actual</h3>
                    <input type="hidden" id="edit-pedido-id">
                    
                    <div class="form-group">
                        <label for="edit-pedido-user" style="color: #aaa; font-size: 0.9rem;">Cliente</label>
                        <select id="edit-pedido-user" class="form-control" style="margin-bottom: 0.5rem; width: 100%;">
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} {{ $user->lastname }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="edit-pedido-estado" style="color: #aaa; font-size: 0.9rem;">Estado</label>
                        <select id="edit-pedido-estado" class="form-control" style="margin-bottom: 1rem; width: 100%;">
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso">En Proceso</option>
                            <option value="completado">Completado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    
                    <div id="edit-pedido-detalles" style="flex: 1; overflow-y: auto; margin-bottom: 1.5rem;">
                        <!-- Detalles dinámicos -->
                    </div>
                    
                    <div style="background: rgba(255,255,255,0.03); padding: 1.5rem; border-radius: 12px; margin-top: auto;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                            <span style="color: #aaa;">Subtotal</span>
                            <strong style="color: var(--primary); font-size: 1.2rem;">$<span id="edit-pedido-total">0.00</span></strong>
                        </div>
                        <button type="button" class="btn btn-primary" style="width: 100%; padding: 1rem; font-size: 1.1rem; border-radius: 12px;" onclick="guardarEdicionPedidoAdminConConfirmacion()"><i class="fa-solid fa-floppy-disk"></i> Guardar Cambios</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')

    <script>
        let platosCatalogo = [];
        let detallesEdicion = {};

        function closeModalAdmin(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        function abrirModal(modalId) {
            document.getElementById(modalId).classList.remove('hidden');
        }

        document.querySelectorAll('.edit-pedido-btn').forEach(button => {
            button.addEventListener('click', function() {
                const pedidoId = this.dataset.pedidoId;
                cargarPedidoParaEdicion(pedidoId);
            });
        });

        // Confirmación eliminar
        document.querySelectorAll('.btn-confirm-delete').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const form = this.closest('form');
                const nombre = form.dataset.nombre || 'este pedido';
                Swal.fire({
                    title: '¿Deseas eliminar ' + nombre + '?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74c3c',
                    cancelButtonColor: '#555',
                    confirmButtonText: '<i class="fa-solid fa-trash"></i> Sí, eliminar',
                    cancelButtonText: '<i class="fa-solid fa-xmark"></i> Cancelar',
                    background: '#1a1a1a',
                    color: '#f5f5f5',
                    customClass: { popup: 'swal-on-top' }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        mostrarCarga('Eliminando pedido...');
                        setTimeout(function() { form.submit(); }, 600);
                    }
                });
            });
        });

        function guardarEdicionPedidoAdminConConfirmacion() {
            document.getElementById('modal-editar-pedido-admin').classList.add('hidden');
            Swal.fire({
                title: '¿Guardar los cambios?',
                text: 'El pedido será actualizado.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#c29545',
                cancelButtonColor: '#555',
                confirmButtonText: '<i class="fa-solid fa-floppy-disk"></i> Guardar',
                cancelButtonText: '<i class="fa-solid fa-xmark"></i> Cancelar',
                background: '#1a1a1a',
                color: '#f5f5f5',
                customClass: { popup: 'swal-on-top' }
            }).then(function(result) {
                if (result.isConfirmed) {
                    mostrarCarga('Guardando cambios...');
                    setTimeout(function() { guardarEdicionPedidoAdmin(); }, 600);
                } else {
                    document.getElementById('modal-editar-pedido-admin').classList.remove('hidden');
                }
            });
        }

        function cargarPedidoParaEdicion(pedidoId) {
            document.getElementById('edit-pedido-id').value = pedidoId;
            
            fetch(`/admin/pedidos/${pedidoId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        detallesEdicion = {};
                        document.getElementById('edit-pedido-user').value = data.pedido.user_id;
                        document.getElementById('edit-pedido-estado').value = data.pedido.estado;
                        data.pedido.detalles.forEach(det => {
                            detallesEdicion[det.plato_id] = det.cantidad;
                        });
                        
                        if (!platosCatalogo || platosCatalogo.length === 0) {
                            cargarPlatosDisponibles(() => {
                                renderizarDetalles();
                                abrirModal('modal-editar-pedido-admin');
                            });
                        } else {
                            renderizarDetalles();
                            abrirModal('modal-editar-pedido-admin');
                        }
                    }
                });
        }

        let categoriaActualAdmin = 'todos';

        function cargarPlatosDisponibles(callback = null) {
            fetch('/admin/platos-all')
                .then(res => res.json())
                .then(data => {
                    platosCatalogo = data;
                    renderizarCategoriasAdmin();
                    renderizarPlatosGrid();
                    if (callback) callback();
                });
        }

        function renderizarCategoriasAdmin() {
            const select = document.getElementById('edit-pedido-categorias');
            select.innerHTML = '';
            
            const categorias = ['todos', ...new Set(platosCatalogo.map(p => p.categoria || 'Sin Categoría'))];
            
            categorias.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat;
                option.textContent = cat === 'todos' ? 'Todas las categorías' : cat.charAt(0).toUpperCase() + cat.slice(1);
                if (categoriaActualAdmin === cat) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        window.cambiarCategoriaAdmin = function(cat) {
            categoriaActualAdmin = cat;
            renderizarPlatosGrid();
        };

        function renderizarPlatosGrid() {
            const grid = document.getElementById('edit-pedido-platos-grid');
            grid.innerHTML = '';
            
            const searchTerm = (document.getElementById('admin-buscador-platos')?.value || '').toLowerCase();

            const platosFiltrados = platosCatalogo.filter(p => {
                const matchCategoria = (categoriaActualAdmin === 'todos' || p.categoria === categoriaActualAdmin);
                const matchSearch = p.nombre.toLowerCase().includes(searchTerm);
                return matchCategoria && matchSearch;
            });
            
            platosFiltrados.forEach(plato => {
                const card = document.createElement('div');
                card.className = 'pedido-card-modern plato-para-pedir ' + (plato.estado === 'agotado' ? 'agotado' : '');
                
                let tag = '';
                if (plato.estado === 'agotado') {
                    tag = '<div class="tag-agotado-float">AGOTADO</div>';
                    card.style.opacity = '0.5';
                    card.style.pointerEvents = 'none';
                    card.style.position = 'relative';
                } else if (plato.estado === 'disponible') {
                    tag = '<div class="tag-disponible-float">Disponible</div>';
                    card.style.position = 'relative';
                }

                const imgUrl = plato.imagen || 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=400&q=80';

                card.innerHTML = `
                    ${tag}
                    <div class="img-wrapper" style="height: 120px; overflow: hidden; border-radius: 12px 12px 0 0;">
                        <img src="${imgUrl}" alt="${plato.nombre}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s;">
                    </div>
                    <div class="card-content" style="padding: 1rem; position: relative;">
                        <h4 style="font-size: 1rem; margin-bottom: 0.5rem; font-weight: 600;">${plato.nombre}</h4>
                        <p class="price" style="color: var(--primary); font-weight: 700; font-size: 1.1rem; margin-bottom: 0;">$${Number(plato.precio).toFixed(2)}</p>
                        <button class="btn-add-modern" title="Añadir" style="position: absolute; right: 1rem; bottom: 1rem; width: 35px; height: 35px; border-radius: 50%; background: transparent; border: 1px solid var(--primary); color: var(--primary); display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='var(--primary)'; this.style.color='#000';" onmouseout="this.style.background='transparent'; this.style.color='var(--primary)';">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        </button>
                    </div>
                `;
                
                // Aplicar estilo de tarjeta general
                card.style.background = 'rgba(255,255,255,0.03)';
                card.style.border = '1px solid rgba(255,255,255,0.08)';
                card.style.borderRadius = '12px';
                card.style.cursor = 'pointer';
                card.style.transition = 'all 0.2s';
                card.style.display = 'flex';
                card.style.flexDirection = 'column';

                card.addEventListener('mouseenter', () => {
                    card.style.transform = 'translateY(-3px)';
                    card.style.borderColor = 'rgba(194,149,69,0.5)';
                    card.style.background = 'rgba(194,149,69,0.1)';
                    const img = card.querySelector('img');
                    if(img) img.style.transform = 'scale(1.05)';
                });
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'translateY(0)';
                    card.style.borderColor = 'rgba(255,255,255,0.08)';
                    card.style.background = 'rgba(255,255,255,0.03)';
                    const img = card.querySelector('img');
                    if(img) img.style.transform = 'scale(1)';
                });

                card.addEventListener('click', () => {
                    if (plato.estado !== 'agotado') {
                        agregarOAumentarPlato(plato);
                    }
                });
                grid.appendChild(card);
            });
        }

        function agregarOAumentarPlato(plato) {
            if (!detallesEdicion[plato.id]) {
                detallesEdicion[plato.id] = 0;
            }
            detallesEdicion[plato.id]++;
            renderizarDetalles();
        }

        function renderizarDetalles() {
            const container = document.getElementById('edit-pedido-detalles');
            container.innerHTML = '';
            
            let total = 0;
            
            Object.keys(detallesEdicion).forEach(platoId => {
                const plato = platosCatalogo.find(p => p.id == platoId);
                if (!plato) return;
                
                const cantidad = detallesEdicion[platoId];
                if (cantidad <= 0) {
                    delete detallesEdicion[platoId];
                    return;
                }
                
                const subtotal = plato.precio * cantidad;
                total += subtotal;
                
                const card = document.createElement('div');
                card.className = 'detalle-card';
                card.innerHTML = `
                    <button class="btn-remove" onclick="eliminarPlato(${platoId})">×</button>
                    <div class="detalle-header">
                        <div class="detalle-nombre">${plato.nombre}</div>
                    </div>
                    <div style="color: #aaa; font-size: 0.9rem; margin-bottom: 0.5rem;">$${Number(plato.precio).toFixed(2)}</div>
                    <div class="detalle-controls">
                        <button class="btn-cantidad" onclick="disminuirPlato(${platoId})">-</button>
                        <input type="number" class="cantidad-input" value="${cantidad}" min="1" onchange="cambiarCantidad(${platoId}, this.value)">
                        <button class="btn-cantidad" onclick="aumentarPlato(${platoId})">+</button>
                    </div>
                    <div style="margin-top: 0.5rem; color: var(--primary); font-weight: 700;">$${Number(subtotal).toFixed(2)}</div>
                `;
                container.appendChild(card);
            });
            
            document.getElementById('edit-pedido-total').textContent = Number(total).toFixed(2);
        }

        function aumentarPlato(platoId) {
            detallesEdicion[platoId]++;
            renderizarDetalles();
        }

        function disminuirPlato(platoId) {
            if (detallesEdicion[platoId] > 1) {
                detallesEdicion[platoId]--;
            } else {
                delete detallesEdicion[platoId];
            }
            renderizarDetalles();
        }

        function cambiarCantidad(platoId, cantidad) {
            cantidad = parseInt(cantidad);
            if (cantidad > 0) {
                detallesEdicion[platoId] = cantidad;
            } else {
                delete detallesEdicion[platoId];
            }
            renderizarDetalles();
        }

        function eliminarPlato(platoId) {
            delete detallesEdicion[platoId];
            renderizarDetalles();
        }

        function guardarEdicionPedidoAdmin() {
            const pedidoId = document.getElementById('edit-pedido-id').value;
            const userId = document.getElementById('edit-pedido-user').value;
            const estado = document.getElementById('edit-pedido-estado').value;
            const detalles = Object.keys(detallesEdicion).map(platoId => ({
                plato_id: parseInt(platoId),
                cantidad: detallesEdicion[platoId]
            }));

            fetch(`/admin/pedidos/${pedidoId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ detalles, user_id: userId, estado: estado })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: '¡Actualizado!',
                        text: 'Pedido actualizado correctamente',
                        icon: 'success',
                        background: '#1a1a1a',
                        color: '#f5f5f5',
                        confirmButtonColor: '#c29545',
                        customClass: { popup: 'swal-on-top' }
                    }).then(() => {
                        closeModalAdmin('modal-editar-pedido-admin');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error al actualizar: ' + (data.message || 'Error desconocido'),
                        icon: 'error',
                        background: '#1a1a1a',
                        color: '#f5f5f5',
                        confirmButtonColor: '#c29545',
                        customClass: { popup: 'swal-on-top' }
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un problema de conexión o del servidor al guardar.',
                    icon: 'error',
                    background: '#1a1a1a',
                    color: '#f5f5f5',
                    confirmButtonColor: '#c29545',
                    customClass: { popup: 'swal-on-top' }
                });
                console.error("Error en guardarEdicionPedidoAdmin:", error);
            });
        }

        const weeklyPedidoChart = document.getElementById('weeklyPedidoChart');
        if (weeklyPedidoChart) {
            new Chart(weeklyPedidoChart, {
                type: 'line',
                data: {
                    labels: {!! json_encode($weeklyLabels) !!},
                    datasets: [{
                        label: 'Pedidos',
                        data: {!! json_encode($weeklyValues) !!},
                        borderColor: '#2ecc71',
                        backgroundColor: 'rgba(46,204,113,0.25)',
                        tension: 0.35,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { labels: { color: '#f2f2f2' } } },
                    scales: {
                        x: { ticks: { color: '#ddd' }, grid: { color: 'rgba(255,255,255,0.08)' } },
                        y: { ticks: { color: '#ddd' }, grid: { color: 'rgba(255,255,255,0.08)' } }
                    }
                }
            });
        }

        const monthlyPedidoChart = document.getElementById('monthlyPedidoChart');
        if (monthlyPedidoChart) {
            new Chart(monthlyPedidoChart, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($monthlyLabels) !!},
                    datasets: [{
                        label: 'Pedidos',
                        data: {!! json_encode($monthlyValues) !!},
                        backgroundColor: 'rgba(194,149,69,0.75)',
                        borderRadius: 12,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { ticks: { color: '#ddd' }, grid: { display: false } },
                        y: { ticks: { color: '#ddd' }, grid: { color: 'rgba(255,255,255,0.08)' } }
                    }
                }
            });
        }
    </script>
@endsection
