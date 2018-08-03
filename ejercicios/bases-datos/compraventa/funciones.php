<?php
/**
 * Compraventa - funciones.php
 *
 * @author    Bartolomé Sintes Marco <bartolome.sintes+mclibre@gmail.com>
 * @copyright 2008 Bartolomé Sintes Marco
 * @license   http://www.gnu.org/licenses/agpl.txt AGPL 3 or later
 * @version   2008-02-27
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

define ('MYSQL', 'MySQL');
define ('SQLITE', 'SQLite');
$dbMotor = SQLITE;                         // Base de datos empleada
if ($dbMotor==MYSQL) {
    define('MYSQL_HOST', 'mysql:host=localhost'); // Nombre de host MYSQL
    define('MYSQL_USUARIO', 'root');       // Nombre de usuario de MySQL
    define('MYSQL_PASSWORD', '');          // Contraseña de usuario de MySQL
    $dbDb        = 'mclibre_compraventa';  // Nombre de la base de datos
    $dbUsuarios  = $dbDb.'.usuarios';      // Nombre de la tabla de Usuarios
    $dbArticulos = $dbDb.'.articulos';     // Nombre de la tabla de Artículos
    $consultaExisteTabla = "SELECT COUNT(*) as existe_db
        FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME='$dbDb'";
} elseif ($dbMotor==SQLITE) {
    $dbDb        = '/home/barto/mclibre/tmp/mclibre/mclibre_compraventa.sqlite3';  // Nombre de la base de datos
    $dbUsuarios  = 'usuarios';             // Nombre de la tabla de Usuarios
    $dbArticulos = 'articulos';            // Nombre de la tabla de Agendas
    $consultaExisteTabla = "SELECT COUNT(*) as existe_db
        FROM sqlite_master WHERE type='table' AND name='$dbUsuarios'";
}

$administradorNombre   = 'root';  // Nombre del usuario Administrador
$administradorPassword = 'root';  // Password del usuario Administrador
// Si $administradorPassword != '', al crearse las tablas, se crea el usuario
// Si $administradorPassword = '', no se crea el usuario
// Lo he hecho para que en el ejemplo colgado en la web la gente pueda entrar
// como Administrador
$tamUsuario      = 20;  // Tamaño del campo Usuario
$tamPassword     = 20;  // Tamaño del campo Contraseña
$tamCifrado      = 32;  // Tamaño del campo contraseña en MD5
$tamArticulo     = 40;  // Tamaño del campo Artículo
$tamPrecio       = 10;  // Tamaño del campo precio
$tamIdComprador  = 10;  // Tamaño del campo id Comprador
$tamIdVendedor   = 10;  // Tamaño del campo id Vendedor
$tamFechaCompra  = 10;
$maxRegUsuarios  = 20;  // Número máximo de registros en la tabla Usuarios
$maxRegArticulos = 20;  // Número máximo de registros por usuario en la tabla Artículos
$recorta = [
    'usuario'     => $tamUsuario,
    'password'    => $tamCifrado,
    'articulo'    => $tamArticulo,
    'precio'      => $tamPrecio,
    'idComprador' => $tamIdComprador,
    'idVendedor'  => $tamIdVendedor,
    'fechaCompra' => $tamFechaCompra
];

function conectaDb()
{
    global $dbMotor, $dbDb;

    try {
        if ($dbMotor==MYSQL) {
            $db = new PDO(MYSQL_HOST, MYSQL_USUARIO, MYSQL_PASSWORD);
            $db->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);
        } elseif ($dbMotor==SQLITE) {
            $db = new PDO('sqlite:'.$dbDb);
        }
        return($db);
    } catch (PDOException $e) {
        cabecera('Error grave');
        print "<p>Error: No puede conectarse con la base de datos.</p>\n";
//        print "<p>Error: " . $e->getMessage() . "</p>\n";
        pie();
        exit();
    }
}

function borraTodoMySQL($db)
{
    global $dbDb, $dbArticulos, $dbUsuarios, $tamUsuario, $tamCifrado, $tamArticulo,
        $tamPrecio, $tamIdComprador, $tamIdVendedor, $tamFechaCompra,
        $administradorNombre, $administradorPassword;

    $consulta = "DROP DATABASE $dbDb";
    if ($db->query($consulta)) {
        print "<p>Base de datos borrada correctamente.</p>\n";
    } else {
        print "<p>Error al borrar la base de datos.</p>\n";
    }
    $consulta = "CREATE DATABASE $dbDb";
    if ($db->query($consulta)) {
        print "<p>Base de datos creada correctamente.</p>\n";
        $consulta_creatabla_usuarios = "CREATE TABLE $dbUsuarios (
            id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
            usuario VARCHAR($tamUsuario),
            password VARCHAR($tamCifrado),
            PRIMARY KEY(id) )";
        if ($db->query($consulta_creatabla_usuarios)) {
            print "<p>Tabla de Usuarios creada correctamente.</p>\n";
        } else {
            print "<p>Error al crear la tabla de Usuarios.</p>\n";
        }
        if ($administradorPassword!='') {
            $consulta = "INSERT INTO $dbUsuarios
                VALUES (NULL, '$administradorNombre', '"
                .md5($administradorPassword)."')";
            if ($db->query($consulta)) {
                print "<p>Registro de Usuario Administrador creado correctamente.</p>\n";
            } else {
                print "<p>Error al crear el registro de Usuario Administrador.</p>\n";
            }
        }
        $consulta_creatabla_articulos = "CREATE TABLE $dbArticulos (
            id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
            articulo VARCHAR($tamArticulo),
            precio FLOAT,
            id_vendedor INTEGER,
            id_comprador INTEGER,
            reservado BOOLEAN,
            fecha_reserva DATETIME,
            comprado BOOLEAN,
            fecha_compra DATE,
            PRIMARY KEY(id) )";
        if ($db->query($consulta_creatabla_articulos)) {
            print "<p>Tabla de Artículos creada correctamente.</p>\n";
        } else {
            print "<p>Error al crear la tabla de Artículos.</p>\n";
        }
    } else {
        print "<p>Error al crear la base de datos.</p>\n";
    }
}

function borraTodoSqlite($db)
{
    global $dbArticulos, $dbUsuarios, $tamUsuario, $tamCifrado,$tamArticulo,
        $tamPrecio, $tamIdComprador, $tamIdVendedor, $tamFechaCompra,
        $administradorNombre, $administradorPassword;

    $consulta = "DROP TABLE $dbUsuarios";
    if ($db->query($consulta)) {
       print "<p>Tabla de Usuarios borrada correctamente.</p>\n";
    } else {
        print "<p>Error al borrar la tabla de Usuarios.</p>\n";
    }
    $consulta = "DROP TABLE $dbArticulos";
    if ($db->query($consulta)) {
       print "<p>Tabla de Articulos borrada correctamente.</p>\n";
    } else {
        print "<p>Error al borrar la tabla de Articulos.</p>\n";
    }
    $consulta_creatabla_usuarios = "CREATE TABLE $dbUsuarios (
        id INTEGER PRIMARY KEY,
        usuario VARCHAR($tamUsuario),
        password VARCHAR($tamCifrado)
        )";
    if ($db->query($consulta_creatabla_usuarios)) {
        print "<p>Tabla de Usuarios creada correctamente.</p>\n";
    } else {
        print "<p>Error al crear la tabla de Usuarios.</p>\n";
    }
    if ($administradorPassword!='') {
        $consulta = "INSERT INTO $dbUsuarios
            VALUES (NULL, '$administradorNombre', '".md5($administradorPassword)."')";
        if ($db->query($consulta)) {
            print "<p>Registro de Usuario Administrador creado correctamente.</p>\n";
        } else {
            print "<p>Error al crear el registro de Usuario Administrador.</p>\n";
        }
    }
    $consulta_creatabla_articulos = "CREATE TABLE $dbArticulos (
        id INTEGER PRIMARY KEY,
        articulo VARCHAR($tamArticulo),
        precio FLOAT,
        id_vendedor INTEGER,
        id_comprador INTEGER,
        reservado BOOLEAN,
        fecha_reserva DATETIME,
        comprado BOOLEAN,
        fecha_compra DATE
        )";
    if ($db->query($consulta_creatabla_articulos)) {
       print "<p>Tabla de Artículos creada correctamente.</p>\n";
    } else {
        print "<p>Error al crear la tabla de Artículos.</p>\n";
    }
}

function recorta($campo, $cadena)
{
    global $recorta;

    $tmp = isset($recorta[$campo]) ? substr($cadena, 0, $recorta[$campo]) : $cadena;
    return $tmp;
}

function recogeParaConsulta($db, $var, $var2='')
{
    $tmp = (isset($_REQUEST[$var])&&($_REQUEST[$var]!='')) ?
        trim(strip_tags($_REQUEST[$var])) : trim(strip_tags($var2));
    if (get_magic_quotes_gpc()) {
        $tmp = stripslashes($tmp);
    }
    $tmp = str_replace('&', '&amp;',  $tmp);
    $tmp = str_replace('"', '&quot;', $tmp);
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
            $tmp = trim(strip_tags($indice));
            if (get_magic_quotes_gpc()) {
                $tmp = stripslashes($tmp);
            }
            $tmp = str_replace('&', '&amp;',  $tmp);
            $tmp = str_replace('"', '&quot;', $tmp);
            $tmp = recorta($var, $tmp);
            if (!is_numeric($tmp)) {
                $tmp = $db->quote($tmp);
            }
            $indiceLimpio = $tmp;

            $tmp = trim(strip_tags($valor));
            if (get_magic_quotes_gpc()) {
                $tmp = stripslashes($tmp);
            }
            $tmp = str_replace('&', '&amp;',  $tmp);
            $tmp = str_replace('"', '&quot;', $tmp);
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
        if (isset($var[0])&&($var[0]=="'")) {
            $var = substr($var, 1, strlen($var)-1);
        }
        if (isset($var[strlen($var)-1])&&($var[strlen($var)-1]=="'")) {
            $var = substr($var, 0, strlen($var)-1);
        }
    }
    return $var;
}

function cabecera($texto, $menu='menu_principal')
{
    global $administradorNombre;

    print "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?".">
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"
       \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />
  <title>www.mclibre.org - Compraventa - $texto</title>
  <link href=\"mclibre-php-soluciones-proyectos.css\" rel=\"stylesheet\" type=\"text/css\" />
</head>

<body onload=\"document.getElementById('cursor').focus()\">
<h1>Compraventa - $texto</h1>
<div id=\"menu\">
<ul>\n";
    if ($menu=='menu_principal') {
        print "  <li><a href=\"index.php\">Conectar</a></li>
    <li><a href=\"listar.php\">Ver artículos</a></li>";
    } elseif ($menu==$administradorNombre) {
        print "    <li><a href=\"adm-borrar-todo-1.php\">Borrar todo</a></li>
    <li><a href=\"salir.php\">Desconectar</a></li>";
    } elseif ($menu=='compra') {
        print "    <li><a href=\"index.php\">Inicio</a></li>
    <li><a href=\"listar.php?compraventa=compra\">Artículos en venta</a></li>
    <li><a href=\"com_reservar1.php\">Reservar</a></li>
    <li><a href=\"com_anularreserva1.php\">Anular</a></li>
    <li><a href=\"com_comprar1.php\">Comprar</a></li>";
    } elseif ($menu=='venta') {
        print "    <li><a href=\"index.php\">Inicio</a></li>
    <li><a href=\"ven_anyadir1.php\">Añadir</a></li>
    <li><a href=\"listar.php?compraventa=venta\">Ver</a></li>
    <li><a href=\"ven_modificar1.php\">Modificar</a></li>
    <li><a href=\"ven_borrar1.php\">Borrar</a></li>";
    } else {
        print "    <li><a href=\"com_index.php\">Comprar</a></li>
    <li><a href=\"ven_index.php\">Vender</a></li>
    <li><a href=\"es_compras.php\">Compras realizadas</a></li>
    <li><a href=\"es_ventas.php\">Ventas realizadas</a></li>
    <li><a href=\"salir.php\">Desconectar</a></li>";
    }
    print "</ul>\n</div>\n\n<div id=\"contenido\">\n";
}

function pie()
{
    global $administradorPassword, $_SESSION;

    if (($administradorPassword!='')&&!isset($_SESSION['compraventaUsuario'])) {
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
  Última modificación de este programa: 27 de febrero de 2008
</address>
<p class="licencia">El programa PHP que genera esta página está bajo
<a rel="license" href="http://www.gnu.org/licenses/agpl.txt">licencia AGPL 3 o
posterior</a>.</p>
</div>
</body>
</html>';
}
