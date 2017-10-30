<?php
/**
 * Citas - cit-etiquetas-3.php
 *
 * @author    Bartolom� Sintes Marco <bartolome.sintes+mclibre@gmail.com>
 * @copyright 2008 Bartolom� Sintes Marco
 * @license   http://www.gnu.org/licenses/agpl.txt AGPL 3 or later
 * @version   2008-06-06
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

session_start();
if (!isset($_SESSION['citasUsuario'])) {
    header('Location:index.php');
    exit();
} else {
    include('funciones.php');
    $db = conectaDb();
    cabecera('Citas - A�adir 3', 'menu_citas');

    $cita = recogeParaConsulta($db, 'cita');
    $etiqueta = recogeParaConsulta($db, 'etiqueta');

// Habr�a que comprobar que la cita recibido existe

    if (($cita=="''") || ($etiqueta=='')) {
        print "<p>La cita y la etiqueta no pueden estar vac�os. "
            ."No se ha guardado el registro.</p>\n";
    } else {
        $consulta = "SELECT COUNT(*) FROM $dbCitas
            WHERE id=$cita";
        $result = $db->query($consulta);
        if (!$result) {
            print "<p>Error en la consulta.</p>\n";
        } elseif ($result->fetchColumn()==0) {
            print "<p>Registro de Cita no encontrado.</p>\n";
        } else {
            $consulta = "SELECT COUNT(*) FROM $dbEtiquetas
                WHERE id=$etiqueta";
            $result = $db->query($consulta);
            if (!$result) {
                print "<p>Error en la consulta.</p>\n";
            } elseif ($result->fetchColumn()==0) {
                print "<p>Registro de Etiqueta no encontrado.</p>\n";
            } else {
                $consulta = "SELECT COUNT(*) FROM $dbEtiCitas
                    WHERE id_cita=$cita
                    AND id_etiqueta=$etiqueta";
                $result = $db->query($consulta);
                if (!$result) {
                    print "<p>Error en la consulta.</p>\n";
                } elseif ($result->fetchColumn()==1) {
                    print "<p>El registro ya existe.</p>\n";
                } else {
                    $consulta = "INSERT INTO $dbEtiCitas
                        VALUES (NULL, $cita, $etiqueta)";
                    if ($db->query($consulta)) {
                        print "<p>Registro creado correctamente.</p>\n";
                    } else {
                        print "<p>Error al crear el registro.<p>\n";
                    }
                }
            }
        }
    }
    $db = NULL;
    pie();
}
?>