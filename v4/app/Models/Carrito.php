<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Carrito extends Model
{
    private function tokenSesion(): string
    {
        if (empty($_SESSION['cart_token'])) {
            $_SESSION['cart_token'] = bin2hex(random_bytes(24));
        }
        return $_SESSION['cart_token'];
    }

    public function obtenerItems(): array
    {
        $token  = $this->tokenSesion();
        $userId = $_SESSION['user']['id'] ?? null;

        if ($userId) {
            $sql = "SELECT c.id, c.producto_id, c.cantidad, c.envoltura_regalo, c.dedicatoria,
                           p.nombre, p.slug, p.codigo_sku, p.tamano, p.color,
                           COALESCE(p.precio_oferta, p.precio) AS precio_unitario,
                           COALESCE(i.stock_actual, 0) AS stock_actual
                    FROM carrito c
                    INNER JOIN productos p ON c.producto_id = p.id
                    LEFT JOIN inventario i ON p.id = i.producto_id
                    WHERE (c.session_token = :token OR c.usuario_id = :uid) AND p.activo = 1
                    ORDER BY c.id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token' => $token, ':uid' => (int)$userId]);
        } else {
            $sql = "SELECT c.id, c.producto_id, c.cantidad, c.envoltura_regalo, c.dedicatoria,
                           p.nombre, p.slug, p.codigo_sku, p.tamano, p.color,
                           COALESCE(p.precio_oferta, p.precio) AS precio_unitario,
                           COALESCE(i.stock_actual, 0) AS stock_actual
                    FROM carrito c
                    INNER JOIN productos p ON c.producto_id = p.id
                    LEFT JOIN inventario i ON p.id = i.producto_id
                    WHERE c.session_token = :token AND p.activo = 1
                    ORDER BY c.id ASC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':token' => $token]);
        }

        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($items as &$item) {
            $item['cantidad']        = (int)$item['cantidad'];
            $item['precio_unitario'] = (float)$item['precio_unitario'];
            $item['stock_actual']    = (int)$item['stock_actual'];
            $item['subtotal_item']   = round($item['cantidad'] * $item['precio_unitario'], 2);
        }

        return $items;
    }

    public function resumen(): array
    {
        $items = $this->obtenerItems();
        $subtotal = 0.0;
        $totalUnidades = 0;

        foreach ($items as $item) {
            $subtotal += $item['subtotal_item'];
            $totalUnidades += $item['cantidad'];
        }

        return [
            'items'          => $items,
            'total_unidades' => $totalUnidades,
            'subtotal'       => round($subtotal, 2),
        ];
    }

    public function agregar(int $productoId, int $cantidad = 1, bool $envoltura = false, string $dedicatoria = ''): array
    {
        $cantidad = max(1, $cantidad);
        $token    = $this->tokenSesion();
        $userId   = !empty($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;

        // Validar stock disponible
        $st = $this->db->prepare(
            "SELECT p.id, p.nombre, COALESCE(i.stock_actual, 0) AS stock_actual
             FROM productos p
             LEFT JOIN inventario i ON p.id = i.producto_id
             WHERE p.id = :pid AND p.activo = 1"
        );
        $st->execute([':pid' => $productoId]);
        $prod = $st->fetch(PDO::FETCH_ASSOC);

        if (!$prod) {
            return ['ok' => false, 'message' => 'El peluche no existe.'];
        }

        $stockDisp = (int)$prod['stock_actual'];
        if ($stockDisp <= 0) {
            return ['ok' => false, 'message' => 'Sin stock disponible por el momento.'];
        }

        $check = $this->db->prepare(
            "SELECT id, cantidad FROM carrito WHERE session_token = :token AND producto_id = :pid LIMIT 1"
        );
        $check->execute([':token' => $token, ':pid' => $productoId]);
        $existente = $check->fetch(PDO::FETCH_ASSOC);

        if ($existente) {
            $nuevaCant = min($stockDisp, (int)$existente['cantidad'] + $cantidad);
            $upd = $this->db->prepare(
                "UPDATE carrito
                 SET cantidad = :cant, usuario_id = COALESCE(:uid, usuario_id),
                     envoltura_regalo = IF(:env = 1, 1, envoltura_regalo),
                     dedicatoria = IF(:ded != '', :ded2, dedicatoria)
                 WHERE id = :id"
            );
            $upd->execute([
                ':cant' => $nuevaCant,
                ':uid'  => $userId,
                ':env'  => $envoltura ? 1 : 0,
                ':ded'  => $dedicatoria,
                ':ded2' => $dedicatoria,
                ':id'   => $existente['id'],
            ]);
        } else {
            $cantInsert = min($stockDisp, $cantidad);
            $ins = $this->db->prepare(
                "INSERT INTO carrito (usuario_id, session_token, producto_id, cantidad, envoltura_regalo, dedicatoria)
                 VALUES (:uid, :token, :pid, :cant, :env, :ded)"
            );
            $ins->execute([
                ':uid'   => $userId,
                ':token' => $token,
                ':pid'   => $productoId,
                ':cant'  => $cantInsert,
                ':env'   => $envoltura ? 1 : 0,
                ':ded'   => $dedicatoria !== '' ? $dedicatoria : null,
            ]);
        }

        return ['ok' => true, 'message' => 'Agregado al carrito: ' . $prod['nombre']];
    }

    public function actualizarCantidad(int $carritoId, int $cantidad): array
    {
        $token = $this->tokenSesion();
        $stmt = $this->db->prepare(
            "SELECT c.id, c.producto_id, COALESCE(i.stock_actual, 0) AS stock_actual
             FROM carrito c
             LEFT JOIN inventario i ON c.producto_id = i.producto_id
             WHERE c.id = :id AND c.session_token = :token LIMIT 1"
        );
        $stmt->execute([':id' => $carritoId, ':token' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return ['ok' => false, 'message' => 'Ítem no encontrado.'];
        }

        if ($cantidad <= 0) {
            return $this->eliminar($carritoId);
        }

        $nuevaCant = min((int)$row['stock_actual'], $cantidad);
        if ($nuevaCant <= 0) {
            return $this->eliminar($carritoId);
        }

        $upd = $this->db->prepare("UPDATE carrito SET cantidad = :cant WHERE id = :id");
        $upd->execute([':cant' => $nuevaCant, ':id' => $carritoId]);

        return ['ok' => true, 'message' => 'Cantidad actualizada.'];
    }

    public function eliminar(int $carritoId): array
    {
        $token = $this->tokenSesion();
        $del = $this->db->prepare("DELETE FROM carrito WHERE id = :id AND session_token = :token");
        $del->execute([':id' => $carritoId, ':token' => $token]);
        return ['ok' => true, 'message' => 'Producto eliminado del carrito.'];
    }

    public function vaciar(): void
    {
        $token  = $this->tokenSesion();
        $userId = $_SESSION['user']['id'] ?? null;
        if ($userId) {
            $del = $this->db->prepare("DELETE FROM carrito WHERE session_token = :token OR usuario_id = :uid");
            $del->execute([':token' => $token, ':uid' => (int)$userId]);
        } else {
            $del = $this->db->prepare("DELETE FROM carrito WHERE session_token = :token");
            $del->execute([':token' => $token]);
        }
    }
}