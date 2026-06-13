@extends('admin.layout')

@section('title', 'Usuarios')

@section('content')
    <div class="summary-grid">
        <div class="summary-card">
            <span class="summary-label">Usuarios</span>
            <strong>{{ $userCount }}</strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Empleados</span>
            <strong>{{ $employeeCount }}</strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Administradores</span>
            <strong>{{ $adminCount }}</strong>
        </div>
    </div>

    <section class="admin-section">
        <h2>Clientes y Empleados</h2>
        <p>Aquí puedes ver cuentas registradas, crear usuarios o agregar empleados manualmente.</p>
        <div class="table-container">
            <table class="styled-table admin-table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
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
                            <td>{{ ucfirst($usuario->rol) }}</td>
                            <td>{{ $usuario->telefono ?? 'N/A' }}</td>
                            <td>{{ $usuario->created_at?->format('d/m/Y') ?? 'N/A' }}</td>
                            <td class="table-actions">
                                <form action="{{ route('admin.usuarios.destroy', $usuario->id) }}" method="POST" style="display:inline-block;" class="form-delete-usuario" data-nombre="{{ $usuario->name }} {{ $usuario->lastname }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-danger btn-icon-only btn-confirm-delete" title="Eliminar usuario">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">No hay cuentas registradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
            {{ $usuarios->links('admin.partials.pagination') }}
        </div>
    </section>

    <section class="admin-section">
        <div class="dual-forms">
            <div class="panel-card">
                <h3>Crear un nuevo usuario</h3>
                <form action="{{ route('admin.usuarios.store') }}" method="POST" class="admin-form">
                    @csrf
                    <input class="form-control" type="text" name="name" placeholder="Nombre" required>
                    <input class="form-control" type="text" name="lastname" placeholder="Apellido" required>
                    <input class="form-control" type="email" name="email" placeholder="Correo electrónico" required>
                    <input class="form-control" type="text" name="telefono" placeholder="Teléfono">
                    <input class="form-control" type="password" name="password" placeholder="Contraseña" required>
                    <input class="form-control" type="password" name="password_confirmation" placeholder="Confirmar contraseña" required>
                    <button type="submit" class="btn btn-primary">Crear Usuario</button>
                </form>
            </div>

            <div class="panel-card">
                <h3>Agregar empleado</h3>
                <form action="{{ route('admin.empleados.store') }}" method="POST" class="admin-form">
                    @csrf
                    <input class="form-control" type="text" name="name" placeholder="Nombre" required>
                    <input class="form-control" type="text" name="lastname" placeholder="Apellido" required>
                    <input class="form-control" type="email" name="email" placeholder="Correo electrónico" required>
                    <input class="form-control" type="text" name="telefono" placeholder="Teléfono">
                    <input class="form-control" type="password" name="password" placeholder="Contraseña" required>
                    <input class="form-control" type="password" name="password_confirmation" placeholder="Confirmar contraseña" required>
                    <button type="submit" class="btn btn-primary">Agregar Empleado</button>
                </form>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
<script>
    document.querySelectorAll('.btn-confirm-delete').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const form = this.closest('form');
            const nombre = form.dataset.nombre || 'este registro';
            Swal.fire({
                title: '¿Deseas eliminar a ' + nombre + '?',
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
                    mostrarCarga('Eliminando usuario...');
                    setTimeout(function() { form.submit(); }, 600);
                }
            });
        });
    });
</script>
@endsection
