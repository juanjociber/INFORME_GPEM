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
  $supervisores =[];

  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (is_numeric($Id) && $Id > 0) {
      $informe = FnBuscarInformeMatriz($conmy, $Id, $Cliid);
      if($informe && $informe->estado !=3){
        $isAuthorized = true;
        $Nombre = $informe->nombre;
        $ClienteNombre = $informe->clinombre;
        $supervisores = FnBuscarSupervisores($conmy,$Cliid);
      }
    }else{
      throw new Exception('El ID es inválido.');
    } 
  } catch (PDOException $ex) {
      $errorMessage = $ex->getMessage();
  } catch (Exception $ex) {
      $errorMessage = $ex->getMessage();
  } finally {
      $conmy = null;
  }

  // VERIFICANDO SI SUPERVISOR PERTENECE AL CLIENTE
  $supervisorValido = false;
  foreach ($supervisores as $supervisor) {
    if ($supervisor['supervisor'] == $informe->supervisor) {
      $supervisorValido = true;
      break;
    }
  }
  $supervisorInputValue = $supervisorValido ? $informe->supervisor : '';
?>

<!doctype html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Informe | GPEM SAC</title>
    <link rel="shortcut icon" href="/mycloud/logos/favicon.ico">
    <link rel="stylesheet" href="/mycloud/library/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/mycloud/library/SweetAlert2/css/sweetalert2.min.css">
    <link rel="stylesheet" href="/mycloud/library/bootstrap-5.1.0-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/mycloud/library/select-gpem-1.0/css/select-gpem-1.0.css">
  </head>
  <style>
    .custom-select-arrow { top: 20%; right: 10px; }
  </style>
  <body>
    <div class="container">
      <!-- CABECERA -->
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
                    <li class="breadcrumb-item active fw-bold" aria-current="page">INFORME</li>
                    <li class="breadcrumb-item fw-bold"><a href="/informes/editarInformeEquipo.php?id=<?php echo ($Id);?>" class="text-decoration-none">EQUIPO</a></li>
                    <li class="breadcrumb-item fw-bold"><a href="/informes/editarInformeResumen.php?id=<?php echo ($Id);?>" class="text-decoration-none">RESUMEN</a></li>
                    <li class="breadcrumb-item fw-bold"><a href="/informes/editarInformeActividad.php?id=<?php echo ($Id);?>" class="text-decoration-none">ACTIVIDAD</a></li>
                    <li class="breadcrumb-item fw-bold"><a href="/informes/editarInformeAnexo.php?id=<?php echo ($Id);?>" class="text-decoration-none">ANEXOS</a></li>
                </ol>
            </nav>
        </div>
      </div>
      <!--DATOS GENERALES-->
      <?php if ($isAuthorized): ?>
        <div class="row g-3">
          <!-- Nro. INFORME -->
          <div class="col-6 col-md-4 col-lg-3">
            <label for="nombreInformeInput" class="form-label mb-0">Nro. Informe</label>
            <input type="text" class="form-control text-secondary text-uppercase fw-bold" id="nombreInformeInput" value="<?php echo ($informe->nombre); ?>" disabled>
          </div>
          <!-- FECHA -->
          <div class="col-6 col-md-4 col-lg-3">
            <label for="fechaInformeInput" class="form-label mb-0">Fecha</label>
            <input type="date" class="form-control text-secondary text-uppercase fw-bold" id="fechaInformeInput" value="<?php echo ($informe->fecha); ?>">
          </div>
          <!-- ORDEN DE TRABAJO -->
          <div class="col-6 col-md-4 col-lg-3">
            <label for="OrdenTrabajoInput" class="form-label mb-0">Orden de trabajo</label>
            <input type="text" class="form-control text-secondary text-uppercase fw-bold" id="OrdenTrabajoInput" value="<?php echo ($informe->ordnombre); ?>" disabled>
          </div>
          <!-- CLIENTE -->
          <div class="col-6 col-md-6 col-lg-3">
            <label for="nombreClienteInput" class="form-label mb-0">Cliente</label>
            <input type="text" class="form-control text-secondary text-uppercase fw-bold" id="nombreClienteInput" value="<?php echo ($informe->clinombre); ?>" disabled>
          </div>
          <!-- CONTACTOS -->
          <div class="custom-select-container col-md-6 col-lg-4">
            <label for="contactoInput" class="form-label mb-0">Contacto</label>
            <div class="custom-select-wrapper">
              <input type="text" id="contactoInput" class="custom-select-input text-secondary text-uppercase fw-bold" value="<?php echo ($informe->clicontacto); ?>" />
              <span class="custom-select-arrow text-secondary text-uppercase fw-bold"><i class="bi bi-chevron-down"></i></span>
              <div id="contactoList" class="custom-select-list ">
                <?php foreach ($supervisores as $supervisor): ?>
                  <div class="custom-select-item" data-value="<?php echo ($supervisor['idsupervisor']); ?>">
                    <?php echo ($supervisor['supervisor']); ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
          <!-- LUGAR -->
          <div class="col-md-6 col-lg-4">
            <label for="ubicacionInput" class="form-label mb-0">Lugar</label>
            <input type="text" class="form-control text-secondary text-uppercase fw-bold" id="ubicacionInput" value="<?php echo ($informe->clidireccion); ?>" >
          </div>      
          <!-- SUPERVISORES -->
          <div class="custom-select-container col-md-6 col-lg-4">
            <label for="supervisorInput" class="form-label mb-0">Supervisor</label>
            <div class="custom-select-wrapper">
              <input type="text" class="custom-select-input text-secondary text-uppercase fw-bold" id="supervisorInput" value="<?php echo  ($supervisorInputValue);?>"/>
              <span class="custom-select-arrow"><i class="bi bi-chevron-down"></i></span>
              <div id="supervisorList" class="custom-select-list">
                <!-- SUPERVISORES -->
                <?php foreach ($supervisores as $supervisor): ?>
                  <div class="custom-select-item" data-value="<?php echo ($supervisor['idsupervisor']); ?>">
                    <?php echo ($supervisor['supervisor']); ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- BOTON GUARDAR -->
        <div class="row mt-4">
          <div class="col-6 col-md-3 col-lg-2 mt-2">
            <button id="guardarDataEquipo" class="btn btn-primary text-uppercase pt-2 pb-2 col-12" onclick="fnGuardarDatosGenerales();" <?php echo !$isAuthorized ? 'disabled' : ''; ?>>Guardar <i class="bi bi-floppy"></i></button>
          </div>
        </div>
      <?php endif ?>
    </div><!-- END CONTAINER -->

    <script src="js/editarInforme.js"></script>
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
