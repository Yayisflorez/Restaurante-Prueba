@extends('admin.layout')

@section('title', 'Reservas')

@section('content')
    <div class="summary-grid">
        <div class="summary-card">
            <span class="summary-label">Reservas totales</span>
            <strong>{{ $reservaCount }}</strong>
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
        <h2>Resumen de reservas</h2>
        <p>Visualiza la cantidad de reservas semanales y mensuales.</p>
        <div class="dashboard-grid">
            <div class="chart-card">
                <h3>Reservas por semana</h3>
                <canvas id="weeklyReservaChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Reservas por mes</h3>
                <canvas id="monthlyReservaChart"></canvas>
            </div>
        </div>
    </section>

    <section class="admin-section">
        <h2>Reservas recientes</h2>
        <div class="table-container">
            <table class="styled-table admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Personas</th>
                        <th>Mesa</th>
                        <th>Zona</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservas as $reserva)
                        <tr>
                            <td>{{ $reserva->id }}</td>
                            <td>{{ $reserva->user?->name ?? 'Cliente' }}</td>
                            <td>{{ $reserva->fecha }}</td>
                            <td>{{ $reserva->hora }}</td>
                            <td>{{ $reserva->personas }}</td>
                            <td>{{ $reserva->mesa }}</td>
                            <td>{{ $reserva->zona }}</td>
                            <td><span class="tag-status tag-{{ $reserva->estado }}">{{ ucfirst(str_replace('_', ' ', $reserva->estado)) }}</span></td>
                            <td class="table-actions">
                                <button type="button" class="btn btn-secondary btn-icon-only edit-reserva-btn"
                                    data-id="{{ $reserva->id }}"
                                    data-fecha="{{ $reserva->fecha }}"
                                    data-hora="{{ $reserva->hora }}"
                                    data-personas="{{ $reserva->personas }}"
                                    data-zona="{{ $reserva->zona }}"
                                    data-mesa="{{ $reserva->mesa }}"
                                    data-notas="{{ $reserva->notas }}"
                                    data-estado="{{ $reserva->estado }}"
                                    data-userid="{{ $reserva->user_id }}"
                                    title="Editar reserva">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <form action="{{ route('admin.reservas.destroy', $reserva->id) }}" method="POST" style="display:inline-block; margin-left:0.5rem;" class="form-delete-reserva" data-nombre="Reserva #{{ $reserva->id }} de {{ $reserva->user?->name ?? 'Cliente' }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-icon-only btn-confirm-delete-reserva" title="Eliminar reserva">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9">No hay reservas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $reservas->links('admin.partials.pagination') }}
        </div>
    </section>

    <!-- Modal Editar Reserva (Admin) -->
    <div class="modal-overlay hidden" id="modal-admin-editar-reserva">
        <div class="modal-content" style="max-width: 900px; width: 95%; max-height: 92vh; overflow-y: auto;">
            <button class="close-modal" onclick="cerrarModalAdminReserva()">&times;</button>
            <h3 class="modal-title">Editar Reserva</h3>
            <input type="hidden" id="admin-edit-reserva-id">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">
                <!-- Columna Izquierda: Calendario + Hora -->
                <div>
                    <!-- Selector de Fecha -->
                    <div class="date-selector-container" style="margin-bottom: 1.5rem;">
                        <div class="date-selector-header">
                            <h3 class="date-selector-title">Selecciona la Fecha</h3>
                            <div class="date-selector-value" id="admin-edit-selected-date-display">Selecciona una fecha</div>
                        </div>
                        <div class="date-selector-body">
                            <div class="date-nav">
                                <button type="button" class="date-nav-btn" id="admin-edit-prev-month">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                                </button>
                                <span class="date-nav-month" id="admin-edit-current-month-display">Junio 2026</span>
                                <button type="button" class="date-nav-btn" id="admin-edit-next-month">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                                </button>
                            </div>
                            <div class="date-grid-header">
                                <span>Dom</span><span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span>
                            </div>
                            <div class="date-grid" id="admin-edit-date-grid"></div>
                        </div>
                        <input type="hidden" id="admin-edit-fecha">
                    </div>

                    <!-- Selector de Hora -->
                    <div class="time-selector-container">
                        <div class="time-selector-header">
                            <h3 class="time-selector-title">Selecciona la Hora</h3>
                            <div class="time-selector-value" id="admin-edit-selected-time-display">00:00</div>
                        </div>
                        <div class="time-selector-body">
                            <div class="time-display">
                                <div class="time-unit">
                                    <button type="button" class="time-adjust-btn time-up" data-unit="hour" data-ctx="admin-edit">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                                    </button>
                                    <span class="time-value" id="admin-edit-hour-display">12</span>
                                    <button type="button" class="time-adjust-btn time-down" data-unit="hour" data-ctx="admin-edit">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                    </button>
                                </div>
                                <span class="time-separator">:</span>
                                <div class="time-unit">
                                    <button type="button" class="time-adjust-btn time-up" data-unit="minute" data-ctx="admin-edit">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                                    </button>
                                    <span class="time-value" id="admin-edit-minute-display">00</span>
                                    <button type="button" class="time-adjust-btn time-down" data-unit="minute" data-ctx="admin-edit">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                    </button>
                                </div>
                                <div class="time-period">
                                    <button type="button" class="period-btn" data-period="AM" data-ctx="admin-edit">AM</button>
                                    <button type="button" class="period-btn active" data-period="PM" data-ctx="admin-edit">PM</button>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" id="admin-edit-hora">
                    </div>
                </div>

                <!-- Columna Derecha: Datos -->
                <div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display:block; margin-bottom:0.4rem; color:#aaa; font-size:0.9rem;">Cliente</label>
                        <select name="user_id" id="admin-edit-user-id" class="form-control">
                            <option value="">Cliente genérico</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} {{ $user->lastname }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display:block; margin-bottom:0.4rem; color:#aaa; font-size:0.9rem;">Estado</label>
                        <select id="admin-edit-estado" class="form-control">
                            <option value="pendiente">Pendiente</option>
                            <option value="confirmada">Confirmada</option>
                            <option value="cancelada">Cancelada</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display:block; margin-bottom:0.4rem; color:#aaa; font-size:0.9rem;">Personas</label>
                        <input type="number" id="admin-edit-personas" class="form-control" min="1" value="1">
                    </div>

                    <!-- Zona -->
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display:block; margin-bottom:0.4rem; color:#aaa; font-size:0.9rem;">Zona</label>
                        <div class="zona-cards" style="gap: 0.5rem;">
                            <label class="zona-card" style="padding: 0.6rem 0.8rem; font-size: 0.85rem;">
                                <input type="radio" name="admin-edit-zona" value="interior" style="display:none;" onchange="cargarMesasAdminEdit('interior')">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                <span>Interior</span>
                            </label>
                            <label class="zona-card" style="padding: 0.6rem 0.8rem; font-size: 0.85rem;">
                                <input type="radio" name="admin-edit-zona" value="terraza" style="display:none;" onchange="cargarMesasAdminEdit('terraza')">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                                <span>Terraza</span>
                            </label>
                            <label class="zona-card" style="padding: 0.6rem 0.8rem; font-size: 0.85rem;">
                                <input type="radio" name="admin-edit-zona" value="privado" style="display:none;" onchange="cargarMesasAdminEdit('privado')">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                <span>Salón Privado</span>
                            </label>
                        </div>
                    </div>

                    <!-- Mesas -->
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display:block; margin-bottom:0.4rem; color:#aaa; font-size:0.9rem;">Mesas (selección múltiple)</label>
                        <div class="mesa-info" style="margin-bottom: 0.5rem;">
                            <span id="admin-edit-mesas-info">0 mesa(s) seleccionada(s)</span>
                        </div>
                        <div id="admin-edit-leyenda-mesas" style="display: flex; gap: 1rem; font-size: 0.8rem; margin-bottom: 0.5rem; flex-wrap: wrap;">
                            <span style="display:flex; align-items:center; gap:4px;"><span style="display:inline-block; width:14px; height:14px; background: rgba(46,204,113,0.3); border: 1px solid #2ecc71; border-radius:4px;"></span> Disponible</span>
                            <span style="display:flex; align-items:center; gap:4px;"><span style="display:inline-block; width:14px; height:14px; background: rgba(231,76,60,0.3); border: 1px solid #e74c3c; border-radius:4px;"></span> Ocupada</span>
                            <span style="display:flex; align-items:center; gap:4px;"><span style="display:inline-block; width:14px; height:14px; background: var(--primary); border-radius:4px;"></span> Seleccionada</span>
                        </div>
                        <div class="mesa-selector-grid" id="admin-edit-mesa-grid"></div>
                        <input type="hidden" id="admin-edit-mesas">
                    </div>

                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label style="display:block; margin-bottom:0.4rem; color:#aaa; font-size:0.9rem;">Notas</label>
                        <textarea id="admin-edit-notas" class="form-control" rows="2"></textarea>
                    </div>

                    <button type="button" class="btn btn-primary" style="width:100%;" onclick="guardarReservaAdminConConfirmacion()">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <section class="admin-section">
        <h2>Crear nueva reserva</h2>
        <p>Agrega una reserva directamente desde el panel de administración.</p>
        <div class="panel-card">
            <form action="{{ route('admin.reservas.store') }}" method="POST" class="admin-form" id="form-crear-reserva-admin">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; align-items: start;">
                    <!-- Columna izquierda: Calendario + Hora -->
                    <div>
                        <div class="date-selector-container" style="margin-bottom: 1.5rem;">
                            <div class="date-selector-header">
                                <h3 class="date-selector-title">Selecciona la Fecha</h3>
                                <div class="date-selector-value" id="admin-create-selected-date-display">Selecciona una fecha</div>
                            </div>
                            <div class="date-selector-body">
                                <div class="date-nav">
                                    <button type="button" class="date-nav-btn" id="admin-create-prev-month">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                                    </button>
                                    <span class="date-nav-month" id="admin-create-current-month-display">Junio 2026</span>
                                    <button type="button" class="date-nav-btn" id="admin-create-next-month">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                                    </button>
                                </div>
                                <div class="date-grid-header">
                                    <span>Dom</span><span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span>
                                </div>
                                <div class="date-grid" id="admin-create-date-grid"></div>
                            </div>
                            <input type="hidden" id="admin-create-fecha" name="fecha" required>
                        </div>

                        <div class="time-selector-container">
                            <div class="time-selector-header">
                                <h3 class="time-selector-title">Selecciona la Hora</h3>
                                <div class="time-selector-value" id="admin-create-selected-time-display">12:00</div>
                            </div>
                            <div class="time-selector-body">
                                <div class="time-display">
                                    <div class="time-unit">
                                        <button type="button" class="time-adjust-btn time-up" data-unit="hour" data-ctx="admin-create">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                                        </button>
                                        <span class="time-value" id="admin-create-hour-display">12</span>
                                        <button type="button" class="time-adjust-btn time-down" data-unit="hour" data-ctx="admin-create">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                        </button>
                                    </div>
                                    <span class="time-separator">:</span>
                                    <div class="time-unit">
                                        <button type="button" class="time-adjust-btn time-up" data-unit="minute" data-ctx="admin-create">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                                        </button>
                                        <span class="time-value" id="admin-create-minute-display">00</span>
                                        <button type="button" class="time-adjust-btn time-down" data-unit="minute" data-ctx="admin-create">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                                        </button>
                                    </div>
                                    <div class="time-period">
                                        <button type="button" class="period-btn" data-period="AM" data-ctx="admin-create">AM</button>
                                        <button type="button" class="period-btn active" data-period="PM" data-ctx="admin-create">PM</button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" id="admin-create-hora" name="hora" required>
                        </div>
                    </div>

                    <!-- Columna derecha: Datos -->
                    <div>
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label>Cliente</label>
                            <select name="user_id" class="form-control">
                                <option value="">Cliente genérico</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} {{ $user->lastname }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label>Estado</label>
                            <select name="estado" class="form-control" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="confirmada">Confirmada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label>Personas</label>
                            <input class="form-control" type="number" name="personas" min="1" required value="1">
                        </div>

                        <!-- Zona -->
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label>Zona</label>
                            <div class="zona-cards" style="gap: 0.5rem;">
                                <label class="zona-card" style="padding: 0.6rem 0.8rem; font-size: 0.85rem;">
                                    <input type="radio" name="zona" value="interior" required style="display:none;" onchange="cargarMesasAdminCreate('interior')">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                    <span>Interior</span>
                                </label>
                                <label class="zona-card" style="padding: 0.6rem 0.8rem; font-size: 0.85rem;">
                                    <input type="radio" name="zona" value="terraza" required style="display:none;" onchange="cargarMesasAdminCreate('terraza')">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                                    <span>Terraza</span>
                                </label>
                                <label class="zona-card" style="padding: 0.6rem 0.8rem; font-size: 0.85rem;">
                                    <input type="radio" name="zona" value="privado" required style="display:none;" onchange="cargarMesasAdminCreate('privado')">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                    <span>Salón Privado</span>
                                </label>
                            </div>
                        </div>

                        <!-- Mesas -->
                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label>Mesas (selección múltiple)</label>
                            <div class="mesa-info" style="margin-bottom: 0.5rem;">
                                <span id="admin-create-mesas-info">0 mesa(s) seleccionada(s)</span>
                            </div>
                            <div id="admin-create-leyenda-mesas" style="display: flex; gap: 1rem; font-size: 0.8rem; margin-bottom: 0.5rem; flex-wrap: wrap;">
                                <span style="display:flex; align-items:center; gap:4px;"><span style="display:inline-block; width:14px; height:14px; background: rgba(46,204,113,0.3); border: 1px solid #2ecc71; border-radius:4px;"></span> Disponible</span>
                                <span style="display:flex; align-items:center; gap:4px;"><span style="display:inline-block; width:14px; height:14px; background: rgba(231,76,60,0.3); border: 1px solid #e74c3c; border-radius:4px;"></span> Ocupada</span>
                                <span style="display:flex; align-items:center; gap:4px;"><span style="display:inline-block; width:14px; height:14px; background: var(--primary); border-radius:4px;"></span> Seleccionada</span>
                            </div>
                            <div class="mesa-selector-grid" id="admin-create-mesa-grid"></div>
                            <input type="hidden" id="admin-create-mesas" name="mesa" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 1rem;">
                            <label>Notas</label>
                            <textarea name="notas" class="form-control" rows="2"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary" style="margin-top:1rem;" onclick="prepararFormCrearReserva(event)">
                            <i class="fa-solid fa-plus"></i> Crear Reserva
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('scripts')
    <style>
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.7);
            backdrop-filter: blur(6px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.hidden { display: none; }
        .modal-overlay:not(.hidden) { display: flex; }
        .modal-content {
            background: var(--bg-sidebar, #161616);
            padding: 2rem;
            border-radius: 16px;
            position: relative;
            border: 1px solid rgba(194,149,69,0.3);
            box-shadow: 0 15px 50px rgba(0,0,0,0.8);
        }
        .close-modal {
            position: absolute; top: 1rem; right: 1rem;
            background: none; border: none; color: #aaa; font-size: 2rem; cursor: pointer;
        }
        .close-modal:hover { color: var(--primary, #c29545); }
        .mesa-no-disponible {
            background: rgba(231,76,60,0.25) !important;
            border-color: #e74c3c !important;
            color: #e74c3c !important;
            cursor: not-allowed !important;
        }
        .mesa-disponible {
            background: rgba(46,204,113,0.15) !important;
            border-color: #2ecc71 !important;
        }
        .mesa-btn.selected {
            background: var(--primary, #c29545) !important;
            color: #000 !important;
            border-color: var(--primary, #c29545) !important;
            font-weight: 700;
        }
        .mesa-btn {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.15);
            color: #f5f5f5;
            padding: 0.6rem 0.5rem;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s;
            min-width: 44px;
        }
        .mesa-selector-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 0.5rem;
        }
    </style>
    <script>
        // ===========================
        // UTILIDADES COMUNES
        // ===========================
        function adminRange(start, end) {
            return Array.from({ length: end - start + 1 }, (_, i) => start + i);
        }
        const adminZonasMesas = {
            'interior': adminRange(1, 10),
            'terraza': adminRange(11, 20),
            'privado': adminRange(21, 30)
        };
        const adminMonths = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        // ===========================
        // CALENDARIOS ADMIN
        // ===========================
        function initAdminCalendar(prefix, allowPast = true) {
            let currentDate = new Date();
            let selectedDate = null;

            function renderCal() {
                const grid = document.getElementById(prefix + '-date-grid');
                const monthDisplay = document.getElementById(prefix + '-current-month-display');
                const dateDisplay = document.getElementById(prefix + '-selected-date-display');
                if (!grid) return;

                grid.innerHTML = '';
                monthDisplay.textContent = adminMonths[currentDate.getMonth()] + ' ' + currentDate.getFullYear();

                const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
                const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
                const today = new Date(); today.setHours(0,0,0,0);

                for (let i = 0; i < firstDay.getDay(); i++) {
                    const empty = document.createElement('button');
                    empty.disabled = true;
                    grid.appendChild(empty);
                }
                for (let d = 1; d <= lastDay.getDate(); d++) {
                    const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), d);
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = d;
                    if (!allowPast && date < today) btn.disabled = true;
                    if (date.toDateString() === today.toDateString()) btn.classList.add('today');
                    if (selectedDate && date.toDateString() === selectedDate.toDateString()) btn.classList.add('selected');
                    btn.addEventListener('click', () => {
                        selectedDate = date;
                        document.getElementById(prefix + '-fecha').value = date.toISOString().split('T')[0];
                        dateDisplay.textContent = date.toLocaleDateString('es-ES', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
                        renderCal();
                        // Recargar mesas si hay zona seleccionada
                        const zona = document.querySelector('input[name="' + prefix.replace('admin-', 'admin-') + '-zona"]:checked') ||
                                     document.querySelector('#modal-admin-editar-reserva input[name="admin-edit-zona"]:checked') ||
                                     document.querySelector('#form-crear-reserva-admin input[name="zona"]:checked');
                        if (zona) {
                            if (prefix === 'admin-edit') cargarMesasAdminEdit(zona.value);
                            else cargarMesasAdminCreate(zona.value);
                        }
                    });
                    grid.appendChild(btn);
                }
            }

            document.getElementById(prefix + '-prev-month')?.addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() - 1); renderCal();
            });
            document.getElementById(prefix + '-next-month')?.addEventListener('click', () => {
                currentDate.setMonth(currentDate.getMonth() + 1); renderCal();
            });

            renderCal();

            // Exponer función para setear fecha
            window['setAdminCalDate_' + prefix] = function(dateStr) {
                if (!dateStr) return;
                const parts = dateStr.split('-');
                selectedDate = new Date(parseInt(parts[0]), parseInt(parts[1]) - 1, parseInt(parts[2]));
                currentDate = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1);
                document.getElementById(prefix + '-fecha').value = dateStr;
                document.getElementById(prefix + '-selected-date-display').textContent =
                    selectedDate.toLocaleDateString('es-ES', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
                renderCal();
            };
        }

        // ===========================
        // SELECTORES DE HORA ADMIN
        // ===========================
        const adminTimeState = {};

        function initAdminTimeSelector(ctx) {
            adminTimeState[ctx] = { hour: 12, minute: 0, period: 'PM' };

            function update() {
                const s = adminTimeState[ctx];
                document.getElementById(ctx + '-hour-display').textContent = String(s.hour).padStart(2, '0');
                document.getElementById(ctx + '-minute-display').textContent = String(s.minute).padStart(2, '0');
                let h24 = s.hour;
                if (s.period === 'AM' && h24 === 12) h24 = 0;
                else if (s.period === 'PM' && h24 !== 12) h24 += 12;
                const time24 = String(h24).padStart(2,'0') + ':' + String(s.minute).padStart(2,'0');
                document.getElementById(ctx + '-hora').value = time24;
                document.getElementById(ctx + '-selected-time-display').textContent = time24;
            }

            document.querySelectorAll('.time-adjust-btn[data-ctx="' + ctx + '"]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const s = adminTimeState[ctx];
                    const unit = btn.dataset.unit;
                    if (unit === 'hour') {
                        if (btn.classList.contains('time-up')) s.hour = s.hour >= 12 ? 1 : s.hour + 1;
                        else s.hour = s.hour <= 1 ? 12 : s.hour - 1;
                    } else {
                        if (btn.classList.contains('time-up')) s.minute = (s.minute + 1) % 60;
                        else s.minute = (s.minute - 1 + 60) % 60;
                    }
                    update();
                    const zonaEl = ctx === 'admin-edit'
                        ? document.querySelector('#modal-admin-editar-reserva input[name="admin-edit-zona"]:checked')
                        : document.querySelector('#form-crear-reserva-admin input[name="zona"]:checked');
                    if (zonaEl) {
                        if (ctx === 'admin-edit') cargarMesasAdminEdit(zonaEl.value);
                        else cargarMesasAdminCreate(zonaEl.value);
                    }
                });
            });
            document.querySelectorAll('.period-btn[data-ctx="' + ctx + '"]').forEach(btn => {
                btn.addEventListener('click', () => {
                    adminTimeState[ctx].period = btn.dataset.period;
                    document.querySelectorAll('.period-btn[data-ctx="' + ctx + '"]').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    update();
                });
            });

            update();

            window['setAdminTime_' + ctx] = function(horaStr) {
                if (!horaStr) return;
                const parts = horaStr.split(':');
                let h = parseInt(parts[0]);
                const m = parseInt(parts[1]) || 0;
                const period = h >= 12 ? 'PM' : 'AM';
                if (h > 12) h -= 12;
                if (h === 0) h = 12;
                adminTimeState[ctx] = { hour: h, minute: m, period };
                document.querySelectorAll('.period-btn[data-ctx="' + ctx + '"]').forEach(b => {
                    b.classList.toggle('active', b.dataset.period === period);
                });
                update();
            };
        }

        // ===========================
        // MESAS ADMIN - CREAR
        // ===========================
        window.cargarMesasAdminCreate = function(zona) {
            const grid = document.getElementById('admin-create-mesa-grid');
            const fecha = document.getElementById('admin-create-fecha').value;
            const hora = document.getElementById('admin-create-hora').value;
            const mesasZona = adminZonasMesas[zona] || adminRange(1, 10);
            grid.innerHTML = '<p style="color:#aaa; font-size:0.85rem;">Verificando disponibilidad...</p>';

            if (!fecha || !hora) {
                renderMesasBtnsCreate(mesasZona, {});
                return;
            }
            fetch('/reservas/disponibilidad', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ fecha, hora, zona })
            })
            .then(r => r.json())
            .then(data => {
                const map = {};
                if (data.success) data.disponibilidad.forEach(item => { map[item.mesa] = item.disponible; });
                renderMesasBtnsCreate(mesasZona, map);
            })
            .catch(() => renderMesasBtnsCreate(mesasZona, {}));
        };

        function renderMesasBtnsCreate(mesasZona, map) {
            const grid = document.getElementById('admin-create-mesa-grid');
            grid.innerHTML = '';
            mesasZona.forEach(mesa => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'mesa-btn';
                btn.textContent = mesa;
                if (map[mesa] === false) {
                    btn.classList.add('mesa-no-disponible');
                    btn.title = 'Mesa ocupada';
                } else {
                    btn.classList.add('mesa-disponible');
                }
                btn.onclick = function() {
                    if (this.classList.contains('mesa-no-disponible')) {
                        Swal.fire({ icon:'warning', title:'Mesa ocupada', text:'Esta mesa ya está reservada.', background:'#1a1a1a', color:'#fff' });
                        return;
                    }
                    this.classList.toggle('selected');
                    actualizarMesasSeleccionadasCreate();
                };
                grid.appendChild(btn);
            });
        }

        function actualizarMesasSeleccionadasCreate() {
            const selected = [];
            document.querySelectorAll('#admin-create-mesa-grid .mesa-btn.selected').forEach(b => selected.push(b.textContent));
            document.getElementById('admin-create-mesas').value = selected.join(',');
            document.getElementById('admin-create-mesas-info').textContent = selected.length + ' mesa(s) seleccionada(s)';
        }

        window.prepararFormCrearReserva = function(e) {
            const mesa = document.getElementById('admin-create-mesas').value;
            const fecha = document.getElementById('admin-create-fecha').value;
            const hora = document.getElementById('admin-create-hora').value;
            if (!fecha || !hora || !mesa) {
                e.preventDefault();
                Swal.fire({ icon:'warning', title:'Campos incompletos', text:'Por favor selecciona fecha, hora y al menos una mesa.', background:'#1a1a1a', color:'#fff' });
                return false;
            }
            mostrarCarga('Creando reserva...');
        };

        // ===========================
        // MESAS ADMIN - EDITAR
        // ===========================
        window.cargarMesasAdminEdit = function(zona) {
            const grid = document.getElementById('admin-edit-mesa-grid');
            const fecha = document.getElementById('admin-edit-fecha').value;
            const hora = document.getElementById('admin-edit-hora').value;
            const mesasZona = adminZonasMesas[zona] || adminRange(1, 10);
            const reservaId = document.getElementById('admin-edit-reserva-id').value;
            grid.innerHTML = '<p style="color:#aaa; font-size:0.85rem;">Verificando disponibilidad...</p>';

            const currentMesas = (document.getElementById('admin-edit-mesas').value || '').split(',').filter(Boolean);

            if (!fecha || !hora) {
                renderMesasBtnsEdit(mesasZona, {}, currentMesas);
                return;
            }
            fetch('/reservas/disponibilidad', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ fecha, hora, zona, excluir_reserva_id: reservaId })
            })
            .then(r => r.json())
            .then(data => {
                const map = {};
                if (data.success) data.disponibilidad.forEach(item => { map[item.mesa] = item.disponible; });
                renderMesasBtnsEdit(mesasZona, map, currentMesas);
            })
            .catch(() => renderMesasBtnsEdit(mesasZona, {}, currentMesas));
        };

        function renderMesasBtnsEdit(mesasZona, map, currentMesas) {
            const grid = document.getElementById('admin-edit-mesa-grid');
            grid.innerHTML = '';
            mesasZona.forEach(mesa => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'mesa-btn';
                btn.textContent = mesa;
                if (currentMesas.includes(String(mesa))) {
                    btn.classList.add('selected');
                } else if (map[mesa] === false) {
                    btn.classList.add('mesa-no-disponible');
                    btn.title = 'Mesa ocupada';
                } else {
                    btn.classList.add('mesa-disponible');
                }
                btn.onclick = function() {
                    if (this.classList.contains('mesa-no-disponible')) {
                        Swal.fire({ icon:'warning', title:'Mesa ocupada', text:'Esta mesa ya está reservada.', background:'#1a1a1a', color:'#fff' });
                        return;
                    }
                    this.classList.toggle('selected');
                    actualizarMesasSeleccionadasEdit();
                };
                grid.appendChild(btn);
            });
            actualizarMesasSeleccionadasEdit();
        }

        function actualizarMesasSeleccionadasEdit() {
            const selected = [];
            document.querySelectorAll('#admin-edit-mesa-grid .mesa-btn.selected').forEach(b => selected.push(b.textContent));
            document.getElementById('admin-edit-mesas').value = selected.join(',');
            document.getElementById('admin-edit-mesas-info').textContent = selected.length + ' mesa(s) seleccionada(s)';
        }

        // ===========================
        // MODAL EDITAR RESERVA
        // ===========================
        document.querySelectorAll('.edit-reserva-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const d = this.dataset;
                document.getElementById('admin-edit-reserva-id').value = d.id;
                document.getElementById('admin-edit-user-id').value = d.userid || '';
                document.getElementById('admin-edit-estado').value = d.estado || 'pendiente';
                document.getElementById('admin-edit-personas').value = d.personas || 1;
                document.getElementById('admin-edit-notas').value = d.notas || '';

                // Poner zona
                const zonaRadio = document.querySelector('input[name="admin-edit-zona"][value="' + d.zona + '"]');
                if (zonaRadio) {
                    zonaRadio.checked = true;
                    // Marcar la zona-card como activa visualmente
                    document.querySelectorAll('#modal-admin-editar-reserva .zona-card').forEach(c => c.classList.remove('checked'));
                    zonaRadio.closest('.zona-card')?.classList.add('checked');
                }

                // Cargar mesas de esta reserva
                document.getElementById('admin-edit-mesas').value = d.mesa || '';

                // Setear fecha
                window['setAdminCalDate_admin-edit']?.(d.fecha);
                // Setear hora
                window['setAdminTime_admin-edit']?.(d.hora);

                // Cargar mesas con disponibilidad
                if (d.zona) cargarMesasAdminEdit(d.zona);

                // Abrir modal
                document.getElementById('modal-admin-editar-reserva').classList.remove('hidden');
            });
        });

        window.cerrarModalAdminReserva = function() {
            document.getElementById('modal-admin-editar-reserva').classList.add('hidden');
        };

        window.prepararFormCrearReserva = function(event) {
            const fecha = document.getElementById('admin-create-fecha').value;
            const hora = document.getElementById('admin-create-hora').value;
            const zona = document.querySelector('input[name="zona"]:checked');
            const mesa = document.getElementById('admin-create-mesas').value;

            if (!fecha || !hora || !zona || !mesa) {
                // Dejar que el form lance su validación HTML5 o evitamos submit
                return;
            }

            // Mostrar carga y dejar submitir normalmente (es un form regular POST)
            mostrarCarga('Creando reserva...');
            // Permite que el submit nativo ocurra
        };

        // ===========================
        // GUARDAR EDICIÓN CON CONFIRMACIÓN
        // ===========================
        window.guardarReservaAdminConConfirmacion = function() {
            document.getElementById('modal-admin-editar-reserva').classList.add('hidden');
            Swal.fire({
                title: '¿Guardar los cambios?',
                text: 'La reserva será actualizada con los nuevos datos.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#c29545',
                cancelButtonColor: '#555',
                confirmButtonText: '<i class="fa-solid fa-floppy-disk"></i> Guardar',
                cancelButtonText: '<i class="fa-solid fa-xmark"></i> Cancelar',
                background: '#1a1a1a',
                color: '#f5f5f5',
                customClass: { popup: 'swal-on-top' }
            }).then(result => {
                if (result.isConfirmed) {
                    mostrarCarga('Guardando reserva...');
                    setTimeout(() => guardarReservaAdmin(), 600);
                } else {
                    document.getElementById('modal-admin-editar-reserva').classList.remove('hidden');
                }
            });
        };

        function guardarReservaAdmin() {
            const id = document.getElementById('admin-edit-reserva-id').value;
            const fecha = document.getElementById('admin-edit-fecha').value;
            const hora = document.getElementById('admin-edit-hora').value;
            const personas = document.getElementById('admin-edit-personas').value;
            const zona = document.querySelector('input[name="admin-edit-zona"]:checked')?.value;
            const mesa = document.getElementById('admin-edit-mesas').value;
            const notas = document.getElementById('admin-edit-notas').value;
            const estado = document.getElementById('admin-edit-estado').value;
            const userId = document.getElementById('admin-edit-user-id').value;

            if (!fecha || !hora || !zona || !mesa) {
                Swal.closeAll?.();
                Swal.fire({ icon:'warning', title:'Campos incompletos', text:'Por favor completa fecha, hora, zona y selecciona al menos una mesa.', background:'#1a1a1a', color:'#fff' });
                return;
            }

            fetch('/admin/reservas/' + id, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ _method: 'PUT', fecha, hora, personas: parseInt(personas), zona, mesa, notas, estado, user_id: userId || null })
            })
            .then(r => r.json())
            .then(data => {
                Swal.close?.();
                if (data.success !== false) {
                    Swal.fire({ icon:'success', title:'¡Guardado!', text:'La reserva fue actualizada.', background:'#1a1a1a', color:'#fff', timer:1500, showConfirmButton:false })
                    .then(() => location.reload());
                } else {
                    Swal.fire({ icon:'error', title:'Error', text: data.message || 'No se pudo guardar.', background:'#1a1a1a', color:'#fff' });
                }
            })
            .catch(() => {
                // Fallback: enviar como form
                Swal.close?.();
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/reservas/' + id;
                const fields = { _token: csrfToken, _method: 'PUT', fecha, hora, personas, zona, mesa, notas, estado, user_id: userId || '' };
                Object.entries(fields).forEach(([k, v]) => {
                    const inp = document.createElement('input'); inp.type = 'hidden'; inp.name = k; inp.value = v;
                    form.appendChild(inp);
                });
                document.body.appendChild(form);
                form.submit();
            });
        }

        // ===========================
        // CONFIRMACIÓN ELIMINAR RESERVA
        // ===========================
        document.querySelectorAll('.btn-confirm-delete-reserva').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const form = this.closest('form');
                const nombre = form.dataset.nombre || 'esta reserva';
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
                    color: '#f5f5f5'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        mostrarCarga('Eliminando reserva...');
                        setTimeout(function() { form.submit(); }, 600);
                    }
                });
            });
        });

        // ===========================
        // INICIALIZAR TODO
        // ===========================
        document.addEventListener('DOMContentLoaded', function() {
            initAdminCalendar('admin-edit', true);
            initAdminCalendar('admin-create', false);
            initAdminTimeSelector('admin-edit');
            initAdminTimeSelector('admin-create');
        });

        // ===========================
        // CHARTS
        // ===========================
        const weeklyReservaChart = document.getElementById('weeklyReservaChart');
        if (weeklyReservaChart) {
            new Chart(weeklyReservaChart, {
                type: 'line',
                data: {
                    labels: {!! json_encode($weeklyLabels) !!},
                    datasets: [{
                        label: 'Reservas',
                        data: {!! json_encode($weeklyValues) !!},
                        borderColor: '#c29545',
                        backgroundColor: 'rgba(194,149,69,0.25)',
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

        const monthlyReservaChart = document.getElementById('monthlyReservaChart');
        if (monthlyReservaChart) {
            new Chart(monthlyReservaChart, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($monthlyLabels) !!},
                    datasets: [{
                        label: 'Reservas',
                        data: {!! json_encode($monthlyValues) !!},
                        backgroundColor: 'rgba(46,204,113,0.75)',
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
