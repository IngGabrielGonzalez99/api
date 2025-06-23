<?php

class ControladorRutas
{
    public function inicio()
    {
        header('Content-Type: application/json');    
        include_once __DIR__ . '/../Rutas/rutas.php';
    }
}

?>