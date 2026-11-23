<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;
use RuntimeException;

class ReporteCupon extends Model
{
    public function listarCupones(): array
    {
        $stmt = $this->db->query("SELECT * FROM cupones ORDER BY id DESC");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function crearCupon(array $datos): void
    {
        $codigo = mb_strtoupper(trim($datos['codigo'] ?? ''));
        $tipo   = ($datos['tipo_descuento'] ?? 'porcentaje') === 'monto' ? 'monto' : 'porcentaje';
        $valor  = (float)($datos['valor'] ?? 0);
        $minimo = max(0.0, (float)($datos['monto_minimo'] ?? 0));

        if ($codigo === '' || $valor <= 0) {
            throw new RuntimeException('Ingresa un código de cupón y un valor mayor a cero.');
        }

        if ($tipo === 'porcentaje' && $valor > 90) {
            throw new RuntimeException('El descuento porcentual no puede superar el 90%.');
        }

        $sql = "INSERT INTO cupones (codigo, tipo_descuento, valor, monto_minimo, activo)
                VALUES (:codigo, :tipo, :valor, :minimo, 1)
                ON DUPLICATE KEY UPDATE
                  tipo_descuento = VALUES(tipo_descuento),
                  valor = VALUES(valor),
                  monto_minimo = VALUES(monto_minimo),
                  activo = 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':codigo' => $codigo,
            ':tipo'   => $tipo,
            ':valor'  => round($valor, 2),
            ':minimo' => round($minimo, 2),
        ]);
    }

    public function alternarEstado(int $id): void
    {
        if ($id <= 0) {
            return;
        }
        $stmt = $this->db->prepare("UPDATE cupones SET activo = IF(activo = 1, 0, 1) WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public function datosReporteVentasWeb(): array
    {
        $sql = "SELECT codigo_pedido, nombre_cliente, telefono_cliente, tipo_entrega,
                       subtotal, descuento, costo_envio, total, estado, created_at
                FROM pedidos
                ORDER BY id DESC";
        $stmt = $this->db->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function datosReportePos(): array
    {
        $stmt = $this->db->query("SELECT * FROM ventas_pos ORDER BY id DESC");
        $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

        return array_map(static function (array $r): array {
            return [
                'codigo_venta'      => (string)($r['codigo_venta'] ?? $r['codigo'] ?? ('POS-' . ($r['id'] ?? ''))),
                'cliente_nombre'    => (string)($r['cliente_nombre'] ?? $r['nombre_cliente'] ?? 'Cliente Mostrador'),
                'cliente_documento' => (string)($r['cliente_documento'] ?? $r['documento'] ?? ''),
                'metodo_pago'       => (string)($r['metodo_pago'] ?? ''),
                'subtotal'          => (string)($r['subtotal'] ?? '0.00'),
                'descuento'         => (string)($r['descuento'] ?? '0.00'),
                'total'             => (string)($r['total'] ?? '0.00'),
                'monto_recibido'    => (string)($r['monto_recibido'] ?? '0.00'),
                'vuelto'            => (string)($r['vuelto'] ?? '0.00'),
                'created_at'        => (string)($r['created_at'] ?? $r['fecha'] ?? ''),
            ];
        }, $rows);
    }

    public function datosReporteInventario(): array
    {
        $sql = "SELECT p.codigo_sku, p.nombre, c.nombre AS categoria, p.tamano, p.color,
                       p.precio, p.precio_oferta,
                       COALESCE(i.stock_actual, 0) AS stock_actual,
                       COALESCE(i.stock_minimo, 5) AS stock_minimo
                FROM productos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN inventario i ON p.id = i.producto_id
                ORDER BY p.id ASC";
        $stmt = $this->db->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }
}