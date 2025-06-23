<?php

class ControladorCursos
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

    public function index($pagina)
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

        $cantidad = 10;
        $desde = ($pagina - 1) * $cantidad;

        $cursos = ModeloCursos::index("cursos", "clientes", $cantidad, $desde);
        http_response_code(200);
        echo json_encode([
            "status" => 200,
            "total" => count($cursos),
            "cursos" => $cursos
        ]);
    }

    public function show($id)
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

        $curso = ModeloCursos::show("cursos", "clientes", $id);
        if ($curso) {
            http_response_code(200);
            echo json_encode([
                "status" => 200,
                "curso" => $curso
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => 404,
                "detalle" => "Curso no encontrado"
            ]);
        }
    }

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
            empty($datos["titulo"]) ||
            empty($datos["descripcion"]) ||
            empty($datos["instructor"]) ||
            !isset($datos["precio"]) ||
            !is_numeric($datos["precio"])
        ) {
            http_response_code(400);
            echo json_encode([
                "status" => 400,
                "detalle" => "Todos los campos son obligatorios y el precio debe ser numérico"
            ]);
            return;
        }

        $datosInsert = [
            "titulo" => $datos["titulo"],
            "descripcion" => $datos["descripcion"],
            "instructor" => $datos["instructor"],
            "imagen" => isset($datos["imagen"]) ? $datos["imagen"] : null,
            "precio" => $datos["precio"],
            "id_creador" => $cliente["id"],
            "created_at" => date('Y-m-d H:i:s'),
            "updated_at" => date('Y-m-d H:i:s')
        ];

        $create = ModeloCursos::create("cursos", $datosInsert);

        if ($create == "ok") {
            http_response_code(201);
            echo json_encode([
                "status" => 201,
                "detalle" => "Curso creado correctamente"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => 500,
                "detalle" => "Error al crear el curso"
            ]);
        }
    }

    public function update($id, $datos)
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

        $curso = ModeloCursos::show("cursos", "clientes", $id);
        if (!$curso || $curso["id_creador"] != $cliente["id"]) {
            http_response_code(403);
            echo json_encode([
                "status" => 403,
                "detalle" => "No tienes permisos para modificar este curso"
            ]);
            return;
        }

        // Validaciones básicas
        if (
            empty($datos["titulo"]) ||
            empty($datos["descripcion"]) ||
            empty($datos["instructor"]) ||
            !isset($datos["precio"]) ||
            !is_numeric($datos["precio"])
        ) {
            http_response_code(400);
            echo json_encode([
                "status" => 400,
                "detalle" => "Todos los campos son obligatorios y el precio debe ser numérico"
            ]);
            return;
        }

        $datosUpdate = [
            "id" => $id,
            "titulo" => $datos["titulo"],
            "descripcion" => $datos["descripcion"],
            "instructor" => $datos["instructor"],
            "imagen" => isset($datos["imagen"]) ? $datos["imagen"] : null,
            "precio" => $datos["precio"],
            "updated_at" => date('Y-m-d H:i:s')
        ];

        $update = ModeloCursos::update("cursos", $datosUpdate);

        if ($update == "ok") {
            http_response_code(200);
            echo json_encode([
                "status" => 200,
                "detalle" => "Curso actualizado correctamente"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => 500,
                "detalle" => "Error al actualizar el curso"
            ]);
        }
    }

    public function delete($id)
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

        $curso = ModeloCursos::show("cursos", "clientes", $id);
        if (!$curso || $curso["id_creador"] != $cliente["id"]) {
            http_response_code(403);
            echo json_encode([
                "status" => 403,
                "detalle" => "No tienes permisos para eliminar este curso"
            ]);
            return;
        }

        $delete = ModeloCursos::delete("cursos", $id);

        if ($delete == "ok") {
            http_response_code(200);
            echo json_encode([
                "status" => 200,
                "detalle" => "Curso eliminado correctamente"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => 500,
                "detalle" => "Error al eliminar el curso"
            ]);
        }
    }
}

?>