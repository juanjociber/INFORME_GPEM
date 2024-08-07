<?php
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once 'Datos/InformesData.php';

  $Id = isset($_GET['id']) ? $_GET['id'] : '';
  $Cliid = 2;
  $isAuthorized = false;
  $errorMessage = '';

  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (is_numeric($Id) && $Id > 0) {
      $informe = FnBuscarInformeMatriz($conmy, $Id, $Cliid);
      if ($informe) {
        $isAuthorized = true;
        $archivos = FnBuscarArchivos($conmy, $Id);
      } 
    } else {
      throw new Exception('El ID es inválido.');
    }
  } catch (PDOException $ex) {
      $errorMessage = $ex->getMessage();
  } catch (Exception $ex) {
      $errorMessage = $ex->getMessage();
  } finally {
      $conmy = null;
  }
?>

<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Anexos | GPEM SAC</title>
    <link rel="shortcut icon" href="/mycloud/logos/favicon.ico">
    <link rel="stylesheet" href="/mycloud/library/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/mycloud/library/SweetAlert2/css/sweetalert2.min.css">
    <link rel="stylesheet" href="/mycloud/library/bootstrap-5.1.0-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/mycloud/library/select-gpem-1.0/css/select-gpem-1.0.css">
  </head>
  <body>
    <div class="container">
      <!-- CABECERA -->
      <div class="row border-bottom mb-3 fs-5">
        <div class="col-12 fw-bold d-flex justify-content-between">
          <p class="m-0 p-0 text-secondary"><?php echo $isAuthorized ? ($informe->clinombre) : 'No Autorizado'; ?></p>
          <input type="text" class="d-none" id="txtIdInforme" value="<?php echo $isAuthorized ? ($informe->id) : ''; ?>" readonly/>
          <p class="m-0 p-0 text-center text-secondary"><?php echo $isAuthorized ? ($informe->nombre) : 'No Autorizado'; ?></p>
        </div>
      </div>
      <!-- ENLACES -->
      <div class="row">
        <div class="col-12">
            <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                <ol class="breadcrumb">                        
                    <li class="breadcrumb-item fw-bold"><a href="/informes/datoGeneral.php?id=<?php echo ($Id) ?>" class="text-decoration-none">INFORME</a></li>
                    <li class="breadcrumb-item fw-bold"><a href="/informes/datoEquipo.php?id=<?php echo ($Id) ?>" class="text-decoration-none">EQUIPO</a></li>
                    <li class="breadcrumb-item fw-bold"><a href="/informes/resumen.php?id=<?php echo ($Id) ?>" class="text-decoration-none">RESUMEN</a></li>
                    <li class="breadcrumb-item fw-bold"><a href="/informes/actividad.php?id=<?php echo ($Id) ?>" class="text-decoration-none">ACTIVIDAD</a></li>
                    <li class="breadcrumb-item active fw-bold" aria-current="page">ANEXOS</li>
                </ol>
            </nav>
        </div>
      </div>
      <!-- BODY -->
      <?php if ($isAuthorized): ?>
        <div class="card mb-4">
          <div class="card-header bg-light"><h5 class="card-title text-secondary">ANEXOS</h5></div>
          <div class="card-body">
            <div class="row">
              <label for="adjuntarImagenInput" class="form-label mb-0">Ingresar archivo</label>
              <div class="col-6 col-md-3 col-lg-2 mt-2">
                <button id="descripcion" class="btn btn-primary text-uppercase pt-2 pb-2 col-12 fw-bold" data-bs-toggle="modal" data-bs-target="#modalAnexo" <?php echo !$isAuthorized ? 'disabled' : ''; ?>><i class="bi bi-paperclip"></i> Agregar</button>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- M O D A L - I M A G E N E S -->
      <div class="modal fade" id="modalAnexo" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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
                  <input type="text" class="form-control" id="txtTitulo" <?php echo !$isAuthorized ? 'disabled' : ''; ?>>
                </div>
                <div class="col-12 mb-2">
                  <label class="form-label mb-0">Descripción</label>
                  <input type="text" class="form-control" id="txtDescripcion" <?php echo !$isAuthorized ? 'disabled' : ''; ?>>
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
              <button type="button" class="btn btn-primary text-uppercase fw-bold pt-2 pb-2 col-12" onclick="FnAgregarImagen();" <?php echo !$isAuthorized ? 'disabled' : ''; ?>><i class="bi bi-floppy"></i>  Guardar</button>
            </div>
          </div>
        </div>
      </div>
      <!-- ARCHIVOS-IMAGEN -->
      <div class="row">
        <?php if ($isAuthorized): ?>
          <?php foreach($archivos as $archivo): ?>
            <?php if ($archivo['tabla'] ==='INFA'): ?>
            <input type="hidden" id="refid" value="<?php echo ($archivo['refid']); ?>">
            <div class="caja-imagen col-6 col-lg-3" id="<?php echo ($archivo['archivoid']); ?>">
              <div class="contenedor-imagen">
                <p class="text-center mt-4 mb-1"><?php echo ($archivo['titulo']); ?></p>
                  <i class="bi bi-x-lg" style="position: absolute; font-size: 23px;color: tomato;top: 40px;left: 5px; top:5px" onclick="fnEliminarAnexo(<?php echo ($archivo['archivoid']); ?>)"></i>
                  <img src="/mycloud/files/ORD_112_651f18cf9b6de.jpeg" class="img-fluid" alt="">
                <p class="text-center"><?php echo ($archivo['descripcion']); ?></p>
              </div>
            </div>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php endif; ?>
      </div> 
    </div>

    <script src="js/anexo.js"></script>
    <script src="/mycloud/library/SweetAlert2/js/sweetalert2.all.min.js"></script>
    <script src="/mycloud/library/bootstrap-5.1.0-dist/js/bootstrap.bundle.min.js"></script>
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
