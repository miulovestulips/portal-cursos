<?php
// app/core/Middleware.php
// Contrato que cumple toda pieza de middleware: se ejecuta ANTES del
// controlador y, si la petición no debe continuar, responde y hace exit.

interface Middleware
{
    public function handle(): void;
}
