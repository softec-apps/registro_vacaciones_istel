<?php
include_once "../redirection.php";
include_once "../flash_messages.php";
session_start();

if (!isset($_SESSION['id_usuarios'])) {
    redirect(RUTA_ABSOLUTA . 'logout');
}
$id_user = $_SESSION['id_usuarios'];
$cedulaTalentoHumano = $_SESSION['cedula'];
$nombreTalentoHumano = $_SESSION['nombres'];
$apellidoTalentoHumano = $_SESSION['apellidos'];
$rol = $_SESSION['rol'];

if ($rol != ROL_TALENTO_HUMANO) {
   redirect(RUTA_ABSOLUTA . "logout");
}

$message = '';
$type = '';
$flash_message = display_flash_message();

if (isset($flash_message)) {
    $message = $flash_message['message'];
    $type = $flash_message['type'];
}

$titulo = "Permisos Aprobados J.S";
include_once("../plantilla/header.php")
?>
<?php
include_once  "../conexion.php";
include_once  "../funciones.php";
$respuesta = permisosAprobados($pdo);
?>
<div class="container-fluid mt-5">
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Permisos aprobados por el supervisor</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive crud-table">
                <table class="table table-bordered" id="tabla_permisos">
                    <thead>
                        <tr>
                            <th>Cédula</th>
                            <th>Funcionario</th>
                            <th>Fecha emitida</th>
                            <th>Tipo permiso</th>
                            <th>Datos solicitud</th>
                            <th>Registrar Solicitud</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($respuesta)) {
                            echo "";

                        }else {
                            $fecha_actual = date('Y-m-d');
                            foreach ($respuesta as $key => $valor){
                                $id_usuarios  = $valor ["id_usuarios"];
                                $id_permiso  = $valor ["id_permisos"];
                                $nombres  = $valor ["nombres"];
                                $apellidos  = $valor ["apellidos"];
                                $cedula_user  = $valor ["cedula"];
                                $fecha_permiso = $valor ["fecha_permiso"];
                                $motivo_permiso = $valor ["motivo_permiso"];
                                $motivo_permiso = str_replace('_', ' ', $motivo_permiso);

                                $permiso_aceptado = $valor['permiso_aceptado'];
                                $ruta_aprueba = $valor ["ruta_aprueba"];
                                $rutaAprueba = verificarRuta($ruta_aprueba);

                        ?>
                        <tr>
                            <td><?=  $cedula_user ;?></td>
                            <td><?=  $nombres . " " . $apellidos ;?></td>
                            <td><?=  $fecha_permiso ;?></td>
                            <td><?=  $motivo_permiso ;?></td>
                            <td>
                                <?php
                                if (!empty($ruta_aprueba)) {
                                    echo '<a class="btn btn-primary m-1" title="Solicitud firmada por el jefe supervisor" href="' . RUTA_ABSOLUTA . $rutaAprueba . '" target="_blank"><i class="fa-solid fa-file-arrow-down"></i></a>';
                                }
                                ?>
                                <form action="../datos_individuales" method="POST" class="d-inline-block m-1">
                                    <input type="hidden" name="id_permisos" value=" <?= $id_permiso ?>">
                                    <button class="btn btn-info m-1" title="Ver los datos de esta solicitud">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </form>
                            </td>

                            <td>
                                <button class="btn btn-success m-1" data-toggle="modal" data-target="#registrar_solicitud" data-registrar="<?= $id_permiso ?>" onclick="aprobar(this)">Registrar</button>
                            </td>
                        </tr>
                    <?php
                        };
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

<!-- Modal de registrar la solicitud-->
<div class="modal fade" id="registrar_solicitud" tabindex="-1" role="dialog" aria-labelledby="modalAdminLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAdminLabel">Registrar el permiso </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="aprobarForm" action="<?php echo RUTA_ABSOLUTA ?>talentoH/procesarSolicitud" method="post"  enctype="multipart/form-data">

                    <input type="hidden" name="id_registrar" id="id_registrar" value ="" />
                    <input type="hidden" name="id_user" id="id_user" value ="<?= $id_user?>" />
                    <input class="form-control" type="hidden" name="registrar" value ="3" />
                    <div class="form-floating mb-3">
                        <div>
                            <input class="form-control" name="user" type="hidden" value="<?= $nombreTalentoHumano . " " . $apellidoTalentoHumano?>" />
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <div>
                            <label>Archivo firmado por la persona que registra este permiso</label>
                            <input class="form-control" type="file" name="archivoRegistra" id="archivoRegistra" required>
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <div>
                            <label>Descripción del archivo</label>
                            <textarea class="form-control" name="archivoDescripcion" id="archivoDescripcion" cols="30" rows="2" required></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary cerrarModal" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Registrar permiso</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(".cerrarModal").click(function(){
        $("#registrar_permisos").modal('hide')
    });
    function aprobar(button) {
        var userId = button.getAttribute('data-registrar');
        // Rellenar el campo oculto con el ID del cliente
        document.getElementById('id_registrar').value = userId;
    }
</script>
<?php include_once("../plantilla/footer.php")?>
