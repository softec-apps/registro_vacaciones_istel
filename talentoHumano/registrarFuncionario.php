<?php
include_once "../redirection.php";
include_once "../flash_messages.php";
session_start();

if (!isset($_SESSION['id_usuarios'])) {
   redirect(RUTA_ABSOLUTA . 'logout');
}
$id_user = $_SESSION['id_usuarios'];
$cedula = $_SESSION['cedula'];
$nombre = $_SESSION['nombres'];
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

$titulo = "Registrar Funcionarios";
include_once("../plantilla/header.php")
?>

    <div class="container-fluid mt-5">

<?php

include_once "../funciones.php";
include_once "../conexion.php";

$resultados = mostrarUsuarios($pdo,$id_user);
?>
<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Registrar funcionarios con el día que inicio a trabajar</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered crud-table" id="tabla_admininstradores">
                <thead>
                    <tr>
                        <th>Cédula </th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Correo electrónico </th>
                        <th>Fecha de ingreso</th>
                        <th class="exclude">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (empty($resultados)) {
                        echo "<script>alert('no ha datos registrados')</script>";
                    }else {
                        foreach ($resultados as $key => $valor){
                            $id_usuarios = $valor ["id_usuarios"];
                            $cedula_usuarios = $valor['cedula'];
                            $nombres_usuarios = $valor['nombres'];
                            $apellidos_usuarios = $valor['apellidos'];
                            $usuario = $valor['usuario'];
                            $clave = $valor['contraseña'];
                            $email_usuarios = $valor['email'];
                            $rol_usuarios = $valor['rol'];
                            $fecha_ingreso = $valor['fecha_ingreso'];
                            $tiempo_trabajo = $valor['tiempo_trabajo'];

                            if ($rol_usuarios == 'Funcionario') {

                                $funcionariosEncontrados = true;
                    ?>
                    <tr>

                        <td>
                            <?= $cedula_usuarios ?>
                        </td>

                        <td>
                            <?= $nombres_usuarios ?>
                        </td>

                        <td>
                            <?= $apellidos_usuarios ?>
                        </td>

                        <td>
                            <?= $email_usuarios ?>
                        </td>

                        <td>
                            <?= $fecha_ingreso ?>
                        </td>
                        <td>
                            <button class="btn btn-primary m-1" title="Editar datos del usuario"
                                data-toggle="modal" data-target="#Editar_datos" data-id="<?= $id_usuarios ?>" data-cedula="<?= $cedula_usuarios?>" data-name="<?= $nombres_usuarios ?>" data-lastname="<?= $apellidos_usuarios ?>" data-user="<?= $usuario ?>" data-password="<?= $clave ?>" data-email="<?= $email_usuarios ?>"
                                data-fecha_ingreso="<?= $fecha_ingreso ?>" data-tiempo_trabajo="<?= $tiempo_trabajo ?>" onclick="cargarDatos(this)">
                                <i class="fa-solid fa-pencil"></i>
                            </button>

                            <button class="btn btn-warning" title="Recuperar contraseña" data-toggle="modal"
                                data-target="#Recuperar" data-id="<?= $id_usuarios ?>" onclick="recuperar(this)">
                                <i class="fa-solid fa-key"></i>
                            </button>

                            <button class="btn btn-danger" title="Eliminar" data-toggle="modal"
                                data-target="#Eliminar" data-id="<?= $id_usuarios ?>" onclick="eliminar(this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>

                    <?php
                            }
                        };
                        if (!isset($funcionariosEncontrados)) {
                            echo "";
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>
<!-- /.container-fluid -->




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
                <p>¿Está seguro de que desea eliminar al funcionario?</p>
                <form id="eliminarForm" action="<?php echo RUTA_ABSOLUTA ?>talentoH/procesarEliminacion" method="post">
                    <input type="hidden" name="cliente_id" id="cliente_id" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" form="eliminarForm" class="btn btn-danger">Eliminar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Editar datos del usuario -->
<div class="modal fade" id="Editar_datos" tabindex="-1" role="dialog" aria-labelledby="modalAdminLabel"
aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAdminLabel">Editar datos del funcionario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="<?php echo RUTA_ABSOLUTA ?>talentoH/procesarEdicion" method="post" onsubmit="return validarFormulario();">
                    <input type="hidden" name="cliente_id" value="">
                    <input type="hidden" name="roles" value="Funcionario">

                    <div class="row mb-3 text-center">
                        <div class="col">
                            <label>Nombre de usuario </label>
                            <input class="form-control" name="user_A" type="text" required />
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label>Número de cédula </label>
                            <input class="form-control" name="cedula" type="text"/>
                        </div>
                        <div class="col">
                            <label>Correo electrónico </label>
                            <input class="form-control" type="email" name="email_A"/>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label>Nombres</label>
                            <input class="form-control" name="nombres" type="text"/>
                        </div>
                        <div class="col">
                            <label>Apellidos </label>
                            <input class="form-control" name="apellidos" type="text"/>
                        </div>
                    </div>


                    <div class="row mb-3" >
                        <div class="col" id="optionTiempo">
                            <label>Modo de trabajo </label>
                            <select class="form-control" name="tiempo_trabajo">
                                <option value="8">Tiempo Completo</option>
                                <option value="4">Medio Tiempo</option>
                            </select>
                        </div>
                        <div class="col" id="opcionesFuncionario">
                            <label>Fecha de ingreso</label>
                            <input class="form-control" type="date" name="fecha_ingreso" required/>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Actualizar información</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Modal para registrar Funcionarios -->
<div class="modal fade" id="registrar_administradores" tabindex="-1" role="dialog" aria-labelledby="modalAdminLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAdminLabel">Registrar funcionario</h5>
                <button type="button" class="close cerrarModal" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="<?php echo RUTA_ABSOLUTA ?>talentoH/procesarRegistro" method="post" onsubmit="return validarFormularioRegistrar();">
                    <input type="hidden" name="cliente_id" value="">
                    <input type="hidden" name="roles1" value="Funcionario">

                    <div class="row mb-3">
                        <div class="col">
                            <label> Número de cédula </label>
                            <input class="form-control" name="cedula" type="text" required />
                        </div>
                        <div class="col">
                            <label>Nombres</label>
                            <input class="form-control" name="nombres" type="text" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label>Apellidos</label>
                            <input class="form-control" name="apellidos" type="text" required />
                        </div>
                        <div class="col">
                            <label>Correo lectrónico </label>
                            <input class="form-control" type="email" name="email_A" required />
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col">
                            <label>Nombre de usuario </label>
                            <input class="form-control" name="user_A" type="text" required />
                        </div>
                        <div class="col">
                            <label>Contraseña del usuario</label>
                            <input class="form-control" type="text" name="password_A" required />
                        </div>
                    </div>

                    <div class="row mb-3" >
                        <div class="col" id="optionTiempo2">
                            <label>Modalidad de Trabajo </label>
                            <select class="form-control" name="tiempo_trabajo" required>
                                <option value="8">Tiempo Completo</option>
                                <option value="4">Medio Tiempo</option>
                            </select>
                        </div>
                        <div class="col" id="opcionesFuncionario">
                            <label>Fecha de ingreso</label>
                            <input class="form-control" type="date" name="fecha_ingreso" id="fecha_ingreso" required/>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Registrar</button>
                        <button type="button" class="btn btn-secondary cerrarModal" data-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal recuperar contraseña -->
<div class="modal fade" id="Recuperar" tabindex="-1" role="dialog" aria-labelledby="modalAdminLabel"
        aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAdminLabel">Recuperar contraseña</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- <p></p> -->
                <form id="recuperarForm" action="<?php echo RUTA_ABSOLUTA ?>talentoHumano/recovePassword" method="post">
                    <input type="hidden" name="user_id" id="user_id" value="">
                    <div class="row mb-3">
                        <div class="col">
                            <label>Nueva contraseña </label>
                            <input class="form-control mb-3" type="text" name="recuperarClave" id="recuperarClave" required>
                            <label>Repetir la contraseña</label>
                            <input class="form-control" type="password" name="repetirClave" id="repetirClave" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="submit" form="recuperarForm" class="btn btn-primary">Recuperar contraseña</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(".cerrarModal").click(function(){
        $("#registrar_administradores").modal('hide')
    });


    function cargarDatos(button) {
        var modal = document.querySelector('#Editar_datos');
        var cliente_id = modal.querySelector('[name="cliente_id"]');
        var cedulaInput = modal.querySelector('[name="cedula"]');
        var nombresInput = modal.querySelector('[name="nombres"]');
        var apellidosInput = modal.querySelector('[name="apellidos"]');
        var user_AInput = modal.querySelector('[name="user_A"]');
        var emailInput = modal.querySelector('[name="email_A"]');
        var fecha_ingresoInput = modal.querySelector('[name="fecha_ingreso"]');
        var tiempo_trabajoInput = modal.querySelector('[name="tiempo_trabajo"]');

        var userId = button.getAttribute('data-id');
        var cedula = button.getAttribute('data-cedula');
        var username = button.getAttribute('data-name');
        var lastname = button.getAttribute('data-lastname');
        var user = button.getAttribute('data-user');
        var email = button.getAttribute('data-email');
        var fecha_ingreso = button.getAttribute('data-fecha_ingreso');
        var tiempo_trabajo = button.getAttribute('data-tiempo_trabajo');

        // Rellenar los campos del modal con los datos obtenidos
        cliente_id.value = userId;
        cedulaInput.value = cedula;
        nombresInput.value = username;
        apellidosInput.value = lastname;
        user_AInput.value = user;
        emailInput.value = email;
        fecha_ingresoInput.value = fecha_ingreso;
        tiempo_trabajoInput.value = tiempo_trabajo;
    }


    function eliminar(button) {
        var userId = button.getAttribute('data-id');
        // Rellenar el campo oculto con el ID del cliente
        document.getElementById('cliente_id').value = userId;
    }

    function validarFormulario() {
        var rolInput = document.querySelector('[name="roles"]');
        var fechaIngresoInput = document.querySelector('[name="fecha_ingreso"]');

        // Obtener el valor del rol seleccionado
        var rolSeleccionado = rolInput.value;

        // Si el rol es "Funcionario"
        if (rolSeleccionado === 'Funcionario') {
            // Obtener la fecha ingresada por el usuario
            var fechaIngreso = new Date(fechaIngresoInput.value);

            // Obtener la fecha actual
            var fechaActual = new Date();

            // Verificar si la fecha está vacía o es superior al año actual
            if (!fechaIngresoInput.value.trim() || fechaIngreso > fechaActual) {
                alert('Por favor, ingrese una fecha de ingreso válida para el Funcionario.');
                return false; // Evita que el formulario se envíe
            }
        }

        // Si todo está bien, permitir el envío del formulario
        return true;
    }

    function validarFormularioRegistrar() {
        var rolInput = document.querySelector('[name="roles1"]');
        var fechaIngresoInput = document.querySelector('#fecha_ingreso');

        // Obtener el valor del rol seleccionado
        var rolSeleccionado = rolInput.value;
        var fechaSeleccionada = fechaIngresoInput.value;

        // Si el rol es "Funcionario"
        if (rolSeleccionado === 'Funcionario') {
            // Obtener la fecha ingresada por el usuario
            var fechaIngreso = new Date(Date.parse(fechaIngresoInput.value));

            // Obtener la fecha actual
            var fechaActual = new Date();

            // Verificar si la fecha está vacía o es superior al año actual
            if (fechaIngreso > fechaActual) {
                alert('Por favor, ingrese una fecha de ingreso válida para el Usuario');
                return false;
            }else if(!fechaIngresoInput.value.trim()){
                alert('Fecha vacia ');
                return false;
            }
        }

        // Si todo está bien, permitir el envío del formulario
        return true;
    }
</script>

<?php
include_once("../plantilla/footer.php")
?>