<?php
/**
 * Memorión (2) - memorion-2-2.php
 *
 * @author    Bartolomé Sintes Marco <bartolome.sintes+mclibre@gmail.com>
 * @copyright 2017 Bartolomé Sintes Marco
 * @license   http://www.gnu.org/licenses/agpl.txt AGPL 3 or later
 * @version   2017-11-20
 * @link      http://www.mclibre.org
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// Nos unimos a la sesión
session_name("memorion-2");
session_start();

// Si no está guardado en la sesión el número de dibujos ...
if (!isset($_SESSION["numeroDibujos"])) {
    // ... redirigimos a la primera página
    header("Location:memorion-2-1.php");
    exit;
}

// Funciones auxiliares
function recoge($var)
{
    $tmp = (isset($_REQUEST[$var]))
    ? trim(htmlspecialchars($_REQUEST[$var], ENT_QUOTES, "UTF-8"))
    : "";
    return $tmp;
}

// Recogemos el dato (botón)
$accion = recoge("accion");

// Si se ha pulsado "Nueva partida" ...
if ($accion == "nueva") {
    // ... borramos la partida actual
    unset($_SESSION["dibujos"]);
    // ... y redirigimos a la primera página
    header("Location:memorion-2-1.php");
    exit;
// Si se ha pulsado "Cambiar número de dibujos" ...
} elseif ($accion == "numero") {
    // ... redirigimos al formulario correspondiente
    header("Location:memorion-2-3.php");
    exit;
}

// Redirigimos a la primera página
header("Location:memorion-2-1.php");
exit;