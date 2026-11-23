<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;
use RuntimeException;

class CatalogoAdmin extends Model
{
    private function columnasProductos(): array
    {
        static $cols = null;
        if ($cols !== null) {
            return $cols;
        }
        $stmt = $this->db->query("SHOW COLUMNS FROM productos");
        $cols = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
        return $cols;
    }

    public function listarTodos(): array
    {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre,
                       COALESCE(i.stock_actual, 0) AS stock_actual
                FROM productos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN inventario i ON p.id = i.producto_id
                ORDER BY p.id DESC";
        $stmt = $this->db->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function obtenerPorId(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function actualizarProducto(int $id, array $datos, ?string $imagenRuta = null): void
    {
        $actual = $this->obtenerPorId($id);
        if (!$actual) {
            throw new RuntimeException('El peluche no existe.');
        }

        $nombre = trim($datos['nombre'] ?? '');
        $sku    = mb_strtoupper(trim($datos['codigo_sku'] ?? ''));
        $catId  = (int)($datos['categoria_id'] ?? $actual['categoria_id']);
        $precio = (float)($datos['precio'] ?? 0);

        if ($nombre === '' || $sku === '' || $precio <= 0) {
            throw new RuntimeException('Nombre, código SKU y precio normal son obligatorios.');
        }

        $ofertaRaw = trim((string)($datos['precio_oferta'] ?? ''));
        $precioOferta = ($ofertaRaw !== '' && (float)$ofertaRaw > 0) ? round((float)$ofertaRaw, 2) : null;

        $tamano = in_array($datos['tamano'] ?? '', ['Mini', 'Mediano', 'Grande', 'Gigante'], true)
            ? $datos['tamano']
            : 'Mediano';

        $etiqueta = in_array($datos['etiqueta'] ?? '', ['Ninguna', 'Nuevo', 'Oferta', 'Mas vendido'], true)
            ? $datos['etiqueta']
            : 'Ninguna';

        $activo = !empty($datos['activo']) ? 1 : 0;

        $cols = $this->columnasProductos();
        $sets = [
            'categoria_id = :cat',
            'codigo_sku = :sku',
            'nombre = :nombre',
            'descripcion = :desc',
            'tamano = :tamano',
            'color = :color',
            'precio = :precio',
            'precio_oferta = :oferta',
            'etiqueta = :etiqueta',
            'activo = :activo',
        ];
        $params = [
            ':cat'    => $catId,
            ':sku'    => $sku,
            ':nombre' => $nombre,
            ':desc'   => trim($datos['descripcion'] ?? '') ?: (string)$actual['descripcion'],
            ':tamano' => $tamano,
            ':color'  => trim($datos['color'] ?? '') ?: 'Pastel',
            ':precio' => round($precio, 2),
            ':oferta' => $precioOferta,
            ':etiqueta'=> $etiqueta,
            ':activo' => $activo,
            ':id'     => $id,
        ];

        if ($imagenRuta !== null && $imagenRuta !== '') {
            foreach (['imagen_url', 'imagen', 'foto'] as $colImg) {
                if (in_array($colImg, $cols, true)) {
                    $sets[] = "`{$colImg}` = :img";
                    $params[':img'] = $imagenRuta;
                    break;
                }
            }
        }

        $sql = "UPDATE productos SET " . implode(', ', $sets) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
    }

    public function obtenerCategorias(): array
    {
        $stmt = $this->db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}