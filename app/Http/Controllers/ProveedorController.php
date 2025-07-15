<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use App\Exports\ProveedoresExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use App\Services\ProveedorNotificationService;

class ProveedorController extends Controller
{
    public function index()
    {
        $proveedores = Proveedor::all();
        return view('proveedores.index', compact('proveedores'));
    }

    public function create()
    {
        return view('proveedores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required',
            'nit' => 'required|unique:proveedors',
            'direccion' => 'required',
            'ciudad' => 'required',
            'telefono' => 'required',
            'email' => 'required|email|unique:proveedors',
            'persona_contacto' => 'required',
            'servicio_producto' => 'required',
            'proveedor_critico' => 'required|boolean',
            'alto_riesgo' => 'required|boolean',
            'criterios_tecnicos' => 'required|numeric|min:0|max:100',
            'camara_comercio' => 'nullable|mimes:pdf|max:10240',
            'rut' => 'nullable|mimes:pdf|max:10240',
            'cedula_representante' => 'nullable|mimes:pdf,jpg,jpeg,png|max:10240',
            'certificacion_bancaria' => 'nullable|mimes:pdf|max:10240',
            'seguridad_social' => 'nullable|mimes:pdf|max:10240',
            'certificacion_alturas' => 'nullable|mimes:pdf|max:10240',
            'matriz_peligros' => 'nullable|mimes:pdf|max:10240',
            'matriz_epp' => 'nullable|mimes:pdf|max:10240',
            'estadisticas' => 'nullable|mimes:pdf|max:10240',
            'market_segment' => 'required|string|max:255',
        ]);

        try {
            // Crear array con todos los datos del proveedor
            $proveedorData = $request->only([
                'nombre', 'nit', 'direccion', 'ciudad', 'telefono', 
                'email', 'persona_contacto', 'market_segment',
                'servicio_producto', 'proveedor_critico', 'alto_riesgo',
                'forma_pago', 'descuento', 'cobertura', 'referencias_comerciales',
                'nivel_precios', 'valores_agregados', 'criterios_tecnicos'
            ]);

            // Crear proveedor con los datos
            $proveedor = Proveedor::create($proveedorData);
            
            $puntajes = [
                'puntaje_forma_pago' => $this->calcularPuntajeFormaPago($request->forma_pago),
                'puntaje_referencias' => $this->calcularPuntajeReferencias($request->referencias_comerciales),
                'puntaje_descuento' => $this->calcularPuntajeDescuento($request->descuento),
                'puntaje_cobertura' => $this->calcularPuntajeCobertura($request->cobertura),
                'puntaje_valores_agregados' => $this->calcularPuntajeValoresAgregados($request->valores_agregados),
                'puntaje_precios' => $this->calcularPuntajePrecios($request->nivel_precios),
                'puntaje_criterios_tecnicos' => $request->criterios_tecnicos ?? 60
            ];
            
            $proveedor->update($puntajes);
            $proveedor->calcularPuntajeTotal();

            // Handle file uploads
            $proveedorPath = 'proveedores/' . Str::slug($proveedor->nombre) . '_' . $proveedor->id;

            $documentos = [
                'camara_comercio', 'rut', 'cedula_representante', 'certificacion_bancaria',
                'seguridad_social', 'certificacion_alturas', 'matriz_peligros',
                'matriz_epp', 'estadisticas'
            ];

            foreach ($documentos as $documento) {
                if ($request->hasFile($documento) && $request->file($documento)->isValid()) {
                    $file = $request->file($documento);
                    $filename = $documento . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs($proveedorPath, $filename, 'public');
                    
                    if ($path) {
                        $proveedor->{$documento . '_path'} = $path;
                    }
                }
            }

            $proveedor->save();
            
            // Enviar notificación por email
            $notificationService = new ProveedorNotificationService();
            $notificationService->sendProveedorCreatedNotification($proveedor);
            
            return redirect()->route('proveedores.index')->with('success', 'Proveedor creado exitosamente.');
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error al crear proveedor: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Ha ocurrido un error al procesar su solicitud: ' . $e->getMessage()])->withInput();
        }
    }

    public function show($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return view('proveedores.show', compact('proveedor'));
    }

    public function edit($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(Request $request, $id)
    {
        $proveedor = Proveedor::findOrFail($id);
        
        $request->validate([
            'nombre' => 'required',
            'nit' => 'required|unique:proveedors,nit,' . $proveedor->id,
            'direccion' => 'required',
            'ciudad' => 'required',
            'telefono' => 'required',
            'email' => 'required|email|unique:proveedors,email,' . $proveedor->id,
            'persona_contacto' => 'required',
            'servicio_producto' => 'required',
            'proveedor_critico' => 'required|boolean',
            'alto_riesgo' => 'required|boolean',
            'criterios_tecnicos' => 'required|numeric|min:0|max:100',
            'camara_comercio' => 'nullable|mimes:pdf|max:10240',
            'rut' => 'nullable|mimes:pdf|max:10240',
            'cedula_representante' => 'nullable|mimes:pdf,jpg,jpeg,png|max:10240',
            'certificacion_bancaria' => 'nullable|mimes:pdf|max:10240',
            'seguridad_social' => 'nullable|mimes:pdf|max:10240',
            'certificacion_alturas' => 'nullable|mimes:pdf|max:10240',
            'matriz_peligros' => 'nullable|mimes:pdf|max:10240',
            'matriz_epp' => 'nullable|mimes:pdf|max:10240',
            'estadisticas' => 'nullable|mimes:pdf|max:10240',
            'market_segment' => 'required|string|max:255',
        ]);

        try {
            $puntajes = [
                'puntaje_forma_pago' => $this->calcularPuntajeFormaPago($request->forma_pago),
                'puntaje_referencias' => $this->calcularPuntajeReferencias($request->referencias_comerciales),
                'puntaje_descuento' => $this->calcularPuntajeDescuento($request->descuento),
                'puntaje_cobertura' => $this->calcularPuntajeCobertura($request->cobertura),
                'puntaje_valores_agregados' => $this->calcularPuntajeValoresAgregados($request->valores_agregados),
                'puntaje_precios' => $this->calcularPuntajePrecios($request->nivel_precios),
                'puntaje_criterios_tecnicos' => $request->criterios_tecnicos ?? 60
            ];

            // Update basic provider data
            $proveedor->update(array_merge($request->except([
                'camara_comercio', 'rut', 'cedula_representante', 'certificacion_bancaria',
                'seguridad_social', 'certificacion_alturas', 'matriz_peligros',
                'matriz_epp', 'estadisticas'
            ]), $puntajes));

            $proveedorPath = 'proveedores/' . Str::slug($proveedor->nombre) . '_' . $proveedor->id;
            
            // Handle file uploads
            $documentos = [
                'camara_comercio', 'rut', 'cedula_representante', 'certificacion_bancaria',
                'seguridad_social', 'certificacion_alturas', 'matriz_peligros',
                'matriz_epp', 'estadisticas'
            ];
            
            foreach ($documentos as $documento) {
                if ($request->hasFile($documento) && $request->file($documento)->isValid()) {
                    // Delete old file if exists
                    if ($proveedor->{$documento . '_path'} && Storage::disk('public')->exists($proveedor->{$documento . '_path'})) {
                        Storage::disk('public')->delete($proveedor->{$documento . '_path'});
                    }

                    $file = $request->file($documento);
                    $filename = $documento . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs($proveedorPath, $filename, 'public');
                    
                    if ($path) {
                        $proveedor->{$documento . '_path'} = $path;
                    }
                }
            }

            $proveedor->save();
            $proveedor->calcularPuntajeTotal();
            
            return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado exitosamente.');
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Error al actualizar proveedor: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Ha ocurrido un error al procesar su solicitud: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        $proveedor = Proveedor::findOrFail($id);
        $proveedor->delete();
        return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado exitosamente.');
    }

    private function calcularPuntajeFormaPago($formaPago)
    {
        return match($formaPago) {
            '0-30' => 20,
            '31-60' => 50,
            '61-90' => 100,
            default => 0
        };
    }

    private function calcularPuntajeReferencias($referencias)
    {
        return match($referencias) {
            '3' => 100,
            '2' => 60,
            '1' => 30,
            default => 0
        };
    }

    private function calcularPuntajeDescuento($descuento)
    {
        return match($descuento) {
            '15' => 100,
            '12' => 75,
            '10' => 50,
            '5' => 25,
            '0' => 0,
            default => 0
        };
    }

    private function calcularPuntajeCobertura($cobertura)
    {
        return match($cobertura) {
            '4' => 100,
            '2' => 70,
            '1' => 50,
            default => 0
        };
    }

    private function calcularPuntajeValoresAgregados($valores)
    {
        if (empty($valores)) {
            return 0;
        }
        return 80;
    }

    private function calcularPuntajePrecios($nivel)
    {
        return match($nivel) {
            'bajo' => 100,
            'promedio' => 50,
            'alto' => 0,
            default => 0
        };
    }

    /**
     * Export providers list to Excel
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
     */
    public function export()
    {
        if (!class_exists('Maatwebsite\Excel\Facades\Excel')) {
            return redirect()->route('proveedores.index')
                ->with('error', 'La funcionalidad de exportación no está disponible. Por favor instale el paquete Laravel Excel.');
        }
        
        return Excel::download(new ProveedoresExport, 'listado-proveedores.xlsx');
    }

    public function showImport()
    {
        return view('proveedores.import');
    }

    public function processImport(Request $request)
    {
        $request->validate([
            'data' => 'required|string'
        ]);

        try {
            \Log::info('Iniciando importación de proveedores', [
                'data_length' => strlen($request->data),
                'user_id' => auth()->id()
            ]);

            $rows = explode("\n", trim($request->data));
            $success = 0;
            $errors = [];

            \Log::info('Datos divididos en filas', [
                'total_rows' => count($rows),
                'first_row_preview' => isset($rows[0]) ? substr($rows[0], 0, 100) : 'N/A'
            ]);

            foreach ($rows as $index => $row) {
                try {
                    $row = trim($row);
                    if (empty($row)) {
                        \Log::info("Saltando fila vacía en posición " . ($index + 1));
                        continue;
                    }

                    $data = explode("\t", $row);
                    
                    \Log::info("Procesando fila " . ($index + 1), [
                        'raw_row' => $row,
                        'fields_count' => count($data),
                        'fields' => $data
                    ]);
                    
                    // Verificar que tenga todos los campos necesarios
                    if (count($data) < 11) {
                        $error = "Fila " . ($index + 1) . ": Faltan campos (se encontraron " . count($data) . ", se requieren 11)";
                        $errors[] = $error;
                        \Log::warning($error);
                        continue;
                    }

                    // Validar campos antes de crear
                    $proveedorData = [
                        'nombre' => trim($data[0]),
                        'nit' => trim($data[1]),
                        'direccion' => trim($data[2]),
                        'ciudad' => trim($data[3]),
                        'telefono' => trim($data[4]),
                        'email' => trim($data[5]),
                        'persona_contacto' => trim($data[6]),
                        'market_segment' => trim($data[7]),
                        'servicio_producto' => trim($data[8]),
                        'alto_riesgo' => filter_var(trim($data[9]), FILTER_VALIDATE_BOOLEAN),
                        'proveedor_critico' => filter_var(trim($data[10]), FILTER_VALIDATE_BOOLEAN),
                        // Valores por defecto para los campos de puntaje
                        'puntaje_forma_pago' => 60,
                        'puntaje_referencias' => 60,
                        'puntaje_descuento' => 0,
                        'puntaje_cobertura' => 50,
                        'puntaje_valores_agregados' => 0,
                        'puntaje_precios' => 50,
                        'puntaje_criterios_tecnicos' => 60
                    ];

                    // Validaciones básicas
                    if (empty($proveedorData['nombre'])) {
                        $errors[] = "Fila " . ($index + 1) . ": El nombre es requerido";
                        continue;
                    }

                    if (empty($proveedorData['nit'])) {
                        $errors[] = "Fila " . ($index + 1) . ": El NIT es requerido";
                        continue;
                    }

                    if (empty($proveedorData['email']) || !filter_var($proveedorData['email'], FILTER_VALIDATE_EMAIL)) {
                        $errors[] = "Fila " . ($index + 1) . ": Email inválido o faltante";
                        continue;
                    }

                    // Verificar duplicados
                    $existingNit = Proveedor::where('nit', $proveedorData['nit'])->first();
                    if ($existingNit) {
                        $errors[] = "Fila " . ($index + 1) . ": Ya existe un proveedor con NIT " . $proveedorData['nit'];
                        continue;
                    }

                    $existingEmail = Proveedor::where('email', $proveedorData['email'])->first();
                    if ($existingEmail) {
                        $errors[] = "Fila " . ($index + 1) . ": Ya existe un proveedor con email " . $proveedorData['email'];
                        continue;
                    }

                    // Crear el proveedor
                    $proveedor = Proveedor::create($proveedorData);

                    \Log::info("Proveedor creado exitosamente", [
                        'id' => $proveedor->id,
                        'nombre' => $proveedor->nombre,
                        'nit' => $proveedor->nit,
                        'fila' => $index + 1
                    ]);

                    // Calcular puntaje total si el método existe
                    if (method_exists($proveedor, 'calcularPuntajeTotal')) {
                        $proveedor->calcularPuntajeTotal();
                    }

                    $success++;

                } catch (\Exception $e) {
                    $error = "Fila " . ($index + 1) . ": " . $e->getMessage();
                    $errors[] = $error;
                    \Log::error("Error al procesar fila " . ($index + 1), [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            \Log::info('Importación completada', [
                'success_count' => $success,
                'error_count' => count($errors)
            ]);

            $message = "Se importaron {$success} proveedores exitosamente.";
            if (count($errors) > 0) {
                $message .= " Hubo " . count($errors) . " errores.";
                return redirect()->route('proveedores.index')->with('warning', $message)->with('errors', $errors);
            }

            return redirect()->route('proveedores.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Error general en importación de proveedores', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('proveedores.index')
                ->with('error', 'Error al procesar la importación: ' . $e->getMessage());
        }
    }

    /**
     * Descargar plantilla de ejemplo para importación
     */
    public function downloadTemplate()
    {
        $content = "Nombre\tNIT\tDirección\tCiudad\tTeléfono\tEmail\tPersona de Contacto\tSegmento de Mercado\tServicio/Producto\tAlto Riesgo\tProveedor Crítico\n";
        $content .= "Proveedor Ejemplo S.A.S\t900123456-1\tCarrera 7 # 123-45\tBogotá\t3001234567\tejemplo@proveedor.com\tJuan Pérez\tTecnología y equipos de cómputo\tVenta de equipos\t0\t1\n";
        $content .= "Suministros ABC Ltda\t800654321-2\tCalle 45 # 67-89\tMedellín\t3209876543\tcontacto@abc.com\tMaria González\tPapelería y útiles de oficina\tSuministros de oficina\t0\t0\n";

        $headers = [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="plantilla_proveedores.txt"',
        ];

        return response($content, 200, $headers);
    }
}