<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Correos Electrónicos para Notificaciones de Cotizaciones
    |--------------------------------------------------------------------------
    |
    | Este archivo contiene las configuraciones de correos para las diferentes
    | secciones o áreas a las que se enviarán notificaciones relacionadas con
    | cotizaciones de solicitudes de compra.
    |
    | IMPORTANTE: Las claves deben coincidir EXACTAMENTE con los valores de 
    | section_area que los usuarios ingresan en las solicitudes de compra.
    |
    */

    // Correos específicos por sección/área (usar EXACTAMENTE como aparecen en el campo section_area)
    'Pre Escolar' => 'preescolar@tvs.edu.co',
    'Preescolar' => 'preescolar@tvs.edu.co',
    'Primaria' => 'primaria@tvs.edu.co',
    'Bachillerato' => 'bachillerato@tvs.edu.co',
    'Secundaria' => 'secundaria@tvs.edu.co',
    'Administración' => 'administracion@tvs.edu.co',
    'Administracion' => 'administracion@tvs.edu.co',
    'Sistemas' => 'sistemas@tvs.edu.co',
    'Recursos Humanos' => 'rrhh@tvs.edu.co',
    'Recursos humanos' => 'rrhh@tvs.edu.co',
    'Contabilidad' => 'contabilidad@tvs.edu.co',
    'Compras' => 'compras@tvs.edu.co',
    'Enfermería' => 'enfermeria@tvs.edu.co',
    'Enfermeria' => 'enfermeria@tvs.edu.co',
    'Coordinación Académica' => 'coordinacion.academica@tvs.edu.co',
    'Coordinacion Academica' => 'coordinacion.academica@tvs.edu.co',
    'Coordinación Disciplinaria' => 'coordinacion.disciplinaria@tvs.edu.co',
    'Coordinacion Disciplinaria' => 'coordinacion.disciplinaria@tvs.edu.co',
    'Rectoría' => 'rectoria@tvs.edu.co',
    'Rectoria' => 'rectoria@tvs.edu.co',
    
    // Correos adicionales que siempre recibirán notificaciones
    'always_notify' => ['coordinacion.compras@tvs.edu.co'],
    
    // Correo predeterminado si no se encuentra la sección
    'default' => 'compras@tvs.edu.co',
];