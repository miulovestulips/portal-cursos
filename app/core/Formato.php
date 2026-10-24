<?php
// app/core/Formato.php
// Formatos exigidos por la forma de evaluación:
//  - moneda: símbolo, coma para miles y solo dos decimales
//  - fechas: día, tres letras del mes y año de 4 dígitos (ej. 05 ago 2026)

class Formato
{
    private const MESES = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];

    public static function moneda($monto): string
    {
        return '$' . number_format((float) $monto, 2, '.', ',');
    }

    public static function fecha(?string $fecha): string
    {
        if ($fecha === null || $fecha === '') {
            return '—';
        }
        $t = strtotime($fecha);
        if ($t === false) {
            return '—';
        }
        return date('d', $t) . ' ' . self::MESES[(int) date('n', $t) - 1] . ' ' . date('Y', $t);
    }
}
