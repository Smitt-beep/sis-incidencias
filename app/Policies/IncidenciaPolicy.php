<?php

namespace App\Policies;

use App\Models\Incidencia;
use App\Models\User;

class IncidenciaPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->rol?->nombre_rol, [
            'Administrador',
            'Profesor',
            'Directora',
            'Subdirector',
        ], true);
    }

    public function view(User $user, Incidencia $incidencia): bool
    {
        return in_array($user->rol?->nombre_rol, [
            'Administrador',
            'Directora',
            'Subdirector',
        ], true) || $this->isOwnerProfessor($user, $incidencia);
    }

    public function create(User $user): bool
    {
        return in_array($user->rol?->nombre_rol, ['Administrador', 'Profesor'], true);
    }

    public function update(User $user, Incidencia $incidencia): bool
    {
        return $user->rol?->nombre_rol === 'Administrador'
            || $this->isOwnerProfessor($user, $incidencia);
    }

    public function delete(User $user, Incidencia $incidencia): bool
    {
        return in_array($user->rol?->nombre_rol, ['Administrador', 'Directora'], true);
    }

    private function isOwnerProfessor(User $user, Incidencia $incidencia): bool
    {
        return $user->rol?->nombre_rol === 'Profesor'
            && (int) $incidencia->usuario_id === (int) $user->getKey();
    }
}
