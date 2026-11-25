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
        // Las actas reservadas solo las ve la dirección (no profesores ni administrador).
        if ($incidencia->es_reservada) {
            return $this->puedeGestionarActa($user);
        }

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

    public function createActa(User $user): bool
    {
        return $this->puedeGestionarActa($user);
    }

    public function update(User $user, Incidencia $incidencia): bool
    {
        if ($incidencia->es_reservada) {
            return $this->puedeGestionarActa($user);
        }

        return $this->puedeGestionarActa($user)
            || $user->rol?->nombre_rol === 'Administrador'
            || $this->isOwnerProfessor($user, $incidencia);
    }

    public function delete(User $user, Incidencia $incidencia): bool
    {
        if ($incidencia->es_reservada) {
            return $user->rol?->nombre_rol === 'Directora';
        }

        return in_array($user->rol?->nombre_rol, ['Administrador', 'Directora'], true);
    }

    /** Imprimir / descargar actas existentes: solo Directora y Subdirector. */
    public function imprimirActa(User $user, Incidencia $incidencia): bool
    {
        return $this->puedeGestionarActa($user) && $incidencia->acta()->exists();
    }

    private function puedeGestionarActa(User $user): bool
    {
        return in_array($user->rol?->nombre_rol, ['Directora', 'Subdirector'], true);
    }

    private function isOwnerProfessor(User $user, Incidencia $incidencia): bool
    {
        return $user->rol?->nombre_rol === 'Profesor'
            && (int) $incidencia->usuario_id === (int) $user->getKey();
    }
}
