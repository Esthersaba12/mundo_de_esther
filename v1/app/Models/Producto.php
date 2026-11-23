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
        $sql = "SELECT p.id, p.codigo_sku, p.nombre, p.slug, p.descripcion, p.tamano,
                       p.color, p.precio, p.precio_oferta, p.etiqueta,
                       c.nombre AS categoria_nombre, c.slug AS categoria_slug,
                       COALESCE(i.stock_actual, 0) AS stock_actual,
                       COALESCE(i.stock_minimo, 5) AS stock_minimo,
                       COALESCE(pi.ruta_imagen, 'assets/images/productos/default-peluche.svg') AS imagen
                FROM productos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN inventario i ON p.id = i.producto_id
                LEFT JOIN producto_imagenes pi ON p.id = pi.producto_id AND pi.es_principal = 1
                WHERE p.activo = 1
                ORDER BY p.destacado DESC, p.id ASC
                LIMIT :limite";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}