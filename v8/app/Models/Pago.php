<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;
use RuntimeException;
use Throwable;

class Pago extends Model
{
    public const ESTADOS_PEDIDO = [
        'Pendiente',
        'Pagado',
        'Preparando',
        'Enviado',
        'Entregado',
        'Cancelado',
    ];

    public function registrarPagoDigital(array $datos): int
    {
        $pedidoId = (int)$datos['pedido_id'];
        $metodo   = in_array($datos['metodo'] ?? '', ['yape', 'whatsapp'], true)
            ? $datos['metodo']
            : 'yape';

        $this->db->beginTransaction();

        try {
            $stmtPed = $this->db->prepare("SELECT id, total, estado FROM pedidos WHERE id = :pid FOR UPDATE");
            $stmtPed->execute([':pid' => $pedidoId]);
            $pedido = $stmtPed->fetch(PDO::FETCH_ASSOC);

            if (!$pedido) {
                throw new RuntimeException('El pedido no existe.');
            }

            $sqlPago = "INSERT INTO pagos (pedido_id, metodo, monto, codigo_operacion, comprobante_ruta, estado_pago)
                        VALUES (:pid, :metodo, :monto, :cod_op, :comp, 'pendiente')";
            $stmtPago = $this->db->prepare($sqlPago);
            $stmtPago->execute([
                ':pid'    => $pedidoId,
                ':metodo' => $metodo,
                ':monto'  => (float)$pedido['total'],
                ':cod_op' => trim($datos['codigo_operacion'] ?? '') ?: null,
                ':comp'   => $datos['comprobante_ruta'] ?? null,
            ]);

            $pagoId = (int)$this->db->lastInsertId();
            $this->db->commit();

            return $pagoId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function obtenerPorPedido(int $pedidoId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM pagos WHERE pedido_id = :pid ORDER BY created_at DESC");
        $stmt->execute([':pid' => $pedidoId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizarEstadoPedido(int $pedidoId, string $nuevoEstado, ?int $verificadorId = null, bool $verificarPago = false): void
    {
        if (!in_array($nuevoEstado, self::ESTADOS_PEDIDO, true)) {
            throw new RuntimeException('Estado de pedido no válido.');
        }

        $this->db->beginTransaction();

        try {
            $updPed = $this->db->prepare("UPDATE pedidos SET estado = :estado WHERE id = :pid");
            $updPed->execute([
                ':estado' => $nuevoEstado,
                ':pid'    => $pedidoId,
            ]);

            if ($verificarPago) {
                $updPago = $this->db->prepare(
                    "UPDATE pagos
                     SET estado_pago = 'verificado', verificado_por = :uid, verificado_at = NOW()
                     WHERE pedido_id = :pid AND estado_pago = 'pendiente'"
                );
                $updPago->execute([
                    ':uid' => $verificadorId,
                    ':pid' => $pedidoId,
                ]);
            }

            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function listarPedidosConPagos(?string $estadoFiltro = null): array
    {
        $sql = "SELECT p.*,
                       (SELECT pg.metodo FROM pagos pg WHERE pg.pedido_id = p.id ORDER BY pg.id DESC LIMIT 1) AS ultimo_metodo,
                       (SELECT pg.codigo_operacion FROM pagos pg WHERE pg.pedido_id = p.id AND pg.codigo_operacion IS NOT NULL ORDER BY pg.id DESC LIMIT 1) AS codigo_operacion,
                       (SELECT pg.comprobante_ruta FROM pagos pg WHERE pg.pedido_id = p.id AND pg.comprobante_ruta IS NOT NULL AND pg.comprobante_ruta != '' ORDER BY pg.id DESC LIMIT 1) AS comprobante_ruta,
                       (SELECT pg.estado_pago FROM pagos pg WHERE pg.pedido_id = p.id ORDER BY pg.id DESC LIMIT 1) AS estado_pago
                FROM pedidos p";

        $params = [];
        if ($estadoFiltro && in_array($estadoFiltro, self::ESTADOS_PEDIDO, true)) {
            $sql .= " WHERE p.estado = :estado";
            $params[':estado'] = $estadoFiltro;
        }

        $sql .= " ORDER BY p.created_at DESC LIMIT 60";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}