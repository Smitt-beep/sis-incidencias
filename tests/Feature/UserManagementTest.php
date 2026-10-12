<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_list_requires_authentication_and_does_not_show_passwords(): void
    {
        $this->get(route('usuarios.index'))->assertRedirect(route('login'));

        $user = User::create([
            'name' => 'Ana Torres',
            'email' => 'ana@example.test',
            'dni' => '12345678',
            'password' => 'clave-secreta',
        ]);

        $this->withSession(['usuario_id' => $user->id])
            ->get(route('usuarios.index'))
            ->assertOk()
            ->assertSee('Ana Torres')
            ->assertSee('12345678')
            ->assertSee('ana@example.test')
            ->assertDontSee('clave-secreta');
    }

    public function test_dashboard_and_user_pages_share_the_panel_layout(): void
    {
        $user = User::create([
            'name' => 'Ana Torres',
            'email' => 'ana@example.test',
            'dni' => '12345678',
            'password' => 'clave-secreta',
        ]);

        $this->withSession(['usuario_id' => $user->id])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('id="panelSidebar"', false)
            ->assertSee('Sistema de Incidencias');

        $this->withSession(['usuario_id' => $user->id])
            ->get(route('usuarios.index'))
            ->assertOk()
            ->assertSee('id="panelSidebar"', false)
            ->assertSee('Gestión de Usuarios');
    }

    public function test_user_can_be_created_with_a_hashed_password(): void
    {
        $this->withSession(['usuario_id' => 1])
            ->post(route('usuarios.store'), [
                'name' => 'Luis Perez',
                'email' => 'luis@example.test',
                'dni' => '87654321',
                'password' => 'clave-nueva',
                'password_confirmation' => 'clave-nueva',
            ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('success');

        $user = User::where('dni', '87654321')->firstOrFail();

        $this->assertTrue(Hash::check('clave-nueva', $user->password));
        $this->assertNotSame('clave-nueva', $user->password);
    }

    public function test_user_can_be_updated_without_replacing_an_empty_password(): void
    {
        $user = User::create([
            'name' => 'Maria Lopez',
            'email' => 'maria@example.test',
            'dni' => '11223344',
            'password' => 'clave-anterior',
        ]);
        $originalPassword = $user->password;

        $this->withSession(['usuario_id' => $user->id])
            ->get(route('usuarios.edit', $user))
            ->assertOk()
            ->assertSee('Nueva contraseña (opcional)')
            ->assertDontSee('clave-anterior');

        $this->withSession(['usuario_id' => $user->id])
            ->put(route('usuarios.update', $user), [
                'name' => 'Maria Lopez Actualizada',
                'email' => 'maria@example.test',
                'dni' => '11223344',
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('success');

        $this->assertSame($originalPassword, $user->fresh()->password);

        $this->withSession(['usuario_id' => $user->id])
            ->put(route('usuarios.update', $user), [
                'name' => 'Maria Lopez Actualizada',
                'email' => 'maria@example.test',
                'dni' => '11223344',
                'password' => 'clave-actualizada',
                'password_confirmation' => 'clave-actualizada',
            ])
            ->assertRedirect(route('usuarios.index'));

        $this->assertTrue(Hash::check('clave-actualizada', $user->fresh()->password));
    }

    public function test_user_can_be_deleted(): void
    {
        $user = User::create([
            'name' => 'Carlos Ruiz',
            'email' => 'carlos@example.test',
            'dni' => '99887766',
            'password' => 'clave-secreta',
        ]);

        $this->withSession(['usuario_id' => $user->id])
            ->delete(route('usuarios.destroy', $user))
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}