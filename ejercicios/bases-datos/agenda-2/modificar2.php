<?php
/**
 * Multiagenda -  modificar2.php
 *
 * @author    Bartolom� Sintes Marco <bartolome.sintes+mclibre@gmail.com>
 * @copyright 2009 Bartolom� Sintes Marco
 * @license   http://www.gnu.org/licenses/agpl.txt AGPL 3 or later
 * @version   2009-05-21
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

include('funciones.php');
session_start();

if (!isset($_SESSION['multiagendaUsuario'])) {
    header('Location:index.php');
    exit();
} else {
    $db = conectaDb();
    $id = recogeParaConsulta($db, 'id');

    if ($id=="''") {
        cabecera('Modificar 2', CABECERA_SIN_CURSOR, $_SESSION['multiagendaUsuario']);
        print "<p>No se ha seleccionado ning�n registro.</p>\n";
    } else {
        $consulta = "SELECT COUNT(*) FROM $dbAgenda
            WHERE id=$id
            AND id_usuario='$_SESSION[multiagendaIdUsuario]'";
        $result = $db->query($consulta);
        if (!$result) {
            cabecera('Modificar 2', CABECERA_SIN_CURSOR, $_SESSION['multiagendaUsuario']);
            print "<p>Error en la consulta.</p>\n";
        } elseif ($result->fetchColumn()==0) {
            cabecera('Modificar 2', CABECERA_SIN_CURSOR, $_SESSION['multiagendaUsuario']);
            print "<p>Registro no encontrado.</p>\n";
        } else {
            $consulta = "SELECT * FROM $dbAgenda
                WHERE id=$id
                AND id_usuario='$_SESSION[multiagendaIdUsuario]'";
            $result = $db->query($consulta);
            if (!$result) {
                cabecera('Modificar 2', CABECERA_SIN_CURSOR, $_SESSION['multiagendaUsuario']);
                print "<p>Error en la consulta.</p>\n";
            } else {
                cabecera('Modificar 2', CABECERA_CON_CURSOR, $_SESSION['multiagendaUsuario']);
                $valor = $result->fetch();
                print "<form action=\"modificar3.php\" method=\"".FORM_METHOD."\">
      <p>Modifique los campos que desee:</p>
      <table>
        <tbody>
          <tr>
            <td>Nombre:</td>
            <td><input type=\"text\" name=\"nombre\" size=\"".TAM_NOMBRE."\" "
              ."maxlength=\"".TAM_NOMBRE."\" value=\"$valor[nombre]\" id=\"cursor\" /></td>
          </tr>
          <tr>
            <td>Apellidos:</td>
            <td><input type=\"text\" name=\"apellidos\" size=\"".TAM_APELLIDOS."\" "
              ."maxlength=\"".TAM_APELLIDOS."\" value=\"$valor[apellidos]\" /></td>
          </tr>
          <tr>
            <td>Tel�fono:</td>
            <td><input type=\"text\" name=\"telefono\" size=\"".TAM_TELEFONO."\" "
              ."maxlength=\"".TAM_TELEFONO."\" value=\"$valor[telefono]\" /></td>
          </tr>
          <tr>
            <td>Correo:</td>
            <td><input type=\"text\" name=\"correo\" size=\"".TAM_CORREO."\" "
              ."maxlength=\"".TAM_CORREO."\" value=\"$valor[correo]\" /></td>
          </tr>
        </tbody>
      </table>
      <p><input type=\"hidden\" name=\"id\" value=\"$id\" />
        <input type=\"submit\" value=\"Actualizar\" /></p>
    </form>\n";
            }
        }
    }

    $db = NULL;
    pie();
}
?>