<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Plato;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function index()
    {
        return $this->dashboard();
    }

    public function dashboard()
    {
        $userCount = User::where('rol', 'user')->count();
        $employeeCount = User::where('rol', 'employee')->count();
        $adminCount = User::where('rol', 'admin')->count();
        $platoCount = Plato::count();
        $reservaCount = Reserva::count();
        $categorias = Categoria::withCount('platos')->get();

        $lastSevenDays = collect(range(6, 0))->map(fn ($daysAgo) => now()->subDays($daysAgo)->format('Y-m-d'));
        $weeklyReservas = Reserva::selectRaw('DATE(created_at) AS date, COUNT(*) AS total')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        $weeklyPedidos = Pedido::selectRaw('DATE(created_at) AS date, COUNT(*) AS total')
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $activityLabels = $lastSevenDays->map(fn ($date) => date('d M', strtotime($date)))->toArray();
        $activityReservas = $lastSevenDays->map(fn ($date) => $weeklyReservas->get($date)->total ?? 0)->toArray();
        $activityPedidos = $lastSevenDays->map(fn ($date) => $weeklyPedidos->get($date)->total ?? 0)->toArray();
        $categoryLabels = $categorias->pluck('nombre')->toArray();
        $categoryCounts = $categorias->pluck('platos_count')->toArray();

        return view('admin.dashboard', [
            'userCount' => $userCount,
            'employeeCount' => $employeeCount,
            'adminCount' => $adminCount,
            'platoCount' => $platoCount,
            'reservaCount' => $reservaCount,
            'activityLabels' => $activityLabels,
            'activityReservas' => $activityReservas,
            'activityPedidos' => $activityPedidos,
            'categoryLabels' => $categoryLabels,
            'categoryCounts' => $categoryCounts,
        ]);
    }

    public function usuarios()
    {
        $usuarios = User::orderBy('created_at', 'desc')->paginate(10);
        $userCount = User::where('rol', 'user')->count();
        $employeeCount = User::where('rol', 'employee')->count();
        $adminCount = User::where('rol', 'admin')->count();

        return view('admin.usuarios', [
            'usuarios' => $usuarios,
            'userCount' => $userCount,
            'employeeCount' => $employeeCount,
            'adminCount' => $adminCount,
        ]);
    }

    public function menu()
    {
        $categorias = Categoria::withCount('platos')->get();
        $platos = Plato::with('categoria')->orderBy('created_at', 'desc')->paginate(10);
        $platoCount = Plato::count();
        $availableCount = Plato::where('estado', 'disponible')->count();
        $unavailableCount = Plato::where('estado', 'agotado')->count();
        $categoriaCount = Categoria::count();
        $bebidasCount = $categorias->filter(fn($categoria) => str_contains(strtolower($categoria->nombre), 'bebida'))->count();
        $otherCount = max(0, $categoriaCount - $bebidasCount);
        $categoryLabels = $categorias->pluck('nombre')->toArray();
        $categoryValues = $categorias->pluck('platos_count')->toArray();

        $weeklyPlatos = Plato::selectRaw('DATE(created_at) AS date, COUNT(*) AS total')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $monthlyPlatos = Plato::selectRaw('MONTH(created_at) AS month, COUNT(*) AS total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('admin.menu', [
            'categorias' => $categorias,
            'platos' => $platos,
            'platoCount' => $platoCount,
            'availableCount' => $availableCount,
            'unavailableCount' => $unavailableCount,
            'categoriaCount' => $categoriaCount,
            'bebidasCount' => $bebidasCount,
            'otherCount' => $otherCount,
            'categoryLabels' => $categoryLabels,
            'categoryValues' => $categoryValues,
            'weeklyLabels' => $weeklyPlatos->pluck('date')->map(fn($date) => date('d M', strtotime($date)))->toArray(),
            'weeklyValues' => $weeklyPlatos->pluck('total')->toArray(),
            'monthlyLabels' => $monthlyPlatos->pluck('month')->map(fn($month) => date('M', mktime(0, 0, 0, $month, 1)))->toArray(),
            'monthlyValues' => $monthlyPlatos->pluck('total')->toArray(),
        ]);
    }

    public function reservas()
    {
        $reservas = Reserva::with('user')->orderBy('created_at', 'desc')->paginate(10);
        $users = User::whereIn('rol', ['user', 'employee'])->orderBy('name')->get();
        $reservaCount = Reserva::count();
        $weeklyReservas = Reserva::selectRaw('DATE(created_at) AS date, COUNT(*) AS total')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $monthlyReservas = Reserva::selectRaw('MONTH(created_at) AS month, COUNT(*) AS total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $weeklyTotal = $weeklyReservas->sum('total');
        $monthlyTotal = $monthlyReservas->sum('total');
        $uniqueClients = Reserva::distinct('user_id')->count('user_id');

        return view('admin.reservas', [
            'reservas' => $reservas,
            'users' => $users,
            'reservaCount' => $reservaCount,
            'weeklyTotal' => $weeklyTotal,
            'monthlyTotal' => $monthlyTotal,
            'uniqueClients' => $uniqueClients,
            'weeklyLabels' => $weeklyReservas->pluck('date')->map(fn($date) => date('d M', strtotime($date)))->toArray(),
            'weeklyValues' => $weeklyReservas->pluck('total')->toArray(),
            'monthlyLabels' => $monthlyReservas->pluck('month')->map(fn($month) => date('M', mktime(0, 0, 0, $month, 1)))->toArray(),
            'monthlyValues' => $monthlyReservas->pluck('total')->toArray(),
        ]);
    }

    public function storeReserva(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'fecha' => 'required|date',
            'hora' => 'required|string|max:10',
            'personas' => 'required|integer|min:1',
            'zona' => 'required|string|max:100',
            'mesa' => 'required|string|max:100',
            'notas' => 'nullable|string|max:255',
            'estado' => 'required|string|in:pendiente,confirmada,cancelada',
        ]);

        Reserva::create([
            'user_id' => $request->input('user_id'),
            'fecha' => $request->input('fecha'),
            'hora' => $request->input('hora'),
            'personas' => $request->input('personas'),
            'zona' => $request->input('zona'),
            'mesa' => $request->input('mesa'),
            'notas' => $request->input('notas'),
            'estado' => $request->input('estado'),
            'codigo_referencia' => strtoupper(Str::random(8)),
        ]);

        return back()->with('success', 'Reserva creada correctamente.');
    }

    public function updateReserva(Request $request, $id)
    {
        $reserva = Reserva::find($id);
        if (! $reserva) {
            return back()->with('error', 'Reserva no encontrada.');
        }

        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'fecha' => 'required|date',
            'hora' => 'required|string|max:10',
            'personas' => 'required|integer|min:1',
            'zona' => 'required|string|max:100',
            'mesa' => 'required|string|max:100',
            'notas' => 'nullable|string|max:255',
            'estado' => 'required|string|in:pendiente,confirmada,cancelada',
        ]);

        $reserva->update([
            'user_id' => $request->input('user_id'),
            'fecha' => $request->input('fecha'),
            'hora' => $request->input('hora'),
            'personas' => $request->input('personas'),
            'zona' => $request->input('zona'),
            'mesa' => $request->input('mesa'),
            'notas' => $request->input('notas'),
            'estado' => $request->input('estado'),
        ]);

        return back()->with('success', 'Reserva actualizada correctamente.');
    }

    public function pedidos()
    {
        $pedidos = Pedido::with('user', 'detalles.plato')->orderBy('created_at', 'desc')->paginate(10);
        $pedidoCount = Pedido::count();
        $uniqueClients = Pedido::distinct('user_id')->count('user_id');
        $users = User::whereIn('rol', ['user', 'employee'])->orderBy('name')->get();

        $weeklyPedidos = Pedido::selectRaw('DATE(created_at) AS date, COUNT(*) AS total')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $monthlyPedidos = Pedido::selectRaw('MONTH(created_at) AS month, COUNT(*) AS total')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $weeklyTotal = $weeklyPedidos->sum('total');
        $monthlyTotal = $monthlyPedidos->sum('total');

        return view('admin.pedidos', [
            'pedidos' => $pedidos,
            'pedidoCount' => $pedidoCount,
            'uniqueClients' => $uniqueClients,
            'weeklyTotal' => $weeklyTotal,
            'monthlyTotal' => $monthlyTotal,
            'weeklyLabels' => $weeklyPedidos->pluck('date')->map(fn($date) => date('d M', strtotime($date)))->toArray(),
            'weeklyValues' => $weeklyPedidos->pluck('total')->toArray(),
            'monthlyLabels' => $monthlyPedidos->pluck('month')->map(fn($month) => date('M', mktime(0, 0, 0, $month, 1)))->toArray(),
            'monthlyValues' => $monthlyPedidos->pluck('total')->toArray(),
            'users' => $users,
        ]);
    }

    public function storeUsuario(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telefono' => 'nullable|string|max:30',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->input('name'),
            'lastname' => $request->input('lastname'),
            'email' => $request->input('email'),
            'telefono' => $request->input('telefono'),
            'password' => Hash::make($request->input('password')),
            'rol' => 'user',
            'remember_token' => Str::random(60),
        ]);

        return back()->with('success', 'Usuario creado correctamente.');
    }

    public function storeEmpleado(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'telefono' => 'nullable|string|max:30',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->input('name'),
            'lastname' => $request->input('lastname'),
            'email' => $request->input('email'),
            'telefono' => $request->input('telefono'),
            'password' => Hash::make($request->input('password')),
            'rol' => 'employee',
            'remember_token' => Str::random(60),
        ]);

        return back()->with('success', 'Empleado creado correctamente.');
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        if (! $user) {
            return back()->with('error', 'Usuario no encontrado.');
        }

        $user->delete();
        return back()->with('success', 'Usuario eliminado correctamente.');
    }

    public function enviarCorreoCliente(Request $request, $id)
    {
        $user = User::find($id);
        if (! $user) {
            return back()->with('error', 'Usuario no encontrado para enviar correo.');
        }
        $request->validate([
            'asunto' => 'required|string|max:255',
            'mensaje' => 'required|string',
        ]);

        try {
            Mail::raw($request->input('mensaje'), function ($message) use ($user, $request) {
                $message->to($user->email)
                    ->subject($request->input('asunto'));
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Error al enviar el correo.');
        }

        return back()->with('success', 'Correo enviado correctamente.');
    }

    public function storePlato(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'precio' => 'required|numeric|min:0',
            'estado' => 'required|string|in:disponible,agotado',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        Plato::create($request->only(['nombre', 'categoria_id', 'descripcion', 'precio', 'estado']));
        return back()->with('success', 'Plato creado correctamente.');
    }

    public function updatePlato(Request $request, $id)
    {
        $plato = Plato::find($id);
        if (! $plato) {
            return back()->with('error', 'Plato no encontrado.');
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'precio' => 'required|numeric|min:0',
            'estado' => 'required|string|in:disponible,agotado',
            'descripcion' => 'nullable|string|max:1000',
        ]);

        $plato->update($request->only(['nombre', 'categoria_id', 'descripcion', 'precio', 'estado']));
        return back()->with('success', 'Plato actualizado correctamente.');
    }

    public function destroyPlato($id)
    {
        $plato = Plato::find($id);
        if (! $plato) {
            return back()->with('error', 'Plato no encontrado.');
        }

        $plato->delete();
        return back()->with('success', 'Plato eliminado correctamente.');
    }

    public function destroyReserva($id)
    {
        $reserva = Reserva::find($id);
        if (! $reserva) {
            return back()->with('error', 'Reserva no encontrada.');
        }

        $reserva->delete();
        return back()->with('success', 'Reserva eliminada correctamente.');
    }

    public function updatePedido(Request $request, $id)
    {
        $pedido = Pedido::find($id);
        if (! $pedido) {
            return back()->with('error', 'Pedido no encontrado.');
        }

        // Si viene array de detalles (desde la modal de edición admin)
        if ($request->has('detalles') && is_array($request->input('detalles'))) {
            $request->validate([
                'detalles' => 'required|array',
                'detalles.*.plato_id' => 'required|integer',
                'detalles.*.cantidad' => 'required|integer|min:1',
                'user_id' => 'nullable|exists:users,id',
                'estado' => 'nullable|string'
            ]);

            // Eliminar detalles existentes
            $pedido->detalles()->delete();

            // Crear nuevos detalles
            $total = 0;
            foreach ($request->detalles as $detalle) {
                $plato = Plato::find($detalle['plato_id']);
                if ($plato) {
                    $subtotal = $plato->precio * $detalle['cantidad'];
                    $total += $subtotal;

                    $pedido->detalles()->create([
                        'plato_id' => $detalle['plato_id'],
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario' => $plato->precio,
                        'subtotal' => $subtotal
                    ]);
                }
            }

            $pedido->total = $total;
            if ($request->has('user_id')) {
                $pedido->user_id = $request->input('user_id');
            }
            if ($request->has('estado')) {
                $pedido->estado = $request->input('estado');
            }
            $pedido->save();

            if ($pedido->user) {
                try {
                    \App\Services\MailerService::sendEmail(
                        $pedido->user->email,
                        $pedido->user->name,
                        'Pedido Modificado - Sabor & Tradición',
                        'Cambios en tu pedido',
                        "Hola {$pedido->user->name},<br><br>Tu pedido con ID #{$pedido->id} ha sido actualizado. El nuevo total a pagar es de <b>$" . number_format($pedido->total, 2) . "</b>."
                    );
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::error("Error al enviar correo: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pedido actualizado correctamente',
                'pedido' => $pedido->load('detalles.plato')
            ]);
        }

        // Si solo viene estado (fallback original)
        $request->validate(['estado' => 'required|string']);
        $pedido->update(['estado' => $request->input('estado')]);
        return back()->with('success', 'Estado del pedido actualizado.');
    }

    public function destroyPedido($id)
    {
        $pedido = Pedido::find($id);
        if (! $pedido) {
            return back()->with('error', 'Pedido no encontrado.');
        }

        $pedido->delete();
        return back()->with('success', 'Pedido eliminado correctamente.');
    }

    public function getPlatosAdmin()
    {
        $platos = Plato::with('categoria')->orderBy('nombre')->get(['id', 'nombre', 'precio', 'categoria_id', 'estado', 'imagen']);
        
        $platosFormateados = $platos->map(function($plato) {
            return [
                'id' => $plato->id,
                'nombre' => $plato->nombre,
                'precio' => $plato->precio,
                'estado' => $plato->estado,
                'imagen' => $plato->imagen,
                'categoria' => $plato->categoria ? strtolower($plato->categoria->nombre) : 'sin categoría'
            ];
        });
        
        return response()->json($platosFormateados);
    }

    public function getPedidoAdmin($id)
    {
        $pedido = Pedido::with(['detalles.plato'])->find($id);

        if (!$pedido) {
            return response()->json(['success' => false, 'message' => 'Pedido no encontrado'], 404);
        }

        $detalles = $pedido->detalles->map(function ($detalle) {
            return [
                'id' => $detalle->id,
                'plato_id' => $detalle->plato_id,
                'plato_nombre' => $detalle->plato->nombre,
                'plato_precio' => $detalle->plato->precio,
                'cantidad' => $detalle->cantidad,
                'subtotal' => $detalle->subtotal
            ];
        });

        return response()->json([
            'success' => true,
            'pedido' => [
                'id' => $pedido->id,
                'user_id' => $pedido->user_id,
                'total' => $pedido->total,
                'estado' => $pedido->estado,
                'detalles' => $detalles
            ]
        ]);
    }
}
