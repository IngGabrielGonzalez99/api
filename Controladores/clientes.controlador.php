<?php
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
}

class ControladorClientes
{
     public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!isset($data['id_cliente']) || !isset($data['llave_secreta'])) {
            http_response_code(400);
            echo json_encode([
                "status" => 400,
                "detalle" => "id_cliente (o nombre) y llave_secreta son requeridos."
            ]);
            return;
        }

        $id_o_nombre = $data['id_cliente'];
        $llave_secreta = $data['llave_secreta'];

        require_once __DIR__ . '/../Modelos/clientes.modelo.php';
        $cliente = ModeloClientes::findByIdOrNameAndSecret($id_o_nombre, $llave_secreta);

        if ($cliente) {
            echo json_encode([
                "status" => 200,
                "detalle" => "Login exitoso",
                "nombre" => $cliente['nombre'],
                "email" => $cliente['email']
            ]);
        } else {
            http_response_code(401);
            echo json_encode([
                "status" => 401,
                "detalle" => "Credenciales incorrectas."
            ]);
        }
    }

    public function create($datos)
    {
        header('Content-Type: application/json');

        // Validar nombre
        if (!isset($datos["nombre"]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $datos["nombre"])) {
            http_response_code(400);
            echo json_encode([
                "status" => 400,
                "detalle" => "El nombre es obligatorio y solo puede contener letras y espacios"
            ]);
            return;
        }

        // Validar apellido
        if (!isset($datos["apellido"]) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ ]+$/', $datos["apellido"])) {
            http_response_code(400);
            echo json_encode([
                "status" => 400,
                "detalle" => "El apellido es obligatorio y solo puede contener letras y espacios"
            ]);
            return;
        }

        // Validar email
        if (!isset($datos["email"]) || !filter_var($datos["email"], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode([
                "status" => 400,
                "detalle" => "El email es obligatorio y debe tener un formato válido"
            ]);
            return;
        }

        // Validar email repetido
        $clientes = ModeloClientes::index("clientes");
        foreach ($clientes as $value) {
            if ($value["email"] == $datos["email"]) {
                http_response_code(409);
                echo json_encode([
                    "status" => 409,
                    "detalle" => "El email ya está registrado"
                ]);
                return;
            }
        }

        // Generar credenciales seguras
        $id_cliente = bin2hex(random_bytes(16));
        $llave_secreta = bin2hex(random_bytes(32));

        $datosInsert = [
            "nombre" => $datos["nombre"],
            "apellido" => $datos["apellido"],
            "email" => $datos["email"],
            "id_cliente" => $id_cliente,
            "llave_secreta" => $llave_secreta,
            "created_at" => date('Y-m-d H:i:s'),
            "updated_at" => date('Y-m-d H:i:s')
        ];

        $create = ModeloClientes::create("clientes", $datosInsert);

        if ($create == "ok") {
            http_response_code(201);
            echo json_encode([
                "status" => 201,
                "detalle" => "Credenciales generadas correctamente",
                "id_cliente" => $id_cliente,
                "llave_secreta" => $llave_secreta
            ]);
            return;
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => 500,
                "detalle" => "Error al crear el cliente"
            ]);
            return;
        }
    }
}
?>