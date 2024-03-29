</main>
</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#layoutSidenav">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">¿Listo para salir?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Seleccione "Cerrar sesión" a continuación si está listo para finalizar su sesión actual.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
                <a class="btn btn-primary" href="<?php echo RUTA_ABSOLUTA; ?>logout">Cerrar sesión</a>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo $ruta_absoluta; ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Core plugin JavaScript-->
<script src="<?php echo $ruta_absoluta; ?>vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom scripts for all pages-->
<script src="<?php echo $ruta_absoluta; ?>js/sb-admin-2.min.js"></script>

<script src="<?php echo $ruta_absoluta; ?>js/scripts.js"></script>


<script src="<?php echo $ruta_absoluta; ?>js/datatables.js"></script>
<script>
    $(document).ready(function() {
        showFlashMessages('<?php echo $message; ?>', '<?php echo $type; ?>');
    });
</script>

</body>

</html>