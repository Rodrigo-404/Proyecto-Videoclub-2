<?php
declare(strict_types=1);
namespace models;

use exceptions\ClienteNoEncontradoException;
use exceptions\CupoSuperadoException;
use exceptions\SoporteNoAlquiladoException;
use exceptions\SoporteNoEncontradoException;
use exceptions\SoporteYaAlquiladoException;
use utils\ClienteUtils;
use utils\SoporteUtils;

class Videoclub
{
    private string $nombre;
    private array $productos = [];
    private int $numProductosAlquilados;
    private int $numTotalAlquileres;
    private array $socios = [];
    private int $numSocios;

    public function __construct(string $nombre)
    {
        $this->nombre = $nombre;
    }

    public function getNumProductosAlquilados(): int
    {
        return $this->numProductosAlquilados;
    }

    public function getNumTotalAlquileres(): int
    {
        return $this->numTotalAlquileres;
    }

    public function getSocios(): array
    {
        return $this->socios;
    }

    private function incluirProducto(Soporte $producto): void {
        $this->productos[] = $producto;
    }

    public function incluirCintaVideo(string $titulo, float $precio, int $duracion): void {
        $this->incluirProducto(new CintaVideo($titulo, count($this->productos), $precio, $duracion));
    }

    public function incluirDvd(string $titulo, float $precio, array $idiomas, string $pantalla): void {
        $this->incluirProducto(new Dvd($titulo, count($this->productos), $precio, $idiomas, $pantalla));
    }

    public function incluirJuego(string $titulo, float $precio, string $consola, int $minJug, int $maxJug): void {
        $this->incluirProducto(new Juego($titulo, count($this->productos), $precio, $consola, $minJug, $maxJug));
    }

    public function incluirSocio(string $nombre, int $maxAlquilaresConcurrentes = 3): void {
        $this->socios[] = new Cliente($nombre, count($this->socios)+1, $maxAlquilaresConcurrentes);
    }

    public function listarProductos(): string {
        return join(
                "</br>",
                array_map(fn($e) => $e->muestraResumen(), $this->productos)
            );
    }

    public function listarSocios(): string {
        return join(
                "</br>",
                array_map(fn($e) => $e->muestraResumen(), $this->socios)
            );
    }

    public function alquilarSocioProducto(int $numeroCliente, int $numeroSoporte): void {
        try {
            $cliente = ClienteUtils::getClienteByNum($this->socios, $numeroCliente);
            $soporte = SoporteUtils::getSoporteByNumSoporte($this->productos, $numeroSoporte);

            // Lanza excepciones dependiendo del error
            if ($cliente === null) {
                throw new ClienteNoEncontradoException("Cliente con número <b>$numeroCliente</b> no existe");
            }
            if ($soporte === null) {
                throw new SoporteNoEncontradoException("Soporte con número <b>$numeroSoporte</b> no existe");
            }

            $cliente->alquilar($soporte);
        } catch (
            ClienteNoEncontradoException |
            SoporteNoEncontradoException |
            CupoSuperadoException |
            SoporteYaAlquiladoException
            $e
        ) {
            echo "<b>[ERROR]</b> " . $e->getMessage() . "</br>";
        } catch (\Exception $e) {
            echo "<b>[ERROR NO CONTROLADO]</b> " . $e->getMessage() . "</br>";
        }
    }

    public function alquilarSocioProductos(int $numSocio, array $numerosProductos): void {
        try {
            $cliente = ClienteUtils::getClienteByNum($this->socios, $numSocio);
            if ($cliente === null) {
                throw new ClienteNoEncontradoException("Cliente con número $numSocio no existe");
            }

            try {
                $soportesAlquilados =
                    array_filter($numerosProductos, fn($numero) => $cliente->tieneAlquilado(
                            SoporteUtils::getSoporteByNumSoporte($this->productos, $numero)
                    ));
            } catch (\TypeError) {
                $numerosProductosActuales = array_map(fn($p) => $p->getNumero(), $this->productos);
                $numerosNoPresentes =
                    join(", ",
                        array_map(fn($n) => "<b>$n</b>",
                            array_filter($numerosProductos,
                                fn($num) => !in_array($num, $numerosProductosActuales, true))));

                throw new SoporteNoEncontradoException("Los soportes con número $numerosNoPresentes no existen");
            }

            if (count($soportesAlquilados) > 0) {
                $listaSoportesAlquilados =
                    join(", ",
                        array_map(fn($numProducto) => "<b>$numProducto</b>", $soportesAlquilados));

                throw new SoporteYaAlquiladoException("Los soportes $listaSoportesAlquilados ya están alquilados.");
            }

            array_walk($numerosProductos, fn($numero) => $this->alquilarSocioProducto($numSocio, $numero));
        } catch (
            SoporteYaAlquiladoException | ClienteNoEncontradoException | SoporteNoEncontradoException
            $e
        ) {
            echo "<b>[ERROR]</b> " . $e->getMessage() . "</br>";
        } catch (\Exception $e) {
            echo "<b>[ERROR NO CONTROLADO]</b> " . $e->getMessage() . "</br>";
        }
    }

    public function devolverSocioProducto(int $numSocio, int $numProducto): void
    {
        try {
            $cliente = ClienteUtils::getClienteByNum($this->socios, $numSocio);
            $soporte = SoporteUtils::getSoporteByNumSoporte($this->productos, $numProducto);

            // Lanza excepciones dependiendo del error
            if ($cliente === null) {
                throw new ClienteNoEncontradoException("Cliente con número $numSocio no existe");
            }
            if ($soporte === null) {
                throw new SoporteNoEncontradoException("Soporte con número $numProducto no existe");
            }

            $cliente->devolver($numProducto);
        } catch (
            ClienteNoEncontradoException | SoporteNoEncontradoException | SoporteNoAlquiladoException
            $e
        ) {
            echo "<b>[ERROR]</b> " . $e->getMessage() . "</br>";
        } catch (\Exception $e) {
            echo "<b>[ERROR NO CONTROLADO]</b> " . $e->getMessage() . "</br>";
        }
    }

    public function devolverSocioProductos(int $numSocio, array $numerosProductos): void
    {
        try {
            $cliente = ClienteUtils::getClienteByNum($this->socios, $numSocio);
            if ($cliente === null) {
                throw new ClienteNoEncontradoException("Cliente con número $numSocio no existe");
            }

            try {
                $noAlquilados = array_filter($numerosProductos,
                    fn($num) => !$cliente->tieneAlquilado(SoporteUtils::getSoporteByNumSoporte($this->productos, $num)));

            } catch (\TypeError $e) {
                $numerosProductosActuales = array_map(fn($p) => $p->getNumero(), $this->productos);
                $numerosNoPresentes =
                    join(", ",
                        array_map(fn($numProducto) => "<b>$numProducto</b>",
                            array_filter($numerosProductos,
                                fn($num) => !in_array($num, $numerosProductosActuales, true))));

                throw new SoporteNoEncontradoException("Los soportes con número $numerosNoPresentes no existen");
            }

            if(count($noAlquilados) > 0) {
                $recuento =
                    join(", ",
                        array_map(fn($e)=>"<b>$e</b>", $noAlquilados));

                throw new SoporteNoAlquiladoException("Los soportes con número $recuento no están alquilados");
            }

            array_walk($numerosProductos, fn($numProducto)=>$this->devolverSocioProducto($numSocio, $numProducto));

        } catch (ClienteNoEncontradoException | SoporteNoEncontradoException | SoporteNoAlquiladoException $e) {
            echo "<b>[ERROR]</b> " . $e->getMessage() . "</br>";
        } catch (\Exception $e) {
            echo "<b>[ERROR NO CONTROLADO]</b> " . $e->getMessage() . "</br>";
        }
    }
}