<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use PDO;

class Cupon extends Model
{
    public function validarYCalcular(string $codigo, float $subtotal): array
    {
        $codigoLimpio = mb_strtoupper(trim($codigo));
        if ($codigoLimpio === '') {
            return ['valido' => false, 'mensaje' => 'Ingresa un código de cupón.', 'descuento' => 0.0, 'cupon' => null];
        }

        $sql = "SELECT * FROM cupones WHERE UPPER(codigo) = :codigo AND activo = 1 LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':codigo' => $codigoLimpio]);
        $cupon = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$cupon) {
            return ['valido' => false, 'mensaje' => 'El cupón ingresado no existe o está inactivo.', 'descuento' => 0.0, 'cupon' => null];
        }

        if ($subtotal < (float)$cupon['monto_minimo']) {
            return [
                'valido'    => false,
                'mensaje'   => 'Este cupón aplica desde compras de S/ ' . number_format((float)$cupon['monto_minimo'], 2),
                'descuento' => 0.0,
                'cupon'     => null,
            ];
        }

        if ($cupon['usos_maximos'] !== null && (int)$cupon['usos_actuales'] >= (int)$cupon['usos_maximos']) {
            return ['valido' => false, 'mensaje' => 'Este cupón ya alcanzó su límite de usos.', 'descuento' => 0.0, 'cupon' => null];
        }

        $descuento = $cupon['tipo_descuento'] === 'porcentaje'
            ? round($subtotal * ((float)$cupon['valor'] / 100), 2)
            : round((float)$cupon['valor'], 2);

        $descuento = min($subtotal, max(0.0, $descuento));

        return [
            'valido'    => true,
            'mensaje'   => 'Cupón ' . $cupon['codigo'] . ' aplicado (-S/ ' . number_format($descuento, 2) . ')',
            'descuento' => $descuento,
            'cupon'     => $cupon,
        ];
    }
}