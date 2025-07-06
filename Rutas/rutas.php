<?php
require_once __DIR__ . '/../Controladores/ordenes.controlador.php';
// Obtener la URI y separar en segmentos
$arrayRutas = explode("/", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
// Eliminar elementos vacíos y reindexar
$arrayRutas = array_values(array_filter($arrayRutas));

// Determinar el método HTTP
$method = $_SERVER['REQUEST_METHOD'];

// Login de cliente
if ($method === 'POST' && isset($arrayRutas[1]) && $arrayRutas[1] === 'clientes' && isset($arrayRutas[2]) && $arrayRutas[2] === 'login') {
    $controladorClientes = new ControladorClientes();
    $controladorClientes->login();
    return;
}

// Create client
if ($method === 'POST' && isset($arrayRutas[1]) && $arrayRutas[1] === 'clientes') {
    $datos = json_decode(file_get_contents('php://input'), true);
    $controladorClientes = new ControladorClientes();
    $controladorClientes->create($datos);
    return;
}

// CRUD Cursos
if ($method === 'POST' && isset($arrayRutas[1]) && $arrayRutas[1] === 'cursos') {
    $datos = json_decode(file_get_contents('php://input'), true);
    $controladorCursos = new ControladorCursos();
    $controladorCursos->create($datos);
    return;
}


// Endpoints de paginación de cursos
if (isset($_GET["pagina"]) && is_numeric($_GET['pagina']) && isset($arrayRutas[1]) && $arrayRutas[1] === 'cursos') {
    $cursos = new ControladorCursos();
    $cursos->index($_GET["pagina"]);
    return;
}

if ($method === 'PUT' && isset($arrayRutas[1]) && $arrayRutas[1] === 'cursos' && isset($arrayRutas[2])) {
    $id = intval($arrayRutas[2]);
    $datos = json_decode(file_get_contents('php://input'), true);
    if (empty($datos)) {
        http_response_code(400);
        echo json_encode([
            "status" => 400,
            "detalle" => "Datos de actualización no recibidos"
        ]);
        return;
    }
    $controladorCursos = new ControladorCursos();
    $controladorCursos->update($id, $datos);
    return;
}

if ($method === 'DELETE' && isset($arrayRutas[1]) && $arrayRutas[1] === 'cursos' && isset($arrayRutas[2])) {
    $id = intval($arrayRutas[2]);
    $controladorCursos = new ControladorCursos();
    $controladorCursos->delete($id);
    return;
}

// Obtener curso por ID
if ($method === 'GET' && isset($arrayRutas[1]) && $arrayRutas[1] === 'cursos' && isset($arrayRutas[2])) {
    $id = intval($arrayRutas[2]);
    $cursos = new ControladorCursos();
    $cursos->show($id);
    return;
}

// Obtener todos los cursos (sin paginación)
if ($method === 'GET' && isset($arrayRutas[1]) && $arrayRutas[1] === 'cursos' && !isset($arrayRutas[2])) {
    $cursos = new ControladorCursos();
    $cursos->index(1); // Por defecto página 1
    return;
}


if ($method === 'POST' && isset($arrayRutas[1]) && $arrayRutas[1] === 'ordenes') {
    $datos = json_decode(file_get_contents('php://input'), true);
    $controladorOrdenes = new ControladorOrdenes();
    $controladorOrdenes->create($datos);
    return;
}

// Obtener todas las órdenes del cliente autenticado
if ($method === 'GET' && isset($arrayRutas[1]) && $arrayRutas[1] === 'ordenes' && !isset($arrayRutas[2])) {
    $controladorOrdenes = new ControladorOrdenes();
    $controladorOrdenes->index();
    return;
}

// Obtener una orden específica
if ($method === 'GET' && isset($arrayRutas[1]) && $arrayRutas[1] === 'ordenes' && isset($arrayRutas[2])) {
    $id = intval($arrayRutas[2]);
    $controladorOrdenes = new ControladorOrdenes();
    $controladorOrdenes->show($id);
    return;
}


// Si no coincide ninguna ruta
http_response_code(404);
echo json_encode([
    "status" => 404,
    "detalle" => "Ruta no encontrada"
]);
