<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Autenticação",
 *     description="Rotas de autenticação do usuário"
 * )
 */
class AuthController extends Controller
{

    /**
     * @OA\POST(
     *     path="/register",
     *     summary="Registra um novo usuário",
     *     tags={"Registor usuário"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "email", "password"},
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="2aHt5@example.com"),
     *                 @OA\Property(property="password", type="string", example="123456"),
     *                 @OA\Property(property="role", type="string", example="user")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Usuário registrado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuário registrado com sucesso"),
     *             @OA\Property(property="token", type="string", example="token"),
     *             @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="2aHt5@example.com"),
     *                     @OA\Property(property="role", type="string", example="user")
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Dados inválidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dados inválidos")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Usuário já cadastrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuário já cadastrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro interno do servidor")
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'nullable|string|in:user,admin'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) return response()->json(['message' => 'Usuário já cadastrado'], 404);

        $role = 'user';
        if ($request->role === 'admin') {
            if (Auth::check() && Auth::user()->isAdmin()) {
                $role = 'admin';
            } else {
                return response()->json(['message' => 'Você não tem permissão para criar um administrador'], 403);
            }
        }


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
        ]);

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuário registrado com sucesso',
            'token' => $token,
            'user' => $user
        ], 201);
    }


    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Realiza login do usuário",
     *     tags={"Login usuário"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"email", "password"},
     *                 @OA\Property(property="email", type="string", example="2aHt5@example.com"),
     *                 @OA\Property(property="password", type="string", example="123456")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Usuário autenticado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="token"),
     *             @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="2aHt5@example.com"),
     *                     @OA\Property(property="role", type="string", example="user")
     *              )
     *         )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Credenciais inválidas",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Credenciais inválidas")
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Usuário não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuário não encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro interno do servidor")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Tentando autenticar o usuário
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado'], 404);
        } else if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Credenciais inválidas'], 401);
        }

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'token' => $token,
                'user' => $user
            ]);
        } else {
            return response()->json(['message' => 'Usuário não autenticado'], 401);
        }
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     summary="Realiza logout do usuário",
     *     tags={"Logout usuário"},
     *     @OA\Response(
     *         response="200",
     *         description="Logout realizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logout realizado com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro interno do servidor")
     *         )
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Nao autorizado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Nao autorizado")
     *         ),
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Proibido",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Proibido")
     *         ),
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logout realizado com sucesso']);
    }
}
