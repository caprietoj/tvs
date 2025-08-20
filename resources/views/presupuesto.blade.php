@extends('adminlte::page')

@section('title', 'Presupuesto Institucional')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1>Presupuesto Institucional</h1>
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Panel de Control Presupuestal</h3>
                </div>
                <div class="card-body p-0">
                    <div style="height: 80vh;">
                        <iframe src="https://docs.google.com/spreadsheets/d/e/2PACX-1vTa6p5Z7trJv0qMl30DxqisXIwDwV1X3n4HdeG-pWrHGck5tcKK7BsL5zyLxQrky6eLVXcGdGVybg9I/pubhtml?widget=true&headers=false" 
                                width="100%" 
                                height="100%" 
                                frameborder="0" 
                                style="border: 0;">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
