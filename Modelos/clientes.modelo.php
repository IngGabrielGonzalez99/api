<?php

require_once "conexion.php";

class ModeloClientes
{
    public static function index($tabla)
    {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM $tabla");
        $stmt->execute();
        $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $stmt = null;
        return $resultado;
    }

    public static function create($tabla, $datos)
    {
        $sql = "INSERT INTO $tabla (nombre, apellido, email, id_cliente, llave_secreta, created_at, updated_at)
                VALUES (:nombre, :apellido, :email, :id_cliente, :llave_secreta, :created_at, :updated_at)";
        $stmt = Conexion::conectar()->prepare($sql);

        $stmt->bindParam(":nombre", $datos["nombre"], PDO::PARAM_STR);
        $stmt->bindParam(":apellido", $datos["apellido"], PDO::PARAM_STR);
        $stmt->bindParam(":email", $datos["email"], PDO::PARAM_STR);
        $stmt->bindParam(":id_cliente", $datos["id_cliente"], PDO::PARAM_STR);
        $stmt->bindParam(":llave_secreta", $datos["llave_secreta"], PDO::PARAM_STR);
        $stmt->bindParam(":created_at", $datos["created_at"], PDO::PARAM_STR);
        $stmt->bindParam(":updated_at", $datos["updated_at"], PDO::PARAM_STR);

        if ($stmt->execute()) {
            $stmt->closeCursor();
            $stmt = null;
            return "ok";
        } else {
            $stmt->closeCursor();
            $stmt = null;
            return "error";
        }
    }

    public static function verificarExistencia($datos)
    {
        $stmt = Conexion::conectar()->prepare("SELECT * FROM clientes_cursos WHERE id_clientes_cursos = :id");
        $stmt->bindParam(":id", $datos["id_clientes_cursos"], PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log("Verificando existencia de id_clientes_cursos=" . $datos["id_clientes_cursos"] . " => " . print_r($row, true));
        $stmt->closeCursor();
        return $row;
    }

    public static function findByIdOrNameAndSecret($id_o_nombre, $llave_secreta)
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT * FROM clientes WHERE (id_cliente = :id_cliente OR nombre = :nombre) AND llave_secreta = :llave_secreta"
        );
        $stmt->bindParam(":id_cliente", $id_o_nombre, PDO::PARAM_STR);
        $stmt->bindParam(":nombre", $id_o_nombre, PDO::PARAM_STR);
        $stmt->bindParam(":llave_secreta", $llave_secreta, PDO::PARAM_STR);
        $stmt->execute();
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $cliente;
    }

}
?>