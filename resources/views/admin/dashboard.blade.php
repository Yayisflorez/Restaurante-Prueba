@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <div class="summary-grid">
        <div class="summary-card">
            <span class="summary-label">Clientes</span>
            <strong>{{ $userCount }}</strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Empleados</span>
            <strong>{{ $employeeCount }}</strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Platos totales</span>
            <strong>{{ $platoCount }}</strong>
        </div>
        <div class="summary-card">
            <span class="summary-label">Reservas</span>
            <strong>{{ $reservaCount }}</strong>
        </div>
    </div>

    <div class="dashboard-grid">
        <section class="dashboard-card">
            <h2>Resumen de actividad</h2>
            <p>Comparación semanal y mensual de reservas y pedidos.</p>
            <canvas id="activityChart"></canvas>
        </section>

        <section class="dashboard-card">
            <h2>Usuarios y roles</h2>
            <p>Reparto de cuentas por tipo.</p>
            <canvas id="userRolesChart"></canvas>
        </section>

        <section class="dashboard-card">
            <h2>Platos por categoría</h2>
            <p>Distribución del menú según categorías.</p>
            <canvas id="categoryChart"></canvas>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        const activityCtx = document.getElementById('activityChart');
        if (activityCtx) {
            new Chart(activityCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($activityLabels) !!},
                    datasets: [
                        {
                            label: 'Reservas',
                            data: {!! json_encode($activityReservas) !!},
                            borderColor: '#c29545',
                            backgroundColor: 'rgba(194,149,69,0.2)',
                            tension: 0.35,
                            fill: true,
                        },
                        {
                            label: 'Pedidos',
                            data: {!! json_encode($activityPedidos) !!},
                            borderColor: '#2ecc71',
                            backgroundColor: 'rgba(46,204,113,0.2)',
                            tension: 0.35,
                            fill: true,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { labels: { color: '#f2f2f2' } },
                        tooltip: { mode: 'index', intersect: false }
                    },
                    scales: {
                        x: { ticks: { color: '#ddd' }, grid: { color: 'rgba(255,255,255,0.08)' } },
                        y: { ticks: { color: '#ddd' }, grid: { color: 'rgba(255,255,255,0.08)' } }
                    }
                }
            });
        }

        const rolesCtx = document.getElementById('userRolesChart');
        if (rolesCtx) {
            new Chart(rolesCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Usuarios', 'Empleados', 'Administradores'],
                    datasets: [{
                        data: [{{ $userCount }}, {{ $employeeCount }}, {{ $adminCount }}],
                        backgroundColor: ['#c29545', '#2ecc71', '#2c3e50'],
                        borderColor: 'rgba(0,0,0,0.1)',
                        borderWidth: 1
                    }]
                },
                options: { responsive: true, plugins: { legend: { labels: { color: '#f2f2f2' } } } }
            });
        }

        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx) {
            new Chart(categoryCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($categoryLabels) !!},
                    datasets: [{
                        label: 'Platos por categoría',
                        data: {!! json_encode($categoryCounts) !!},
                        backgroundColor: 'rgba(194,149,69,0.8)',
                        borderRadius: 12,
                        borderSkipped: false,
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
