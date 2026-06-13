<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Services\MailerService;

class AuthController extends Controller
{
    public function registerPost(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'tipo_documento_id' => 'required|integer',
            'numero_documento' => 'required|string|max:255',
            'telefono' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'lastname' => $request->lastname,
            'tipo_documento_id' => $request->tipo_documento_id,
            'numero_documento' => $request->numero_documento,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => 'user'
        ]);

        MailerService::sendEmail(
            $request->email, 
            $request->name, 
            '¡Bienvenido a Sabor & Tradición!', 
            'Bienvenido a nuestra familia', 
            'Hola ' . $request->name . ',<br><br>Tu cuenta ha sido creada exitosamente. Estamos emocionados de tenerte con nosotros. Podrás realizar reservas y pedidos desde tu panel.'
        );

        return redirect()->route('login')->with('success', 'Cuenta creada exitosamente. Por favor, inicie sesión.');
    }

    public function loginPost(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();
            MailerService::sendEmail(
                $user->email,
                $user->name,
                'Nuevo Inicio de Sesión - Sabor & Tradición',
                'Alerta de Inicio de Sesión',
                'Hola ' . $user->name . ',<br><br>Acabamos de detectar un nuevo inicio de sesión en tu cuenta. Si fuiste tú, puedes ignorar este mensaje.'
            );

            $destination = $user->rol === 'admin' ? route('admin.index') : route('home2');
            return redirect()->intended($destination);
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }
    
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'telefono' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
        ]);

        $user->name = $request->name;
        $user->lastname = $request->lastname;
        $user->telefono = $request->telefono;
        $user->email = $request->email;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        MailerService::sendEmail(
            $user->email,
            $user->name,
            'Perfil Actualizado - Sabor & Tradición',
            'Cambios en tu Perfil',
            'Hola ' . $user->name . ',<br><br>Te informamos que los datos de tu perfil han sido actualizados exitosamente.'
        );

        return response()->json(['success' => true, 'message' => 'Perfil actualizado correctamente']);
    }
}
