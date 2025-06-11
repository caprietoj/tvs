@extends('adminlte::page')

@section('title', 'Crear Solicitud de Compra o Materiales')

@section('content_header')
    <h1 style="color: #364E76;"><i class="fas fa-clipboard-list mr-2"></i>Nueva Solicitud</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card card-outline" style="border-top-color: #364E76;">
                <div class="card-header" style="background-color: #364E76; color: white;">
                    <h3 class="card-title">Seleccione el tipo de solicitud que desea crear</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card card-type-selector mb-4" id="purchaseCard">
                                <div class="ribbon-wrapper ribbon-lg">
                                    <div class="ribbon" style="background-color: #364E76; color: white;">
                                        Compras
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <div class="icon-container mb-3">
                                        <i class="fas fa-shopping-cart fa-4x" style="color: #364E76;"></i>
                                    </div>
                                    <h4 class="card-title">Solicitud de Compra</h4>
                                    <p class="card-text">
                                        Utilice este formulario para solicitar la compra de insumos, 
                                        equipos y materiales para su departamento o área de trabajo.
                                    </p>
                                    <ul class="list-group list-group-flush text-left mb-4">
                                        <li class="list-group-item"><i class="fas fa-check text-success mr-2"></i>Solicitudes de productos específicos</li>
                                        <li class="list-group-item"><i class="fas fa-check text-success mr-2"></i>Cotizaciones y presupuestos</li>
                                        <li class="list-group-item"><i class="fas fa-check text-success mr-2"></i>Servicios externos</li>
                                    </ul>
                                    <button type="button" id="purchaseButton" class="btn btn-lg btn-block" style="background-color: #364E76; color: white;">
                                        <i class="fas fa-shopping-cart mr-2"></i> Crear Solicitud de Compra
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-type-selector" id="materialsCard">
                                <div class="ribbon-wrapper ribbon-lg">
                                    <div class="ribbon bg-success">
                                        Materiales
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <div class="icon-container mb-3">
                                        <i class="fas fa-box fa-4x text-success"></i>
                                    </div>
                                    <h4 class="card-title">Solicitud de Materiales</h4>
                                    <p class="card-text">
                                        Utilice este formulario para solicitar materiales de oficina 
                                        y papelería para su departamento.
                                    </p>
                                    <ul class="list-group list-group-flush text-left mb-4">
                                        <li class="list-group-item"><i class="fas fa-check text-success mr-2"></i>Material de oficina</li>
                                        <li class="list-group-item"><i class="fas fa-check text-success mr-2"></i>Materiales didácticos</li>
                                        <li class="list-group-item"><i class="fas fa-check text-success mr-2"></i>Papelería</li>
                                    </ul>
                                    <button type="button" id="materialsButton" class="btn btn-lg btn-success btn-block">
                                        <i class="fas fa-box mr-2"></i> Crear Solicitud de Materiales
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-type-selector" id="copiesCard">
                                <div class="ribbon-wrapper ribbon-lg">
                                    <div class="ribbon bg-info">
                                        Fotocopias
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <div class="icon-container mb-3">
                                        <i class="fas fa-copy fa-4x text-info"></i>
                                    </div>
                                    <h4 class="card-title">Solicitud de Fotocopias</h4>
                                    <p class="card-text">
                                        Utilice este formulario para solicitar servicios de fotocopiado
                                        para su departamento.
                                    </p>
                                    <ul class="list-group list-group-flush text-left mb-4">
                                        <li class="list-group-item"><i class="fas fa-check text-info mr-2"></i>Servicio de fotocopiado</li>
                                        <li class="list-group-item"><i class="fas fa-check text-info mr-2"></i>Copias en blanco y negro</li>
                                        <li class="list-group-item"><i class="fas fa-check text-info mr-2"></i>Copias a color</li>
                                    </ul>
                                    <button type="button" id="copiesButton" class="btn btn-lg btn-info btn-block">
                                        <i class="fas fa-copy mr-2"></i> Crear Solicitud de Fotocopias
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('purchase-requests.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i> Volver al listado de solicitudes
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    :root {
        --institutional-blue: #364E76;
    }
    
    .card-type-selector {
        transition: all 0.3s ease;
        position: relative;
        border: 2px solid #f8f9fa;
        overflow: hidden;
        height: 100%;
    }
    
    .card-type-selector:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }
    
    .card-type-selector.selected {
        border-color: var(--institutional-blue);
        box-shadow: 0 10px 20px rgba(54, 78, 118, 0.2);
    }
    
    .card-type-selector.selected#materialsCard {
        border-color: #28a745;
        box-shadow: 0 10px 20px rgba(40, 167, 69, 0.2);
    }
    
    .card-type-selector.selected#copiesCard {
        border-color: #17a2b8;
        box-shadow: 0 10px 20px rgba(23, 162, 184, 0.2);
    }
    
    .icon-container {
        margin: 15px auto;
        height: 100px;
        width: 100px;
        line-height: 100px;
        border-radius: 50%;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }
    
    .card-type-selector:hover .icon-container {
        transform: scale(1.1);
    }
    
    .btn-lg {
        padding: 12px 20px;
        font-size: 1.1rem;
    }
    
    .list-group-item {
        border-left: none;
        border-right: none;
        border-bottom: 1px solid rgba(0,0,0,.125);
        padding: 0.75rem 0;
    }
    
    .list-group-flush:last-child .list-group-item:last-child {
        border-bottom: 0;
    }
    
    .ribbon-wrapper {
        height: 70px;
        overflow: hidden;
        position: absolute;
        right: -2px;
        top: -2px;
        width: 70px;
        z-index: 10;
    }
    
    .ribbon-wrapper .ribbon {
        box-shadow: 0 0 3px rgba(0,0,0,.3);
        font-size: 0.8rem;
        line-height: 100%;
        padding: 0.375rem 0;
        position: relative;
        right: -2px;
        text-align: center;
        text-shadow: 0 -1px 0 rgba(0,0,0,.4);
        text-transform: uppercase;
        top: 10px;
        transform: rotate(45deg);
        width: 90px;
    }
    
    .btn-institutional {
        background-color: var(--institutional-blue);
        color: white;
    }
    
    .btn-institutional:hover {
        background-color: #2a3d5d;
        color: white;
    }
    
    .text-institutional {
        color: var(--institutional-blue);
    }
</style>
@stop

@section('js')
<script>
    $(function() {
        // Selección de tipo de solicitud al hacer clic en la tarjeta
        $('#purchaseCard').click(function() {
            $(this).addClass('selected');
            $('#materialsCard').removeClass('selected');
            $('#copiesCard').removeClass('selected');
        });
        
        $('#materialsCard').click(function() {
            $(this).addClass('selected');
            $('#purchaseCard').removeClass('selected');
            $('#copiesCard').removeClass('selected');
        });
        
        $('#copiesCard').click(function() {
            $(this).addClass('selected');
            $('#purchaseCard').removeClass('selected');
            $('#materialsCard').removeClass('selected');
        });
        
        // Redirigir a los formularios correspondientes
        $('#purchaseButton').click(function() {
            window.location.href = '{{ route("purchase-requests.create-purchase") }}';
        });
        
        $('#materialsButton').click(function() {
            window.location.href = '{{ route("purchase-requests.create-materials") }}';
        });
        
        $('#copiesButton').click(function() {
            window.location.href = '{{ route("purchase-requests.create-copies") }}';
        });
        
        // Añadir efecto hover en las tarjetas
        $('.card-type-selector').hover(
            function() {
                $(this).find('.btn').addClass('pulse');
            },
            function() {
                $(this).find('.btn').removeClass('pulse');
            }
        );
    });
</script>
@stop