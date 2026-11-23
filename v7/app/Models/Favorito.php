<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Favorito extends Model
{
    public function idsPorUsuario(int $usuarioId): array
    {
        $stmt = $this->db->prepare("SELECT producto_id FROM favoritos WHERE usuario_id = :uid");
        $stmt->execute([':uid' => $usuarioId]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    public function listarProductosFavoritos(int $usuarioId): array
    {
        $sql = "SELECT p.id, p.codigo_sku, p.nombre, p.slug, p.tamano, p.color,
                       p.precio, p.precio_oferta, p.etiqueta,
                       c.nombre AS categoria_nombre,
                       COALESCE(i.stock_actual, 0) AS stock_actual
                FROM favoritos f
                INNER JOIN productos p ON f.producto_id = p.id
                INNER JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN inventario i ON p.id = i.producto_id
                WHERE f.usuario_id = :uid AND p.activo = 1
                ORDER BY f.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':uid' => $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function toggle(int $usuarioId, int $productoId): bool
    {
        $check = $this->db->prepare("SELECT id FROM favoritos WHERE usuario_id = :uid AND producto_id = :pid");
        $check->execute([':uid' => $usuarioId, ':pid' => $productoId]);

        if ($check->fetch()) {
            $del = $this->db->prepare("DELETE FROM favoritos WHERE usuario_id = :uid AND producto_id = :pid");
            $del->execute([':uid' => $usuarioId, ':pid' => $productoId]);
            return false; // Removido
        }

        $ins = $this->db->prepare("INSERT INTO favoritos (usuario_id, producto_id) VALUES (:uid, :pid)");
        $ins->execute([':uid' => $usuarioId, ':pid' => $productoId]);
        return true; // Agregado
    }
}