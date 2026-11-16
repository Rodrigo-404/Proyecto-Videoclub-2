<?php

use models\Videoclub;
use utils\ClienteUtils;

$vc = new Videoclub("Severo 8A");
//voy a incluir unos cuantos soportes de prueba
$vc->incluirJuego("God of War", 19.99, "PS4", 1, 1);
$vc->incluirJuego("The Last of Us Part II", 49.99, "PS4", 1, 1);
$vc->incluirDvd("Torrente", 4.5, ["es"],"16:9");
$vc->incluirDvd("Origen", 4.5, ["es","en","fr"], "16:9");
$vc->incluirDvd("El Imperio Contraataca", 3, ["es","en"],"16:9");
$vc->incluirCintaVideo("Los cazafantasmas", 3.5, 107);
$vc->incluirCintaVideo("El nombre de la Rosa", 1.5, 140);
//listo los productos
echo $vc->listarProductos() . "</br>";

//voy a crear algunos socios
$vc->incluirSocio("Amancio Ortega");
$vc->incluirSocio("Pablo Picasso", 2);
$vc->alquilarSocioProducto(1,2);
$vc->alquilarSocioProducto(1,3);
//alquilo otra vez el soporte 2 al socio 1.
// no debe dejarme porque ya lo tiene alquilado
$vc->alquilarSocioProducto(1,2);
//alquilo el soporte 6 al socio 1.
$vc->alquilarSocioProducto(1,6);
//se alquila el soporte 5 al socio 1.
//no se puede porque el socio 1 tiene 3 alquileres como máximo
$vc->alquilarSocioProducto(1,5);
//listo los socios
echo $vc->listarSocios() . "</br>";

$vc->alquilarSocioProductos(1, [1,2,4]); //Soporte 2 alquilado, no alquila nada.
$vc->alquilarSocioProductos(2, [1,2]); // Los Alquila

$vc->devolverSocioProductos(2,[1]); // Devuelve soporte 1
$vc->devolverSocioProductos(2, [1,2]); // No puede devolver el soporte 1 porque no lo tiene, no hace nada
$vc->devolverSocioProductos(2, [2, 33]); // Soporte con número 33 no existe, no hace nada

$socio2 = ClienteUtils::getClienteByNum($vc->getSocios(), 2);
$socio2->listaAlquileres(); // Debe aparecer solo el soporte 2