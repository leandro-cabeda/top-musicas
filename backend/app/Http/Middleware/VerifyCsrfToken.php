<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCsrfToken
{
    // Incluir para ingnorar qualquer outra rota que precise de CSRF
    protected $except = [
        'http://localhost:8000/*',
        'musicas/sugerir',
        '/*',
    ];
}
