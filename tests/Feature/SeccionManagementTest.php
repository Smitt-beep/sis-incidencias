<?php

namespace Tests\Feature;

use App\Models\Seccion;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeccionManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('dni', 8);
            $table->string('password');
            $table->string('estado_aprobacion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('secciones', function (Blueprint $table): void {
            $table->id();
            $table->string('grado');
            $table->string('seccion');
        });
    }

    public function test_authorized_roles_can_view_sections_sorted_by_grade_and_section(): void
    {
        $this->createApprovedUser('Administrador');
        Seccion::create(['grado' => '2.º', 'seccion' => 'B']);
        Seccion::create(['grado' => '1.º', 'seccion' => 'A']);

        $response = $this->get(route('secciones.index'));

        $response->assertOk()
            ->assertSee('Gestión de Secciones')
            ->assertSeeInOrder(['1.º', 'A', '2.º', 'B'])
            ->assertSee('Secciones');
    }

    public function test_section_can_be_registered_and_edited(): void
    {
        $this->createApprovedUser('Directora');

        $this->post(route('secciones.store'), [
            'grado' => '6.º',
            'seccion' => 'C',
        ])->assertRedirect(route('secciones.index'))
            ->assertSessionHas('success');

        $seccion = Seccion::firstOrFail();

        $this->put(route('secciones.update', $seccion), [
            'grado' => '7.º',
            'seccion' => 'A',
        ])->assertRedirect(route('secciones.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('secciones', [
            'id' => $seccion->id,
            'grado' => '7.º',
            'seccion' => 'A',
        ]);
    }

    public function test_section_fields_are_required_and_duplicate_combinations_are_rejected(): void
    {
        $this->createApprovedUser('Subdirector');
        $original = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);

        $this->from(route('secciones.create'))
            ->post(route('secciones.store'), ['grado' => '', 'seccion' => ''])
            ->assertRedirect(route('secciones.create'))
            ->assertSessionHasErrors(['grado', 'seccion']);

        $this->from(route('secciones.create'))
            ->post(route('secciones.store'), ['grado' => '1.º', 'seccion' => 'A'])
            ->assertRedirect(route('secciones.create'))
            ->assertSessionHasErrors('seccion');

        $this->post(route('secciones.store'), ['grado' => '2.º', 'seccion' => 'A'])
            ->assertRedirect(route('secciones.index'))
            ->assertSessionHas('success');

        $this->put(route('secciones.update', $original), ['grado' => '2.º', 'seccion' => 'A'])
            ->assertSessionHasErrors('seccion');

        $this->assertDatabaseCount('secciones', 2);
    }

    public function test_professor_cannot_access_section_administration_routes(): void
    {
        $this->createApprovedUser('Profesor');
        $seccion = Seccion::create(['grado' => '1.º', 'seccion' => 'A']);

        $this->get(route('secciones.index'))->assertForbidden();
        $this->get(route('secciones.create'))->assertForbidden();
        $this->get(route('secciones.edit', $seccion))->assertForbidden();
        $this->post(route('secciones.store'), ['grado' => '2.º', 'seccion' => 'B'])->assertForbidden();
        $this->put(route('secciones.update', $seccion), ['grado' => '2.º', 'seccion' => 'B'])
            ->assertForbidden();

        $this->assertDatabaseCount('secciones', 1);
    }

    private function createApprovedUser(string $role): User
    {
        $user = User::create([
            'name' => 'Usuario de prueba',
            'email' => strtolower($role) . '@example.test',
            'dni' => '12345678',
            'password' => 'clave-segura',
            'estado_aprobacion' => 'Aprobado',
            'activo' => true,
        ]);

        $this->withSession([
            'usuario_id' => $user->id,
            'usuario_rol' => $role,
        ]);

        return $user;
    }
}
