<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;
use RuntimeException;
use Throwable;

class Inventario extends Model
{
    public function listarCompleto(bool $soloStockBajo = false): array
    {
        $sql = "SELECT p.id, p.codigo_sku, p.nombre, p.slug, p.tamano, p.color,
                       p.precio, p.precio_oferta, p.etiqueta, p.activo,
                       c.nombre AS categoria_nombre,
                       COALESCE(i.stock_actual, 0) AS stock_actual,
                       COALESCE(i.stock_minimo, 5) AS stock_minimo
                FROM productos p
                INNER JOIN categorias c ON p.categoria_id = c.id
                LEFT JOIN inventario i ON p.id = i.producto_id";

        if ($soloStockBajo) {
            $sql .= " WHERE COALESCE(i.stock_actual, 0) <= COALESCE(i.stock_minimo, 5)";
        }

        $sql .= " ORDER BY (COALESCE(i.stock_actual, 0) <= COALESCE(i.stock_minimo, 5)) DESC, p.nombre ASC";
        $stmt = $this->db->query($sql);
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function registrarMovimiento(int $productoId, int $usuarioId, string $tipo, int $cantidad, string $motivo): array
    {
        if (!in_array($tipo, ['entrada', 'salida', 'ajuste'], true)) {
            throw new RuntimeException('Tipo de movimiento no válido.');
        }
        if ($cantidad < 0 || ($tipo !== 'ajuste' && $cantidad === 0)) {
            throw new RuntimeException('La cantidad ingresada no es válida.');
        }

        $this->db->beginTransaction();

        try {
            $stmt = $this->db->prepare(
                "SELECT p.id, p.nombre, i.id AS inv_id, COALESCE(i.stock_actual, 0) AS stock_actual
                 FROM productos p
                 LEFT JOIN inventario i ON p.id = i.producto_id
                 WHERE p.id = :pid
                 FOR UPDATE"
            );
            $stmt->execute([':pid' => $productoId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                throw new RuntimeException('El producto seleccionado no existe.');
            }

            $stockAnterior = (int)$row['stock_actual'];
            $stockNuevo = match ($tipo) {
                'entrada' => $stockAnterior + $cantidad,
                'salida'  => $stockAnterior - $cantidad,
                'ajuste'  => $cantidad,
            };

            if ($stockNuevo < 0) {
                throw new RuntimeException('No hay suficiente stock para realizar esta salida.');
            }

            if (!empty($row['inv_id'])) {
                $upd = $this->db->prepare("UPDATE inventario SET stock_actual = :nuevo WHERE producto_id = :pid");
                $upd->execute([':nuevo' => $stockNuevo, ':pid' => $productoId]);
            } else {
                $insInv = $this->db->prepare(
                    "INSERT INTO inventario (producto_id, stock_actual, stock_minimo) VALUES (:pid, :nuevo, 5)"
                );
                $insInv->execute([':pid' => $productoId, ':nuevo' => $stockNuevo]);
            }

            $cantMov = $tipo === 'ajuste' ? abs($stockNuevo - $stockAnterior) : $cantidad;
            $insMov = $this->db->prepare(
                "INSERT INTO movimientos_inventario
                 (producto_id, usuario_id, tipo, cantidad, stock_anterior, stock_nuevo, motivo, referencia_tipo)
                 VALUES (:pid, :uid, :tipo, :cant, :ant, :nuevo, :motivo, 'manual')"
            );
            $insMov->execute([
                ':pid'    => $productoId,
                ':uid'    => $usuarioId,
                ':tipo'   => $tipo,
                ':cant'   => $cantMov,
                ':ant'    => $stockAnterior,
                ':nuevo'  => $stockNuevo,
                ':motivo' => trim($motivo) !== '' ? trim($motivo) : 'Movimiento manual desde inventario',
            ]);

            $this->db->commit();

            return [
                'nombre'         => $row['nombre'],
                'stock_anterior' => $stockAnterior,
                'stock_nuevo'    => $stockNuevo,
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function ultimosMovimientos(int $limite = 25): array
    {
        $sql = "SELECT m.*, p.nombre AS producto_nombre, p.codigo_sku,
                       COALESCE(u.nombre, 'Sistema') AS usuario_nombre
                FROM movimientos_inventario m
                INNER JOIN productos p ON m.producto_id = p.id
                LEFT JOIN usuarios u ON m.usuario_id = u.id
                ORDER BY m.id DESC
                LIMIT :lim";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearProductoConStock(array $datos, int $usuarioId): int
    {
        $nombre = trim($datos['nombre'] ?? '');
        $sku    = mb_strtoupper(trim($datos['codigo_sku'] ?? ''));
        $catId  = (int)($datos['categoria_id'] ?? 1);
        $precio = (float)($datos['precio'] ?? 0);
        $stockInicial = max(0, (int)($datos['stock_inicial'] ?? 0));
        $stockMinimo  = max(1, (int)($datos['stock_minimo'] ?? 5));

        if ($nombre === '' || $sku === '' || $precio <= 0) {
            throw new RuntimeException('Completa nombre, código SKU y precio válido.');
        }

        $slugBase = trim(preg_replace('/[^a-z0-9]+/i', '-', mb_strtolower($nombre)), '-');
        $slug = $slugBase . '-' . random_int(100, 999);

        $this->db->beginTransaction();

        try {
            $ins = $this->db->prepare(
                "INSERT INTO productos (categoria_id, codigo_sku, nombre, slug, descripcion, tamano, color, precio, etiqueta, activo)
                 VALUES (:cat, :sku, :nombre, :slug, :desc, :tamano, :color, :precio, :etiqueta, 1)"
            );
            $ins->execute([
                ':cat'      => $catId,
                ':sku'      => $sku,
                ':nombre'   => $nombre,
                ':slug'     => $slug,
                ':desc'     => trim($datos['descripcion'] ?? '') ?: 'Peluche suave de colección Mundo de Esther.',
                ':tamano'   => in_array($datos['tamano'] ?? '', ['Mini', 'Mediano', 'Grande', 'Gigante'], true) ? $datos['tamano'] : 'Mediano',
                ':color'    => trim($datos['color'] ?? '') ?: 'Pastel',
                ':precio'   => $precio,
                ':etiqueta' => in_array($datos['etiqueta'] ?? '', ['Ninguna', 'Nuevo', 'Oferta', 'Mas vendido'], true) ? $datos['etiqueta'] : 'Nuevo',
            ]);

            $prodId = (int)$this->db->lastInsertId();

            $insInv = $this->db->prepare(
                "INSERT INTO inventario (producto_id, stock_actual, stock_minimo) VALUES (:pid, :stock, :min)"
            );
            $insInv->execute([
                ':pid'   => $prodId,
                ':stock' => $stockInicial,
                ':min'   => $stockMinimo,
            ]);

            if ($stockInicial > 0) {
                $insMov = $this->db->prepare(
                    "INSERT INTO movimientos_inventario
                     (producto_id, usuario_id, tipo, cantidad, stock_anterior, stock_nuevo, motivo, referencia_tipo)
                     VALUES (:pid, :uid, 'entrada', :cant, 0, :nuevo, 'Stock inicial de alta de producto', 'manual')"
                );
                $insMov->execute([
                    ':pid'   => $prodId,
                    ':uid'   => $usuarioId,
                    ':cant'  => $stockInicial,
                    ':nuevo' => $stockInicial,
                ]);
            }

            $this->db->commit();
            return $prodId;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function obtenerCategorias(): array
    {
        $stmt = $this->db->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
        return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    }

    public function resumenEjecutivo(): array
    {
        $ventasWeb = (float)$this->db->query(
            "SELECT COALESCE(SUM(total), 0) FROM pedidos WHERE estado != 'Cancelado'"
        )->fetchColumn();

        $pedidosPendientes = (int)$this->db->query(
            "SELECT COUNT(*) FROM pedidos WHERE estado = 'Pendiente'"
        )->fetchColumn();

        $ventasPos = (float)$this->db->query(
            "SELECT COALESCE(SUM(total), 0) FROM ventas_pos"
        )->fetchColumn();

        $unidadesStock = (int)$this->db->query(
            "SELECT COALESCE(SUM(stock_actual), 0) FROM inventario"
        )->fetchColumn();

        $alertasBajo = (int)$this->db->query(
            "SELECT COUNT(*) FROM inventario WHERE stock_actual <= stock_minimo"
        )->fetchColumn();

        return [
            'ventas_web'         => $ventasWeb,
            'pedidos_pendientes' => $pedidosPendientes,
            'ventas_pos'         => $ventasPos,
            'ingresos_totales'   => $ventasWeb + $ventasPos,
            'unidades_stock'     => $unidadesStock,
            'alertas_bajo'       => $alertasBajo,
        ];
    }
}