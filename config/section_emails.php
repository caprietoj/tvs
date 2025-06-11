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
        'Pre Escolar' => 'preschool@tvs.edu.co',
        'Preescolar' => 'preschool@tvs.edu.co',
        'Primaria' => 'preschool@tvs.edu.co',
        'PEP' => 'ldiazb@tvs.edu.co',
        'PAI' => ['escuelamedia@tvs.edu.co', 'coordpai@tvs.edu.co'],
        'Bachillerato' => 'escuelamedia@tvs.edu.co',
        'Diploma' => 'dp@tvs.edu.co',
        'Administracion' => 'administrativedirector@tvs.edu.co',
        'Administración' => 'administrativedirector@tvs.edu.co', // Con acento
        'Dirección General' => 'generaldirector@tvs.edu.co',
        'CAS' => 'mpsilva@tvs.edu.co',
        'Departamento de Apoyo' => 'psicologia2@tvs.edu.co',
        'Biblioteca' => 'library@tvs.edu.co',
        // Área de Sistemas y TI
        'Sistemas' => 'jefesistemas@tvs.edu.co',
        'Tecnología' => 'jefesistemas@tvs.edu.co',
        'TI' => 'jefesistemas@tvs.edu.co',
        // Otras áreas comunes
        'Contabilidad' => 'contabilidad@tvs.edu.co',
        'Compras' => 'compras@tvs.edu.co',
        'Recursos Humanos' => 'rrhh@tvs.edu.co',
        'Enfermería' => 'enfermeria@tvs.edu.co',
        'Psicología' => 'psicologia@tvs.edu.co',
        'Coordinación Académica' => 'coordinacion.academica@tvs.edu.co',
        'Coordinación Disciplinaria' => 'coordinacion.disciplinaria@tvs.edu.co',
        'Rectoría' => 'rectoria@tvs.edu.co',
    ],

    // Configuración específica para solicitudes de materiales - emails de aprobación por sección
    'materials_approval_emails' => [
        'Pre Escolar' => ['preschool@tvs.edu.co', 'asistentepyp@tvs.edu.co'],
        'Preescolar' => ['preschool@tvs.edu.co', 'asistentepyp@tvs.edu.co'],
        'Primaria' => ['preschool@tvs.edu.co', 'asistentepyp@tvs.edu.co'],
        'Bachillerato' => ['escuelamedia@tvs.edu.co', 'dp@tvs.edu.co', 'asistentebachillerato@tvs.edu.co'],
        'Administracion' => ['administrativedirector@tvs.edu.co'],
        'Administración' => ['administrativedirector@tvs.edu.co'], // Con acento
        'Sistemas' => ['jefesistemas@tvs.edu.co'],
        'Tecnología' => ['jefesistemas@tvs.edu.co'],
        'TI' => ['jefesistemas@tvs.edu.co'],
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
        'academic' => 'generaldirector@tvs.edu.co',
        'administrative' => 'administrativedirector@tvs.edu.co',
    ],

    // Correos adicionales que siempre deben ser notificados (independientemente de la sección)
    // COMENTADO: 'always_notify' causa que jefesistemas reciba todos los emails
    // Solo se debe usar para emails que verdaderamente necesiten ir a todos
    'always_notify' => [
        // Email de sistemas comentado temporalmente para solucionar problema de pre-aprobaciones
    ],

    // Correo predeterminado cuando no se encuentra una sección específica
     'default' => 'compras@tvs.edu.co',
];