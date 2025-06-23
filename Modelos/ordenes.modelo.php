<?php
class ModeloOrdenes
{
    // Buscar o crear registro en clientes_cursos
    public static function buscarOCrearClienteCurso($id_cliente, $id_curso)
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT id_clientes_cursos FROM clientes_cursos WHERE id_cliente = :id_cliente AND id_curso = :id_curso"
        );
        $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
        $stmt->bindParam(":id_curso", $id_curso, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        if ($row) {
            return $row["id_clientes_cursos"];
        } else {
            $pdo = Conexion::conectar();
            $stmt = $pdo->prepare(
                "INSERT INTO clientes_cursos (id_cliente, id_curso, created_at, updated_at) VALUES (:id_cliente, :id_curso, NOW(), NOW())"
            );
            $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
            $stmt->bindParam(":id_curso", $id_curso, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $id = $pdo->lastInsertId();
                $stmt->closeCursor();
                return $id;
            } else {
                $error = $stmt->errorInfo();
                error_log("Error al insertar en clientes_cursos: " . print_r($error, true));
                $stmt->closeCursor();
                return false;
            }
        }
    }

    // Crear orden
    public static function create($datos)
    {
        error_log("Insertando orden con:");
        error_log("id_clientes_cursos: " . $datos["id_clientes_cursos"]);
        error_log("id_metodo_pago: " . $datos["id_metodo_pago"]);
        error_log("total: " . $datos["total"]);

        $stmt = Conexion::conectar()->prepare(
            "INSERT INTO ordenes_clientes (id_clientes_cursos, id_metodo_pago, fecha_orden, total, created_at, updated_at)
             VALUES (:id_clientes_cursos, :id_metodo_pago, :fecha_orden, :total, NOW(), NOW())"
        );
        $stmt->bindParam(":id_clientes_cursos", $datos["id_clientes_cursos"], PDO::PARAM_INT);
        $stmt->bindParam(":id_metodo_pago", $datos["id_metodo_pago"], PDO::PARAM_INT);
        $stmt->bindParam(":fecha_orden", $datos["fecha_orden"], PDO::PARAM_STR);
        $stmt->bindParam(":total", $datos["total"]);
        if ($stmt->execute()) {
            $id = Conexion::conectar()->lastInsertId();
            $stmt->closeCursor();
            return $id;
        } else {
            $stmt->closeCursor();
            return false;
        }
    }

    // Obtener todas las órdenes del cliente autenticado
    public static function obtenerOrdenesPorCliente($id_cliente)
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT o.*, 
                    cc.id_curso, 
                    c.titulo AS curso_titulo, 
                    m.nombre AS metodo_pago
             FROM ordenes_clientes o
             INNER JOIN clientes_cursos cc ON o.id_clientes_cursos = cc.id_clientes_cursos
             INNER JOIN cursos c ON cc.id_curso = c.id
             INNER JOIN metodos_pago m ON o.id_metodo_pago = m.id
             WHERE cc.id_cliente = :id_cliente"
        );
        $stmt->bindParam(":id_cliente", $id_cliente, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }

    // Obtener una orden específica
    public static function obtenerOrdenPorId($id_orden)
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT o.*, 
                    cc.id_cliente, 
                    cc.id_curso, 
                    c.titulo AS curso_titulo, 
                    m.nombre AS metodo_pago
             FROM ordenes_clientes o
             INNER JOIN clientes_cursos cc ON o.id_clientes_cursos = cc.id_clientes_cursos
             INNER JOIN cursos c ON cc.id_curso = c.id
             INNER JOIN metodos_pago m ON o.id_metodo_pago = m.id
             WHERE o.id_orden = :id_orden"
        );
        $stmt->bindParam(":id_orden", $id_orden, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $resultado;
    }
}
?>