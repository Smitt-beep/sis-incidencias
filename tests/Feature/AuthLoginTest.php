<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_requests_only_dni_and_password(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('name="dni"', false)
            ->assertSee('name="password"', false)
            ->assertDontSee('name="email"', false);
    }

    public function test_user_can_log_in_with_matching_dni_and_password(): void
    {
        $user = User::create([
            'name' => 'Ana Torres',
            'email' => 'ana@example.test',
            'dni' => '12345678',
            'password' => 'clave-segura',
        ]);

        $this->post(route('login.post'), [
            'dni' => '12345678',
            'password' => 'clave-segura',
        ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('usuario_id', $user->id)
            ->assertSessionHas('usuario_nombre', 'Ana Torres')
            ->assertSessionHas('usuario_correo', 'ana@example.test')
            ->assertSessionHas('usuario_dni', '12345678');
    }

    public function test_login_rejects_credentials_that_do_not_match(): void
    {
        User::create([
            'name' => 'Ana Torres',
            'email' => 'ana@example.test',
            'dni' => '12345678',
            'password' => 'clave-segura',
        ]);

        $this->from(route('login'))
            ->post(route('login.post'), [
                'dni' => '12345678',
                'password' => 'clave-incorrecta',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('login_error');
    }

    public function test_logout_invalidates_the_custom_user_session(): void
    {
        $this->withSession([
            'usuario_id' => 1,
            'usuario_nombre' => 'Ana Torres',
            'usuario_correo' => 'ana@example.test',
            'usuario_dni' => '12345678',
        ])
            ->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionMissing('usuario_id')
            ->assertSessionMissing('usuario_correo')
            ->assertSessionHas('_token');
    }
}