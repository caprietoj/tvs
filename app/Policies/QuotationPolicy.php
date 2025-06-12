<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuotationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any quotations.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasRole('admin') || 
               $user->hasRole('compras') || 
               $user->hasPermissionTo('view quotations');
    }

    /**
     * Determine if the user can view the quotation.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Quotation  $quotation
     * @return bool
     */
    public function view(User $user, Quotation $quotation)
    {
        // Administradores y personal de compras pueden ver todas las cotizaciones
        if ($user->hasRole('admin') || $user->hasRole('compras') || $user->hasPermissionTo('view quotations')) {
            return true;
        }
        
        // El creador de la solicitud puede ver las cotizaciones
        $purchaseRequest = $quotation->purchaseRequest;
        if ($purchaseRequest && $purchaseRequest->user_id == $user->id) {
            return true;
        }
        
        // Los coordinadores o directores de la sección pueden ver
        if ($purchaseRequest && $user->hasRole('coordinador') && 
            $user->section == $purchaseRequest->section_area) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine if the user can create quotations.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasRole('admin') || 
               $user->hasRole('compras') || 
               $user->hasPermissionTo('add quotations');
    }

    /**
     * Determine if the user can delete the quotation.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Quotation  $quotation
     * @return bool
     */
    public function delete(User $user, Quotation $quotation)
    {
        return $user->hasRole('admin') || 
               $user->hasRole('compras') || 
               ($quotation->uploaded_by == $user->id);
    }
}
