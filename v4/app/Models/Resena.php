<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Resena extends Model
{
    public function listarPorProducto(int $productoId): array
    {
        $sql = "SELECT r.id, r.calificacion, r.comentario, r.created_at, u.nombre AS autor
                FROM resenas r
                INNER JOIN usuarios u ON r.usuario_id = u.id
                WHERE r.producto_id = :pid AND r.aprobado = 1
                ORDER BY r.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pid' => $productoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear(int $productoId, int $usuarioId, int $calificacion, string $comentario): void
    {
        $calificacion = max(1, min(5, $calificacion));
        $sql = "INSERT INTO resenas (producto_id, usuario_id, calificacion, comentario, aprobado)
                VALUES (:pid, :uid, :cal, :com, 1)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':pid' => $productoId,
            ':uid' => $usuarioId,
            ':cal' => $calificacion,
            ':com' => trim($comentario),
        ]);
    }
}