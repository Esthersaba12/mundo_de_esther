<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;
use RuntimeException;
use Throwable;

class Pos extends Model
{
    public const METODOS_PAGO = ['efectivo', 'yape', 'tarjeta'];

    private function columnasVentasPos(): array
    {
        static $cols = null;
        if ($cols !== null) {
            return $cols;
        }
        $stmt = $this->db->query("SHOW COLUMNS FROM ventas_pos");
        $cols = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
        return $cols;
    }

    private function colCodigoVenta(array $cols): string
    {
        foreach (['codigo_venta', 'numero_ticket', 'codigo', 'folio', 'nro_comprobante'] as $cand) {
            if (in_array($cand, $cols, true)) {
                return $cand;
            }
        }
        return 'id';
    }

    private function colClienteVenta(array $cols): string
    {
        foreach (['cliente_nombre', 'nombre_cliente', 'cliente'] as $cand) {
            if (in_array($cand, $cols, true)) {
                return $cand;
            }
        }
        return "'Cliente Mostrador'";
    }

    private function colVendedorVenta(array $cols): ?string
    {
        foreach (['vendedor_id', 'usuario_id', 'cajero_id'] as $cand) {
            if (in_array($cand, $cols, true)) {
                return $cand;
            }
        }
        return null;
    }

    public function buscarProductos(string $termino = ''): array
    {
        $termino = trim($termino);
        $sql = "SELECT p.id, p.codigo_sku, p.nombre, p.tamano, p.color,
                       COALESCE(p.precio_oferta, p.precio) AS precio_venta,
                       COALESCE(i.stock_actual, 0) AS stock_actual
                FROM productos p
                LEFT JOIN inventario i ON p.id = i.producto_id
                WHERE p.activo = 1 AND COALESCE(i.stock_actual, 0) > 0";

        $params = [];
        if ($termino !== '') {
            $sql .= " AND (p.nombre LIKE :q OR p.codigo_sku LIKE :q2 OR p.color LIKE :q3)";
            $like = '%' . $termino . '%';
            $params[':q']  = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }

        $sql .= " ORDER BY p.nombre ASC LIMIT 30";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['id']           = (int)$r['id'];
            $r['precio_venta'] = (float)$r['precio_venta'];
            $r['stock_actual'] = (int)$r['stock_actual'];
        }
        return $rows;
    }

    public function registrarVenta(int $vendedorId, array $datosVenta, array $items): array
    {
        if (empty($items)) {
            throw new RuntimeException('Agrega al menos un peluche al ticket de venta.');
        }

        $metodoPago = in_array($datosVenta['metodo_pago'] ?? '', self::METODOS_PAGO, true)
            ? $datosVenta['metodo_pago']
            : 'efectivo';

        $descuento = max(0.0, round((float)($datosVenta['descuento'] ?? 0), 2));
        $montoRecibido = max(0.0, round((float)($datosVenta['monto_recibido'] ?? 0), 2));

        $this->db->beginTransaction();

        try {
            $subtotal = 0.0;
            $lineasValidadas = [];

            $stmtStock = $this->db->prepare(
                "SELECT i.stock_actual, p.nombre, COALESCE(p.precio_oferta, p.precio) AS precio_venta
                 FROM inventario i
                 INNER JOIN productos p ON i.producto_id = p.id
                 WHERE i.producto_id = :pid AND p.activo = 1
                 FOR UPDATE"
            );

            foreach ($items as $it) {
                $pid  = (int)($it['producto_id'] ?? 0);
                $cant = (int)($it['cantidad'] ?? 0);
                if ($pid <= 0 || $cant <= 0) {
                    continue;
                }

                $stmtStock->execute([':pid' => $pid]);
                $prod = $stmtStock->fetch(PDO::FETCH_ASSOC);

                if (!$prod || (int)$prod['stock_actual'] < $cant) {
                    throw new RuntimeException(
                        'Stock insuficiente para "' . ($prod['nombre'] ?? 'producto') . '". Disponible: ' . (int)($prod['stock_actual'] ?? 0)
                    );
                }

                $precioUnit = (float)$prod['precio_venta'];
                $subItem    = round($precioUnit * $cant, 2);
                $subtotal  += $subItem;

                $lineasValidadas[] = [
                    'producto_id'     => $pid,
                    'cantidad'        => $cant,
                    'precio_unitario' => $precioUnit,
                    'subtotal'        => $subItem,
                    'stock_anterior'  => (int)$prod['stock_actual'],
                    'stock_nuevo'     => (int)$prod['stock_actual'] - $cant,
                ];
            }

            if (empty($lineasValidadas)) {
                throw new RuntimeException('No hay ítems válidos en el ticket.');
            }

            $subtotal  = round($subtotal, 2);
            $descuento = min($subtotal, $descuento);
            $total     = round(max(0.0, $subtotal - $descuento), 2);

            if ($metodoPago === 'efectivo') {
                if ($montoRecibido < $total) {
                    throw new RuntimeException('El monto recibido en efectivo (S/ ' . number_format($montoRecibido, 2) . ') es menor al total (S/ ' . number_format($total, 2) . ').');
                }
                $vuelto = round($montoRecibido - $total, 2);
            } else {
                $montoRecibido = $total;
                $vuelto = 0.0;
            }

            $cols = $this->columnasVentasPos();
            $colCod = $this->colCodigoVenta($cols);
            $codigoVenta = ($colCod === 'id') ? '' : ('POS-' . date('Ymd') . '-' . random_int(1000, 9999));

            $dataMap = [
                'vendedor_id'       => $vendedorId,
                'usuario_id'        => $vendedorId,
                'cajero_id'         => $vendedorId,
                'cliente_nombre'    => trim($datosVenta['cliente_nombre'] ?? '') ?: 'Cliente Mostrador',
                'nombre_cliente'    => trim($datosVenta['cliente_nombre'] ?? '') ?: 'Cliente Mostrador',
                'cliente_documento' => trim($datosVenta['cliente_documento'] ?? '') ?: null,
                'metodo_pago'       => $metodoPago,
                'subtotal'          => $subtotal,
                'descuento'         => $descuento,
                'total'             => $total,
                'monto_recibido'    => $montoRecibido,
                'vuelto'            => $vuelto,
                'notas'             => trim($datosVenta['notas'] ?? '') ?: null,
            ];
            if ($colCod !== 'id') {
                $dataMap[$colCod] = $codigoVenta;
            }

            $camposInsert = [];
            $placeholders = [];
            $paramsVenta  = [];
            foreach ($dataMap as $campo => $valor) {
                if (in_array($campo, $cols, true)) {
                    $camposInsert[] = $campo;
                    $ph = ':' . $campo;
                    $placeholders[] = $ph;
                    $paramsVenta[$ph] = $valor;
                }
            }

            $sqlVenta = "INSERT INTO ventas_pos (" . implode(', ', $camposInsert) . ")
                         VALUES (" . implode(', ', $placeholders) . ")";
            $stmtVenta = $this->db->prepare($sqlVenta);
            $stmtVenta->execute($paramsVenta);

            $ventaId = (int)$this->db->lastInsertId();
            if ($codigoVenta === '') {
                $codigoVenta = (string)$ventaId;
            }

            $stmtDet = $this->db->prepare(
                "INSERT INTO detalle_venta_pos (venta_id, producto_id, cantidad, precio_unitario, subtotal)
                 VALUES (:vid, :pid, :cant, :precio, :sub)"
            );
            $stmtUpdInv = $this->db->prepare(
                "UPDATE inventario SET stock_actual = :nuevo WHERE producto_id = :pid"
            );
            $stmtMov = $this->db->prepare(
                "INSERT INTO movimientos_inventario
                 (producto_id, usuario_id, tipo, cantidad, stock_anterior, stock_nuevo, motivo, referencia_tipo, referencia_id)
                 VALUES (:pid, :uid, 'salida', :cant, :ant, :nuevo, :motivo, 'venta_pos', :ref_id)"
            );

            foreach ($lineasValidadas as $linea) {
                $stmtDet->execute([
                    ':vid'    => $ventaId,
                    ':pid'    => $linea['producto_id'],
                    ':cant'   => $linea['cantidad'],
                    ':precio' => $linea['precio_unitario'],
                    ':sub'    => $linea['subtotal'],
                ]);

                $stmtUpdInv->execute([
                    ':nuevo' => $linea['stock_nuevo'],
                    ':pid'   => $linea['producto_id'],
                ]);

                $stmtMov->execute([
                    ':pid'    => $linea['producto_id'],
                    ':uid'    => $vendedorId,
                    ':cant'   => $linea['cantidad'],
                    ':ant'    => $linea['stock_anterior'],
                    ':nuevo'  => $linea['stock_nuevo'],
                    ':motivo' => 'Venta POS ' . $codigoVenta,
                    ':ref_id' => $ventaId,
                ]);
            }

            $this->db->commit();

            return [
                'id'           => $ventaId,
                'codigo_venta' => $codigoVenta,
                'total'        => $total,
                'vuelto'       => $vuelto,
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function buscarPorCodigo(string $codigo): ?array
    {
        $codigo = trim($codigo);
        if ($codigo === '') {
            return null;
        }

        $cols = $this->columnasVentasPos();
        $colCod = $this->colCodigoVenta($cols);
        $colVend = $this->colVendedorVenta($cols);

        if ($colVend !== null) {
            $sql = "SELECT v.*, v.`{$colCod}` AS codigo_venta,
                           COALESCE(u.nombre, 'Staff') AS vendedor_nombre
                    FROM ventas_pos v
                    LEFT JOIN usuarios u ON v.`{$colVend}` = u.id
                    WHERE v.`{$colCod}` = :codigo LIMIT 1";
        } else {
            $sql = "SELECT v.*, v.`{$colCod}` AS codigo_venta,
                           'Staff' AS vendedor_nombre
                    FROM ventas_pos v
                    WHERE v.`{$colCod}` = :codigo LIMIT 1";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':codigo' => $codigo]);
        $venta = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$venta) {
            return null;
        }

        $det = $this->db->prepare(
            "SELECT d.*, p.nombre, p.codigo_sku, p.tamano
             FROM detalle_venta_pos d
             INNER JOIN productos p ON d.producto_id = p.id
             WHERE d.venta_id = :vid"
        );
        $det->execute([':vid' => $venta['id']]);
        $venta['items'] = $det->fetchAll(PDO::FETCH_ASSOC);

        return $venta;
    }

    public function ultimasVentas(int $limite = 15): array
    {
        $cols = $this->columnasVentasPos();
        $colCod = $this->colCodigoVenta($cols);
        $colCli = $this->colClienteVenta($cols);
        $colVend = $this->colVendedorVenta($cols);
        $cliExpr = str_starts_with($colCli, "'") ? $colCli : "v.`{$colCli}`";

        if ($colVend !== null) {
            $sql = "SELECT v.id, v.`{$colCod}` AS codigo_venta,
                           {$cliExpr} AS cliente_nombre,
                           v.metodo_pago, v.total, v.created_at,
                           COALESCE(u.nombre, 'Staff') AS vendedor
                    FROM ventas_pos v
                    LEFT JOIN usuarios u ON v.`{$colVend}` = u.id
                    ORDER BY v.id DESC
                    LIMIT :lim";
        } else {
            $sql = "SELECT v.id, v.`{$colCod}` AS codigo_venta,
                           {$cliExpr} AS cliente_nombre,
                           v.metodo_pago, v.total, v.created_at,
                           'Staff' AS vendedor
                    FROM ventas_pos v
                    ORDER BY v.id DESC
                    LIMIT :lim";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}