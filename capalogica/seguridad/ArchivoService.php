<?php
declare(strict_types=1);

require_once __DIR__ . '/../core/BaseService.php';

final class ArchivoService extends BaseService
{
    public function voucher(int $idVoucher): array
    {
        if ($idVoucher < 1) {
            return [];
        }
        return $this->fila($this->call('CALL sp_voucher_obtener(?)', 'i', [$idVoucher]));
    }
}
