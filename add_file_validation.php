<?php

$file = 'app/Http/Controllers/PurchaseRequestController.php';
$content = file_get_contents($file);

// Cambiar la línea 408 específicamente
$lines = explode("\n", $content);
$lines[407] = "            \$rules['no_quotation_reason'] = 'required|string';";
$lines[407] .= "\n            \$rules['quotation_file'] = 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240'; // máximo 10MB";

$newContent = implode("\n", $lines);
file_put_contents($file, $newContent);

echo "Validación del archivo agregada correctamente.\n";
