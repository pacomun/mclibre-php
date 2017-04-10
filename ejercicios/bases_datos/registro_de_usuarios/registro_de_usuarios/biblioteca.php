<?php
/**
 * Registro usuarios - biblioteca.php
 *
 * @author    Bartolomé Sintes Marco <bartolome.sintes+mclibre@gmail.com>
 * @copyright 2013 Bartolomé Sintes Marco
 * @license   http://www.gnu.org/licenses/agpl.txt AGPL 3 or later
 * @version   2013-03-07
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

define("FORM_METHOD",         "get");  // Formularios se envían con GET
//define("FORM_METHOD",         "post"); // Formularios se envían con POST
define("MYSQL",               "MySQL");
define("SQLITE",              "SQLite");
define("MENU_PRINCIPAL",      "menuPrincipal"); // Menú principal
define("MENU_VOLVER",         "menuVolver");    // Menú Volver a inicio
define("CABECERA_CON_CURSOR", true);   // Para función cabecera()
define("CABECERA_SIN_CURSOR", false);  // Para función cabecera()
define("MAX_REG_USUARIOS",    20);     // Número máximo de registros en la tabla Usuarios
$tamUsuario        = 20;  // Tamaño del campo Usuarios > Usuario
$tamPassword       = 20;  // Tamaño del campo Usuarios > Contraseña
$tamCifrado        = 32;  // Tamaño del campo Usuarios > Contraseña en MD5
$tamMaxRegUsuarios = 20;  // Número máximo de registros en la tabla Usuarios

$dbMotor = SQLITE;                         // Base de datos empleada
if ($dbMotor == MYSQL) {
    define("MYSQL_HOST",     "mysql:host=localhost"); // Nombre de host MYSQL
    define("MYSQL_USUARIO",  "root");      // Nombre de usuario de MySQL
    define("MYSQL_PASSWORD", "");          // Contraseña de usuario de MySQL
    $dbDb       = "mclibre_registrousuarios";  // Nombre de la base de datos
    $dbUsuarios = $dbDb . ".usuarios";      // Nombre de la tabla de Usuarios
    $consultaExisteTabla = "SELECT COUNT(*) as existe_db
        FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='$dbDb'";
} elseif ($dbMotor == SQLITE) {
    $dbDb       = "/tmp/mclibre/mclibre_registrousuarios.sqlite";  // Nombre de la base de datos
    $dbUsuarios = "usuarios";             // Nombre de la tabla de Usuarios
    $consultaExisteTabla = "SELECT COUNT(*) as existe_db
        FROM sqlite_master WHERE type='table' AND name='$dbUsuarios'";
}

$administradorNombre   = "root";  // Nombre del usuario Administrador
$administradorPassword = "root";  // Password del usuario Administrador
// Si $administradorPassword != "", al crearse las tablas, se crea el usuario
// Si $administradorPassword == "", no se crea el usuario
// Lo he hecho para que en el ejemplo colgado en la web la gente pueda entrar
// como Administrador

$recorta = array(
    "usuario"   => $tamUsuario,
    "password"  => $tamCifrado);

ini_set("session.save_handler", "files"); // Por si session.save_handler = user en php.ini

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
    } catch(PDOException $e) {
        cabecera("Error grave", CABECERA_SIN_CURSOR, MENU_PRINCIPAL);
        print "<p>Error: No puede conectarse con la base de datos.</p>\n";
        print "<p>Error: " . $e->getMessage() . "</p>\n";
        pie();
        exit();
    }
}

function borraTodoMySQL($db)
{
    global $dbDb, $dbUsuarios, $administradorNombre, $administradorPassword,
        $tamUsuario, $tamCifrado;

    $consulta = "DROP DATABASE $dbDb";
    if ($db->query($consulta)) {
        print "<p>Base de datos borrada correctamente.</p>\n";
    } else {
        print "<p>Error al borrar la base de datos.</p>\n";
    }
    $consulta = "CREATE DATABASE $dbDb";
    if ($db->query($consulta)) {
        print "<p>Base de datos creada correctamente.</p>\n";
        $consultaCreaTablaUsuarios = "CREATE TABLE $dbUsuarios (
            id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
            usuario VARCHAR($tamUsuario),
            password VARCHAR($tamCifrado),
            PRIMARY KEY(id) )";
        if ($db->query($consultaCreaTablaUsuarios)) {
            print "<p>Tabla de Usuarios creada correctamente.</p>\n";
        } else {
            print "<p>Error al crear la tabla de Usuarios.</p>\n";
        }
        if ($administradorPassword!="") {
            $consulta = "INSERT INTO $dbUsuarios VALUES (NULL,
                '$administradorNombre', '" . md5($administradorPassword) . "')";
            if ($db->query($consulta)) {
                print "<p>Registro de Usuario Administrador creado correctamente.</p>\n";
            } else {
                print "<p>Error al crear el registro de Usuario Administrador.</p>\n";
            }
        }
    } else {
        print "<p>Error al crear la base de datos.</p>\n";
    }
}

function borraTodoSqlite($db)
{
    global $dbUsuarios, $administradorNombre, $administradorPassword,
        $tamUsuario, $tamCifrado;

    $consulta = "DROP TABLE $dbUsuarios";
    if ($db->query($consulta)) {
       print "<p>Tabla de Usuarios borrada correctamente.</p>\n";
    } else {
        print "<p>Error al borrar la tabla de Usuarios.</p>\n";
    }
    $consultaCreaTablaUsuarios = "CREATE TABLE $dbUsuarios (
        id INTEGER PRIMARY KEY,
        usuario VARCHAR($tamUsuario),
        password VARCHAR($tamCifrado)
        )";
    if ($db->query($consultaCreaTablaUsuarios)) {
        print "<p>Tabla de Usuarios creada correctamente.</p>\n";
    } else {
        print "<p>Error al crear la tabla de Usuarios.</p>\n";
    }
    if ($administradorPassword!="") {
        $consulta = "INSERT INTO $dbUsuarios VALUES (NULL,
                '$administradorNombre', '" . md5($administradorPassword)."')";
        if ($db->query($consulta)) {
            print "<p>Registro de Usuario Administrador creado correctamente.</p>\n";
        } else {
            print "<p>Error al crear el registro de Usuario Administrador.</p>\n";
        }
    }
}

function recorta($campo, $cadena)
{
    global $recorta;

    $tmp = isset($recorta[$campo])
        ? substr($cadena, 0, $recorta[$campo])
        : $cadena;
    return $tmp;
}

function recoge($var, $var2="")
{
    $tmp = (isset($_REQUEST[$var]) && trim(strip_tags($_REQUEST[$var])) != "")
    ? strip_tags(trim(htmlspecialchars($_REQUEST[$var], ENT_QUOTES, "UTF-8")))
    : strip_tags(trim(htmlspecialchars($var2, ENT_QUOTES, "UTF-8")));
    if (get_magic_quotes_gpc()) {
        $tmp = stripslashes($tmp);
    }
    $tmp = recorta($var, $tmp);
    return $tmp;
}

function cabecera($texto, $conCursor=CABECERA_SIN_CURSOR, $menu=MENU_PRINCIPAL)
{
    global $administradorNombre;

    print "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
       \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
  <title>www.mclibre.org - Registro de usuarios - $texto</title>
  <link href=\"mclibre_php_soluciones_proyectos.css\" rel=\"stylesheet\" type=\"text/css\" />
</head>\n\n";
    if ($conCursor) {
        print "<body onload=\"document.getElementById('cursor').focus()\">\n";
    } else {
        print "<body>\n";
    }
    print "<h1>Registro de usuarios - $texto</h1>
<div id=\"menu\">
<ul>\n";
    if ($menu == MENU_PRINCIPAL) {
        print "  <li><a href=\"index.php\">Conectar</a></li>";
    } elseif ($menu == $administradorNombre) {
        print "  <li><a href=\"borrartodo1.php\">Borrar todo</a></li>
  <li><a href=\"salir.php\">Desconectar</a></li>";
    } else {
        print "  <li><a href=\"salir.php\">Desconectar</a></li>";
    }
    print "</ul>\n</div>\n\n<div id=\"contenido\">\n";
}

function pie()
{
    global $administradorPassword, $_SESSION;

    if (($administradorPassword!="") &&!isset($_SESSION["multiagendaUsuario"])) {
        print "<p><strong>Nota</strong>: El usuario Administrador "
            ."se llama <strong>root</strong> y su contraseña es\ntambién "
            ."<strong>root</strong>.</p>\n";
    }
    print '</div>

<div id="pie">
<address>
  Este programa forma parte del curso "Páginas web con PHP" disponible en <a
  href="http://www.mclibre.org/">http://www.mclibre.org</a><br />
  Autor: Bartolomé Sintes Marco<br />
  Última modificación de este programa: 7 de marzo de 2013
</address>
<p class="licencia">El programa PHP que genera esta página está bajo
<a rel="license" href="http://www.gnu.org/licenses/agpl.txt">licencia AGPL 3 o
posterior</a>.</p>
</div>
</body>
</html>';
}
