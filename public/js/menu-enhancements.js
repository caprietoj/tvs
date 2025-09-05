/**
 * Mejoras de navegación para el menú reorganizado
 * Funcionalidades adicionales para mejor UX
 */

$(document).ready(function() {
    // ===== INICIALIZACIÓN ===== //
    initializeMenuEnhancements();
    initializeBreadcrumbs();
    initializeSearchFunctionality();
    initializeKeyboardNavigation();
    
    // ===== MEJORAS DEL MENÚ ===== //
    
    function initializeMenuEnhancements() {
        // Recordar estado del menú
        rememberMenuState();
        
        // Mejorar tooltips en sidebar colapsado
        enhanceCollapsedTooltips();
        
        // Añadir indicadores visuales
        addVisualIndicators();
        
        // Mejorar animaciones
        enhanceAnimations();
    }
    
    // Recordar qué secciones estaban abiertas
    function rememberMenuState() {
        // Guardar estado al hacer clic
        $('.nav-sidebar .has-treeview > .nav-link').on('click', function() {
            var menuId = $(this).closest('.nav-item').data('menu-id') || $(this).text().trim();
            var isOpen = $(this).closest('.nav-item').hasClass('menu-open');
            
            if (menuId) {
                localStorage.setItem('menu_' + menuId, !isOpen);
            }
        });
        
        // Restaurar estado al cargar
        $('.nav-sidebar .has-treeview').each(function() {
            var menuId = $(this).data('menu-id') || $(this).find('> .nav-link').text().trim();
            var savedState = localStorage.getItem('menu_' + menuId);
            
            if (savedState === 'true' && !$(this).hasClass('menu-open')) {
                $(this).find('> .nav-link').click();
            }
        });
    }
    
    // Tooltips mejorados para sidebar colapsado
    function enhanceCollapsedTooltips() {
        $('.nav-sidebar > .nav-item > .nav-link').each(function() {
            var title = $(this).find('p').text() || $(this).text().replace(/\s+/g, ' ').trim();
            $(this).attr('title', title);
        });
    }
    
    // Indicadores visuales para secciones activas
    function addVisualIndicators() {
        // Añadir pulso sutil a elementos activos
        $('.nav-link.active').addClass('pulse-active');
        
        // Indicador de carga al hacer clic
        $('.nav-sidebar .nav-link').on('click', function(e) {
            if ($(this).attr('href') && $(this).attr('href') !== '#') {
                $(this).addClass('loading');
                
                // Remover después de un tiempo
                setTimeout(() => {
                    $(this).removeClass('loading');
                }, 2000);
            }
        });
    }
    
    // Animaciones mejoradas
    function enhanceAnimations() {
        // Animación al expandir submenús
        $('.nav-sidebar .has-treeview > .nav-link').on('click', function() {
            var $treeview = $(this).siblings('.nav-treeview');
            if ($treeview.length) {
                $treeview.find('.nav-item').each(function(index) {
                    $(this).css('animation-delay', (index * 50) + 'ms');
                });
            }
        });
    }
    
    // ===== BREADCRUMBS INTELIGENTES ===== //
    
    function initializeBreadcrumbs() {
        updateBreadcrumbs();
        
        // Actualizar breadcrumbs al navegar
        $('.nav-sidebar .nav-link').on('click', function() {
            if ($(this).attr('href') && $(this).attr('href') !== '#') {
                setTimeout(updateBreadcrumbs, 100);
            }
        });
    }
    
    function updateBreadcrumbs() {
        var $activeLink = $('.nav-sidebar .nav-link.active').first();
        if ($activeLink.length) {
            var breadcrumbPath = buildBreadcrumbPath($activeLink);
            displayBreadcrumbs(breadcrumbPath);
        }
    }
    
    function buildBreadcrumbPath($activeLink) {
        var path = [];
        var $current = $activeLink;
        
        // Construir ruta desde el elemento activo hacia arriba
        while ($current.length) {
            var text = $current.find('p').text() || $current.text().replace(/\s+/g, ' ').trim();
            if (text && text !== 'search') {
                path.unshift({
                    text: text,
                    url: $current.attr('href') || '#'
                });
            }
            
            $current = $current.closest('.nav-treeview').prev('.nav-link');
        }
        
        return path;
    }
    
    function displayBreadcrumbs(path) {
        var $breadcrumb = $('.content-header .breadcrumb');
        if ($breadcrumb.length && path.length > 1) {
            $breadcrumb.empty();
            
            // Añadir elemento "Inicio"
            $breadcrumb.append('<li class="breadcrumb-item"><a href="/home"><i class="fas fa-home"></i> Inicio</a></li>');
            
            // Añadir elementos del path
            path.forEach(function(item, index) {
                var isLast = index === path.length - 1;
                var $item = $('<li class="breadcrumb-item"></li>');
                
                if (isLast) {
                    $item.addClass('active').text(item.text);
                } else {
                    $item.html('<a href="' + item.url + '">' + item.text + '</a>');
                }
                
                $breadcrumb.append($item);
            });
        }
    }
    
    // ===== BÚSQUEDA EN EL MENÚ ===== //
    
    function initializeSearchFunctionality() {
        // Crear barra de búsqueda si no existe
        if (!$('.menu-search').length) {
            var searchHtml = `
                <div class="menu-search p-3" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control form-control-sm" placeholder="🔍 Buscar en menú..." id="menuSearchInput">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary btn-sm" type="button" id="clearMenuSearch">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('.main-sidebar .nav-sidebar').prepend(searchHtml);
        }
        
        // Funcionalidad de búsqueda
        $('#menuSearchInput').on('input', function() {
            var searchTerm = $(this).val().toLowerCase();
            filterMenuItems(searchTerm);
        });
        
        // Limpiar búsqueda
        $('#clearMenuSearch').on('click', function() {
            $('#menuSearchInput').val('');
            filterMenuItems('');
        });
    }
    
    function filterMenuItems(searchTerm) {
        if (searchTerm === '') {
            // Mostrar todos los elementos
            $('.nav-sidebar .nav-item').show();
            $('.nav-sidebar .nav-treeview').show();
            return;
        }
        
        $('.nav-sidebar .nav-item').each(function() {
            var $item = $(this);
            var itemText = $item.find('.nav-link').text().toLowerCase();
            var hasMatch = itemText.includes(searchTerm);
            
            // Buscar en submenús también
            if (!hasMatch) {
                $item.find('.nav-treeview .nav-link').each(function() {
                    if ($(this).text().toLowerCase().includes(searchTerm)) {
                        hasMatch = true;
                        return false; // break
                    }
                });
            }
            
            if (hasMatch) {
                $item.show();
                // Expandir si tiene submenús con coincidencias
                if ($item.hasClass('has-treeview')) {
                    $item.addClass('menu-open');
                    $item.find('.nav-treeview').show();
                }
            } else {
                $item.hide();
            }
        });
    }
    
    // ===== NAVEGACIÓN POR TECLADO ===== //
    
    function initializeKeyboardNavigation() {
        var $menuItems = $('.nav-sidebar .nav-link');
        var currentIndex = -1;
        
        $(document).on('keydown', function(e) {
            // Solo activar si no estamos en un input
            if ($(e.target).is('input, textarea, select')) return;
            
            switch(e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    navigateMenu(1);
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    navigateMenu(-1);
                    break;
                case 'Enter':
                    e.preventDefault();
                    activateCurrentItem();
                    break;
                case 'Escape':
                    clearMenuSelection();
                    break;
            }
        });
        
        function navigateMenu(direction) {
            $menuItems.removeClass('keyboard-focus');
            
            if (currentIndex === -1) {
                currentIndex = direction > 0 ? 0 : $menuItems.length - 1;
            } else {
                currentIndex += direction;
                if (currentIndex >= $menuItems.length) currentIndex = 0;
                if (currentIndex < 0) currentIndex = $menuItems.length - 1;
            }
            
            $menuItems.eq(currentIndex).addClass('keyboard-focus').focus();
        }
        
        function activateCurrentItem() {
            if (currentIndex >= 0) {
                $menuItems.eq(currentIndex)[0].click();
            }
        }
        
        function clearMenuSelection() {
            $menuItems.removeClass('keyboard-focus');
            currentIndex = -1;
        }
    }
    
    // ===== UTILIDADES ADICIONALES ===== //
    
    // Mostrar notificaciones de navegación
    function showNavigationToast(message, type = 'info') {
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            // Fallback para mostrar mensaje
            console.log(`${type.toUpperCase()}: ${message}`);
        }
    }
    
    // Detectar sección actual automáticamente
    function detectCurrentSection() {
        var currentPath = window.location.pathname;
        $('.nav-sidebar .nav-link').removeClass('active');
        
        $('.nav-sidebar .nav-link').each(function() {
            var href = $(this).attr('href');
            if (href && currentPath.includes(href)) {
                $(this).addClass('active');
                
                // Expandir menús padre
                var $parent = $(this).closest('.nav-treeview').prev('.nav-link');
                while ($parent.length) {
                    $parent.closest('.nav-item').addClass('menu-open');
                    $parent = $parent.closest('.nav-treeview').prev('.nav-link');
                }
            }
        });
    }
    
    // Ejecutar detección al cargar
    detectCurrentSection();
    
    // Actualizar cada vez que cambie la URL (para SPAs)
    if (window.history && window.history.pushState) {
        var originalPushState = window.history.pushState;
        window.history.pushState = function() {
            originalPushState.apply(window.history, arguments);
            setTimeout(detectCurrentSection, 100);
        };
    }
});

// ===== CSS DINÁMICO ADICIONAL ===== //

// Añadir estilos para navegación por teclado
$('head').append(`
<style>
.nav-link.keyboard-focus {
    outline: 2px solid #007bff !important;
    outline-offset: 2px;
    background-color: rgba(0, 123, 255, 0.1) !important;
}

.pulse-active {
    animation: pulse-glow 2s infinite;
}

@keyframes pulse-glow {
    0% { box-shadow: 0 0 5px rgba(40, 167, 69, 0.4); }
    50% { box-shadow: 0 0 15px rgba(40, 167, 69, 0.6); }
    100% { box-shadow: 0 0 5px rgba(40, 167, 69, 0.4); }
}

.menu-search .form-control {
    background: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: white !important;
}

.menu-search .form-control::placeholder {
    color: rgba(255, 255, 255, 0.6) !important;
}

.menu-search .btn {
    background: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.2) !important;
    color: rgba(255, 255, 255, 0.8) !important;
}

.menu-search .btn:hover {
    background: rgba(255, 255, 255, 0.2) !important;
    color: white !important;
}
</style>
`);
