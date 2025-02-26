<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(AdminUserSeeder::class); // Executa o seeder antes do teste
    }

    /** @test */
    public function test_login_route()
    {
        // Dados para a requisição
        $credentials = [
            'email' => 'leandro.admin@hotmail.com',
            'password' => '123456',
        ];

        // Enviar o POST para a rota de login
        $response = $this->postJson('/login', $credentials);
        //dd($response->json());

        // Verificar se a resposta tem o status 200
        $response->assertStatus(200);


        $response->assertJsonStructure([
            'token',
            'user',
        ]);
    }

    /** @test */
    public function test_registration_requires_valid_email()
    {
        $response = $this->postJson('/register', [
            'name' => 'New User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'role' => 'user',
        ]);

        // Esperar um erro de validação
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_login_fake()
    {
        // Criar um usuário de teste no banco
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'), // Senha precisa ser criptografada
        ]);

        // Dados de login
        $credentials = [
            'email' => 'user@example.com',
            'password' => 'password123',
        ];

        // Enviar requisição para login
        $response = $this->postJson('/login', $credentials);

        // Verificar se a resposta é 200
        $response->assertStatus(200);

        // Verificar se o token foi retornado
        $response->assertJsonStructure([
            'token',
            'user',
        ]);
    }
}
