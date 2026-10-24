<?php
// app/core/Validator.php
// Reglas de validación del SERVIDOR (independientes de las del cliente).

class Validator
{
    public static function email(string $v): bool
    {
        return mb_strlen($v) <= 150 && filter_var($v, FILTER_VALIDATE_EMAIL) !== false;
    }

    /** Mínimo 8 caracteres, 1 mayúscula, 1 número y 1 carácter especial (ni letra ni número). */
    public static function password(string $v): bool
    {
        return (bool) preg_match('/^(?=.*\p{Lu})(?=.*\p{N})(?=.*[^\p{L}\p{N}]).{8,}$/u', $v);
    }

    public static function longitud(string $v, int $min, int $max): bool
    {
        $n = mb_strlen(trim($v));
        return $n >= $min && $n <= $max;
    }

    /** Fecha estricta YYYY-MM-DD. */
    public static function fecha(string $v): bool
    {
        $d = DateTime::createFromFormat('Y-m-d', $v);
        return $d !== false && $d->format('Y-m-d') === $v;
    }

    public static function fechaNacimiento(string $v): bool
    {
        if (!self::fecha($v)) {
            return false;
        }
        $hoy = new DateTime('today');
        $nac = new DateTime($v);
        return $nac < $hoy && $nac > new DateTime('1900-01-01');
    }

    public static function urlHttps(string $v): bool
    {
        return mb_strlen($v) <= 255
            && filter_var($v, FILTER_VALIDATE_URL) !== false
            && str_starts_with($v, 'https://');
    }
}
