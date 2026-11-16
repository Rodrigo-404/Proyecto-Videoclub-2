<?php
namespace utils;

use enums\ReturnMode;
use models\Cliente;
use utils\ArrayUtils;

class ClienteUtils
{
    /**
     * Devuelve el cliente dado el array de socios y el número de Cliente.
     * @param array $socios
     * @param int $numeroCliente
     * @return Cliente|null
     */
    public static function getClienteByNum(array $socios, int $numeroCliente): ?Cliente
    {
        $result = ArrayUtils::searchByCondition(
            $socios,
            ReturnMode::SINGLE_ELEMENT,
            fn($e) => $e->getNumero() === $numeroCliente
        );

        return $result instanceof Cliente? $result: null;
    }
}