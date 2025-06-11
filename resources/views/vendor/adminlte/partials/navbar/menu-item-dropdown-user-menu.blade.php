@php
    // Forzar siempre el uso de la ruta nombrada para logout
    $logout_url = route('logout');
    $logout_url = View::getSection('logout_url') ?? $logout_url;
@endphp
@php
    $profile_url = View::getSection('profile_url') ?? config('adminlte.profile_url', 'logout');
@endphp

<li class="nav-item dropdown user-menu">
    {{-- User menu toggler --}}
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        <img src="{{ Auth::user()->adminlte_image() }}" class="user-image img-circle elevation-2" alt="{{ Auth::user()->name }}">
        <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
    </a>

    {{-- User menu dropdown --}}
    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
        {{-- User menu header --}}
        <li class="user-header position-relative" style="padding: 20px;">
            <div class="user-image-container mb-2">
                <img src="{{ Auth::user()->adminlte_image() }}" 
                     class="img-circle elevation-3" 
                     alt="{{ Auth::user()->name }}"
                     style="width: 90px; height: 90px; border: 3px solid rgba(54, 78, 118, 0.9); object-fit: cover;">
            </div>
            <p class="mb-0 text-dark">
                <span class="d-block" style="font-size: 1.2rem; font-weight: 600;">
                    {{ Auth::user()->name }}
                </span>
                <small class="d-block mt-1" style="font-size: 0.9rem; opacity: 0.8;">
                    <i class="fas fa-user-tag mr-1"></i>
                    {{ Auth::user()->adminlte_desc() }}
                </small>
            </p>
        </li>

        {{-- Impersonation warning --}}
        @if(session()->has('impersonate'))
            <li class="user-header bg-warning">
                <p class="mb-0">
                    <i class="fas fa-user-secret"></i> 
                    Estás viendo como: {{ Auth::user()->name }}
                </p>
                <small>Cuenta original: {{ App\Models\User::find(session('impersonate'))->name }}</small>
                <div class="mt-2">
                    <a href="{{ route('impersonate.stop') }}" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-sign-out-alt"></i> Volver a mi cuenta
                    </a>
                </div>
            </li>
        @endif

        {{-- Configured user menu links --}}
        @each('adminlte::partials.navbar.dropdown-item', $adminlte->menu("navbar-user"), 'item')

        {{-- User menu body --}}
        @hasSection('usermenu_body')
            <li class="user-body">
                @yield('usermenu_body')
            </li>
        @endif

        {{-- Impersonation status --}}
        @if(isset($impersonating) && $impersonating)
            <li class="user-body border-top border-bottom text-center py-3">
                <div class="d-flex flex-column align-items-center">
                    <div class="badge badge-warning w-100 mb-2">
                        <i class="fas fa-user-secret"></i> Viendo como {{ Auth::user()->name }}
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('impersonate.stop') }}" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-sign-out-alt"></i> Salir de esta sesión
                        </a>
                    </div>
                </div>
            </li>
        @endif

        {{-- User menu footer --}}
        <li class="user-footer">
            @if($profile_url)
                <a href="{{ $profile_url }}" class="btn btn-default btn-flat">
                    <i class="fa fa-fw fa-user text-lightblue"></i>
                    Perfil
                </a>
            @endif
            <a class="btn btn-default btn-flat float-right @if(!$profile_url) btn-block @endif"
               href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fa fa-fw fa-power-off text-red"></i>
                Cerrar Sesión
            </a>
            <form id="logout-form" action="{{ $logout_url }}" method="POST" style="display: none;">
                @if(config('adminlte.logout_method'))
                    {{ method_field(config('adminlte.logout_method')) }}
                @endif
                {{ csrf_field() }}
            </form>
        </li>
    </ul>
</li>
