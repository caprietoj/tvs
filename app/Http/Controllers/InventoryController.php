<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InventoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:inventario.view')->only(['index', 'show']);
        $this->middleware('can:inventario.create')->only(['create', 'store']);
        $this->middleware('can:inventario.edit')->only(['edit', 'update']);
        $this->middleware('can:inventario.delete')->only(['destroy']);
        $this->middleware('can:inventario.import')->only(['importForm', 'processImport']);
    }

    /**
     * Mostrar listado de productos en inventario
     */
    public function index(Request $request)
    {
        $query = InventoryItem::with('user');
        
        // Aplicar filtros si existen
        if ($request->filled('producto')) {
            $query->where('producto', 'like', '%' . $request->producto . '%');
        }

        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'low':
                    $query->whereRaw('stock < cantidad_sugerida');
                    break;
                case 'ok':
                    $query->whereRaw('stock >= cantidad_sugerida');
                    break;
            }
        }
        
        if ($request->filled('sort_by')) {
            $direction = $request->filled('sort_dir') && $request->sort_dir == 'desc' ? 'desc' : 'asc';
            
            switch ($request->sort_by) {
                case 'producto':
                    $query->orderBy('producto', $direction);
                    break;
                case 'cantidad_sugerida':
                    $query->orderBy('cantidad_sugerida', $direction);
                    break;
                case 'stock':
                    $query->orderBy('stock', $direction);
                    break;
                default:
                    $query->latest();
                    break;
            }
        } else {
            $query->latest();
        }

        $inventoryItems = $query->get();
        
        return view('inventory.index', compact('inventoryItems'));
    }

    /**
     * Mostrar formulario para crear nuevo ítem
     */
    public function create()
    {
        return view('inventory.create');
    }

    /**
     * Almacenar un nuevo ítem de inventario
     */
    public function store(Request $request)
    {
        $request->validate([
            'producto' => 'required|string|max:255',
            'cantidad_sugerida' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $item = InventoryItem::create([
            'producto' => $request->producto,
            'cantidad_sugerida' => $request->cantidad_sugerida,
            'stock' => $request->stock,
            'user_id' => Auth::id()
        ]);

        // Verificar si se debe enviar alerta
        if ($item->requiereAlerta()) {
            $item->enviarAlertaStock();
        }

        return redirect()->route('inventory.index')
            ->with('success', 'Producto agregado correctamente al inventario.');
    }

    /**
     * Mostrar formulario para editar ítem
     */
    public function edit(InventoryItem $inventory)
    {
        return view('inventory.edit', compact('inventory'));
    }

    /**
     * Actualizar un ítem de inventario
     */
    public function update(Request $request, InventoryItem $inventory)
    {
        $request->validate([
            'producto' => 'required|string|max:255',
            'cantidad_sugerida' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $oldStock = $inventory->stock;
        
        $inventory->update([
            'producto' => $request->producto,
            'cantidad_sugerida' => $request->cantidad_sugerida,
            'stock' => $request->stock,
            'user_id' => Auth::id()
        ]);

        // Si el stock disminuyó por debajo del nivel sugerido, verificar alerta
        if ($oldStock >= $inventory->cantidad_sugerida && $inventory->stock < $inventory->cantidad_sugerida) {
            $inventory->update(['alerta_enviada' => false]);
        }

        // Verificar si se debe enviar alerta
        if ($inventory->requiereAlerta()) {
            $inventory->enviarAlertaStock();
        }

        return redirect()->route('inventory.index')
            ->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Eliminar un ítem de inventario
     */
    public function destroy(InventoryItem $inventory)
    {
        $inventory->delete();
        return redirect()->route('inventory.index')
            ->with('success', 'Producto eliminado correctamente.');
    }

    /**
     * Mostrar formulario de importación masiva
     */
    public function importForm()
    {
        return view('inventory.import');
    }

    /**
     * Procesar la importación masiva de datos
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'data' => 'required|string'
        ]);

        try {
            $rows = explode("\n", trim($request->data));
            $success = 0;
            $errors = [];
            $items = [];

            foreach ($rows as $index => $row) {
                try {
                    if (empty(trim($row))) continue;
                    
                    // Mejoramos la detección de datos separando por cualquier cantidad de espacios o tabulaciones
                    $data = preg_split('/[\t\s]+/', trim($row));
                    
                    // Filtrar elementos vacíos
                    $data = array_values(array_filter($data, function($val) {
                        return trim($val) !== '';
                    }));
                    
                    // Verificar que tenga todos los campos necesarios
                    if (count($data) < 3) {
                        $errors[] = "Fila " . ($index + 1) . ": Formato incorrecto. Se encontraron " . count($data) . " elementos. Formato requerido: PRODUCTO, CANTIDAD_SUGERIDA, STOCK";
                        continue;
                    }

                    // Extraer el nombre del producto (que puede tener varios elementos si contiene espacios)
                    $productName = '';
                    for ($i = 0; $i < count($data) - 2; $i++) {
                        $productName .= $data[$i] . ' ';
                    }
                    $productName = trim($productName);
                    
                    // Los últimos dos elementos son siempre cantidades
                    $cantidadSugerida = intval(str_replace(['.',','], '', $data[count($data) - 2]));
                    $stock = intval(str_replace(['.',','], '', $data[count($data) - 1]));

                    // Validar los datos
                    $validator = Validator::make([
                        'producto' => $productName,
                        'cantidad_sugerida' => $cantidadSugerida,
                        'stock' => $stock,
                    ], [
                        'producto' => 'required|string|max:255',
                        'cantidad_sugerida' => 'required|integer|min:0',
                        'stock' => 'required|integer|min:0',
                    ]);

                    if ($validator->fails()) {
                        $errors[] = "Fila " . ($index + 1) . ": " . $validator->errors()->first();
                        continue;
                    }

                    // Crear o actualizar el ítem
                    $item = InventoryItem::updateOrCreate(
                        ['producto' => $productName],
                        [
                            'cantidad_sugerida' => $cantidadSugerida,
                            'stock' => $stock,
                            'user_id' => Auth::id(),
                        ]
                    );
                    
                    $items[] = $item;
                    $success++;

                } catch (\Exception $e) {
                    $errors[] = "Fila " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            // Verificar alertas para los nuevos ítems
            foreach ($items as $item) {
                if ($item->requiereAlerta()) {
                    $item->enviarAlertaStock();
                }
            }

            $message = "Se importaron {$success} productos exitosamente.";
            if (count($errors) > 0) {
                $message .= " Hubo " . count($errors) . " errores.";
                return redirect()->route('inventory.index')
                    ->with('warning', $message)
                    ->with('importErrors', $errors);
            }

            return redirect()->route('inventory.index')
                ->with('success', $message);

        } catch (ValidationException $e) {
            return redirect()->route('inventory.import')
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->route('inventory.import')
                ->with('error', 'Error al procesar la importación: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Verificar y enviar alertas de stock bajo
     * Este método puede ser llamado desde un comando programado
     */
    public function checkLowStockAlerts()
    {
        $items = InventoryItem::all();
        $alertCount = 0;

        foreach ($items as $item) {
            if ($item->requiereAlerta() && $item->enviarAlertaStock()) {
                $alertCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Se enviaron {$alertCount} alertas de stock bajo."
        ]);
    }

    /**
     * Mostrar detalles de un ítem de inventario y sus movimientos
     */
    public function show(InventoryItem $inventory)
    {
        // Cargar los movimientos de inventario, ordenados del más reciente al más antiguo
        $movements = $inventory->movements()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
            
        return view('inventory.show', compact('inventory', 'movements'));
    }

    /**
     * Procesar una devolución al inventario
     */
    public function returnToInventory(Request $request, InventoryItem $inventory)
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'required|string|max:255',
            'solicitante' => 'required|string|max:255',
        ]);

        try {
            // Iniciar transacción
            \DB::beginTransaction();
            
            // Actualizar el stock
            $oldStock = $inventory->stock;
            $inventory->stock = $oldStock + $request->cantidad;
            $inventory->save();
            
            // Registrar el movimiento
            InventoryMovement::create([
                'inventory_item_id' => $inventory->id,
                'tipo_movimiento' => 'devolucion',
                'cantidad' => $request->cantidad,
                'detalle' => 'Devolución: ' . $request->motivo,
                'solicitante' => $request->solicitante,
                'user_id' => Auth::id()
            ]);
            
            \DB::commit();
            
            \Log::info("Devolución al inventario: {$inventory->producto}, Cantidad: {$request->cantidad}, Stock anterior: {$oldStock}, Nuevo stock: {$inventory->stock}, Motivo: {$request->motivo}");
            
            return redirect()->route('inventory.show', $inventory->id)
                ->with('success', "Se ha devuelto {$request->cantidad} unidades al inventario. Nuevo stock: {$inventory->stock}");
                
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al procesar devolución: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Error al procesar la devolución: ' . $e->getMessage());
        }
    }
}
