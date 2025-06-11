<?php

namespace App\Exports;

use App\Models\Event;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EventsWithNoveltiesExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles
{
    /**
     * Formatea de manera segura los campos de tiempo
     * 
     * @param mixed $timeValue
     * @return string
     */
    private function formatTime($timeValue)
    {
        if (empty($timeValue)) {
            return '';
        }
        
        // Si es un objeto Carbon/DateTime
        if ($timeValue instanceof \Carbon\Carbon || $timeValue instanceof \DateTime) {
            return $timeValue->format('H:i');
        }
        
        // Si es string, intentar parsearlo
        if (is_string($timeValue)) {
            try {
                // Si ya está en formato HH:MM
                if (preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $timeValue)) {
                    return substr($timeValue, 0, 5); // Tomar solo HH:MM
                }
                
                // Intentar parsearlo como Carbon
                $parsed = \Carbon\Carbon::parse($timeValue);
                return $parsed->format('H:i');
            } catch (\Exception $e) {
                return $timeValue; // Devolver el valor original si no se puede parsear
            }
        }
        
        return '';
    }
    
    /**
     * Formatea de manera segura los campos de fecha
     * 
     * @param mixed $dateValue
     * @return string
     */
    private function formatDate($dateValue)
    {
        if (empty($dateValue)) {
            return '';
        }
        
        // Si es un objeto Carbon/DateTime
        if ($dateValue instanceof \Carbon\Carbon || $dateValue instanceof \DateTime) {
            return $dateValue->format('d/m/Y');
        }
        
        // Si es string, intentar parsearlo
        if (is_string($dateValue)) {
            try {
                $parsed = \Carbon\Carbon::parse($dateValue);
                return $parsed->format('d/m/Y');
            } catch (\Exception $e) {
                return $dateValue; // Devolver el valor original si no se puede parsear
            }
        }
        
        return '';
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Event::with('novelties', 'novelties.user')->get();
    }

    /**
     * @param Event $event
     * @return array
     */
    public function map($event): array
    {
        // Generar información detallada de servicios requeridos
        $detailedServices = [];
        
        // Metro Junior
        if ($event->metro_junior_required) {
            $details = [];
            if ($event->route) $details[] = "Ruta: {$event->route}";
            if ($event->passengers) $details[] = "Pasajeros: {$event->passengers}";
            if ($event->departure_time) $details[] = "Salida: " . $this->formatTime($event->departure_time);
            if ($event->return_time) $details[] = "Regreso: " . $this->formatTime($event->return_time);
            if ($event->metro_junior_observations) $details[] = "Obs: {$event->metro_junior_observations}";
            
            $serviceDetails = !empty($details) ? ' (' . implode(', ', $details) . ')' : '';
            $status = $event->metro_junior_confirmed ? 'Confirmado' : 'Pendiente';
            $detailedServices[] = "Metro Junior: {$status}{$serviceDetails}";
        }
        
        // Aldimark
        if ($event->aldimark_required) {
            $details = [];
            if ($event->aldimark_requirement) $details[] = "Req: {$event->aldimark_requirement}";
            if ($event->aldimark_time) $details[] = "Hora: " . $this->formatTime($event->aldimark_time);
            if ($event->aldimark_details) $details[] = "Detalles: {$event->aldimark_details}";
            
            $serviceDetails = !empty($details) ? ' (' . implode(', ', $details) . ')' : '';
            $status = $event->aldimark_confirmed ? 'Confirmado' : 'Pendiente';
            $detailedServices[] = "Aldimark: {$status}{$serviceDetails}";
        }
        
        // Mantenimiento
        if ($event->maintenance_required) {
            $details = [];
            if ($event->maintenance_requirement) $details[] = "Req: {$event->maintenance_requirement}";
            if ($event->maintenance_setup_date) $details[] = "Fecha: " . $this->formatDate($event->maintenance_setup_date);
            if ($event->maintenance_setup_time) $details[] = "Hora: " . $this->formatTime($event->maintenance_setup_time);
            
            $serviceDetails = !empty($details) ? ' (' . implode(', ', $details) . ')' : '';
            $status = $event->maintenance_confirmed ? 'Confirmado' : 'Pendiente';
            $detailedServices[] = "Mantenimiento: {$status}{$serviceDetails}";
        }
        
        // Servicios Generales
        if ($event->general_services_required) {
            $details = [];
            if ($event->general_services_requirement) $details[] = "Req: {$event->general_services_requirement}";
            if ($event->general_services_setup_date) $details[] = "Fecha: " . $this->formatDate($event->general_services_setup_date);
            if ($event->general_services_setup_time) $details[] = "Hora: " . $this->formatTime($event->general_services_setup_time);
            
            $serviceDetails = !empty($details) ? ' (' . implode(', ', $details) . ')' : '';
            $status = $event->general_services_confirmed ? 'Confirmado' : 'Pendiente';
            $detailedServices[] = "Servicios Generales: {$status}{$serviceDetails}";
        }
        
        // Sistemas
        if ($event->systems_required) {
            $details = [];
            if ($event->systems_requirement) $details[] = "Req: {$event->systems_requirement}";
            if ($event->systems_setup_date) $details[] = "Fecha: " . $this->formatDate($event->systems_setup_date);
            if ($event->systems_setup_time) $details[] = "Hora: " . $this->formatTime($event->systems_setup_time);
            if ($event->systems_observations) $details[] = "Obs: {$event->systems_observations}";
            
            $serviceDetails = !empty($details) ? ' (' . implode(', ', $details) . ')' : '';
            $status = $event->systems_confirmed ? 'Confirmado' : 'Pendiente';
            $detailedServices[] = "Sistemas: {$status}{$serviceDetails}";
        }
        
        // Compras
        if ($event->purchases_required) {
            $details = [];
            if ($event->purchases_requirement) $details[] = "Req: {$event->purchases_requirement}";
            if ($event->purchases_observations) $details[] = "Obs: {$event->purchases_observations}";
            
            $serviceDetails = !empty($details) ? ' (' . implode(', ', $details) . ')' : '';
            $status = $event->purchases_confirmed ? 'Confirmado' : 'Pendiente';
            $detailedServices[] = "Compras: {$status}{$serviceDetails}";
        }
        
        // Comunicaciones
        if ($event->communications_required) {
            $details = [];
            if ($event->communications_coverage) $details[] = "Cubrimiento: {$event->communications_coverage}";
            if ($event->communications_observations) $details[] = "Obs: {$event->communications_observations}";
            
            $serviceDetails = !empty($details) ? ' (' . implode(', ', $details) . ')' : '';
            $status = $event->communications_confirmed ? 'Confirmado' : 'Pendiente';
            $detailedServices[] = "Comunicaciones: {$status}{$serviceDetails}";
        }
        
        $servicesText = !empty($detailedServices) ? implode("\n", $detailedServices) : 'Ninguno';
        
        // Concatenar novedades si existen
        $novelties = [];
        foreach ($event->novelties as $novelty) {
            $novelties[] = date('d/m/Y H:i', strtotime($novelty->created_at)) . ' - ' . 
                           $novelty->user->name . ': ' . $novelty->observation;
        }
        $noveltiesText = !empty($novelties) ? implode("\n", $novelties) : 'Sin novedades';

        return [
            'Consecutivo' => $event->consecutive,
            'Nombre del evento' => $event->event_name,
            'Fecha de solicitud' => $this->formatDate($event->request_date),
            'Fecha de servicio' => $this->formatDate($event->service_date),
            'Hora del evento' => $this->formatTime($event->event_time),
            'Lugar' => $event->location,
            'Responsable' => $event->responsible,
            'Parqueadero CAFAM' => $event->cafam_parking ? 'Sí' : 'No',
            'Servicios' => $servicesText,
            'Novedades' => $noveltiesText,
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Consecutivo',
            'Nombre del evento',
            'Fecha de solicitud',
            'Fecha de servicio',
            'Hora del evento',
            'Lugar',
            'Responsable',
            'Parqueadero CAFAM',
            'Servicios',
            'Novedades'
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Eventos y Novedades';
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la fila de encabezados
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1A4884']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
            
            // Estilo para todas las celdas
            'A1:J1000' => [
                'alignment' => ['vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP],
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            ],
            
            // Ajuste específico para la columna de servicios (columna I)
            'I2:I1000' => [
                'alignment' => ['wrapText' => true, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP],
            ],
            
            // Ajuste específico para la columna de novedades (columna J)
            'J2:J1000' => [
                'alignment' => ['wrapText' => true, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP],
            ],
        ];
    }
}