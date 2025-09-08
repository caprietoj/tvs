<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'The Victorias Shool',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => true,
    'use_full_favicon' => true,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b>TVS</b>',
    'logo_img' => 'img/logo-menu.png',  // Actualizado a la ruta correcta
    'logo_img_class' => 'brand-image',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'TVS Logo',


    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => false, // Desactivamos el preloader
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/AdminLTELogo.png',
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => true,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => 'nav-child-indent nav-compact',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => true,
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => true,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => true,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        // Navbar items y búsqueda (mantener existentes)
        [
            'type' => 'navbar-search',
            'text' => 'search',
            'topnav_right' => true,
        ],
        [
            'type' => 'fullscreen-widget',
            'topnav_right' => true,
        ],

        // === MENÚ PRINCIPAL ===
        [
            'text' => 'Inicio',
            'url'  => '/home',
            'icon' => 'fas fa-home',
            'can'  => 'view.dashboard',
        ],

        // === MÓDULO DE SOLICITUDES ===
        [
            'text' => '📝 Mis Solicitudes',
            'icon' => 'fas fa-clipboard-list',
            'submenu' => [
                [
                    'text' => '🎫 Help-Desk Sistemas',
                    'url'  => 'tickets',
                    'icon' => 'fas fa-laptop-code',
                    'can'  => 'ticket.view',
                    'active' => ['tickets*'],
                ],
                [
                    'text' => '👥 Help-Desk RRHH',
                    'icon' => 'fas fa-users',
                    'active' => ['documents*', 'document-requests*', 'loan-requests*'],
                    'submenu' => [
                        [
                            'text' => 'Nueva Solicitud',
                            'url'  => 'documents',
                            'icon' => 'fas fa-plus-circle',
                            'active' => ['documents*'],
                            'can'  => 'documents-new',
                        ],
                        [
                            'text' => 'Mis Solicitudes',
                            'url'  => 'document-requests',
                            'icon' => 'fas fa-list',
                            'active' => ['document-requests*'],
                        ],
                        [
                            'text' => 'Solicitar Préstamo',
                            'url'  => 'loan-requests',
                            'icon' => 'fas fa-hand-holding-usd',
                            'active' => ['loan-requests*'],
                        ],
                    ],
                ],
                [
                    'text' => '🛒 Solicitudes de Compra',
                    'url'  => 'purchase-requests',
                    'icon' => 'fas fa-shopping-cart',
                    'can' => 'solicitudes_compra',
                    'active' => ['purchase-requests*', 'purchase-form-type*'],
                ],
                [
                    'text' => '💻 Préstamo de Equipos',
                    'icon' => 'fas fa-laptop',
                    'can'  => 'equipment.reserva',
                    'active' => ['equipment/request*', 'equipment/loans*', 'equipment/blocks*'],
                    'submenu' => [
                        [
                            'text' => 'Solicitar Préstamo',
                            'url'  => 'equipment/request',
                            'icon' => 'fas fa-plus',
                            'active' => ['equipment/request*'],
                        ],
                        [
                            'text' => 'Ver Préstamos',
                            'url'  => 'equipment/loans',
                            'icon' => 'fas fa-list',
                            'active' => ['equipment/loans*'],
                        ],
                        [
                            'text' => 'Gestionar Bloqueos',
                            'url'  => 'equipment/blocks',
                            'icon' => 'fas fa-lock',
                            'can'  => 'equipment.blocks.manage',
                            'active' => ['equipment/blocks*'],
                        ],
                    ],
                ],
            ],
        ],

        // === MÓDULO DE GESTIÓN ACADÉMICA ===
        [
            'text' => '🎓 Gestión Académica',
            'icon' => 'fas fa-graduation-cap',
            'can'  => 'view.salidas',
            'submenu' => [
                [
                    'text' => '🚌 Salidas Pedagógicas',
                    'icon' => 'fas fa-bus',
                    'active' => ['salidas*'],
                    'submenu' => [
                        [
                            'text' => 'Nueva Salida',
                            'url'  => 'salidas/create',
                            'icon' => 'fas fa-plus',
                        ],
                        [
                            'text' => 'Mis Salidas',
                            'url'  => 'salidas',
                            'icon' => 'fas fa-list',
                        ],
                    ],
                ],
                [
                    'text' => '📅 Eventos Institucionales',
                    'icon' => 'fas fa-calendar-alt',
                    'active' => ['events*'],
                    'submenu' => [
                        [
                            'text' => 'Nuevo Evento',
                            'url'  => 'events/create',
                            'icon' => 'fas fa-plus',
                            'can' => 'view.events',
                        ],
                        [
                            'text' => 'Lista de Eventos',
                            'url'  => 'events',
                            'icon' => 'fas fa-list',
                            'can' => 'view.events',
                        ],
                        [
                            'text' => 'Calendario',
                            'url'  => 'events/calendar',
                            'icon' => 'fas fa-calendar',
                            'can' => 'view.calendar',
                        ],
                    ],
                ],
                [
                    'text' => '🏢 Reserva de Espacios',
                    'icon' => 'fas fa-building',
                    'can'  => 'view.space-reservations',
                    'active' => ['space-reservations*', 'spaces*', 'school-cycles*', 'holidays*', 'space-blocks*'],
                    'submenu' => [
                        [
                            'text' => 'Nueva Reserva',
                            'url'  => 'space-reservations/create',
                            'icon' => 'fas fa-plus-circle',
                        ],
                        [
                            'text' => 'Mis Reservas',
                            'url'  => 'space-reservations',
                            'icon' => 'fas fa-bookmark',
                        ],
                        [
                            'text' => 'Calendario',
                            'url'  => 'space-reservations/calendar',
                            'icon' => 'fas fa-calendar',
                        ],
                    ],
                ],
                [
                    'text' => '⭐ Evaluación Desempeño',
                    'url'  => 'performance-evaluations',
                    'icon' => 'fas fa-user-check',
                    'active' => ['performance-evaluations*'],
                ],
            ],
        ],

        // === MÓDULO DE ALMACÉN Y COMPRAS ===
        [
            'text' => '📦 Almacén y Compras',
            'icon' => 'fas fa-warehouse',
            'can' => 'almacen',
            'active' => ['proveedores*', 'evaluaciones*', 'quotations*', 'quotation-approvals*', 'approvals*', 'purchase-orders*', 'inventory*', 'copies-requests*'],
            'submenu' => [
                [
                    'text' => '🏪 Proveedores',
                    'url'  => 'proveedores',
                    'icon' => 'fas fa-store',
                    'can' => 'listado-proveedores',
                    'active' => ['proveedores*']
                ],
                [
                    'text' => '💰 Cotizaciones',
                    'url'  => 'quotations',
                    'icon' => 'fas fa-file-invoice-dollar',
                    'can' => 'cotizaciones',
                    'active' => ['quotations*'],
                ],
                [
                    'text' => '✅ Aprobaciones',
                    'icon' => 'fas fa-check-circle',
                    'active' => ['quotation-approvals*', 'approvals*'],
                    'submenu' => [
                        [
                            'text' => 'Preaprobaciones',
                            'url'  => 'quotation-approvals',
                            'icon' => 'fas fa-clock',
                            'can' => 'preaprobaciones',
                        ],
                        [
                            'text' => 'Aprobaciones Finales',
                            'url'  => 'approvals',
                            'icon' => 'fas fa-thumbs-up',
                            'can' => 'aprobaciones',
                        ],
                    ],
                ],
                [
                    'text' => '📋 Órdenes de Compra',
                    'url'  => 'purchase-orders',
                    'icon' => 'fas fa-file-invoice',
                    'can' => 'ordenes_compra',
                    'active' => ['purchase-orders*']
                ],
                [
                    'text' => '📦 Inventario',
                    'url'  => 'inventory',
                    'icon' => 'fas fa-boxes',
                    'can'  => 'inventario.view',
                    'active' => ['inventory*'],
                ],
                [
                    'text' => '📄 Fotocopias',
                    'url'  => 'copies-requests',
                    'icon' => 'fas fa-copy',
                    'can' => 'fotocopias_list',
                    'active' => ['copies-requests*']
                ],
            ],
        ],

        // === MÓDULO DE ENCUESTAS ===
        [
            'text' => '📊 Encuestas',
            'icon' => 'fas fa-poll',
            'active' => ['surveys*'],
            'submenu' => [
                [
                    'text' => '👔 Cliente Interno',
                    'icon' => 'fas fa-user-tie',
                    'submenu' => [
                        [
                            'text' => 'Almacén',
                            'url'  => 'surveys/internal-client/warehouse',
                            'icon' => 'fas fa-warehouse',
                        ],
                        [
                            'text' => 'Enfermería',
                            'url'  => 'surveys/internal-client/enfermeria',
                            'icon' => 'fas fa-user-nurse',
                        ],
                        [
                            'text' => 'Sistemas',
                            'url'  => 'surveys/internal-client/systems',
                            'icon' => 'fas fa-desktop',
                        ],
                    ],
                ],
                [
                    'text' => '🤝 Servicios Complementarios',
                    'url'  => 'surveys/complementary-services/transport',
                    'icon' => 'fas fa-handshake',
                    'active' => ['surveys/complementary-services*'],
                ],
                [
                    'text' => '👨‍👩‍👧‍👦 Padres y Estudiantes',
                    'url'  => 'surveys/parent-student',
                    'icon' => 'fas fa-users',
                    'active' => ['surveys/parent-student*'],
                ],
            ],
        ],

        // === MÓDULO ADMINISTRATIVO ===
        [
            'text' => '🏢 Administración',
            'icon' => 'fas fa-building',
            'submenu' => [
                [
                    'text' => '📈 KPIs e Indicadores',
                    'icon' => 'fas fa-chart-line',
                    'can'  => 'view.kpis',
                    'submenu' => [
                        [
                            'text' => '❤️ Enfermería',
                            'icon' => 'fas fa-heartbeat',
                            'submenu' => [
                                [
                                    'text' => 'Crear KPI',
                                    'url'  => 'enfermeria/kpis/create',
                                    'icon' => 'fas fa-plus',
                                    'can' => 'kpis.enfermeria.create',
                                ],
                                [
                                    'text' => 'Ver KPIs',
                                    'url'  => 'enfermeria/kpis',
                                    'icon' => 'fas fa-chart-bar',
                                    'can' => 'kpis.enfermeria.index',
                                ],
                                [
                                    'text' => 'Indicadores',
                                    'url'  => 'enfermeria/umbral/show',
                                    'icon' => 'fas fa-sliders-h',
                                    'can' => 'umbral.enfermeria.show',
                                ],
                            ],
                        ],
                        [
                            'text' => '🛒 Compras',
                            'icon' => 'fas fa-shopping-cart',
                            'submenu' => [
                                [
                                    'text' => 'Crear KPI',
                                    'url'  => 'compras/kpis/create',
                                    'icon' => 'fas fa-plus',
                                    'can' => 'kpis.compras.create',
                                ],
                                [
                                    'text' => 'Ver KPIs',
                                    'url'  => 'compras/kpis',
                                    'icon' => 'fas fa-chart-bar',
                                    'can' => 'kpis.compras.index',
                                ],
                                [
                                    'text' => 'Indicadores',
                                    'url'  => 'compras/umbral/show',
                                    'icon' => 'fas fa-sliders-h',
                                    'can' => 'umbral.compras.show',
                                ],
                            ],
                        ],
                        [
                            'text' => '👥 Recursos Humanos',
                            'icon' => 'fas fa-users',
                            'submenu' => [
                                [
                                    'text' => 'Crear KPI',
                                    'url'  => 'rrhh/kpis/create',
                                    'icon' => 'fas fa-plus',
                                    'can' => 'kpis.recursoshumanos.create',
                                ],
                                [
                                    'text' => 'Ver KPIs',
                                    'url'  => 'rrhh/kpis',
                                    'icon' => 'fas fa-chart-bar',
                                    'can' => 'kpis.recursoshumanos.index',
                                ],
                                [
                                    'text' => 'Indicadores',
                                    'url'  => 'rrhh/umbral/show',
                                    'icon' => 'fas fa-sliders-h',
                                    'can' => 'umbral.recursoshumanos.show',
                                ],
                            ],
                        ],
                        [
                            'text' => '💻 Sistemas',
                            'icon' => 'fas fa-laptop-code',
                            'submenu' => [
                                [
                                    'text' => 'Crear KPI',
                                    'url'  => 'sistemas/kpis/create',
                                    'icon' => 'fas fa-plus',
                                    'can' => 'kpis.sistemas.create',
                                ],
                                [
                                    'text' => 'Ver KPIs',
                                    'url'  => 'sistemas/kpis',
                                    'icon' => 'fas fa-chart-bar',
                                    'can' => 'kpis.sistemas.index',
                                ],
                                [
                                    'text' => 'Indicadores',
                                    'url'  => 'sistemas/umbral/index',
                                    'icon' => 'fas fa-sliders-h',
                                    'can' => 'umbral.sistemas.index',
                                ],
                            ],
                        ],
                        [
                            'text' => '🧮 Contabilidad',
                            'icon' => 'fas fa-calculator',
                            'can'     => 'view.budget',
                            'submenu' => [
                                [
                                    'text' => 'Ejecución Presupuestal',
                                    'url'  => 'contabilidad/budget',
                                    'icon' => 'fas fa-money-bill',
                                    'can'  => 'Ejecución Presupuestal',
                                ],
                                [
                                    'text' => 'Registrar Presupuesto',
                                    'url'  => 'contabilidad/budget/create',
                                    'icon' => 'fas fa-plus-circle',
                                    'can'  => 'Registrar Presupuesto',
                                ],
                                [
                                    'text' => 'Recaudo de Cartera',
                                    'url'  => 'contabilidad/cartera',
                                    'icon' => 'fas fa-coins',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'text' => '🗂️ Gestión Documental',
                    'icon' => 'fas fa-file-pdf',
                    'can'  => 'documents-general-access',
                    'submenu' => [
                        [
                            'text' => '❤️ Enfermería',
                            'url'  => 'enfermeria/documents',
                            'icon' => 'fas fa-heartbeat',
                            'can' => 'documents-enfermeria',
                        ],
                        [
                            'text' => '🛒 Compras',
                            'url'  => 'compras/documents',
                            'icon' => 'fas fa-shopping-cart',
                            'can' => 'documents-compras',
                        ],
                        [
                            'text' => '👥 RRHH',
                            'url'  => 'rrhh/documents',
                            'icon' => 'fas fa-users',
                            'can' => 'documents-rrhh',
                        ],
                        [
                            'text' => '💻 Sistemas',
                            'url'  => 'sistemas/documents',
                            'icon' => 'fas fa-laptop-code',
                            'can' => 'documents-sistemas',
                        ],
                        [
                            'text' => '🧮 Contabilidad',
                            'url'  => 'contabilidad/documents',
                            'icon' => 'fas fa-calculator',
                            'can' => 'documents-contabilidad',
                        ],
                        [
                            'text' => '🏢 Institucional',
                            'url'  => 'institucional/documents',
                            'icon' => 'fas fa-building',
                            'can' => 'institucional-access',
                        ],
                    ],
                ],
                [
                    'text' => '🗺️ Previsitas',
                    'url'  => 'previsitas',
                    'icon' => 'fas fa-map-marker-alt',
                    'can'  => 'previsitas.view',
                    'active' => ['previsitas*'],
                ],
                // [
                //     'text' => '💰 Presupuesto',
                //     'url'  => 'presupuesto',
                //     'icon' => 'fas fa-calculator',
                //     'can'  => 'admin',
                //     'active' => ['presupuesto*'],
                // ],
            ],
        ],

        // === MÓDULO DE INFORMES ===
        [
            'text' => '📊 Informes y Reportes',
            'icon' => 'fas fa-chart-bar',
            'can'  => 'view.reports',
            'submenu' => [
                [
                    'text' => '🎫 Help-Desk',
                    'url'  => 'tickets/dashboard',
                    'icon' => 'fas fa-ticket-alt',
                    'active' => ['tickets/dashboard*'],
                ],
                [
                    'text' => '🔧 Mantenimiento',
                    'url'  => 'maintenance/dashboard',
                    'icon' => 'fas fa-tools',
                    'active' => ['maintenance/dashboard*'],
                ],
                [
                    'text' => '⏰ Biométrico',
                    'url'  => 'attendance/dashboard/actual',
                    'icon' => 'fas fa-clock',
                    'active' => ['attendance/dashboard*'],
                ],
                [
                    'text' => '📅 Biométrico Semanal',
                    'url'  => 'weekly-biometric/dashboard',
                    'icon' => 'fas fa-chart-pie',
                    'active' => ['weekly-biometric/dashboard*'],
                ],
                [
                    'text' => '😷 Ausentismos',
                    'url'  => 'ausentismos/dashboard',
                    'icon' => 'fas fa-user-slash',
                    'active' => ['ausentismos/dashboard*'],
                ],
                [
                    'text' => '📅 Eventos',
                    'url'  => 'events/dashboard',
                    'icon' => 'fas fa-calendar-check',
                    'active' => ['events/dashboard*'],
                ],
                [
                    'text' => '📈 KPIs',
                    'url'  => 'admin/kpis/report',
                    'icon' => 'fas fa-chart-line',
                    'active' => ['admin/kpis*'],
                ],
                [
                    'text' => '💻 Equipos',
                    'url'  => 'equipment/dashboard',
                    'icon' => 'fas fa-laptop',
                    'active' => ['equipment/dashboard*'],
                ],
                [
                    'text' => '📄 Fotocopias',
                    'url'  => 'photocopies/dashboard',
                    'icon' => 'fas fa-print',
                    'active' => ['photocopies/dashboard*'],
                ],
            ],
        ],

        // === MÓDULO DE CONFIGURACIÓN ===
        [
            'text' => '⚙️ Configuración',
            'icon' => 'fas fa-cogs',
            'submenu' => [
                [
                    'text' => '👤 Mi Perfil',
                    'url' => 'admin/settings',
                    'icon' => 'fas fa-user',
                ],
                [
                    'text' => '👥 Usuarios',
                    'url'  => 'admin/users',
                    'icon' => 'fas fa-users',
                    'can' => 'view.users',
                    'active' => ['admin/users*'],
                ],
                [
                    'text' => '🔄 Cambiar Usuario',
                    'url'  => 'admin/users',
                    'icon' => 'fas fa-user-secret',
                    'can'  => 'inpersonate',
                    'active' => ['impersonate/*'],
                ],
                [
                    'text' => '🛡️ Roles y Permisos',
                    'url'  => 'admin/roles',
                    'icon' => 'fas fa-user-shield',
                    'can' => 'view.roles',
                    'active' => ['admin/roles*'],
                ],
                [
                    'text' => '📤 Subir Información',
                    'icon' => 'fas fa-upload',
                    'can' => 'view.upload',
                    'submenu' => [
                        [
                            'text' => 'Biométrico Diario',
                            'url'  => 'attendance/upload',
                            'icon' => 'fas fa-clock',
                            'active' => ['attendance/upload*'],
                        ],
                        [
                            'text' => 'Biométrico Semanal',
                            'url'  => 'weekly-biometric',
                            'icon' => 'fas fa-calendar-week',
                            'active' => ['weekly-biometric*'],
                        ],
                        [
                            'text' => 'Ausentismos',
                            'url'  => 'ausentismos/upload',
                            'icon' => 'fas fa-user-slash',
                            'active' => ['ausentismos/upload*'],
                        ],
                    ],
                ],
                [
                    'text' => '📢 Avisos',
                    'icon' => 'fas fa-bullhorn',
                    'can' => 'view.announcements',
                    'submenu' => [
                        [
                            'text' => 'Crear Aviso',
                            'url'  => 'announcements/create',
                            'icon' => 'fas fa-plus',
                        ],
                        [
                            'text' => 'Gestionar Avisos',
                            'url'  => 'announcements',
                            'icon' => 'fas fa-list',
                        ],
                    ],
                ],
                [
                    'text' => '🏢 Espacios (Admin)',
                    'icon' => 'fas fa-building',
                    'can'  => 'admin.spaces',
                    'submenu' => [
                        [
                            'text' => 'Gestionar Espacios',
                            'url'  => 'spaces',
                            'icon' => 'fas fa-building',
                            'active' => ['spaces*'],
                        ],
                        [
                            'text' => 'Ciclos Escolares',
                            'url'  => 'school-cycles',
                            'icon' => 'fas fa-sync-alt',
                            'active' => ['school-cycles*'],
                        ],
                        [
                            'text' => 'Días Festivos',
                            'url'  => 'holidays',
                            'icon' => 'fas fa-calendar-check',
                            'active' => ['holidays*'],
                        ],
                        [
                            'text' => 'Bloqueos',
                            'url'  => 'space-blocks',
                            'icon' => 'fas fa-ban',
                            'active' => ['space-blocks*'],
                        ],
                        [
                            'text' => 'Aprobar Reservas',
                            'url'  => 'space-reservations/pending',
                            'icon' => 'fas fa-clipboard-check',
                            'active' => ['space-reservations/pending*'],
                        ],
                    ],
                ],
                [
                    'text' => '💻 Equipos (Admin)',
                    'icon' => 'fas fa-laptop',
                    'can'  => 'equipment.manage',
                    'submenu' => [
                        [
                            'text' => 'Inventario Inicial',
                            'url'  => 'equipment/inventory',
                            'icon' => 'fas fa-clipboard-list',
                            'active' => ['equipment/inventory*'],
                        ],
                        [
                            'text' => 'Gestionar Equipos',
                            'url'  => 'equipment',
                            'icon' => 'fas fa-boxes',
                            'active' => ['equipment*'],
                        ],
                    ],
                ],
                [
                    'text' => '🔧 Configuración General',
                    'url'  => 'admin/configuration',
                    'icon' => 'fas fa-sliders-h',
                    'can'  => 'manage.configuration',
                ],
            ],
        ],

        // === AYUDA ===
        [
            'text' => '❓ Ayuda',
            'url'  => 'help-videos',
            'icon' => 'fas fa-question-circle',
            'active' => ['help-videos*'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => true,  // Reactivar Select2
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2-bootstrap4-theme/1.0.0/select2-bootstrap4.min.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@11',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
        'Summernote' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.css',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,

    // Agregar estilos personalizados
    'custom_css' => '
        .menu-item {
            margin-bottom: 5px;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }
        .menu-item:hover,
        .menu-item.active {
            border-left-color: #3498db;
            background: rgba(52, 152, 219, 0.1);
        }
        .submenu-item {
            padding-left: 15px;
            border-left: 2px solid transparent;
            transition: all 0.3s ease;
        }
        .submenu-item:hover,
        .submenu-item.active {
            border-left-color:rgb(5, 105, 172);
            background: rgba(52, 152, 219, 0.05);
        }
        .nav-sidebar .nav-item > .nav-link {
            padding: 12px 15px;
        }
        .nav-sidebar .nav-link:focus {
            color: #fff;
            background-color: rgba(52, 152, 219, 0.2);
        }
        .nav-treeview {
            padding-left: 15px;
        }
        .nav-treeview > .nav-item > .nav-link {
            padding: 10px 15px;
        }
    ',

    // Add custom CSS files
    'css' => [
        'css/custom-sidebar.css',
    ],

    // Add custom JS files
    'js' => [
        'js/menu-enhancements.js',
    ],
];