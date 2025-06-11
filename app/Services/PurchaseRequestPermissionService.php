<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PurchaseRequestPermissionService
{
    /**
     * Obtener la configuración de correos por sección
     */
    private function getSectionEmails()
    {
        return config('section_emails.sections', []);
    }

    /**
     * Verificar si el usuario tiene permisos administrativos completos
     */
    public function hasFullAccess(User $user = null)
    {
        $user = $user ?: Auth::user();
        
        return $user->hasAnyRole(['admin', 'compras', 'almacen']);
    }

    /**
     * Verificar si el usuario es coordinador de una sección específica
     */
    public function isSectionCoordinator(User $user = null)
    {
        $user = $user ?: Auth::user();
        $userEmail = $user->email;
        
        $sectionEmails = $this->getSectionEmails();
        
        foreach ($sectionEmails as $section => $emails) {
            // Si es un array de emails, verificar si el usuario está en la lista
            if (is_array($emails)) {
                if (in_array($userEmail, $emails)) {
                    return $section;
                }
            } 
            // Si es un solo email, verificar coincidencia directa
            else {
                if ($userEmail === $emails) {
                    return $section;
                }
            }
        }
        
        return false;
    }

    /**
     * Obtener las secciones que puede ver un coordinador
     */
    public function getCoordinatorSections(User $user = null)
    {
        $user = $user ?: Auth::user();
        $userEmail = $user->email;
        $sections = [];
        
        $sectionEmails = $this->getSectionEmails();
        
        foreach ($sectionEmails as $section => $emails) {
            // Si es un array de emails, verificar si el usuario está en la lista
            if (is_array($emails)) {
                if (in_array($userEmail, $emails)) {
                    $sections[] = $section;
                }
            } 
            // Si es un solo email, verificar coincidencia directa
            else {
                if ($userEmail === $emails) {
                    $sections[] = $section;
                }
            }
        }
        
        return $sections;
    }

    /**
     * Aplicar filtros de consulta basados en los permisos del usuario
     */
    public function applyQueryFilters($query, User $user = null)
    {
        $user = $user ?: Auth::user();
        
        // Si tiene acceso completo, no aplicar filtros
        if ($this->hasFullAccess($user)) {
            return $query;
        }
        
        // Verificar si es coordinador de sección
        $coordinatorSections = $this->getCoordinatorSections($user);
        
        if (!empty($coordinatorSections)) {
            // Si es coordinador, mostrar solicitudes de su(s) sección(es) Y sus propias solicitudes
            return $query->where(function($q) use ($user, $coordinatorSections) {
                $q->where('user_id', $user->id) // Sus propias solicitudes
                  ->orWhereIn('section_area', $coordinatorSections) // Solicitudes de su sección (para compras)
                  ->orWhereIn('section', $coordinatorSections); // Solicitudes de su sección (para materiales/fotocopias)
            });
        }
        
        // Usuario normal: solo sus propias solicitudes
        return $query->where('user_id', $user->id);
    }

    /**
     * Verificar si un usuario puede ver una solicitud específica
     */
    public function canViewRequest($purchaseRequest, User $user = null)
    {
        $user = $user ?: Auth::user();
        
        // Si tiene acceso completo
        if ($this->hasFullAccess($user)) {
            return true;
        }
        
        // Si es el propietario de la solicitud
        if ($purchaseRequest->user_id === $user->id) {
            return true;
        }
        
        // Si es coordinador de la sección
        $coordinatorSections = $this->getCoordinatorSections($user);
        if (!empty($coordinatorSections)) {
            // Verificar si la solicitud pertenece a alguna de sus secciones
            $requestSection = $purchaseRequest->section_area ?? $purchaseRequest->section;
            if (in_array($requestSection, $coordinatorSections)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Verificar si un usuario puede editar una solicitud específica
     */
    public function canEditRequest($purchaseRequest, User $user = null)
    {
        $user = $user ?: Auth::user();
        
        // Solo el propietario puede editar (a menos que tenga rol admin)
        if ($user->hasRole('admin')) {
            return true;
        }
        
        return $purchaseRequest->user_id === $user->id;
    }
}
