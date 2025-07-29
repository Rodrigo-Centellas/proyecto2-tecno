<?php

namespace App\Helpers;

class NumeroTextoHelper
{
    private static $unidades = [
        '', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'
    ];
    
    private static $decenas = [
        '', '', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 
        'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'
    ];
    
    private static $centenas = [
        '', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 
        'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'
    ];
    
    private static $especiales = [
        'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 
        'DIECISÉIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'
    ];

    /**
     * Convierte un número a su representación en texto
     * 
     * @param int $numero
     * @return string
     */
    public static function convertir($numero)
    {
        if ($numero === 0) return 'CERO';
        if ($numero === 1) return 'UN';
        
        $resultado = '';
        
        // Millones
        if ($numero >= 1000000) {
            $millones = intval($numero / 1000000);
            if ($millones === 1) {
                $resultado .= 'UN MILLÓN ';
            } else {
                $resultado .= self::convertir($millones) . ' MILLONES ';
            }
            $numero = $numero % 1000000;
        }
        
        // Miles
        if ($numero >= 1000) {
            $miles = intval($numero / 1000);
            if ($miles === 1) {
                $resultado .= 'MIL ';
            } else {
                $resultado .= self::convertir($miles) . ' MIL ';
            }
            $numero = $numero % 1000;
        }
        
        // Centenas
        if ($numero >= 100) {
            $cen = intval($numero / 100);
            if ($numero === 100) {
                $resultado .= 'CIEN ';
            } else {
                $resultado .= self::$centenas[$cen] . ' ';
            }
            $numero = $numero % 100;
        }
        
        // Decenas y unidades
        if ($numero >= 20) {
            $dec = intval($numero / 10);
            $uni = $numero % 10;
            $resultado .= self::$decenas[$dec];
            if ($uni > 0) {
                $resultado .= ' Y ' . self::$unidades[$uni];
            }
        } elseif ($numero >= 10) {
            $resultado .= self::$especiales[$numero - 10];
        } elseif ($numero > 0) {
            $resultado .= self::$unidades[$numero];
        }
        
        return trim($resultado);
    }

    /**
     * Convierte un número a texto con la palabra BOLIVIANOS
     * 
     * @param int $numero
     * @return string
     */
    public static function aBolivianos($numero)
    {
        $texto = self::convertir($numero);
        return $texto . ($numero === 1 ? ' BOLIVIANO' : ' BOLIVIANOS') . ' 00/100';
    }
}