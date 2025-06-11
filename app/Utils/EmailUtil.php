<?php

namespace App\Utils;

use Illuminate\Support\Facades\Log;

class EmailUtil {
    
    public static function sendNotificationEmail($to, $subject, $htmlContent, $from = null) {
        if (!$from) {
            $from = "intranet@tvs.edu.co";
        }
        
        $headers = [
            "From: " . $from,
            "Reply-To: " . $from,
            "Content-Type: text/html; charset=UTF-8",
            "X-Mailer: TVS Intranet System",
            "X-Priority: 1 (High)"
        ];
        
        $headerString = implode("\r\n", $headers);
        
        try {
            $result = @mail($to, $subject, $htmlContent, $headerString);
            
            if ($result) {
                Log::info("Correo enviado exitosamente", [
                    "to" => $to,
                    "subject" => $subject,
                    "method" => "native_mail"
                ]);
                return true;
            } else {
                Log::error("Error enviando correo", [
                    "to" => $to,
                    "subject" => $subject,
                    "method" => "native_mail"
                ]);
                return false;
            }
        } catch (Exception $e) {
            Log::error("Excepción enviando correo", [
                "to" => $to,
                "subject" => $subject,
                "error" => $e->getMessage()
            ]);
            return false;
        }
    }
    
    public static function sendQuotationPreApprovalEmail($purchaseRequestId, $userName, $section, $recipientEmail) {
        $subject = "Pre-aprobación de Cotizaciones Requerida - Solicitud #" . $purchaseRequestId;
        
        $htmlContent = "<html><head>";
        $htmlContent .= "<style>";
        $htmlContent .= "body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }";
        $htmlContent .= ".header { background-color: #007bff; color: white; padding: 20px; text-align: center; }";
        $htmlContent .= ".content { padding: 20px; }";
        $htmlContent .= ".details { background-color: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0; }";
        $htmlContent .= ".footer { background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; }";
        $htmlContent .= "</style></head><body>";
        $htmlContent .= "<div class=\"header\">";
        $htmlContent .= "<h1>The Victoria School</h1>";
        $htmlContent .= "<h2>Notificación de Pre-aprobación</h2>";
        $htmlContent .= "</div>";
        $htmlContent .= "<div class=\"content\">";
        $htmlContent .= "<p>Estimado Director/Coordinador,</p>";
        $htmlContent .= "<p>Se requiere su pre-aprobación para las cotizaciones de la siguiente solicitud de compra:</p>";
        $htmlContent .= "<div class=\"details\">";
        $htmlContent .= "<strong>Detalles de la Solicitud:</strong><br>";
        $htmlContent .= "• <strong>ID de Solicitud:</strong> #" . $purchaseRequestId . "<br>";
        $htmlContent .= "• <strong>Solicitante:</strong> " . $userName . "<br>";
        $htmlContent .= "• <strong>Sección:</strong> " . $section . "<br>";
        $htmlContent .= "• <strong>Fecha:</strong> " . date("Y-m-d H:i:s");
        $htmlContent .= "</div>";
        $htmlContent .= "<p>Las cotizaciones han sido cargadas y están listas para su revisión y pre-aprobación.</p>";
        $htmlContent .= "<p><strong>Acción requerida:</strong> Por favor ingrese al sistema para revisar y pre-aprobar las cotizaciones.</p>";
        $htmlContent .= "<p>Gracias por su atención.</p>";
        $htmlContent .= "</div>";
        $htmlContent .= "<div class=\"footer\">";
        $htmlContent .= "<p>Este es un mensaje automático del Sistema de Compras TVS</p>";
        $htmlContent .= "<p>Por favor no responda a este correo</p>";
        $htmlContent .= "</div>";
        $htmlContent .= "</body></html>";
        
        return self::sendNotificationEmail($recipientEmail, $subject, $htmlContent);
    }
}
