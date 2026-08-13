<?php
date_default_timezone_set('America/Bogota'); // Ajusta la zona horaria

// Rutas de backup
$backupDir = '/home/tvs-intranet/backups/directory/';
$date = date('Y-m-d_H-i-s');
$folderBackupFile = $backupDir . 'htdocs_backup_' . $date . '.tar.gz';

// Crear el directorio de backup si no existe
if (!is_dir($backupDir)) {
    if (mkdir($backupDir, 0777, true)) {
        echo "Directorio de backup creado: $backupDir\n";
    } else {
        echo "Error: No se pudo crear el directorio de backup: $backupDir\n";
        exit(1);
    }
}

// Verificar si la carpeta existe
$folderToBackup = '/home/tvs-intranet/htdocs/intranet.tvs.edu.co/public';
if (!is_dir($folderToBackup)) {
    echo "Error: La carpeta a respaldar no existe: $folderToBackup\n";
    exit(1);
}

// Comando para crear el archivo comprimido
$tarCommand = "tar -czf $folderBackupFile -C $folderToBackup .";
exec($tarCommand, $output, $result);

// Verificar el resultado del comando
if ($result === 0) {
    echo "Backup de la carpeta creado correctamente: $folderBackupFile\n";
} else {
    echo "Error al crear el backup de la carpeta. Código de error: $result\n";
    echo "Detalles: " . implode("\n", $output) . "\n";
}
?>
