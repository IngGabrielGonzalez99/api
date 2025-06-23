<?php
require_once __DIR__ . '/../Modelos/ordenes.modelo.php';
require_once __DIR__ . '/../Modelos/metodos_pago.modelo.php';

class ControladorOrdenes
{
    private function autenticarCliente()
    {
        $clientes = ModeloClientes::index("clientes");
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            foreach ($clientes as $cliente) {
                if (
                    $_SERVER['PHP_AUTH_USER'] === $cliente["id_cliente"] &&
                    $_SERVER['PHP_AUTH_PW'] === $cliente["llave_secreta"]
                ) {
                    return $cliente;
                }
            }
        }
        return false;
    }

    // Obtener todas las órdenes del cliente autenticado
    public function index()
    {
        header('Content-Type: application/json');
        $cliente = $this->autenticarCliente();
        if (!$cliente) {
            http_response_code(401);
            echo json_encode([
                "status" => 401,
                "detalle" => "No autorizado"
            ]);
            return;
        }

        $ordenes = ModeloOrdenes::obtenerOrdenesPorCliente($cliente["id"]);
        http_response_code(200);
        echo json_encode([
            "status" => 200,
            "ordenes" => $ordenes
        ]);
    }

    // Obtener una orden específica
    public function show($id_orden)
    {
        header('Content-Type: application/json');
        $cliente = $this->autenticarCliente();
        if (!$cliente) {
            http_response_code(401);
            echo json_encode([
                "status" => 401,
                "detalle" => "No autorizado"
            ]);
            return;
        }

        $orden = ModeloOrdenes::obtenerOrdenPorId($id_orden);
        if (!$orden || $orden["id_cliente"] != $cliente["id"]) {
            http_response_code(404);
            echo json_encode([
                "status" => 404,
                "detalle" => "Orden no encontrada"
            ]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            "status" => 200,
            "orden" => $orden
        ]);
    }

    // Crear una nueva orden
    public function create($datos)
    {
        header('Content-Type: application/json');
        $cliente = $this->autenticarCliente();
        if (!$cliente) {
            http_response_code(401);
            echo json_encode([
                "status" => 401,
                "detalle" => "No autorizado"
            ]);
            return;
        }

        // Validaciones básicas
        if (
            empty($datos["id_curso"]) ||
            empty($datos["id_metodo_pago"])
        ) {
            http_response_code(400);
            echo json_encode([
                "status" => 400,
                "detalle" => "Faltan datos obligatorios para la orden"
            ]);
            return;
        }

        // Obtener el precio del curso
        $curso = ModeloCursos::showById($datos["id_curso"]);
        if (!$curso) {
            http_response_code(404);
            echo json_encode([
                "status" => 404,
                "detalle" => "El curso no existe"
            ]);
            return;
        }
        $total = $curso["precio"];

        // Buscar o crear registro en clientes_cursos
        $id_clientes_cursos = ModeloOrdenes::buscarOCrearClienteCurso($cliente["id"], $datos["id_curso"]);
        error_log("ID clientes_cursos usado: " . $id_clientes_cursos);

        $datosOrden = [
            "id_clientes_cursos" => $id_clientes_cursos,
            "id_metodo_pago" => $datos["id_metodo_pago"],
            "fecha_orden" => date('Y-m-d H:i:s'),
            "total" => $total
        ];
        $idOrden = ModeloOrdenes::create($datosOrden);


        http_response_code(201);
        echo json_encode([
            "status" => 201,
            "detalle" => "Orden creada correctamente",
            "id_orden" => $idOrden
        ]);
    }
}
?>