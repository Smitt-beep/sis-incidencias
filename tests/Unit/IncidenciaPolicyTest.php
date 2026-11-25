<?php

namespace Tests\Unit;

use App\Models\Incidencia;
use App\Models\Rol;
use App\Models\User;
use App\Policies\IncidenciaPolicy;
use PHPUnit\Framework\TestCase;

class IncidenciaPolicyTest extends TestCase
{
    public function test_professor_can_view_and_update_only_owned_incidents(): void
    {
        $policy = new IncidenciaPolicy();
        $professor = $this->userWithRole('Profesor', 10);
        $ownIncident = new Incidencia(['usuario_id' => 10]);
        $otherIncident = new Incidencia(['usuario_id' => 11]);

        $this->assertTrue($policy->viewAny($professor));
        $this->assertTrue($policy->view($professor, $ownIncident));
        $this->assertTrue($policy->update($professor, $ownIncident));
        $this->assertFalse($policy->view($professor, $otherIncident));
        $this->assertFalse($policy->update($professor, $otherIncident));
        $this->assertFalse($policy->delete($professor, $ownIncident));
    }

    public function test_administrator_can_manage_all_incidents(): void
    {
        $policy = new IncidenciaPolicy();
        $administrator = $this->userWithRole('Administrador', 1);
        $incident = new Incidencia(['usuario_id' => 10]);

        $this->assertTrue($policy->viewAny($administrator));
        $this->assertTrue($policy->view($administrator, $incident));
        $this->assertTrue($policy->create($administrator));
        $this->assertTrue($policy->update($administrator, $incident));
        $this->assertTrue($policy->delete($administrator, $incident));
    }

    public function test_director_can_view_update_and_delete_but_not_create(): void
    {
        $policy = new IncidenciaPolicy();
        $director = $this->userWithRole('Directora', 2);
        $incident = new Incidencia(['usuario_id' => 10]);

        $this->assertTrue($policy->viewAny($director));
        $this->assertTrue($policy->view($director, $incident));
        $this->assertFalse($policy->create($director));
        $this->assertTrue($policy->update($director, $incident));
        $this->assertTrue($policy->update($director, new Incidencia([
            'usuario_id' => 10,
            'es_reservada' => true,
        ])));
        $this->assertTrue($policy->delete($director, $incident));
    }

    public function test_subdirector_can_update_incidents_but_cannot_create_or_delete_them(): void
    {
        $policy = new IncidenciaPolicy();
        $subdirector = $this->userWithRole('Subdirector', 3);
        $incident = new Incidencia(['usuario_id' => 10]);

        $this->assertTrue($policy->viewAny($subdirector));
        $this->assertTrue($policy->view($subdirector, $incident));
        $this->assertFalse($policy->create($subdirector));
        $this->assertTrue($policy->update($subdirector, $incident));
        $this->assertTrue($policy->update($subdirector, new Incidencia([
            'usuario_id' => 10,
            'es_reservada' => true,
        ])));
        $this->assertFalse($policy->delete($subdirector, $incident));
    }

    public function test_administrator_cannot_update_reserved_incidents(): void
    {
        $policy = new IncidenciaPolicy();
        $administrator = $this->userWithRole('Administrador', 1);
        $reservedIncident = new Incidencia([
            'usuario_id' => 10,
            'es_reservada' => true,
        ]);

        $this->assertFalse($policy->update($administrator, $reservedIncident));
    }

    private function userWithRole(string $roleName, int $userId): User
    {
        $user = new User();
        $user->setAttribute('id', $userId);
        $user->setRelation('rol', new Rol(['nombre_rol' => $roleName]));

        return $user;
    }
}
