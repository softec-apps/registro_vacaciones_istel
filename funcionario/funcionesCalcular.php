<?php
include_once  "../conexion.php";
include_once  "../funciones.php";
function seleccionar($pdo){
    try {
        $consulta = "SELECT limiteVacaciones, diasPorAño, diasAnuales FROM configuracion";
        $stmt = $pdo->prepare($consulta);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        // Asignar los valores a las variables
        $limiteVacaciones = $resultado["limiteVacaciones"];
        $diasPorAnoTrabajado = $resultado["diasPorAño"];
        $diasPorAno = $resultado["diasAnuales"];

        // Puedes devolver las variables si es necesario
        return [
            'limiteVacaciones' => $limiteVacaciones,
            'diasPorAnoTrabajado' => $diasPorAnoTrabajado,
            'diasPorAno' => $diasPorAno
        ];
    } catch (PDOException $e) {
        create_flash_message(
            'Ocurrio un error con el sistema',
            'error'
        );
        redirect(RUTA_ABSOLUTA . "logout");
    }

}

$seleccionar = seleccionar($pdo);
// Puedes acceder a las variables individualmente
$limiteVacaciones = $seleccionar['limiteVacaciones'];
$diasPorAnoTrabajado = $seleccionar['diasPorAnoTrabajado'];
$diasPorAno = $seleccionar['diasPorAno'];

function calcularDiasVacaciones($diasTrabajados, $horasDePermiso, $limiteVacaciones, $diasPorAnoTrabajado, $diasPorAno,$tiempo_trabajo) {
    $anosTrabajados = obtenerAnosTrabajados($diasTrabajados, $diasPorAno);
    $diasVacaciones = $anosTrabajados * $diasPorAnoTrabajado;

    // Descuenta las horas de permiso del total de días de vacaciones
    $diasVacaciones -= $horasDePermiso / $tiempo_trabajo; // 8 horas = 1 día

    // Limita los días de vacaciones al límite establecido
    // $diasVacaciones = min($diasVacaciones, $limiteVacaciones);

    return $diasVacaciones;
}
function obtenerAnosTrabajados($diasTrabajados, $diasPorAno) {
    $anosTrabajados = floor($diasTrabajados / $diasPorAno);
    return $anosTrabajados;
}

function obtenerDiasTrabajadosParaUsuario($pdo, $id_usuario) {
    try {
        $query = "SELECT id_usuarios, cedula, nombres, apellidos FROM usuarios WHERE id_usuarios = :id AND rol = 'Funcionario'";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $statement->execute();

        $usuario = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            return null; // Manejar la situación en la que no se encuentra el usuario
        }

        $diasTrabajados = consulta_unica($pdo, $id_usuario);
        $horasDePermiso = horas_ocupadas($pdo, $id_usuario);
        $fechaIngreso = obtenerFechaIngreso($pdo, $id_usuario);

        return [
            'id_usuario' => $id_usuario,
            'cedula' => $usuario['cedula'],
            'nombre' => $usuario['nombres'],
            'apellido' => $usuario['apellidos'],
            'dias_trabajados' => $diasTrabajados,
            'horas_permiso' => $horasDePermiso,
            'fecha_ingreso' => $fechaIngreso,
        ];

    } catch (PDOException $e) {
        create_flash_message(
            'Ocurrio un error con el sistema',
            'error'
        );
        redirect(RUTA_ABSOLUTA . "logout");
        // echo "Error de exepcion" .$e->getMessage();
    }
}

function obtenerFechaIngreso($pdo, $id_usuario) {
    try {
        $query = "SELECT fecha_ingreso FROM usuarios WHERE id_usuarios = :id";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':id', $id_usuario, PDO::PARAM_INT);
        $statement->execute();

        $resultado = $statement->fetch(PDO::FETCH_ASSOC);
        return $resultado['fecha_ingreso'];

    } catch (PDOException $e) {
        create_flash_message(
            'Ocurrio un error con el sistema',
            'error'
        );
        redirect(RUTA_ABSOLUTA . "logout");
        // echo "Error de exepcion" .$e->getMessage();
    }
}

function consulta_unica($pdo, $id_usuario_insertado) {
    try {
        // Obtener la fecha de ingreso del usuario
        $query = "SELECT fecha_ingreso FROM usuarios WHERE id_usuarios = :id";
        $statement = $pdo->prepare($query);
        $statement->bindParam(':id', $id_usuario_insertado, PDO::PARAM_INT);
        $statement->execute();

        $resultado = $statement->fetch(PDO::FETCH_ASSOC);
        $fecha_ingreso_usuario = $resultado['fecha_ingreso'];

        // Crear objetos DateTime para las fechas
        $fecha_actual_obj = new DateTime(date('Y-m-d'));

        $fecha_ingreso_obj = new DateTime($fecha_ingreso_usuario);

        // Calcular los días y las horas trabajadas
        $intervalo = $fecha_ingreso_obj->diff($fecha_actual_obj);
        $diasTrabajados = $intervalo->days;

        return $diasTrabajados;

    } catch (PDOException $e) {
        create_flash_message(
            'Ocurrio un error con el sistema',
            'error'
        );
        redirect(RUTA_ABSOLUTA . "logout");
        // echo "Error de exepcion" .$e->getMessage();
    }
}

function horas_ocupadas($pdo, $id_usuario_insertado) {
    try {
        $consulta_select = "SELECT SUM(horas_ocupadas) AS total_horas_ocupadas FROM registros_permisos WHERE id_usuarios = :id_trb AND (permiso_aceptado = 1 OR permiso_aceptado = 3)";

        $stmt = $pdo->prepare($consulta_select);
        $stmt->bindParam(':id_trb', $id_usuario_insertado, PDO::PARAM_STR);
        $stmt->execute();

        $result_select = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result_select && isset($result_select['total_horas_ocupadas'])) {
            return $result_select['total_horas_ocupadas'];
        } else {
            // Manejar la situación en la que no se obtuvieron resultados o no hay 'total_horas_ocupadas'
            return 0; // O cualquier valor predeterminado que desees usar
        }

    } catch (PDOException $e) {
        create_flash_message(
            'Ocurrio un error con el sistema',
            'error'
        );
        redirect(RUTA_ABSOLUTA . "logout");
        // echo "Error de exepcion" .$e->getMessage();
    }
}
// Ejemplo de uso para un solo usuario
function obtenerMensajeDiasVacaciones($id_usuario,$nombre_usuario,$apellidos_u,$limiteVacaciones,$diasPorAnoTrabajado,$diasPorAno,$pdo,$tiempo_trabajo) {
    $resultadoUsuario = obtenerDiasTrabajadosParaUsuario($pdo, $id_usuario);

    if ($resultadoUsuario) {
        $diasTrabajados = $resultadoUsuario['dias_trabajados'];
        $horasDePermisoSolicitadas = $resultadoUsuario['horas_permiso'];

        $diasDeVacaciones = calcularDiasVacaciones(
            $diasTrabajados,
            $horasDePermisoSolicitadas,
            $limiteVacaciones,
            $diasPorAnoTrabajado,
            $diasPorAno,
            $tiempo_trabajo
        );

        $diasDeVacaciones = number_format($diasDeVacaciones,2);

        $diasDePermisoSolicitados = $horasDePermisoSolicitadas / $tiempo_trabajo;
        return "El usuario  $nombre_usuario   $apellidos_u tiene $diasDeVacaciones días de vacaciones y se han ocupado en total $diasDePermisoSolicitados días de permiso.";

    } else {
        create_flash_message(
            'Error: El usuario no fue encontrado.',
            'error'
        );
    }
}



function diasSelect($id,$pdo){
    try {
        $consulta = "SELECT usuarios.id_usuarios,registros_permisos.id_permisos,usuarios.cedula,usuarios.nombres,usuarios.apellidos, registros_permisos.horas_ocupadas,registros_permisos.permiso_aceptado,registros_permisos.dias_solicitados,registros_permisos.horas_solicitadas,usuarios.tiempo_trabajo,registros_permisos.motivo_permiso,registros_permisos.ruta_solicita,registros_permisos.ruta_aprueba,registros_permisos.ruta_registra FROM usuarios,registros_permisos WHERE usuarios.id_usuarios = registros_permisos.id_usuarios AND registros_permisos.id_usuarios = :id_usuario AND COALESCE(registros_permisos.ruta_solicita, '') != '' ";
        $stmt = $pdo->prepare($consulta);

        $stmt->bindParam(':id_usuario',$id,PDO::PARAM_STR);
        $stmt->execute();
        $res_vista_permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $res_vista_permisos;

    } catch (PDOException $e) {
        create_flash_message(
            'Ocurrio un error con el sistema',
            'error'
        );
        redirect(RUTA_ABSOLUTA . "logout");
    }
}

$nuevosDatos = diasSelect($id, $pdo);

function subirArchivos($id,$pdo){
    try {
        $consulta = "SELECT usuarios.id_usuarios,registros_permisos.id_permisos,usuarios.cedula,usuarios.nombres,usuarios.apellidos, registros_permisos.horas_ocupadas,registros_permisos.permiso_aceptado,registros_permisos.dias_solicitados,registros_permisos.horas_solicitadas,usuarios.tiempo_trabajo,registros_permisos.motivo_permiso,registros_permisos.ruta_solicita,registros_permisos.ruta_aprueba,registros_permisos.ruta_registra FROM usuarios,registros_permisos WHERE usuarios.id_usuarios = registros_permisos.id_usuarios AND registros_permisos.id_usuarios = :id_usuario AND COALESCE(registros_permisos.ruta_solicita, '') = '' ";
        $stmt = $pdo->prepare($consulta);

        $stmt->bindParam(':id_usuario',$id,PDO::PARAM_STR);
        $stmt->execute();
        $res_vista_permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $res_vista_permisos;

    } catch (PDOException $e) {
        create_flash_message(
            'Ocurrio un error con el sistema',
            'error'
        );
        redirect(RUTA_ABSOLUTA . "logout");
    }
}

function datosdeArchivos($pdo,$id){
    try {
        $con = "SELECT usuarios.id_usuarios,usuarios.nombres,usuarios.apellidos,usuarios.cedula,registros_permisos.id_permisos,archivos.id_archivo,archivos.ruta_solicita,archivos.ruta_aprueba,archivos.ruta_registra FROM usuarios JOIN registros_permisos ON usuarios.id_usuarios = registros_permisos.id_usuarios JOIN archivos ON registros_permisos.id_permisos = archivos.id_permiso WHERE usuarios.id_usuarios = :id_usuarios AND COALESCE(registros_permisos.ruta_solicita, '') != '' AND COALESCE(registros_permisos.ruta_aprueba, '') != ''";
        $stmt = $pdo->prepare($con);

        $stmt->bindParam(':id_usuarios',$id,PDO::PARAM_INT);
        $stmt->execute();
        $res_vista = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Cerrar la conexión
        $pdo = null;
        return $res_vista;

    } catch (PDOException $e) {
        return "Error de excepción: " . $e->getMessage();
    }
}


function archivosAprobados($pdo,$id){
    try {
        $con = "SELECT registros_permisos.id_permisos,archivos.id_archivo,archivos.ruta_aprueba,registros_permisos.motivo_permiso FROM registros_permisos, archivos,usuarios WHERE usuarios.id_usuarios = registros_permisos.id_usuarios AND usuarios.id_usuarios = :id_usuarios AND registros_permisos.id_permisos  = archivos.id_permiso AND COALESCE(registros_permisos.ruta_aprueba, '') != '' ";
        $stmt = $pdo->prepare($con);
        $stmt->bindParam(':id_usuarios',$id,PDO::PARAM_INT);

        $stmt->execute();
        $res_vista = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pdo = null;
        return $res_vista;

    } catch (PDOException $e) {
        return "Error de excepción: " . $e->getMessage();
    }
}

function archivosRegistrados($pdo,$id){
    try {
        $con = "SELECT registros_permisos.id_permisos,archivos.id_archivo,archivos.ruta_registra,registros_permisos.motivo_permiso FROM registros_permisos, archivos,usuarios WHERE usuarios.id_usuarios = registros_permisos.id_usuarios AND usuarios.id_usuarios = :id_usuarios AND registros_permisos.id_permisos  = archivos.id_permiso AND COALESCE(registros_permisos.ruta_registra, '') != '' ";
        $stmt = $pdo->prepare($con);

        $stmt->bindParam(':id_usuarios',$id,PDO::PARAM_INT);

        $stmt->execute();
        $res_vista = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pdo = null;
        return $res_vista;

    } catch (PDOException $e) {
        return "Error de excepción: " . $e->getMessage();
    }
}



function datosdeArchivosDelUsuario($pdo,$id){
    try {
        $con = "SELECT registros_permisos.id_permisos,archivos.id_archivo,archivos.ruta_solicita,archivos.descripcion_solicita FROM registros_permisos, archivos,usuarios WHERE usuarios.id_usuarios = registros_permisos.id_usuarios AND usuarios.id_usuarios = :id_usuarios AND registros_permisos.id_permisos  = archivos.id_permiso AND COALESCE(registros_permisos.ruta_solicita, '') != '' ";
        $stmt = $pdo->prepare($con);

        $stmt->bindParam(':id_usuarios',$id,PDO::PARAM_INT);

        $stmt->execute();
        $res_vista = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pdo = null;
        return $res_vista;

    } catch (PDOException $e) {
        return "Error de excepción: " . $e->getMessage();
    }
}

function diasSelect2($id,$pdo){
    try {
        $consulta = "SELECT usuarios.id_usuarios,registros_permisos.id_permisos,usuarios.cedula,usuarios.nombres,usuarios.apellidos, registros_permisos.horas_ocupadas,registros_permisos.permiso_aceptado,registros_permisos.dias_solicitados,registros_permisos.horas_solicitadas,usuarios.tiempo_trabajo,registros_permisos.motivo_rechazo, registros_permisos.motivo_permiso FROM usuarios,registros_permisos WHERE usuarios.id_usuarios = registros_permisos.id_usuarios AND registros_permisos.id_usuarios = :id_usuario AND registros_permisos.permiso_aceptado = 2";
        $stmt = $pdo->prepare($consulta);

        $stmt->bindParam(':id_usuario',$id,PDO::PARAM_STR);
        $stmt->execute();
        $res_vista_permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $res_vista_permisos;

    } catch (PDOException $e) {
        create_flash_message(
            'Ocurrio un error con el sistema',
            'error'
        );
        redirect(RUTA_ABSOLUTA . "logout");
    }
}
$nuevosDatos2 = diasSelect2($id, $pdo);
