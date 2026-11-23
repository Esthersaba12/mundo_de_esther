<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;
use RuntimeException;
use Throwable;

class Pedido extends Model
{
    public function crearDesdeCarrito(array $datosCheckout, array $itemsCarrito, ?array $cuponValido = null): array
    {
        if (empty($itemsCarrito)) {
            throw new RuntimeException('Tu carrito está vacío.');
        }

        $this->db->beginTransaction();

        try {
            $subtotal = 0.0;
            $itemsBloqueados = [];

            // 1. Bloquear filas de inventario y verificar stock real dentro de la transacción
            $stmtStock = $this->db->prepare(
                "SELECT i.id, i.stock_actual, p.nombre, COALESCE(p.precio_oferta, p.precio) AS precio_actual
                 FROM inventario i
                 INNER JOIN productos p ON i.producto_id = p.id
                 WHERE i.producto_id = :pid AND p.activo = 1
                 FOR UPDATE"
            );

            foreach ($itemsCarrito as $item) {
                $pid  = (int)$item['producto_id'];
                $cant = (int)$item['cantidad'];

                $stmtStock->execute([':pid' => $pid]);
                $inv = $stmtStock->fetch(PDO::FETCH_ASSOC);

                if (!$inv || (int)$inv['stock_actual'] < $cant) {
                    throw new RuntimeException(
                        'Stock insuficiente para "' . ($inv['nombre'] ?? 'peluche') . '". Disponible: ' . (int)($inv['stock_actual'] ?? 0)
                    );
                }

                $precioUnit = (float)$inv['precio_actual'];
                $subItem    = round($precioUnit * $cant, 2);
                $subtotal  += $subItem;

                $itemsBloqueados[] = [
                    'producto_id'      => $pid,
                    'cantidad'         => $cant,
                    'precio_unitario'  => $precioUnit,
                    'subtotal'         => $subItem,
                    'stock_anterior'   => (int)$inv['stock_actual'],
                    'stock_nuevo'      => (int)$inv['stock_actual'] - $cant,
                    'dedicatoria_item' => $item['dedicatoria'] ?? null,
                ];
            }

            $subtotal = round($subtotal, 2);
            $envolturaActiva = !empty($datosCheckout['envoltura_regalo']) ? 1 : 0;
            $costoEnvoltura = $envolturaActiva ? (float)COSTO_ENVOLTURA_REGALO : 0.0;

            $tipoEntrega = ($datosCheckout['tipo_entrega'] ?? 'delivery') === 'recojo_tienda'
                ? 'recojo_tienda'
                : 'delivery';
            $costoEnvio = $tipoEntrega === 'delivery' ? (float)COSTO_ENVIO_ESTANDAR : 0.0;

            $descuento = 0.0;
            $cuponId   = null;
            if ($cuponValido && !empty($cuponValido['valido']) && !empty($cuponValido['cupon'])) {
                $descuento = (float)$cuponValido['descuento'];
                $cuponId   = (int)$cuponValido['cupon']['id'];
            }

            $total = round(max(0.0, $subtotal + $costoEnvoltura + $costoEnvio - $descuento), 2);
            $codigoPedido = 'MDE-' . date('Ymd') . '-' . random_int(1000, 9999);
            $usuarioId = !empty($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

            // 2. Insertar cabecera del pedido
            $sqlPedido = "INSERT INTO pedidos (
                codigo_pedido, usuario_id, cupon_id, nombre_cliente, telefono_cliente, email_cliente,
                tipo_entrega, direccion_entrega, envoltura_regalo, costo_envoltura, costo_envio,
                dedicatoria_general, subtotal, descuento, total, estado
            ) VALUES (
                :codigo, :uid, :cupon_id, :nombre, :telefono, :email,
                :tipo_entrega, :direccion, :envoltura, :costo_envoltura, :costo_envio,
                :dedicatoria, :subtotal, :descuento, :total, 'Pendiente'
            )";

            $stmtPedido = $this->db->prepare($sqlPedido);
            $stmtPedido->execute([
                ':codigo'          => $codigoPedido,
                ':uid'             => $usuarioId,
                ':cupon_id'        => $cuponId,
                ':nombre'          => trim($datosCheckout['nombre_cliente']),
                ':telefono'        => trim($datosCheckout['telefono_cliente']),
                ':email'           => trim($datosCheckout['email_cliente'] ?? '') ?: null,
                ':tipo_entrega'    => $tipoEntrega,
                ':direccion'       => $tipoEntrega === 'delivery' ? trim($datosCheckout['direccion_entrega'] ?? '') : 'Recojo en Tienda',
                ':envoltura'       => $envolturaActiva,
                ':costo_envoltura' => $costoEnvoltura,
                ':costo_envio'     => $costoEnvio,
                ':dedicatoria'     => trim($datosCheckout['dedicatoria_general'] ?? '') ?: null,
                ':subtotal'        => $subtotal,
                ':descuento'       => $descuento,
                ':total'           => $total,
            ]);

            $pedidoId = (int)$this->db->lastInsertId();

            // 3. Insertar detalle, descontar inventario y registrar movimiento
            $stmtDet = $this->db->prepare(
                "INSERT INTO detalle_pedido (pedido_id, producto_id, cantidad, precio_unitario, subtotal, dedicatoria_item)
                 VALUES (:pid, :prod_id, :cant, :precio, :sub, :ded)"
            );
            $stmtUpdInv = $this->db->prepare(
                "UPDATE inventario SET stock_actual = :nuevo WHERE producto_id = :prod_id"
            );
            $stmtMov = $this->db->prepare(
                "INSERT INTO movimientos_inventario
                 (producto_id, usuario_id, tipo, cantidad, stock_anterior, stock_nuevo, motivo, referencia_tipo, referencia_id)
                 VALUES (:prod_id, :uid, 'salida', :cant, :ant, :nuevo, :motivo, 'pedido', :ref_id)"
            );

            foreach ($itemsBloqueados as $row) {
                $stmtDet->execute([
                    ':pid'     => $pedidoId,
                    ':prod_id' => $row['producto_id'],
                    ':cant'    => $row['cantidad'],
                    ':precio'  => $row['precio_unitario'],
                    ':sub'     => $row['subtotal'],
                    ':ded'     => $row['dedicatoria_item'],
                ]);

                $stmtUpdInv->execute([
                    ':nuevo'   => $row['stock_nuevo'],
                    ':prod_id' => $row['producto_id'],
                ]);

                $stmtMov->execute([
                    ':prod_id' => $row['producto_id'],
                    ':uid'     => $usuarioId,
                    ':cant'    => $row['cantidad'],
                    ':ant'     => $row['stock_anterior'],
                    ':nuevo'   => $row['stock_nuevo'],
                    ':motivo'  => 'Venta web pedido ' . $codigoPedido,
                    ':ref_id'  => $pedidoId,
                ]);
            }

            // 4. Incrementar uso de cupón si aplica
            if ($cuponId !== null) {
                $stmtCup = $this->db->prepare("UPDATE cupones SET usos_actuales = usos_actuales + 1 WHERE id = :cid");
                $stmtCup->execute([':cid' => $cuponId]);
            }

            $this->db->commit();

            return [
                'id'            => $pedidoId,
                'codigo_pedido' => $codigoPedido,
                'total'         => $total,
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
        $stmt = $this->db->prepare("SELECT * FROM pedidos WHERE codigo_pedido = :codigo LIMIT 1");
        $stmt->execute([':codigo' => trim($codigo)]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$pedido) {
            return null;
        }

        $det = $this->db->prepare(
            "SELECT d.*, p.nombre, p.codigo_sku, p.tamano
             FROM detalle_pedido d
             INNER JOIN productos p ON d.producto_id = p.id
             WHERE d.pedido_id = :pid"
        );
        $det->execute([':pid' => $pedido['id']]);
        $pedido['items'] = $det->fetchAll(PDO::FETCH_ASSOC);

        return $pedido;
    }

    public function listarPorUsuario(int $usuarioId): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, codigo_pedido, tipo_entrega, total, estado, created_at
             FROM pedidos
             WHERE usuario_id = :uid
             ORDER BY created_at DESC"
        );
        $stmt->execute([':uid' => $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}