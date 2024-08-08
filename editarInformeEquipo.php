<?php
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once 'Datos/InformesData.php';

  $Id = isset($_GET['id']) ? $_GET['id'] : '';
  $Cliid = 2;
  $isAuthorized = false;
  $errorMessage = '';
  $Estado=0;
  $Nombre='';
  $ClienteNombre="";

  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (is_numeric($Id) && $Id > 0) {
        $informe = FnBuscarInformeMatriz($conmy, $Id, $Cliid);
        if ($informe && $informe->estado !=3) {
            $isAuthorized = true;
            $Nombre = $informe->nombre;
            $ClienteNombre = $informe->clinombre;
            $archivos = FnBuscarArchivos($conmy, $Id);
        } 
    } else {
        throw new Exception('El ID es inválido.');
    }
  } catch (PDOException $e) {
      $errorMessage = $e->getMessage();
  } catch (Exception $e) {
      $errorMessage = $e->getMessage();
  } finally {
      $conmy = null;
  }
?>

<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Datos del Equipo | GPEM SAC</title>
    <link rel="shortcut icon" href="/mycloud/logos/favicon.ico">
    <link rel="stylesheet" href="/mycloud/library/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/mycloud/library/SweetAlert2/css/sweetalert2.min.css">
    <link rel="stylesheet" href="/mycloud/library/bootstrap-5.1.0-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/mycloud/library/select-gpem-1.0/css/select-gpem-1.0.css">
  </head>
  <body>
    <div class="container">
      <div class="row border-bottom mb-3 fs-5">
          <div class="col-12 fw-bold d-flex justify-content-between">
              <p class="m-0 p-0 text-secondary"><?php echo $isAuthorized ? $ClienteNombre : ''; ?></p>
              <input type="text" class="d-none" id="idInforme" value="<?php echo $Id; ?>" readonly/>
              <p class="m-0 p-0 text-center text-secondary"><?php echo $isAuthorized ? $Nombre : ''; ?></p>
          </div>
      </div>
      <!-- ENLACES -->
      <div class="row">
          <div class="col-12">
              <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                  <ol class="breadcrumb">                        
                  <li class="breadcrumb-item fw-bold"><a href="/informes/editarInforme.php?id=<?php echo ($Id) ?>" class="text-decoration-none">INFORME</a></li>
                      <li class="breadcrumb-item active fw-bold" aria-current="page">EQUIPO</li>
                      <li class="breadcrumb-item fw-bold"><a href="/informes/editarInformeResumen.php?id=<?php echo ($Id) ?>" class="text-decoration-none">RESUMEN</a></li>
                      <li class="breadcrumb-item fw-bold"><a href="/informes/editarInformeActividad.php?id=<?php echo ($Id) ?>" class="text-decoration-none">ACTIVIDAD</a></li>
                      <li class="breadcrumb-item fw-bold"><a href="/informes/editarInformeanexo.php?id=<?php echo ($Id) ?>" class="text-decoration-none">ANEXOS</a></li>
                  </ol>
              </nav>
          </div>
      </div>
      <!-- BOTON EDITAR -->
      <?php if ($isAuthorized): ?>
        <div class="row mb-3">
          <div class="col-12">
            <button type="button" class="btn btn-outline-primary fw-bold" onclick="fnBuscarEquipoPorId(<?php echo ($informe->id); ?>);" <?php echo !$isAuthorized ? 'disabled' : ''; ?>><i class="bi bi-pencil-square"> EDITAR</i></button>
          </div>
        </div>
        <hr>
      <?php endif; ?>
      <!--DATOS EQUIPOS-->
      <?php if ($isAuthorized): ?>
      <div class="row g-3">
        <div class="col-6 col-lg-4 col-xl-3 mt-2">
            <label class="form-label mb-0">Nombre</label>
            <p class="mb-0 text-secondary text-uppercase fw-bold" style="font-size:15px" id="nombreEquipo"><?php echo ($informe->equnombre); ?></p>
        </div>
        <div class="col-6 col-lg-4 col-xl-3 mt-2">
          <label class="form-label mb-0">Marca</label>
          <p class="mb-0 text-secondary text-uppercase fw-bold" style="font-size:15px" id="marcaEquipo"><?php echo ($informe->equmarca); ?></p>
        </div>
        <div class="custom-select-container col-6 col-lg-4 col-xl-3 mt-2">
          <label class="form-label mb-0">Modelo</label>
          <p class="mb-0 text-secondary text-uppercase fw-bold" style="font-size:15px" id="modeloEquipo"><?php echo ($informe->equmodelo); ?></p>
        </div>
        <div class="col-6 col-lg-4 col-xl-3 mt-2">
          <label class="form-label mb-0">Serie</label>
          <p class="mb-0 text-secondary text-uppercase fw-bold" style="font-size:15px" id="serieEquipo"><?php echo ($informe->equserie); ?></p>
        </div>
        <div class="col-6 col-lg-4 col-xl-3 mt-2">
          <label class="form-label mb-0">Kilometraje</label>
          <p class="mb-0 text-secondary text-uppercase fw-bold" style="font-size:15px" id="kilometrajeEquipo"><?php echo ($informe->equkm); ?></p>
        </div>
        <div class="col-6 col-lg-5 col-xl-8 mt-2">
          <label class="form-label mb-0">Horas de motor</label>
          <p class="mb-0 text-secondary text-uppercase fw-bold" style="font-size:15px" id="horasMotorEquipo"><?php echo ($informe->equhm); ?></p>
        </div>
        <!-- BOTO AGREGAR -->
        <div class="row mb-3 mt-4">
          <div class="col-12">
            <button type="button" class="btn btn-outline-secondary fw-bold" onclick="fnAbrirModalRegistrarImagen();" <?php echo !$isAuthorized ? 'disabled' : ''; ?>><i class="bi bi-paperclip"></i> AGREGAR</button>
          </div>
        </div>
        <?php endif; ?>
        <!-- ARCHIVOS (TÍTULOS-IMAGENES-DESCRIPCIÓN) -->
        <div class="row m-0 mt-2 mb-2 p-0 d-flex justify-content-evenly">
          <?php if ($isAuthorized): ?>
            <?php foreach($archivos as $archivo): ?>
              <?php if($archivo['tabla']==='INF'): ?>
              <div class="col-5 col-lg-4 col-xl-3 mb-4 border border-secondary border-opacity-50 position-relative">
                <p class="text-center mt-4 mb-1 text-secondary text-uppercase fw-bold"><?php echo ($archivo['titulo']); ?></p>
                  <i class="bi bi-x-lg" style="position: absolute; font-size: 23px;color: tomato;top: 40px;left: 5px; top:5px" onclick="fnEliminarImagen(<?php echo ($archivo['id']); ?>)"></i>              
                  <img src="/mycloud/files/<?php echo ($archivo['nombre']); ?>" class="img-fluid" alt="">
                <p class="text-center text-secondary text-uppercase fw-bold"><?php echo ($archivo['descripcion']); ?></p>
              </div>
              <?php endif; ?>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- M O D A L   D A T O S  D E  E Q U I P O -->
    <div class="modal fade" id="modalEquipo" tabindex="-1" aria-labelledby="equipoModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title fs-5 text-uppercase" id="equipoModalLabel">Actualizar datos del equipo</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <!-- START MODAL-BODY -->
          <div class="modal-body" id='modal-body'>
            <div class="row">
              <div class="col-md-12 mt-2">
                <label for="" class="form-label mb-0">Nombre</label>
                <input type="text" id="nombreModalEquipo" class="form-control text-secondary text-uppercase" row=3 placeholder="Ingrese nombre de equipo." <?php echo !$isAuthorized ? 'disabled' : ''; ?>/>
              </div>
              <div class ="col-md-12 mt-2">
                <label for="" class="form-label mb-0">Marca</label>
                <input type="text" id="marcaModalEquipo" class="form-control text-secondary text-uppercase" placeholder="Ingrese marca." <?php echo !$isAuthorized ? 'disabled' : ''; ?>></textarea>
              </div>
              <div class="col-md-12 mt-2">
                <label for="" class="form-label mb-0">Modelo</label>
                <input type="text" id="modeloModalEquipo" class="form-control text-secondary text-uppercase" placeholder="Ingrese modelo." <?php echo !$isAuthorized ? 'disabled' : ''; ?>/>
              </div>
              <div class="col-md-12 mt-2">
                <label for="" class="form-label mb-0">Serie</label>
                <input type="text" id="serieModalEquipo" class="form-control text-secondary text-uppercase" placeholder="Ingrese número de serie." <?php echo !$isAuthorized ? 'disabled' : ''; ?>/>
              </div>
              <div class ="col-md-12 mt-2">
                <label for="" class="form-label mb-0">Kilometraje</label>
                <input type="text" id="kilometrajeModalEquipo" class="form-control text-secondary text-uppercase" placeholder="Ingrese kilometraje." <?php echo !$isAuthorized ? 'disabled' : ''; ?>></textarea>
              </div>
              <div class ="col-md-12 mt-2">
                <label for="" class="form-label mb-0">Horas de motor</label>
                <input type="text" id="horaMotorModalEquipo" class="form-control text-secondary text-uppercase" placeholder="Ingrese horas de motor." <?php echo !$isAuthorized ? 'disabled' : ''; ?>></textarea>
              </div>
              <div id="contenedorGuardarActividad" class="col-6 mt-4">
                <button id="guardarActividad" class="btn btn-primary fw-bold text-uppercase pt-2 pb-2 col-12"onclick="fnEditarDatosEquipo();" ><i class="bi bi-floppy"></i> Guadar</button>
              </div>
            </div>
          </div>
          <!-- END MODAL-BODY -->
        </div>
      </div>
    </div><!-- END MODAL -->

    <input type="hidden" id="tabla" value="INF"> 
    <!-- M O D A L - I M A G E N E S -->
    <div class="modal fade" id="modalAgregarImagen" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-scrollable ">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title fs-5 text-uppercase" id="modalAgregarImagenLabel">Agregar Imagen </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body pb-1">
          <input type="hidden" id="cabeceraIdInput">
            <div class="row">
              <div class="col-12 mb-2">
                <label class="form-label mb-0">Título</label>
                <input type="text" class="form-control text-secondary text-uppercase" id="txtTitulo" <?php echo !$isAuthorized ? 'disabled' : ''; ?>>
              </div>
              <div class="col-12 mb-2">
                <label class="form-label mb-0">Descripción</label>
                <input type="text" class="form-control text-secondary text-uppercase" id="txtDescripcion" <?php echo !$isAuthorized ? 'disabled' : ''; ?>>
              </div>                        
              <div class="col-12">
                <label for="adjuntarImagenInput" class="form-label mb-0">Imagen</label>
                <input id="fileImagen" type="file" accept="image/*,.pdf" class="form-control mb-2" <?php echo !$isAuthorized ? 'disabled' : ''; ?>/>
              </div>
              <div class="col-12 m-0">
                  <div class="col-md-12 text-center" id="divImagen"><i class="fas fa-images fs-2"></i></div>
              </div>
            </div>
          </div>
          <div id="msjAgregarImagen" class="modal-body pt-1"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary text-uppercase fw-bold pt-2 pb-2 col-12" onclick="FnAgregarImagen(); return false;"><i class="bi bi-floppy"></i>  Guardar</button>
          </div>
        </div>
      </div>
    </div><!-- END IMAGENES  -->

    <script src="js/editarInformeEquipo.js"></script>
    <script src="/mycloud/library/bootstrap-5.1.0-dist/js/bootstrap.min.js"></script>
    <script src="/mycloud/library/SweetAlert2/js/sweetalert2.all.min.js"></script>
      <?php if ($errorMessage): ?>
        <script>
          document.addEventListener('DOMContentLoaded', function() {
              Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: '<?php echo addslashes($errorMessage); ?>',
                  timer: 2000
              });
          });
        </script>
      <?php endif; ?>
  </body>
</html>