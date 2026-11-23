<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Usuario extends Model
{
    public function buscarPorEmail(string $email): ?array
    {
        $sql = "SELECT u.id, u.rol_id, u.nombre, u.email, u.telefono, u.direccion,
                       u.password_hash, u.activo, r.nombre AS rol_nombre
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE u.email = :email
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => mb_strtolower(trim($email))]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function buscarPorId(int $id): ?array
    {
        $sql = "SELECT u.id, u.rol_id, u.nombre, u.email, u.telefono, u.direccion,
                       u.activo, u.created_at, r.nombre AS rol_nombre
                FROM usuarios u
                INNER JOIN roles r ON u.rol_id = r.id
                WHERE u.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    public function registrarCliente(array $datos): int
    {
        $sql = "INSERT INTO usuarios (rol_id, nombre, email, telefono, direccion, password_hash, activo)
                VALUES (3, :nombre, :email, :telefono, :direccion, :password_hash, 1)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':nombre'        => trim($datos['nombre']),
            ':email'         => mb_strtolower(trim($datos['email'])),
            ':telefono'      => trim($datos['telefono'] ?? ''),
            ':direccion'     => trim($datos['direccion'] ?? ''),
            ':password_hash' => password_hash($datos['password'], PASSWORD_DEFAULT),
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function actualizarHash(int $id, string $plainPassword): void
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET password_hash = :hash WHERE id = :id");
        $stmt->execute([
            ':hash' => password_hash($plainPassword, PASSWORD_DEFAULT),
            ':id'   => $id,
        ]);
    }
}