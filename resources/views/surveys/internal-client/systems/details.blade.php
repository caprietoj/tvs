<div class="row">
    <div class="col-md-6">
        <h6><i class="fas fa-info-circle"></i> Información General</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Fecha:</strong></td>
                <td>{{ $result->response_timestamp->format('d/m/Y H:i:s') }}</td>
            </tr>
            <tr>
                <td><strong>Dependencia:</strong></td>
                <td>{{ $result->dependencia }}</td>
            </tr>
            <tr>
                <td><strong>Período:</strong></td>
                <td>{{ $result->getMonthName() }} {{ $result->survey_year }}</td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6><i class="fas fa-star"></i> Calificaciones</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Tiempos de Respuesta:</strong></td>
                <td>
                    <span class="badge badge-{{ $result->tiempos_respuesta == 'Excelente' ? 'success' : ($result->tiempos_respuesta == 'Buena' ? 'primary' : 'warning') }}">
                        {{ $result->tiempos_respuesta }}
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Efectividad Técnica:</strong></td>
                <td>
                    <span class="badge badge-{{ $result->efectividad_tecnica == 'Muy efectiva' ? 'success' : ($result->efectividad_tecnica == 'Efectiva' ? 'primary' : 'warning') }}">
                        {{ $result->efectividad_tecnica }}
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Profesionalismo:</strong></td>
                <td>
                    <span class="badge badge-{{ $result->profesionalismo == 'Excelente' ? 'success' : ($result->profesionalismo == 'Bueno' ? 'primary' : 'warning') }}">
                        {{ $result->profesionalismo }}
                    </span>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <h6><i class="fas fa-desktop"></i> Equipos y Tecnología</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Estado de Equipos:</strong></td>
                <td>
                    <span class="badge badge-{{ $result->estado_equipos == 'Excelente' ? 'success' : ($result->estado_equipos == 'Bueno' ? 'primary' : 'warning') }}">
                        {{ $result->estado_equipos }}
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Apoyo en Usabilidad:</strong></td>
                <td>
                    <span class="badge badge-{{ $result->apoyo_usabilidad == 'Excelente' ? 'success' : ($result->apoyo_usabilidad == 'Bueno' ? 'primary' : 'warning') }}">
                        {{ $result->apoyo_usabilidad }}
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Calidad Internet:</strong></td>
                <td>
                    <span class="badge badge-{{ $result->calidad_internet == 'Excelente' ? 'success' : ($result->calidad_internet == 'Buena' ? 'primary' : 'warning') }}">
                        {{ $result->calidad_internet }}
                    </span>
                </td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6><i class="fas fa-calendar-alt"></i> Eventos y Plataformas</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Intervención en Eventos:</strong></td>
                <td>
                    <span class="badge badge-{{ $result->intervencion_eventos == 'Excelente' ? 'success' : ($result->intervencion_eventos == 'Buenas' ? 'primary' : 'warning') }}">
                        {{ $result->intervencion_eventos }}
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Plataformas de Interacción:</strong></td>
                <td>{{ $result->plataformas_interaccion }}</td>
            </tr>
            @if($result->otra_plataforma)
            <tr>
                <td><strong>Otra Plataforma:</strong></td>
                <td>{{ $result->otra_plataforma }}</td>
            </tr>
            @endif
        </table>
    </div>
</div>

@if($result->comentarios_personal || $result->comentarios_equipos || $result->comentarios_eventos)
<div class="row">
    <div class="col-12">
        <h6><i class="fas fa-comments"></i> Comentarios</h6>
        @if($result->comentarios_personal)
        <div class="alert alert-info">
            <strong>Comentarios sobre Personal:</strong><br>
            {{ $result->comentarios_personal }}
        </div>
        @endif
        @if($result->comentarios_equipos)
        <div class="alert alert-warning">
            <strong>Comentarios sobre Equipos:</strong><br>
            {{ $result->comentarios_equipos }}
        </div>
        @endif
        @if($result->comentarios_eventos)
        <div class="alert alert-primary">
            <strong>Comentarios sobre Eventos:</strong><br>
            {{ $result->comentarios_eventos }}
        </div>
        @endif
    </div>
</div>
@endif

@if($result->aspectos_destacados || $result->oportunidades_mejora)
<div class="row">
    <div class="col-md-6">
        @if($result->aspectos_destacados)
        <h6><i class="fas fa-thumbs-up text-success"></i> Aspectos Destacados</h6>
        <div class="alert alert-success">
            {{ $result->aspectos_destacados }}
        </div>
        @endif
    </div>
    <div class="col-md-6">
        @if($result->oportunidades_mejora)
        <h6><i class="fas fa-tools text-warning"></i> Oportunidades de Mejora</h6>
        <div class="alert alert-warning">
            {{ $result->oportunidades_mejora }}
        </div>
        @endif
    </div>
</div>
@endif

@if($result->problemas_conectividad)
<div class="row">
    <div class="col-12">
        <h6><i class="fas fa-wifi text-danger"></i> Problemas de Conectividad</h6>
        <div class="alert alert-danger">
            {{ $result->problemas_conectividad }}
        </div>
    </div>
</div>
@endif
