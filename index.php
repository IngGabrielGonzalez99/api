<?php 

require_once "Controladores/rutas.controlador.php";
require_once "Controladores/cursos.controlador.php";
require_once "Controladores/clientes.controlador.php";
require_once "Modelos/clientes.modelo.php";
require_once "Modelos/cursos.modelos.php";
require_once "Rutas/rutas.php";

$rutas= new ControladorRutas();
$rutas->inicio();

?>