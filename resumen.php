<?php
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once 'Datos/InformesData.php';

  $Id = isset($_GET['id']) ? $_GET['id'] : '';
  $Cliid = 2;
  $isAuthorized = false;
  $errorMessage = ''; 
  $datos = array();

  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (is_numeric($Id) && $Id > 0) {
      $informe = FnBuscarInformeMatriz($conmy, $Id, $Cliid);
      if ($informe) {
        $isAuthorized = true;
        $datos = FnBuscarActividades($conmy, $Id);
        $conclusiones=array();
        $recomendaciones=array();
        $antecedentes=array();

        foreach($datos as $dato){
          if($dato['tipo']=='con'){
            $conclusiones[]=array('actividad'=>$dato['actividad'],'id'=>$dato['id'],'tipo'=>$dato['tipo']);
          }else if($dato['tipo']=='rec'){
            $recomendaciones[]=array('actividad'=>$dato['actividad'],'id'=>$dato['id'],'tipo'=>$dato['tipo']);
          }else if($dato['tipo']=='ant'){
            $antecedentes[]=array('actividad'=>$dato['actividad'],'id'=>$dato['id'],'tipo'=>$dato['tipo']);
          }	
        }
      } else {
        throw new Exception('Informe no encontrado.');
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
    <link rel="stylesheet" href="css/main.css">
    <title>Resumen | GPEM SAC</title>
    <link rel="shortcut icon" href="/mycloud/logos/favicon.ico">
    <link rel="stylesheet" href="/mycloud/library/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/mycloud/library/SweetAlert2/css/sweetalert2.min.css">
    <link rel="stylesheet" href="/mycloud/library/bootstrap-5.1.0-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/mycloud/library/select-gpem-1.0/css/select-gpem-1.0.css">
    <style>
      .input-grop-icons{ display: flex; justify-content: flex-end;}
      .bi-plus-lg::before{ font-weight:bold!important; }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="row border-bottom mb-3 fs-5">
        <div class="col-12 fw-bold d-flex justify-content-between">
          <p class="m-0 p-0 text-secondary"><?php echo $isAuthorized ? ($informe->clinombre) : 'No Autorizado'; ?></p>
          <input type="text" class="d-none" id="txtIdInforme" value="<?php echo $informe->id; ?>" readonly/>
          <input type="text" class="d-none" id="txtIdtblDetalleInf" readonly/>
          <input type="text" class="d-none" id="txtInfid" readonly/>
          <p class="m-0 p-0 text-center text-secondary"><?php echo $isAuthorized ? ($informe->nombre) : 'No Autorizado'; ?></p>
        </div>
      </div>
      <div class="row">
        <div class="col-12">
          <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item fw-bold"><a href="/informes/datoGeneral.php?id=<?php echo $Id ?>" class="text-decoration-none">INFORME</a></li>
              <li class="breadcrumb-item fw-bold"><a href="/informes/datoEquipo.php?id=<?php echo $Id ?>" class="text-decoration-none">EQUIPO</a></li>
              <li class="breadcrumb-item active fw-bold" aria-current="page">RESUMEN</li>
              <li class="breadcrumb-item fw-bold"><a href="/informes/actividad.php?id=<?php echo $Id ?>" class="text-decoration-none">ACTIVIDAD</a></li>
              <li class="breadcrumb-item fw-bold"><a href="/informes/anexos.php?id=<?php echo $Id ?>" class="text-decoration-none">ANEXOS</a></li>
            </ol>
          </nav>
        </div>
      </div>

      <?php if ($isAuthorized): ?>
      <!--RESUMEN-->
      <div class="row" id="containerActividad">
        <label class="text-secondary text-uppercase bg-secondary bg-gradient bg-opacity-10 fw-bold p-2 bg-primary d-flex justify-content-between align-items-center">Actividades</label>
        <!-- ITEM ACTIVIDADES -->
        <div class="mt-1 p-2 d-flex justify-content-between align-items-center border border-opacity-0">
          <p class="mb-0 text-uppercase text-secondary fw-bold" id="actividadId" style="text-align: justify;"><?php echo $informe->actividad; ?></p>
          <i class="bi bi-pencil-square" onclick="fnEditarActividad(<?php echo $informe->id; ?>)"></i>
        </div>
      </div>
      
      <!-- ITEM ANTECEDENTES -->
      <div class="row mt-2">
          <label class="p-2 mt-2 text-secondary text-uppercase bg-secondary bg-gradient bg-opacity-10 fw-bold d-flex justify-content-between align-items-center">Antecedentes <i class="bi bi-plus-lg fw-bold text-secondary" data-tipo="ant" onclick="abrirModalAgregar('antecedente','ant')"></i></label>
          <div class="mt-1 border border-opacity-50">
            <?php foreach ($antecedentes as $antecedente) : ?>
              <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex";>
                  <i class="bi bi-stop" style="margin-right:10px"></i>
                  <p class="mb-0 text-uppercase text-secondary fw-bold" data-tipo="<?php echo $antecedente['tipo']; ?>" id="antecedenteId" style="text-align: justify;"><?php echo $antecedente['actividad']; ?></p>
                </div>
                <div class="input-grop-icons">
                  <span class="input-group-text bg-white border border-0"><i class="bi bi-pencil-square" data-tipo="<?php echo $antecedente['tipo']; ?>" onclick="abrirModalEditar(<?php echo $antecedente['id']; ?>, 'antecedente')"></i></span>
                  <span class="input-group-text bg-white border border-0"><i class="bi bi-trash3" onclick="abrirModalEliminar(<?php echo $antecedente['id']; ?>)"></i></span>
                </div>
              </div>
            <?php endforeach ?>
          </div>
      </div>
        <!-- ITEM CONCLUSION -->
        <div class="row">
          <label class="p-2 mt-2 text-secondary text-uppercase bg-secondary bg-gradient bg-opacity-10 fw-bold d-flex justify-content-between align-items-center">Conclusiones <i class="bi bi-plus-lg fw-bold text-secondary" data-tipo="con" onclick="abrirModalAgregar('conclusion','con')"></i></label>
          <div class="mt-1 border border-opacity-50">
            <?php foreach ($conclusiones as $conclusion) : ?>
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex">
                <i class="bi bi-stop" style="margin-right:10px"></i>
                <p class="mb-0 text-uppercase text-secondary fw-bold" data-tipo="<?php echo $conclusion['tipo']; ?>" id="conclusionId>" style="text-align: justify;"><?php echo $conclusion['actividad']; ?></p>
              </div>
              <div class="input-grop-icons">
                <span class="input-group-text bg-white border border-0"><i class="bi bi-pencil-square" data-tipo="<?php echo $conclusion['tipo']; ?>" onclick="abrirModalEditar(<?php echo $conclusion['id']; ?>, 'conclusion')"></i></span>
                <span class="input-group-text bg-white border border-0"><i class="bi bi-trash3" onclick="abrirModalEliminar(<?php echo $conclusion['id']; ?>)"></i></span>
              </div>
            </div>
            <?php endforeach ?>
          </div>
        </div>
        <!-- ITEM RECOMENDACIÓN -->
        <div class="row">
          <label class="p-2 mt-2 text-secondary text-uppercase bg-secondary bg-gradient bg-opacity-10 fw-bold d-flex justify-content-between align-items-center">Recomendaciones <i class="bi bi-plus-lg fw-bold text-secondary" data-tipo="rec" onclick="abrirModalAgregar('recomendacion','rec')"></i></label>           
          <div class="mt-1 border border-opacity-50">
            <?php foreach ($recomendaciones as $recomendacion) : ?>
            <div class="d-flex justify-content-between align-items-center">
              <div class="d-flex">
                <i class="bi bi-stop" style="margin-right:10px"></i>
                <p class="mb-0 text-uppercase text-secondary fw-bold" data-tipo="<?php echo $recomendacion['tipo']; ?>" id="recomendacionId" style="text-align: justify;"><?php echo $recomendacion['actividad']; ?></p>
              </div>
              <div class="input-grop-icons">
                <span class="input-group-text bg-white border border-0"><i class="bi bi-pencil-square" data-tipo="<?php echo $recomendacion['tipo']; ?>" onclick="abrirModalEditar(<?php echo $recomendacion['id']; ?>, 'recomendacion')"></i></span>
                <span class="input-group-text bg-white border border-0"><i class="bi bi-trash3" onclick="abrirModalEliminar(<?php echo $recomendacion['id']; ?>)"></i></span>
              </div>
            </div>
            <?php endforeach ?>
          </div>
        </div>

        <!-- MODAL EDITAR : ACTIVIDAD -->
        <div class="modal fade" id="modalActividad" tabindex="-1" aria-labelledby="modalGeneralLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-uppercase" id="modalGeneralLabel">Modificar actividad</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form id="formGeneral">
                  <textarea type="text" class="form-control" id="modalActividadInput" name="actividad" rows="3" placeholder=""></textarea>
                  <textarea type="text" class="form-control d-none" id="diagnosticoModalInput" name="diagnostico" rows="3" placeholder=""></textarea>
                  <textarea type="text" class="form-control d-none" id="trabajoModalInput" name="trabajos" rows="3" placeholder=""></textarea>
                  <textarea type="text" class="form-control d-none" id="observacionModalInput" name="observaciones" rows="3" placeholder=""></textarea>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-primary text-uppercase fw-light" id="modalGuardarBtn" onclick="fnModificarActividadInforme()"><i class="bi bi-floppy"></i> Guardar</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- MODAL REGISTRAR : ANTECEDENTE-CONCLUSION-RECOMENDACIÓN -->
        <div class="modal fade" id="agregarActividadModal" tabindex="-1" aria-labelledby="cabeceraRegistrarModal" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-uppercase" id="cabeceraRegistrarModal"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form id="formGeneral">
                  <textarea type="text" class="form-control" id="registroActividadInput" name="actividad" rows="3" placeholder=""></textarea>
                  <textarea type="text" class="form-control d-none" id="registroDiagnosticoInput" name="diagnostico" rows="3" placeholder=""></textarea>
                  <textarea type="text" class="form-control d-none" id="registroTrabajoInput" name="trabajos" rows="3" placeholder=""></textarea>
                  <textarea type="text" class="form-control d-none" id="registroObservacionInput" name="observaciones" rows="3" placeholder=""></textarea>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-primary text-uppercase fw-light" id="modalGuardarBtn" onclick="fnRegistrarActividadDetalle()"><i class="bi bi-floppy"></i> Guardar</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- MODAL EDITAR : ANTECEDENTE-CONCLUSION-RECOMENDACIÓN- -->
        <div class="modal fade" id="modalGeneral" tabindex="-1" aria-labelledby="cabeceraModal" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-uppercase" id="cabeceraModal"></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body">
                <form id="formGeneral">
                  <textarea type="text" class="form-control" id="actividadModalInput" name="actividad" rows="3" placeholder=""></textarea>
                  <textarea type="text" class="form-control d-none" id="diagnosticoModalInput" name="diagnostico" rows="3" placeholder=""></textarea>
                  <textarea type="text" class="form-control d-none" id="trabajoModalInput" name="trabajos" rows="3" placeholder=""></textarea>
                  <textarea type="text" class="form-control d-none" id="observacionModalInput" name="observaciones" rows="3" placeholder=""></textarea>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-primary text-uppercase fw-light" id="modalGuardarBtn" onclick="FnModificarActividad()"><i class="bi bi-floppy"></i> Guardar</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>
    </div>
    <script src="js/resumen.js"></script>
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
