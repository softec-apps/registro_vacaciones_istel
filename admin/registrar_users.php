<?php
include_once "../redirection.php";
include_once "../funciones.php";
include_once "../conexion.php";
include_once "../flash_messages.php";

if ($_POST) {
    try {
        $cedula = $_POST["cedula"];
        $nombres = ucwords($_POST["nombres"]);
        $apellidos = ucwords($_POST["apellidos"]);
        $email = $_POST["email_A"];
        $usuario = $_POST["user_A"];
        $clave = $_POST["password_A"];
        $rol = $_POST["roles1"];
        $fecha_ingreso = $_POST["fecha_ingreso"];
        $tiempo_trabajo = $_POST["tiempo_trabajo"];

        $id_insertado = insertUsers($cedula, $nombres, $apellidos, $email, $usuario, $clave, $rol, $fecha_ingreso,$tiempo_trabajo,$pdo);

        if (is_numeric($id_insertado)) {

            create_flash_message(
                'Usuario Registrado ',
                'success'
            );

            redirect(RUTA_ABSOLUTA . "admin/admin");

            // echo"DATOS Insertados";
        } elseif (strpos($id_insertado, "Error de repetición de cédula") !== false) {

            create_flash_message(
                'La cédula o el nombre de usuario Ya existe en la base de datos.',
                'error'
            );

            redirect(RUTA_ABSOLUTA . "admin/admin");

            // echo "<script>alert('La cédula ya existe o el Usuario Ya existe en la base de datos.')</script>";
        }elseif (strpos($id_insertado, "Error de repetición de email") !== false) {

            create_flash_message(
                'El email ya existe en la base de datos.',
                'error'
            );

            redirect(RUTA_ABSOLUTA . "admin/admin");
            // echo "<script>alert('El email ya existe en la base de datos.')</script>";

        }else {
            throw new Exception("Error al insertar usuario Funcionario");
        }
    }catch (PDOException $pdoEx) {
        // Capturar excepciones específicas de PDO
        create_flash_message(
            'Ocurrio un error con el sistema1',
            'error'
        );
        redirect(RUTA_ABSOLUTA . "admin/admin");
        // echo "Error de PDO: " . $pdoEx->getMessage();
    }
    catch (Exception $e) {
        create_flash_message(
            'Ocurrio un error con el sistema2'  . $e->getMessage(),
            'error'
        );
        redirect(RUTA_ABSOLUTA . "admin/admin");
        // echo "Error general: " . $e->getMessage();
    }
}
?>
