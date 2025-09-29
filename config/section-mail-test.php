<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Correos de Notificación por Sección/Área
    |--------------------------------------------------------------------------
    |
    | Este archivo define las direcciones de correo electrónico asociadas
    | a cada sección o área del colegio para envío de notificaciones.
    |
    | Cuando un usuario crea una solicitud y selecciona una sección,
    | el sistema utilizará este archivo para determinar a qué dirección
    | de correo enviar las notificaciones relacionadas.
    |
    */

    // Configuración de correos por sección/área
    'sections' => [
        // Área Académica
        'Pre Escolar' => 'preschool@test.com',
        'Preescolar' => 'preschool@test.com',
        'Primaria' => 'preschool@test.com',
        'PEP' => 'ldiazb@test.com',
        'PAI' => ['escuelamedia@test.com', 'coordpai@test.com'],
        'Bachillerato' => 'escuelamedia@test.com',
        'Diploma' => 'dp@test.com',
        'Administracion' => 'administrativedirector@test.com',
        'Administración' => 'administrativedirector@test.com', // Con acento
        'Dirección General' => 'generaldirector@tvs.edu.co',
        'CAS' => 'mpsilva@test.com',
        'Departamento de Apoyo' => 'psicologia2@test.com',
        'Biblioteca' => 'library@test.com',
        // Área de Sistemas y TI
        'Sistemas' => 'jefesistemas@test.com',
        'Tecnología' => 'jefesistemas@test.com',
        'TI' => 'jefesistemas@test.com',
        // Otras áreas comunes
        'Contabilidad' => 'contabilidad@test.com',
        'Asistente Contabilidad' => 'asistentecontabilidad@test.com',
        'Compras' => 'compras@test.com',
        'Tesorería' => 'tesoreria@test.com',
        'Recursos Humanos' => 'rrhh@test.com',
        'Enfermería' => 'enfermeria@test.com',
        'Psicología' => 'psicologia@test.com',
        'Coordinación Académica' => 'coordinacion.academica@test.com',
        'Coordinación Disciplinaria' => 'coordinacion.disciplinaria@test.com',
        'Rectoría' => 'rectoria@test.com',
        'Tesorería' => 'tesoreria@test.com',
        'Auxiliar Almacén' => 'auxiliaralmacen@test.com',
    ],

    // Configuración específica para solicitudes de materiales - emails de aprobación por sección
    'materials_approval_emails' => [
        'Pre Escolar' => ['preschool@test.com', 'asistentepyp@test.com'],
        'Preescolar' => ['preschool@test.com', 'asistentepyp@test.com'],
        'Primaria' => ['preschool@test.com', 'asistentepyp@test.com'],
        'PAI' => ['escuelamedia@test.com', 'coordpai@test.com'],
        'Bachillerato' => ['escuelamedia@test.com', 'dp@test.com', 'asistentebachillerato@test.com'],
        'Administracion' => ['administrativedirector@test.com'],
        'Administración' => ['administrativedirector@test.com'], // Con acento
        'Sistemas' => ['jefesistemas@test.com'],
        'Tecnología' => ['jefesistemas@test.com'],
        'TI' => ['jefesistemas@test.com'],
    ],

    // Clasificación de secciones por tipo
    'section_types' => [
        // Secciones académicas
        'academic' => [
            'Pre Escolar',
            'Preescolar',
            'Primaria',
            'PEP',
            'PAI',
            'Bachillerato',
            'Diploma',
            'Dirección General',
            'CAS',
            'Departamento de Apoyo',
            'Biblioteca',
            'Coordinación Académica',
            'Coordinación Disciplinaria',
            'Rectoría',
        ],
        // Secciones administrativas
        'administrative' => [
            'Administración',
            'Administracion',
            'Sistemas',
            'Tecnología',
            'TI',
            'Contabilidad',
            'Compras',
            'Recursos Humanos',
            'Enfermería',
            'Psicología',
        ],
    ],

    // Correos de directores para aprobaciones
    'directors' => [
        'academic' => 'generaldirector@test.com',
        'administrative' => 'administrativedirector@test.com',
    ],

    // Correos adicionales que siempre deben ser notificados (independientemente de la sección)
    'always_notify' => [
        'compras@test.com', // Compras debe estar notificado en todas las etapas del flujo
    ],

    // Correo predeterminado cuando no se encuentra una sección específica
     'default' => 'compras@test.com',
];