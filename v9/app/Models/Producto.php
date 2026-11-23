<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Producto extends Model
{
    public function obtenerDestacados(int $limite = 6): array
    {
        $sql = "SELECT p.id, p.codigo_sku, p.nombre, p.slug, p.descripcion, p.tamano,
                       p.color, p.precio, p.precio_oferta, p.etiqueta,
                       c.nombre AS categoria_nombre, c.slug AS categoria_slug,
                       COALESCE(i.stock_actual, 0) AS stock_actual,
                       COALESCE(pi.ruta_imagen, 'assets/images/productos/default-peluche.svg') AS imagen
                FROM productos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN inventario i ON p.id = i.producto_id
                LEFT JOIN producto_imagenes pi ON p.id = pi.producto_id AND pi.es_principal = 1
                WHERE p.activo = 1 AND p.destacado = 1
                ORDER BY p.id ASC
                LIMIT :limite";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerCatalogoInicial(int $limite = 12): array
    {
        return $this->filtrar(['limite' => $limite]);
    }

    public function filtrar(array $filtros = []): array
    {
        $where  = ['p.activo = 1'];
        $params = [];

        if (!empty($filtros['q'])) {
            $where[] = '(p.nombre LIKE :q OR p.descripcion LIKE :q2 OR p.codigo_sku LIKE :q3)';
            $term = '%' . trim($filtros['q']) . '%';
            $params[':q']  = $term;
            $params[':q2'] = $term;
            $params[':q3'] = $term;
        }

        if (!empty($filtros['categoria'])) {
            $where[] = 'c.slug = :categoria';
            $params[':categoria'] = trim($filtros['categoria']);
        }

        if (!empty($filtros['tamano'])) {
            $where[] = 'p.tamano = :tamano';
            $params[':tamano'] = trim($filtros['tamano']);
        }

        if (!empty($filtros['color'])) {
            $where[] = 'p.color = :color';
            $params[':color'] = trim($filtros['color']);
        }

        if (!empty($filtros['etiqueta'])) {
            $where[] = 'p.etiqueta = :etiqueta';
            $params[':etiqueta'] = trim($filtros['etiqueta']);
        }

        if (isset($filtros['precio_min']) && $filtros['precio_min'] !== '') {
            $where[] = 'COALESCE(p.precio_oferta, p.precio) >= :precio_min';
            $params[':precio_min'] = (float)$filtros['precio_min'];
        }

        if (isset($filtros['precio_max']) && $filtros['precio_max'] !== '') {
            $where[] = 'COALESCE(p.precio_oferta, p.precio) <= :precio_max';
            $params[':precio_max'] = (float)$filtros['precio_max'];
        }

        $orderBy = match ($filtros['orden'] ?? 'destacados') {
            'precio_asc'  => 'COALESCE(p.precio_oferta, p.precio) ASC',
            'precio_desc' => 'COALESCE(p.precio_oferta, p.precio) DESC',
            'nuevos'      => 'p.id DESC',
            default       => 'p.destacado DESC, p.id ASC',
        };

        $sql = "SELECT p.id, p.codigo_sku, p.nombre, p.slug, p.descripcion, p.tamano,
                       p.color, p.precio, p.precio_oferta, p.etiqueta,
                       c.nombre AS categoria_nombre, c.slug AS categoria_slug,
                       COALESCE(i.stock_actual, 0) AS stock_actual,
                       COALESCE(i.stock_minimo, 5) AS stock_minimo,
                       COALESCE(pi.ruta_imagen, 'assets/images/productos/default-peluche.svg') AS imagen,
                       COALESCE((SELECT ROUND(AVG(r.calificacion), 1) FROM resenas r WHERE r.producto_id = p.id AND r.aprobado = 1), 5.0) AS rating_promedio,
                       (SELECT COUNT(*) FROM resenas r WHERE r.producto_id = p.id AND r.aprobado = 1) AS total_resenas
                FROM productos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN inventario i ON p.id = i.producto_id
                LEFT JOIN producto_imagenes pi ON p.id = pi.producto_id AND pi.es_principal = 1
                WHERE " . implode(' AND ', $where) . "
                ORDER BY {$orderBy}";

        if (!empty($filtros['limite'])) {
            $sql .= ' LIMIT ' . (int)$filtros['limite'];
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorSlug(string $slug): ?array
    {
        $sql = "SELECT p.*, c.nombre AS categoria_nombre, c.slug AS categoria_slug,
                       COALESCE(i.stock_actual, 0) AS stock_actual,
                       COALESCE(i.stock_minimo, 5) AS stock_minimo
                FROM productos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN inventario i ON p.id = i.producto_id
                WHERE p.slug = :slug AND p.activo = 1
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slug' => $slug]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            return null;
        }

        $imgStmt = $this->db->prepare(
            "SELECT id, ruta_imagen, es_principal, orden
             FROM producto_imagenes
             WHERE producto_id = :pid
             ORDER BY es_principal DESC, orden ASC, id ASC"
        );
        $imgStmt->execute([':pid' => $producto['id']]);
        $producto['imagenes'] = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

        return $producto;
    }

    public function obtenerRelacionados(int $categoriaId, int $excludeProductoId, int $limite = 4): array
    {
        $sql = "SELECT p.id, p.codigo_sku, p.nombre, p.slug, p.tamano, p.color,
                       p.precio, p.precio_oferta, p.etiqueta,
                       c.nombre AS categoria_nombre,
                       COALESCE(i.stock_actual, 0) AS stock_actual
                FROM productos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN inventario i ON p.id = i.producto_id
                WHERE p.activo = 1 AND p.categoria_id = :cat_id AND p.id != :exclude_id
                ORDER BY p.destacado DESC, p.id ASC
                LIMIT :limite";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':cat_id', $categoriaId, PDO::PARAM_INT);
        $stmt->bindValue(':exclude_id', $excludeProductoId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerColoresDisponibles(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT color FROM productos WHERE activo = 1 ORDER BY color ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}