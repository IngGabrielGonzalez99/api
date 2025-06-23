<?php
class ModeloMetodosPago
{
    public static function showById($id)
    {
        $stmt = Conexion::conectar()->prepare(
            "SELECT * FROM metodos_pago WHERE id = :id"
        );
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        $metodo = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $metodo;
    }
}
?>