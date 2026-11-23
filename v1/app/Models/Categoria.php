<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Categoria extends Model
{
    public function obtenerActivas(): array
    {
        $sql = "SELECT id, nombre, slug, descripcion, icono, orden
                FROM categorias
                WHERE activo = 1
                ORDER BY orden ASC, nombre ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}