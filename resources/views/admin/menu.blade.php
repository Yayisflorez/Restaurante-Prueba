@extends('admin.layout')

@section('title', 'Menú')

@section('content')
    <div class="summary-grid">
        <div class="summary-card">
            <span class="summary-label">Platos totales</span>
            <strong>{{ $platoCount }}</strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Disponibles</span>
            <strong>{{ $availableCount }}</strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Agotados</span>
            <strong>{{ $unavailableCount }}</strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Categorías</span>
            <strong>{{ $categoriaCount }}</strong>
        </div>
    </div>

    <section class="admin-section">
        <h2>Resumen del menú</h2>
        <p>Administra todos los platos, deshabilita los que estén agotados y revisa las estadísticas por categoría.</p>
        <div class="dashboard-grid">
            <div class="chart-card">
                <h3>Platos agregados esta semana</h3>
                <canvas id="weeklyPlatoChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Platos agregados este mes</h3>
                <canvas id="monthlyPlatoChart"></canvas>
            </div>
        </div>
    </section>

    <section class="admin-section">
        <h2>Platos existentes</h2>
        <p>Editar, deshabilitar o eliminar cualquier plato del menú.</p>
        <div class="table-container">
            <table class="styled-table admin-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($platos as $plato)
                        <tr>
                            <td>{{ $plato->nombre }}</td>
                            <td>${{ number_format($plato->precio, 2) }}</td>
                            <td>{{ $plato->categoria?->nombre ?? 'Sin categoría' }}</td>
                            <td><span class="tag-status tag-{{ $plato->estado }}">{{ ucfirst($plato->estado) }}</span></td>
                            <td class="table-actions">
                                <button type="button" class="btn btn-secondary btn-icon-only edit-plato-btn" data-target="edit-plato-{{ $plato->id }}" title="Editar plato">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <form action="{{ route('admin.platos.destroy', $plato->id) }}" method="POST" style="display:inline-block; margin-left:0.5rem;" class="form-delete-plato" data-nombre="{{ $plato->nombre }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-icon-only btn-confirm-delete" title="Eliminar plato">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <tr class="edit-row" id="edit-plato-{{ $plato->id }}" style="display:none;">
                            <td colspan="5">
                                <form action="{{ route('admin.platos.update', $plato->id) }}" method="POST" class="admin-form">
                                    @csrf
                                    @method('PUT')
                                    <div class="grid-2cols" style="gap:1rem;">
                                        <div>
                                            <label>Nombre</label>
                                            <input class="form-control" type="text" name="nombre" value="{{ $plato->nombre }}" required>
                                        </div>
                                        <div>
                                            <label>Precio</label>
                                            <input class="form-control" type="number" step="0.01" name="precio" value="{{ $plato->precio }}" required>
                                        </div>
                                        <div>
                                            <label>Categoría</label>
                                            <select name="categoria_id" class="form-control" required>
                                                @foreach($categorias as $categoria)
                                                    <option value="{{ $categoria->id }}" {{ $plato->categoria_id === $categoria->id ? 'selected' : '' }}>{{ $categoria->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label>Estado</label>
                                            <select name="estado" class="form-control">
                                                <option value="disponible" {{ $plato->estado === 'disponible' ? 'selected' : '' }}>Disponible</option>
                                                <option value="agotado" {{ $plato->estado === 'agotado' ? 'selected' : '' }}>Agotado</option>
                                            </select>
                                        </div>
                                        <div style="grid-column: 1 / -1;">
                                            <label>Descripción</label>
                                            <textarea name="descripcion" class="form-control" rows="3">{{ $plato->descripcion }}</textarea>
                                        </div>
                                    </div>
                                    <div class="button-row" style="margin-top:1rem;">
                                        <button type="button" class="btn btn-primary btn-confirm-save"><i class="fa-solid fa-floppy-disk"></i> Guardar cambios</button>
                                        <button type="button" class="btn btn-secondary cancel-edit" data-target="edit-plato-{{ $plato->id }}">Cancelar</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    @if($platos->isEmpty())
                        <tr><td colspan="5">No hay platos registrados aún.</td></tr>
                    @endif
                </tbody>
            </table>
            {{ $platos->links('admin.partials.pagination') }}
        </div>
    </section>

    <section class="admin-section">
        <h2>Agregar nuevo plato</h2>
        <p>Crea nuevos platos con nombre, precio, descripción y estado.</p>
        <div class="panel-card">
            <form action="{{ route('admin.platos.store') }}" method="POST" class="admin-form">
                @csrf
                <div class="grid-2cols" style="gap:1rem;">
                    <div>
                        <label>Nombre</label>
                        <input class="form-control" type="text" name="nombre" placeholder="Nombre del plato" required>
                    </div>
                    <div>
                        <label>Precio</label>
                        <input class="form-control" type="number" step="0.01" name="precio" placeholder="0.00" required>
                    </div>
                    <div>
                        <label>Categoría</label>
                        <select name="categoria_id" class="form-control" required>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}">{{ $categoria->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label>Estado</label>
                        <select name="estado" class="form-control" required>
                            <option value="disponible">Disponible</option>
                            <option value="agotado">Agotado</option>
                        </select>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <label>Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción del plato"></textarea>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary" style="margin-top:1rem;">Agregar Plato</button>
            </form>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        document.querySelectorAll('.edit-plato-btn').forEach(button => {
            button.addEventListener('click', () => {
                const target = document.getElementById(button.dataset.target);
                if (target) {
                    target.style.display = target.style.display === 'table-row' ? 'none' : 'table-row';
                }
            });
        });

        document.querySelectorAll('.cancel-edit').forEach(button => {
            button.addEventListener('click', () => {
                const target = document.getElementById(button.dataset.target);
                if (target) {
                    target.style.display = 'none';
                }
            });
        });

        // Confirmación eliminar
        document.querySelectorAll('.btn-confirm-delete').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const form = this.closest('form');
                const nombre = form.dataset.nombre || 'este plato';
                Swal.fire({
                    title: '¿Deseas eliminar "' + nombre + '"?',
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
                        mostrarCarga('Eliminando plato...');
                        setTimeout(function() { form.submit(); }, 600);
                    }
                });
            });
        });

        // Confirmación guardar edición
        document.querySelectorAll('.btn-confirm-save').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const form = this.closest('form');
                Swal.fire({
                    title: '¿Guardar los cambios?',
                    text: 'Los datos del plato serán actualizados.',
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
                        setTimeout(function() { form.submit(); }, 600);
                    }
                });
            });
        });

        const weeklyPlatoChart = document.getElementById('weeklyPlatoChart');
        if (weeklyPlatoChart) {
            new Chart(weeklyPlatoChart, {
                type: 'line',
                data: {
                    labels: {!! json_encode($weeklyLabels ?? []) !!},
                    datasets: [{
                        label: 'Platos',
                        data: {!! json_encode($weeklyValues ?? []) !!},
                        borderColor: '#2ecc71',
                        backgroundColor: 'rgba(46,204,113,0.2)',
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

        const monthlyPlatoChart = document.getElementById('monthlyPlatoChart');
        if (monthlyPlatoChart) {
            new Chart(monthlyPlatoChart, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($monthlyLabels ?? []) !!},
                    datasets: [{
                        label: 'Platos',
                        data: {!! json_encode($monthlyValues ?? []) !!},
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
