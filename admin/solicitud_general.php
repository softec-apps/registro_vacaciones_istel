
<?php
include_once "../redirection.php";
include_once "../flash_messages.php";
session_start();

if (!isset($_SESSION['id_usuarios'])) {
    redirect(RUTA_ABSOLUTA . 'logout');
}
$cedula = $_SESSION['cedula'];
$nombre = $_SESSION['nombres'];
$rol = $_SESSION['rol'];

if (($rol == ROL_JEFE)||($rol == ROL_TALENTO_HUMANO)) {
   redirect(RUTA_ABSOLUTA . "logout");
}
$message = '';
$type = '';
$flash_message = display_flash_message();

if (isset($flash_message)) {
    $message = $flash_message['message'];
    $type = $flash_message['type'];
}

$titulo = "Solicitud";
include_once("../plantilla/header.php")
?>


<div class="container-fluid mt-5">
<?php
include_once  "../conexion.php";
include_once  "../resta_solicitud.php";
include_once  "../funciones.php";
$vista = obtenerUsuariosConPermios($pdo);

?>
    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">Vista general para ver todos los funcionarios que han solicitado un permiso</h1>

    <!-- DataTales Example -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Aqui se puede generar una solicitud para el usuario </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered crud-table" id="tabla_vacaciones_funcionarios">
                    <thead>
                        <tr>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Cedula</th>
                            <th>D.T</th>
                            <th>H.DT.A</th>
                            <th>P.A</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (empty($vista)) {
                            echo "";

                        }else {
                            foreach ($vista as $key => $valor){
                                $id_usuarios  = $valor ["id_usuario"];
                                $id_permiso  = $valor ["id_permisos"];
                                $nombres = $valor ["nombre"];
                                $cedula = $valor ["cedula"];
                                $apellidos = $valor ["apellido"];
                                $diasTrabajados = $valor ["dias_trabajados"];
                                $permiso_aceptado = $valor ["permisoUsuario"];

                                $horasDePermisoSolicitadas = $valor ["horas_permiso"];
                                $fechaIngreso = $valor ["fecha_ingreso"];
                                $tiempoTrabajo = $valor ["tiempo_trabajo"];



                                $diasDeVacaciones = calcularDiasVacaciones(
                                    $diasTrabajados,
                                    $horasDePermisoSolicitadas,
                                    $limiteVacaciones,
                                    $diasPorAnoTrabajado,
                                    $diasPorAno,
                                    $tiempoTrabajo
                                );

                                $diasDePermisoSolicitados = $horasDePermisoSolicitadas / $tiempoTrabajo;
                                $dias_totales = $diasDeVacaciones + $diasDePermisoSolicitados;
                        ?>
                        <tr>
                            <td>
                                <?= $nombres ?>
                            </td>

                            <td>
                                <?= $apellidos ?>
                            </td>

                            <td>
                            <?= $cedula ?>
                            </td>

                            <td>
                            <?= $diasTrabajados ?>
                            </td>

                            <td>
                            <?= $dias_totales ?>
                            </td>

                            <td>
                            <?php if ($permiso_aceptado == 0): ?>
                                <button class="btn btn-danger" title="No aceptado" data-toggle="modal"
                                    data-target="#aceptarSolicitud" data-id="<?= $id_permiso ?>" onclick="aceptar(this)"><i class="fa-solid fa-xmark"></i></button>

                            <?php elseif ($permiso_aceptado == 1): ?>

                                <button class="btn btn-success" title="Aceptado" data-toggle="modal"
                                    data-target="#cancelarSolicitud" data-id="<?= $id_permiso ?>" onclick="cancelar(this)"><i class="fa-solid fa-check"></i></button>

                            <?php elseif ($permiso_aceptado == 2): ?>
                                <button class="btn btn-info" title="Permiso Rechazado" ><i class="fa-solid fa-xmark"></i></button>

                            <?php elseif ($permiso_aceptado == 3): ?>
                                <button class="btn btn-primary" title="Ya registrado" ><i class="fa-solid fa-check"></i></button>
                            <?php endif; ?>
                            </td>

                            <td>
                                <form action="../datos_individuales" method="POST" class="d-inline-block m-1">
                                    <input type="hidden" name="id_permisos" value=" <?= $id_permiso ?>">
                                    <button class="btn btn-info m-1" title="Ver los datos de esta solicitud">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </form>

                                <button class="btn btn-danger m-1" title="Eliminar" data-toggle="modal"
                                    data-target="#Eliminar" data-id="<?= $id_permiso ?>" onclick="eliminar(this)">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>

                        </tr>
                        <?php
                            };
                        };
                        ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

<!-- Modal para solicitar permisos -->
<div class="modal fade" id="registrar_vacaciones" tabindex="-1" role="dialog" aria-labelledby="modalAdminLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAdminLabel">Solicitar Permiso </h5>
                <button type="button" class="close cerrarModal" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="../solicitar_permiso" method="post">
                    <input type="hidden" name="nombre_usuario" id="nombre_usuario">
                    <div class="form-floating mb-3">
                        <div>
                            <label>Seleccione la cedula del funcionario que solicita el permiso</label>
                            <select class="form-select" name="id_usuario" onchange="updateHiddenField(this)" required>
                                <option value="" disabled selected>Seleccione un usuario</option>
                                <?php
                                    $itero = cedulas($pdo);

                                    foreach ($itero as $key => $posicion):
                                    $id_iterado = $posicion ["id_usuarios"];
                                    $cedula_iterada = $posicion ["cedula"];
                                    $nombres_iterado = $posicion ["nombres"];
                                    $apellidos_iterado = $posicion ["apellidos"];
                                ?>
                                <option value="<?php echo $id_iterado;?>"><?php echo $nombres_iterado . " ".$apellidos_iterado ;?></option>

                                <?php
                                endforeach;
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <div>
                            <label>Ingrese la provincia </label>
                            <input class="form-control" type="text" name="provincia"  />
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <div>
                            <label>Ingrese el Regimen </label>
                            <input class="form-control" type="text" name="regimen"  />
                        </div>
                    </div>


                    <div class="form-floating mb-3">
                        <div>
                            <label>Ingrese la coordinacion Zonal </label>
                            <input class="form-control" type="text" name="coordinacion"  />
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <div>
                            <label>Ingrese la Dirrecion o Unidad </label>
                            <input class="form-control" type="text" name="direccion"  />
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <div>
                            <label>Ingrese la fecha inicio del permisos</label>
                            <input class="form-control" type="date" name="fecha_inicio"  />
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <div>
                            <label>Ingrese la fecha fin del permisos</label>
                            <input class="form-control" type="date" name="fecha_fin"  />
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <div>
                            <label>Ingrese la hora inicio del permisos(opcional)</label>
                            <input class="form-control" type="time" name="hora_inicio"  />
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <div>
                            <label>Ingrese la hora fin del permisos(opcional)</label>
                            <input class="form-control" type="time" name="hora_fin"  />
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <div>
                            <label>Seleccione el motivo del permisos</label>
                            <select class="form-control" name="motivo">
                                <option value="LICENCIA_POR_CALAMIDAD_DOMESTICA">Licencia por calamidad domestica</option>
                                <option value="LICENCIA_POR_ENFERMEDAD">Licencia por enfermedad</option>
                                <option value="LICENCIA_POR_MATERNIDAD">Licencia por maternidad</option>
                                <option value="LICENCIA_POR_MATRIMONIO_O_UNION_DE_ECHO">Licencia por matrimonio o union de echo</option>
                                <option value="LICENCIA_POR_PATERNIDAD">Licencia por paternidad</option>
                                <option value="PERMISO_PARA_ESTUDIOS_REGULARES">Permiso pra estudios regulares</option>
                                <option value="PERMISO_DE_DIAS_CON_CARGO_A_VACACIONES">Permisos de dias con cargo a vacaciones</option>
                                <option value="PERMISO_POR_ASUNTOS_OFICIALES">Permiso por asuntos oficales</option>
                                <option value="PERMISO_PARA_ATENCION_MEDICA">Permiso para atencion medica</option>
                                <option value="OTROS">otros</option>
                            </select>
                        </div>
                    </div>

                    <input class="form-control" type="hidden" name="permiso_aceptado" value="0" />

                    <div class="form-floating mb-3">
                        <div>
                            <label>Ingrese observaciones o justificativos del permisos(opcional)</label>
                            <input class="form-control" type="text" name="observaciones"  />
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger cerrarModal" data-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success">Solicitar Permiso</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Eliminar -->
<div class="modal fade" id="Eliminar" tabindex="-1" role="dialog" aria-labelledby="modalAdminLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAdminLabel">Confirmar eliminación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea eliminar este registro de un permiso?</p>
                <form id="eliminarForm" action="<?php echo RUTA_ABSOLUTA ?>admin/eliminar" method="post">
                    <input type="hidden" name="id_permiso" id="id_permiso" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" form="eliminarForm" class="btn btn-danger">Eliminar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal aceptar Solicitud -->
<div class="modal fade" id="aceptarSolicitud" tabindex="-1" role="dialog" aria-labelledby="modalAdminLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAdminLabel">¿Está seguro de que desea Aceptar esta solicitud ?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- <p>¿Está seguro de que desea Aceptar esta solicitud ?</p> -->
                <form id="AceptarS" action="<?php echo RUTA_ABSOLUTA ?>procesar" method="POST">
                    <input type="hidden" name="id_aprueba" id="id_aprueba" value ="" />
                    <input class="form-control" type="hidden" name="aprobar" value ="1" />
                    <div class="form-floating mb-3">
                        <div>
                            <label>Ingrese su nombre para aprobar  </label>
                            <input class="form-control" type="text" name="user" required/>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" form="AceptarS" class="btn btn-primary">Aprobar</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal para cancelar una solicitud -->
<div class="modal fade" id="cancelarSolicitud" tabindex="-1" role="dialog" aria-labelledby="modalAdminLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAdminLabel">Confirmar Cancelacion del Permiso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>¿Está seguro de que desea Cancelar esta solicitud ?</p>
                <form id="cancelarS" action="<?php echo RUTA_ABSOLUTA ?>procesar" method="POST">
                    <input type="hidden" name="id_cancelar" id="id_cancelar" value ="<?= $id_permiso ?>" />
                    <input class="form-control" type="hidden" name="cancelar" value ="0" />
                    <input class="form-control" type="hidden" name="user" value="" />
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" form="cancelarS" class="btn btn-warning">Aceptar</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(".cerrarModal").click(function(){
        $("#registrar_vacaciones").modal('hide');
    });aceptar
    function eliminar(button) {
        var userId = button.getAttribute('data-id');
        // Rellenar el campo oculto con el ID del cliente
        document.getElementById('id_permiso').value = userId;
    }
    function aceptar(button) {
        var userId = button.getAttribute('data-id');
        // Rellenar el campo oculto con el ID del cliente
        document.getElementById('id_aprueba').value = userId;
    }
    function cancelar(button) {
        var userId = button.getAttribute('data-id');
        // Rellenar el campo oculto con el ID del cliente
        document.getElementById('id_cancelar').value = userId;
    }
    function updateHiddenField(selectElement) {
        var selectedOption = selectElement.options[selectElement.selectedIndex];
        var nombreUsuario = selectedOption.text;
        document.getElementById('nombre_usuario').value = nombreUsuario;
    }
</script>
<?php include_once("../plantilla/footer.php")?>