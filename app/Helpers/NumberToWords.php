<?php

namespace App\Helpers;

class NumberToWords
{
    public static function convert($numero)
    {
        $numero = round($numero);
        if ($numero == 0) return 'Cero pesos';
        
        $unidades = ['', 'Un', 'Dos', 'Tres', 'Cuatro', 'Cinco', 'Seis', 'Siete', 'Ocho', 'Nueve'];
        $especiales = ['Diez', 'Once', 'Doce', 'Trece', 'Catorce', 'Quince', 'Dieciséis', 'Diecisiete', 'Dieciocho', 'Diecinueve'];
        $decenas = ['', '', 'Veinte', 'Treinta', 'Cuarenta', 'Cincuenta', 'Sesenta', 'Setenta', 'Ochenta', 'Noventa'];
        $centenas = ['', 'Ciento', 'Doscientos', 'Trescientos', 'Cuatrocientos', 'Quinientos', 'Seiscientos', 'Setecientos', 'Ochocientos', 'Novecientos'];
        
        $resultado = '';
        
        // Millones
        if ($numero >= 1000000) {
            $millones = intval($numero / 1000000);
            if ($millones == 1) {
                $resultado .= 'Un millón ';
            } else {
                $resultado .= self::convertirGrupo($millones, $unidades, $especiales, $decenas, $centenas) . ' millones ';
            }
            $numero %= 1000000;
        }
        
        // Miles
        if ($numero >= 1000) {
            $miles = intval($numero / 1000);
            if ($miles == 1) {
                $resultado .= 'Mil ';
            } else {
                $resultado .= self::convertirGrupo($miles, $unidades, $especiales, $decenas, $centenas) . ' mil ';
            }
            $numero %= 1000;
        }
        
        // Unidades, decenas y centenas
        if ($numero > 0) {
            $resultado .= self::convertirGrupo($numero, $unidades, $especiales, $decenas, $centenas);
        }
        
        // Si no hay resultado aún, es cero
        if (trim($resultado) === '') {
            $resultado = 'Cero';
        }
        
        // Agregar "peso" o "pesos"
        $resultado = trim($resultado);
        if ($numero == 1 && !strpos($resultado, 'millón') && !strpos($resultado, 'mil')) {
            $resultado .= ' peso';
        } else {
            $resultado .= ' pesos';
        }
        
        return $resultado;
    }
    
    private static function convertirGrupo($n, $unidades, $especiales, $decenas, $centenas)
    {
        if ($n == 0) return '';
        if ($n == 100) return 'Cien';
        
        $resultado = '';
        
        // Centenas
        if ($n >= 100) {
            $c = intval($n / 100);
            $resultado .= $centenas[$c];
            $n %= 100;
            if ($n > 0) $resultado .= ' ';
        }
        
        // Decenas y unidades
        if ($n >= 20) {
            $d = intval($n / 10);
            $resultado .= $decenas[$d];
            $n %= 10;
            if ($n > 0) {
                $resultado .= ' y ' . $unidades[$n];
            }
        } elseif ($n >= 10) {
            $resultado .= $especiales[$n - 10];
        } elseif ($n > 0) {
            $resultado .= $unidades[$n];
        }
        
        return $resultado;
    }
}
