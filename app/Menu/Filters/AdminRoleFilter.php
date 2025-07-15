<?php

namespace App\Menu\Filters;

use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class AdminRoleFilter implements FilterInterface
{
    /**
     * Transform a menu item.
     *
     * @param  array  $item  A menu item
     * @return array
     */
    public function transform($item)
    {
        // Si el item requiere rol admin y el usuario no lo tiene, lo ocultamos
        if (isset($item['admin_only']) && $item['admin_only'] === true) {
            if (!auth()->check() || !auth()->user()->hasRole('admin')) {
                return false; // Ocultar el item
            }
        }

        return $item;
    }
}
