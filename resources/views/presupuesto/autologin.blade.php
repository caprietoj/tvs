@extends('adminlte::page')

@section('title', 'Redirigiendo a Presupuesto')

@section('content_header')
    <h1>Conectando con Sistema de Presupuesto</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-3">Generando acceso seguro al sistema de presupuesto...</p>
            <p class="text-muted small">Si no es redirigido automáticamente, 
                <a href="{{ config('app.presupuesto_url', env('PRESUPUESTO_URL', 'http://127.0.0.1:9000')) }}/login" target="_blank">haga clic aquí</a>
            </p>
        </div>
    </div>
@stop

@section('js')
<script>
async function irAPresupuesto() {
    const presupuestoUrl = '{{ config("app.presupuesto_url", env("PRESUPUESTO_URL", "http://127.0.0.1:9000")) }}';
    const apiSecret = '{{ config("app.presupuesto_api_secret", env("PRESUPUESTO_API_SECRET", "")) }}';
    
    try {
        const response = await fetch(`${presupuestoUrl}/api/autologin/generate-token`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                user_id: {{ Auth::id() }},
                user_email: '{{ Auth::user()->email }}',
                user_name: '{{ Auth::user()->name }}',
                secret: apiSecret
            })
        });
        
        const data = await response.json();
        
        if (data.success && data.url) {
            // Abrir en nueva pestaña
            window.open(data.url, '_blank');
            // Volver al dashboard
            setTimeout(() => {
                window.location.href = '{{ route("home") }}';
            }, 500);
        } else {
            console.error('Error en respuesta:', data);
            // Fallback al login normal
            window.open(`${presupuestoUrl}/login`, '_blank');
            setTimeout(() => {
                window.location.href = '{{ route("home") }}';
            }, 500);
        }
    } catch (error) {
        console.error('Error de conexión:', error);
        // Fallback al login normal
        window.open(`${presupuestoUrl}/login`, '_blank');
        setTimeout(() => {
            window.location.href = '{{ route("home") }}';
        }, 500);
    }
}

// Ejecutar automáticamente al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    irAPresupuesto();
});
</script>
@stop
