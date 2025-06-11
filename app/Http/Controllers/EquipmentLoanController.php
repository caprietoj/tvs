/**
     * Procesar la solicitud de préstamo
     */
    public function processRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'equipment_id' => 'required|exists:equipment,id',
            'loan_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required',
            'teacher_name' => 'required|max:255',
            'grade' => 'required|max:255',
            'units_requested' => 'required|integer|min:1',
            'section' => 'required|in:bachillerato,preescolar_primaria,administrativo',
            'subsection' => 'nullable|in:preescolar,primaria',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            $equipment = Equipment::findOrFail($request->equipment_id);
            $loanDate = $request->loan_date;
            $startTime = $request->start_time;
            $endTime = $request->end_time;
            $unitsRequested = $request->units_requested;
            $section = $request->section;
            $subsection = $request->subsection; // Capturar la subsección
            
            // Determinar la subsección basado en el grado si no se especifica
            if ($section === 'preescolar_primaria' && empty($subsection)) {
                $grade = strtolower($request->grade);
                
                if (str_contains($grade, 'preescolar') || 
                    str_contains($grade, 'pre-') ||
                    str_contains($grade, 'kinder') ||
                    str_contains($grade, 'trans') ||
                    str_contains($grade, 'jardín') || 
                    str_contains($grade, 'jardin') ||
                    preg_match('/\bpk\b/', $grade) ||
                    preg_match('/\bk[0-9]\b/', $grade)) {
                    $subsection = 'preescolar';
                } else {
                    $subsection = 'primaria';
                }
                
                // Registrar la detección automática
                Log::info("Subsección determinada automáticamente como '{$subsection}' para el grado: {$request->grade}");
            }
            
            // Verificar disponibilidad específica por subsección para preescolar/primaria
            $availableUnits = $this->getAvailableUnits(
                $equipment->id, 
                $loanDate, 
                $startTime, 
                $endTime, 
                $section, 
                $subsection
            );
            
            if ($unitsRequested > $availableUnits) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'error' => "No hay suficientes unidades disponibles para este horario. Disponibles: {$availableUnits}"
                ], 422);
            }

            // Crear préstamo
            $loan = new EquipmentLoan();
            $loan->equipment_id = $equipment->id;
            $loan->user_id = auth()->check() ? auth()->id() : null;
            $loan->loan_date = $loanDate;
            $loan->start_time = $startTime;
            $loan->end_time = $endTime;
            $loan->teacher_name = $request->teacher_name;
            $loan->grade = $request->grade;
            $loan->units_requested = $unitsRequested;
            $loan->section = $section;
            $loan->subsection = $subsection; // Guardar la subsección
            $loan->status = 'pending';
            $loan->auto_return = true; // Establecer devolución automática por defecto
            $loan->save();
            
            // Registrar actividad y enviar notificaciones
            $this->recordLoanActivity($loan, 'created');
            $this->sendLoanNotifications($loan);

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Su solicitud ha sido registrada correctamente. El equipo será devuelto automáticamente al finalizar el período de clase.',
                'loan' => $loan->load('equipment'),  // Incluir datos del equipo para referencia
                'redirect' => route('equipment.loans')
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al procesar solicitud de préstamo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Ocurrió un error al procesar su solicitud. Por favor intente nuevamente.'
            ], 500);
        }
    }

    /**
     * Obtener unidades disponibles para un equipo en un horario específico
     *
     * @param int $equipmentId
     * @param string $date
     * @param string $startTime
     * @param string $endTime
     * @param string $section
     * @param string|null $subsection
     * @return int
     */
    private function getAvailableUnits($equipmentId, $date, $startTime, $endTime, $section, $subsection = null)
    {
        // Obtener el total de unidades del equipo
        $equipment = Equipment::findOrFail($equipmentId);
        $totalUnits = $equipment->units;

        // Consultar los préstamos existentes que coincidan con el horario
        $query = EquipmentLoan::where('equipment_id', $equipmentId)
            ->where('loan_date', $date)
            ->where('status', '!=', 'rejected') // Ignorar rechazados
            ->where(function($q) use ($startTime, $endTime) {
                // Préstamos que se solapan con el horario solicitado
                $q->where(function($q1) use ($startTime, $endTime) {
                    $q1->where('start_time', '>=', $startTime)
                        ->where('start_time', '<', $endTime);
                })->orWhere(function($q2) use ($startTime, $endTime) {
                    $q2->where('end_time', '>', $startTime)
                        ->where('end_time', '<=', $endTime);
                })->orWhere(function($q3) use ($startTime, $endTime) {
                    $q3->where('start_time', '<=', $startTime)
                        ->where('end_time', '>=', $endTime);
                });
            });

        // Filtrar por subsección si es preescolar_primaria
        if ($section === 'preescolar_primaria' && $subsection) {
            // Registrar la solicitud de disponibilidad con subsección
            Log::info("Verificando disponibilidad para {$section}, subsección: {$subsection}, fecha: {$date}, horario: {$startTime}-{$endTime}");
            
            // Solo considerar préstamos de la misma subsección
            $query->where(function($q) use ($subsection) {
                $q->where('subsection', $subsection)
                  ->orWhereNull('subsection'); // Para compatibilidad con registros antiguos sin subsección
            });
        } else {
            // Para otras secciones, filtrar por sección
            $query->where('section', $section);
        }

        // Calcular unidades ya reservadas
        $reservedUnits = $query->sum('units_requested');
        
        // Registrar el resultado del cálculo
        Log::info("Cálculo de disponibilidad - Total: {$totalUnits}, Reservadas: {$reservedUnits}, Disponibles: " . ($totalUnits - $reservedUnits));

        // Calcular unidades disponibles
        return max(0, $totalUnits - $reservedUnits);
    }