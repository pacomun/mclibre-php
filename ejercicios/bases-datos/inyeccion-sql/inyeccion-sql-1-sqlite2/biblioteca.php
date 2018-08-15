<?php
/**
 * Inyección SQL 1 - biblioteca.php
 *
 * @author    Bartolomé Sintes Marco <bartolome.sintes+mclibre@gmail.com>
 * @copyright 2011 Bartolomé Sintes Marco
 * @license   http://www.gnu.org/licenses/agpl.txt AGPL 3 or later
 * @version   2011-05-18
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

define("CABECERA_CON_CURSOR", true);            // Para función cabecera()
define("CABECERA_SIN_CURSOR", false);           // Para función cabecera()
define("FORM_METHOD",         "get");           // Formularios se envían con GET
//define("FORM_METHOD",         "post");          // Formularios se envían con POST
define("MENU_PRINCIPAL",      "menuPrincipal"); // Menú principal
define("MENU_VOLVER",         "menuVolver");    // Menú Volver a inicio
define("MYSQL",               "MySQL");
define("SQLITE",              "SQLite");
define("MAX_REG_TABLA", 20);  // Número máximo de registros en la tabla
$tamUsuario    = 80; // Tamaño del campo Usuario
$tamContraseña = 80; // Tamaño del campo Contraseña
$recorta = [
    "user"     => $tamUsuario,
    "password" => $tamContraseña
];

$dbMotor = SQLITE;                        // Base de datos empleada
if ($dbMotor == MYSQL) {
    define("MYSQL_HOST", "mysql:host=localhost"); // Nombre de host MYSQL
    define("MYSQL_USUARIO", "root");       // Nombre de usuario de MySQL
    define("MYSQL_PASSWORD", "");          // Contraseña de usuario de MySQL
    $dbDb    = "mclibre_inyeccion_sql_1";  // Nombre de la base de datos
    $dbTabla = $dbDb . ".tabla";             // Nombre de la tabla
} elseif ($dbMotor == SQLITE) {
    $dbDb    = "/home/barto/mclibre/tmp/mclibre/mclibre_inyeccion_sql_1.sqlite";  // Nombre de la base de datos
    $dbTabla = "tabla";                   // Nombre de la tabla
}

function conectaDb()
{
    global $dbMotor, $dbDb;

    try {
        if ($dbMotor == MYSQL) {
            $db = new PDO(MYSQL_HOST, MYSQL_USUARIO, MYSQL_PASSWORD);
            $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        } elseif ($dbMotor == SQLITE) {
            $db = new PDO("sqlite:" . $dbDb);
        }
        return($db);
    } catch (PDOException $e) {
        cabecera("Error grave", MENU_PRINCIPAL, CABECERA_SIN_CURSOR);
        print "    <p>Error: No puede conectarse con la base de datos.</p>\n";
        print "\n";
        print "    <p>Error: " . $e->getMessage() . "</p>\n";
        print "\n";
        pie();
        exit();
    }
}

function recorta($campo, $cadena)
{
    global $recorta;

    $tmp = isset($recorta[$campo]) ? substr($cadena, 0, $recorta[$campo]) : $cadena;
    return $tmp;
}

function recogeParaConsulta($db, $var, $var2="")
{
    $tmp = (isset($_REQUEST[$var]) && ($_REQUEST[$var] != "")) ?
        strip_tags(trim(htmlspecialchars($_REQUEST[$var]))) : strip_tags(trim(htmlspecialchars($var2)));
    if (get_magic_quotes_gpc()) {
        $tmp = stripslashes($tmp);
    }
    $tmp = recorta($var, $tmp);
    if (!is_numeric($tmp)) {
        $tmp = $db->quote($tmp);
    }
    return $tmp;
}

function recogeMatrizParaConsulta($db, $var)
{
    $tmpMatriz = [];
    if (isset($_REQUEST[$var]) && is_array($_REQUEST[$var])) {
        foreach ($_REQUEST[$var] as $indice => $valor) {
            $tmp = strip_tags(trim(htmlspecialchars($indice)));
            if (get_magic_quotes_gpc()) {
                $tmp = stripslashes($tmp);
            }
            $tmp = recorta($var, $tmp);
            if (!is_numeric($tmp)) {
                $tmp = $db->quote($tmp);
            }
            $indiceLimpio = $tmp;

            $tmp = strip_tags(trim(htmlspecialchars($valor)));
            if (get_magic_quotes_gpc()) {
                $tmp = stripslashes($tmp);
            }
            $tmp = recorta($var, $tmp);
            if (!is_numeric($tmp)) {
                $tmp = $db->quote($tmp);
            }
            $valorLimpio  = $tmp;

            $tmpMatriz[$indiceLimpio] = $valorLimpio;
        }
    }
    return $tmpMatriz;
}

function quitaComillasExteriores($var)
{
    if (is_string($var)) {
        if (isset($var[0]) && ($var[0] == "'")) {
            $var = substr($var, 1, strlen($var)-1);
        }
        if (isset($var[strlen($var)-1]) && ($var[strlen($var)-1] == "'")) {
            $var = substr($var, 0, strlen($var)-1);
        }
    }
    return $var;
}

function sqlite_query_multi($db, $query) {
    $pattern = "/^(.*;)(.*;)/s";
    if (preg_match($pattern, $query, $match)) {
     // multi-statement query
        $result = sqlite_query($db, $match[1]);
        sqlite_exec($db, $match[2]);
    } else {
     // single-statement query
        $result = sqlite_query($db,$query);
    }
    return (@$result);
}

function cabecera($texto, $menu, $conCursor)
{
    print "<!DOCTYPE html>\n";
    print "<html lang=\"es\">\n";
    print "<head>\n";
    print "  <meta charset=\"utf-8\" />\n";
    print "  <title>Inyección SQL 1. $texto.\n";
    print "    Ejercicios. Programación web en PHP. Bartolomé Sintes Marco</title>\n";
    print "  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />\n";
    print "  <link href=\"mclibre-php-soluciones-proyectos.css\" rel=\"stylesheet\" type=\"text/css\" title=\"Color\" />\n";
    print "</head>\n";
    print "\n";

    if ($conCursor) {
        print "<body onload=\"document.getElementById('cursor').focus()\">\n";
    } else {
        print "<body>\n";
    }
    print "  <h1>Inyección SQL 1 - $texto</h1>\n";
    print "\n";
    print "  <div id=\"menu\">\n";
    print "    <ul>\n";
    if ($menu == MENU_PRINCIPAL) {
        print "      <li><a href=\"entrar-1.php\">Entrar en el sistema</a></li>\n";
        print "      <li><a href=\"insertar-1.php\">Añadir usuarios</a></li>\n";
        print "      <li><a href=\"borrar-todo-1.php\">Borrar todo</a></li>\n";
    } elseif ($menu == MENU_VOLVER) {
        print "      <li><a href=\"index.php\">Página inicial</a></li>\n";
    } else {
        print "      <li>Error en la selección de menú</li>\n";
    }
    print "    </ul>\n";
    print "  </div>\n";
    print "\n";
    print "  <div id=\"contenido\">\n";
}

function pie()
{
    print "  </div>\n";
    print "\n";

    print "  <footer>\n";
    print "    <p class=\"ultmod\">\n";
    print "      Última modificación de esta página:\n";
    print "      <time datetime=\"2011-05-18\">18 de mayo de 2011</time>\n";
    print "    </p>\n";
    print "\n";
    print "    <p class=\"licencia\">\n";
    print "      Este programa forma parte del curso <a href=\"http://www.mclibre.org/consultar/php/\">\n";
    print "      Programación web en PHP</a> por <a href=\"http://www.mclibre.org/\">Bartolomé\n";
    print "      Sintes Marco</a>.<br />\n";
    print "      El programa PHP que genera esta página está bajo\n";
    print "      <a rel=\"license\" href=\"http://www.gnu.org/licenses/agpl.txt\">licencia AGPL 3 o posterior</a>.</p>\n";
    print "  </footer>\n";
    print "</body>\n";
    print "</html>\n";
}