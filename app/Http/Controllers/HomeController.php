<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categorias = \App\Models\Categoria::with('platos')->get();
        return view('home', compact('categorias'));
    }

    public function login()
    {
        return view('login');
    }

    public function register()
    {
        $tipo_documentos = \App\Models\TipoDocumento::all();
        return view('register', compact('tipo_documentos'));
    }

    public function home2()
    {
        $categorias = \App\Models\Categoria::with('platos')->get();
        return view('home2', compact('categorias'));
    }
}
