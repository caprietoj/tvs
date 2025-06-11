<?php

namespace App\Policies;

use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PurchaseRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determinar si el usuario puede ver una solicitud de compra.
     */
    public function view(User $user, PurchaseRequest $purchaseRequest)
    {
        // El creador de la solicitud siempre puede verla
        if ($user->id === $purchaseRequest->user_id) {
            return true;
        }
        
        // Usuarios con rol específico pueden ver cualquier solicitud
        return $user->hasAnyRole(['admin', 'compras', 'seccion', 'gerente']);
    }

    /**
     * Determinar si el usuario puede crear solicitudes de compra.
     */
    public function create(User $user)
    {
        // Cualquier usuario autenticado puede crear solicitudes
        return true;
    }

    /**
     * Determinar si el usuario puede actualizar una solicitud de compra.
     */
    public function update(User $user, PurchaseRequest $purchaseRequest)
    {
        // Solo el creador o usuarios con roles específicos pueden editar
        return $user->id === $purchaseRequest->user_id || 
               $user->hasAnyRole(['admin', 'compras']);
    }

    /**
     * Determinar si el usuario puede eliminar una solicitud de compra.
     */
    public function delete(User $user, PurchaseRequest $purchaseRequest)
    {
        // Solo el creador (si está en estado pendiente) o admin pueden eliminar
        if ($user->id === $purchaseRequest->user_id && $purchaseRequest->status === 'pending') {
            return true;
        }
        
        return $user->hasRole('admin');
    }

    /**
     * Determinar si el usuario puede aprobar una solicitud de compra.
     */
    public function approve(User $user, PurchaseRequest $purchaseRequest)
    {
        // Solo usuarios con roles específicos pueden aprobar
        return $user->hasAnyRole(['admin', 'compras', 'gerente']);
    }

    /**
     * Determinar si el usuario puede agregar cotizaciones a una solicitud.
     */
    public function addQuotation(User $user, PurchaseRequest $purchaseRequest)
    {
        // Permitir a cualquier usuario autenticado agregar cotizaciones
        // a solicitudes de tipo "compra" que no estén rechazadas o completadas
        if ($purchaseRequest->type !== 'purchase') {
            return false;
        }

        if (in_array($purchaseRequest->status, ['rejected', 'completed'])) {
            return false;
        }

        return true;
    }

    /**
     * Determinar si el usuario puede ver las cotizaciones de una solicitud.
     */
    public function viewQuotation(User $user, PurchaseRequest $purchaseRequest)
    {
        // Cualquier usuario que pueda ver la solicitud también puede ver sus cotizaciones
        return $this->view($user, $purchaseRequest);
    }

    /**
     * Determinar si el usuario puede eliminar cotizaciones de una solicitud.
     */
    public function deleteQuotation(User $user, PurchaseRequest $purchaseRequest)
    {
        // Solo el creador de la solicitud o usuarios con roles específicos
        if ($user->id === $purchaseRequest->user_id) {
            return true;
        }
        
        return $user->hasAnyRole(['admin', 'compras']);
    }
}