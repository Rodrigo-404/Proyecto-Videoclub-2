<?php
declare(strict_types=1);
namespace models;

use exceptions\CupoSuperadoException;
use exceptions\SoporteNoAlquiladoException;
use exceptions\SoporteYaAlquiladoException;
use TypeError;
use utils\ArrayUtils;
use utils\SoporteUtils;

class Cliente
{
    public string $nombre;
    private int $numero;
    private array $soportesAlquilados = [];
    private int $numSoportesAlquilados = 0;
    private int $maxAlquilerConcurrente;

    /**
     * @param string $nombre
     * @param int $numero
     * @param int $maxAlquilerConcurrente
     */
    public function __construct(string $nombre, int $numero, int $maxAlquilerConcurrente=3)
    {
        $this->nombre = $nombre;
        $this->numero = $numero;
        $this->maxAlquilerConcurrente = $maxAlquilerConcurrente;
    }

    public function getNumero(): int
    {
        return $this->numero;
    }

    public function setNumero(int $numero): void
    {
        $this->numero = $numero;
    }

    public function getNumSoportesAlquilados(): int
    {
        return $this->numSoportesAlquilados;
    }

    public function getSoportesAlquilados(): array
    {
        return $this->soportesAlquilados;
    }

    public function muestraResumen(): string {
        return "Cliente $this->nombre tiene " . count($this->soportesAlquilados) . " alquileres";
    }

    /**
     * @param Soporte $s
     * @return bool
     * @throws TypeError Si se le pasa por parámetro un valor nulo.
     */
    public function tieneAlquilado(Soporte $s): bool{
        return in_array($s, $this->soportesAlquilados, true);
    }

    /**
     * @throws SoporteYaAlquiladoException
     * @throws CupoSuperadoException
     */
    public function alquilar(Soporte $s): bool {
        if($this->tieneAlquilado($s)) {
            throw new SoporteYaAlquiladoException(
                "No se puede alquilar el soporte con número <b>{$s->getNumero()}</b> para el cliente {$this->nombre}".
                " porque ya lo tiene alquilado."
            );
        } else if ($this->numSoportesAlquilados >= $this->maxAlquilerConcurrente) {
            throw new CupoSuperadoException(
                "Cliente {$this->nombre} supera alquiler: tiene".
                " <b>{$this->numSoportesAlquilados}/{$this->maxAlquilerConcurrente}</b> soportes alquilados."
            );
        }

        $s->setAlquilado(true);
        $this->numSoportesAlquilados++;
        $this->soportesAlquilados[] = $s;

        return true;
    }

    /**
     * @throws SoporteNoAlquiladoException
     */
    public function devolver(int $numSoporte): bool {
        $soporte = SoporteUtils::getSoporteByNumSoporte($this->soportesAlquilados, $numSoporte);
        $index = ArrayUtils::indexOf($this->soportesAlquilados, $soporte);

        if (!in_array($soporte, $this->soportesAlquilados)) {
            throw new SoporteNoAlquiladoException("El número de soporte $numSoporte no está alquilado.");
        }

        $this->soportesAlquilados[$index]->setAlquilado(false); // Lo desalquilamos.
        $this->numSoportesAlquilados--; // Restamos número de soportes alquilados.

        unset($this->soportesAlquilados[$index]);

        // REINDEXAR
        $this->soportesAlquilados = array_values($this->soportesAlquilados);

        return true;
    }

    public function listaAlquileres(): void {
        echo "$this->nombre tiene $this->numSoportesAlquilados: </br>" .
            join(" | ",  $this->soportesAlquilados) . "</br>";
    }
}