<?php 
	require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
	//$Id=$_GET['id'];
  $Id = isset($_GET['id']) ? $_GET['id'] : '';
  $Cliid = 2;
  $isAuthorized = false;
  $errorMessage = ''; 

	$Nombre='';
	$Estado=0;
	$tablaHTML ='';

	function construirArbol($registros, $padreId = 0) {
		$arbol = array();
		foreach ($registros as $registro) {
			if ($registro['ownid'] == $padreId) {
				$hijos = construirArbol($registros, $registro['id']);
				if (!empty($hijos)) {
					$registro['hijos'] = $hijos;
				}					
				$arbol[] = $registro;
			}
		}			
		return $arbol;
	}

	function FnGenerarInformeHtmlAcordeon($arbol, $imagenes, $nivel = 0, $indice ='1') {
		$html='';
		$contador=1;		

		foreach ($arbol as $key=>$nodo) {
      //ASIGNANDO VALOR A NODOGLOBAL
			$indiceActual = $nivel==0?$contador++:$indice.'.'.($key+1);
			$html.='<div class="accordion-item" id="'.$nodo['id'].'">';
			$html.='
				<h2 class="accordion-header" id="accordion-header-'.$nodo['id'].'">
          <div class="cabecera">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-accordion-'.$nodo['id'].'" aria-expanded="true" aria-controls="collapse-accordion-'.$contador.'">
						'.$indiceActual.' - '.$nodo['actividad'].'
            </button>
            <div class="accordion-botones">
              <i class="bi bi-plus-lg icono" onclick="fnCrearSubActividad('.$nodo['id'].')"></i>
              <i class="bi bi-pencil-square icono" onclick="fnEditarActividad('.$nodo['id'].')"></i>
              <i class="bi bi-paperclip icono" onclick="fnAbrirModalRegistrarImagen('.$nodo['id'].')"></i>
              <i class="bi bi-trash3 icono" onclick="fnEliminarActividad('.$nodo['id'].')"></i>
            </div>
          </div>
				</h2>
				<div id="collapse-accordion-'.$nodo['id'].'" class="accordion-collapse collapse show" aria-labelledby="accordion-header-'.$nodo['id'].'">
					<div class="accordion-body">
						<div class="row">
							<div class="col-6">
                <label class="form-label mb-0">Diagnóstico</label>
                <p class="mb-1 text-secondary text-uppercase fw-bold" style="font-size=15px" id="diagnostico-'.$nodo['id'].'">'.$nodo['diagnostico'].'</p>
              </div>
							<div class="col-6">
                <label class="form-label mb-0">Trabajos</label>
                <p class="mb-1 text-secondary text-uppercase fw-bold" style="font-size=15px" id="trabajo-'.$nodo['id'].'">'.$nodo['trabajos'].'</p>
              </div>
							<div class="col-12">
                <label class="form-label mb-0">Observaciones</label>
                <p class="mb-1 text-secondary text-uppercase fw-bold" style="font-size=15px" id="observacion-'.$nodo['id'].'">'.$nodo['observaciones'].'</p>
              </div>
						</div>
						<div class="row m-0 mt-2 mb-2 p-0 d-flex justify-content-evenly" id="'.$nodo['id'].'">';
							if(isset($imagenes[$nodo['id']])){
								foreach($imagenes[$nodo['id']] as $elemento){
									$html.='
                    <div class="col-5 col-lg-4 col-xl-3 mb-4 border border-secondary border-opacity-50 position-relative" id="archivo-'.$elemento['id'].'">
                      <p class="text-center mt-4 mb-1 text-secondary text-uppercase fw-bold">'.$elemento['titulo'].'</p>
                        <i class="bi bi-x-lg" style="position: absolute; font-size: 23px;color: tomato;top: 40px;left: 5px; top:5px" onclick="fnEliminarImagen('.$elemento['id'].')"></i>
                        <img src="/mycloud/files/'.$elemento['nombre'].'" class="img-fluid" alt="">
                      <p class="text-center text-secondary text-uppercase fw-bold">'.$elemento['descripcion'].'</p>
                    </div>';
								}
							}
						$html.='</div>';
			if (!empty($nodo['hijos'])) {
				$html.='<div class="accordion" id="accordion-container-'.$nodo['id'].'">';
				$html.=FnGenerarInformeHtmlAcordeon($nodo['hijos'], $imagenes, $nivel+1, $indiceActual);
				$html.='</div>';
			}
			$html.='</div>';
			$html.='</div>';
			$html.='</div>';
		}
		return $html;
	}

	function FnGenerarInformeHtml($arbol, $imagenes, $nivel = 0, $indice ='1') {
		$html='<table width="100%" style="border: #b2b2b2 1px solid">';
		$contador=1;		
		foreach ($arbol as $key=>$nodo) {
			$indiceActual = $nivel==0?$contador++:$indice.'.'.($key+1);
			$html.='<tr><td colspan="2" style="border: red 1px solid">'.$indiceActual.' - '.$nodo['actividad'].'</td></tr>';		
			$imagen=array();
			if(isset($imagenes[$nodo['id']])){
				$html.='<tr><td><table width="100%" style="border: #b2b2b2 1px solid; color:red">';
				$i=1;
				foreach($imagenes[$nodo['id']] as $elemento){
					if($i==2){
						$html.='<td style="border: black 1px solid">'.$elemento['nombre'].'</td></tr>';
						$i=1;
					}else{
						$html.='<tr><td style="border: black 1px solid">'.$elemento['nombre'].'</td>';
						$i+=1;
					}
				}
				if($i==2){$html.='</tr>';}
				$html.='</table></td></tr>';
			}			
			if (!empty($nodo['hijos'])) {
				$html.='<tr><td colspan="2" style="border: blue 1px solid">';
				$html.=FnGenerarInformeHtml($nodo['hijos'], $imagenes, $nivel+1, $indiceActual);
				$html.='</td></tr>';
			}
		}
		$html.='</table>';
		return $html;
	}

	try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    function FnBuscarArchivos($conmy, $ids, $tabla, $tipo) {
      if (empty($ids)) {
          return array(); 
      }
      $cadenaIds = implode(',', $ids);
      $sql = "SELECT id, refid, nombre, descripcion, titulo FROM tblarchivos WHERE refid IN ($cadenaIds) AND tabla = ? AND tipo = ?";
      $stmt = $conmy->prepare($sql);
      $stmt->execute([$tabla, $tipo]);
      $imagenes = array();
      while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $imagenes[$row['refid']][] = array(
          'id' => (int)$row['id'],
          'nombre' => $row['nombre'],
          'descripcion' => $row['descripcion'],
          'titulo' => $row['titulo']
        );
      }
      return $imagenes;
    }

    function FnBuscarInformeMatriz($conmy, $Id, $Cliid) {
      try {
          $stmt = $conmy->prepare("SELECT id, nombre, cli_nombre, estado FROM tblinforme WHERE id = ? AND cliid = ?");
          $stmt->execute([$Id, $Cliid]);
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
          if ($row) {
            $informe = new stdClass();
            $informe->id = $row['id'];
            $informe->nombre = $row['nombre'];
            $informe->cli_nombre = $row['cli_nombre'];
            $informe->estado = $row['estado'];
            return $informe;
          } else {
              throw new Exception('Informe no disponible para el cliente.');
          }
      } catch (PDOException $ex) {
          throw new Exception('Error al buscar informe: ' . $ex->getMessage());
      } catch (Exception $ex) {
          throw new Exception($ex->getMessage());
      }
    }
  

    function FnBuscarActividades($conmy, $Id) {
      $stmt = $conmy->prepare("SELECT id, ownid, tipo, actividad, diagnostico, trabajos, observaciones FROM tbldetalleinforme WHERE infid = ?");
      $stmt->execute([$Id]);
      return $stmt->fetchAll(PDO::FETCH_ASSOC); 
    }

    if (is_numeric($Id) && $Id > 0) {
      $row = FnBuscarInformeMatriz($conmy, $Id, $Cliid);
      if ($row) {
        $isAuthorized = true;
        $Id = $row['id'];
        $Nombre = $row['nombre'];
        $Estado = $row['estado'];
        $ClienteNombre = $row['cli_nombre'];
        $actividades = FnBuscarActividades($conmy, $Id);
        $arbol = construirArbol($actividades);
        $ids = array_column($actividades, 'id'); 
        $imagenes = FnBuscarArchivos($conmy, $ids, 'INFD', 'IMG'); 
      }else{
        $errorMessage = 'El ID es inválido.';
      }
    }
    $tablaHTML = '<div class="accordion" id="accordion-container-'.$nodo['id'].'">';
    $tabla = FnGenerarInformeHtmlAcordeon($arbol, $imagenes);
    $tablaHTML .= $tabla;
    $tablaHTML .= '</div>';
  } catch (PDOException $ex) {
    $errorMessage = $ex->getMessage();
  } catch (Exception $ex) {
    $errorMessage = $ex->getMessage();

  } finally {
      $conmy = null;
  }

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Actividades | GPEM SAC</title>
  <link rel="shortcut icon" href="/mycloud/logos/favicon.ico">
  <link rel="stylesheet" href="/mycloud/library/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="/mycloud/library/SweetAlert2/css/sweetalert2.min.css">
  <link rel="stylesheet" href="/mycloud/library/bootstrap-5.1.0-dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="/mycloud/library/select-gpem-1.0/css/select-gpem-1.0.css">
</head>
<body>
  <input type="hidden" id="idInforme" value="<?php echo ($Id); ?>">
	<div class="container">
      <div class="row border-bottom mb-3 fs-5">
        <div class="col-12 fw-bold d-flex justify-content-between">
          <p class="m-0 p-0 text-secondary"><?php echo $isAuthorized ? ($ClienteNombre): 'No Autorizado'; ?></p>
            <input type="text" class="d-none" value="" readonly/>
          <p class="m-0 p-0 text-center text-secondary"><?php echo $isAuthorized ? ($Nombre) : 'No Autorizado'; ?></p>
        </div>
      </div>
    <div class="row">
      <div class="col-12">
        <nav style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item fw-bold"><a href="/informes/datoGeneral.php?id=<?php echo ($Id) ?>" class="text-decoration-none">INFORME</a></li>
            <li class="breadcrumb-item fw-bold"><a href="/informes/datoEquipo.php?id=<?php echo ($Id) ?>" class="text-decoration-none">EQUIPO</a></li>
            <li class="breadcrumb-item fw-bold"><a href="/informes/resumen.php?id=<?php echo ($Id) ?>" class="text-decoration-none">RESUMEN</a></li>
            <li class="breadcrumb-item active fw-bold" aria-current="page">ACTIVIDAD</li>
            <li class="breadcrumb-item fw-bold"><a href="/informes/anexos.php?id=<?php echo ($Id) ?>" class="text-decoration-none">ANEXOS</a></li>
          </ol>
        </nav>
      </div>
    </div>
    <?php if ($isAuthorized): ?>
      <div class="row mb-1 border-bottom">
        <div class="col-5 col-lg-2 mb-2">
            <button type="button" class="btn btn-outline-primary form-control text-uppercase" data-bs-toggle="modal" data-bs-target="#modalNuevaActividad"><i class="bi bi-plus-lg"></i> Agregar</button>
        </div>
      </div>    
    <?php endif ?>
		<div class="row">
			<div class="col-12">
        <?php
          echo $tablaHTML;
        ?>
      </div>
		</div>

    <!-- START AGREGAR ACTIVIDAD - M O D A L -->
    <div class="modal fade" id="modalNuevaActividad" tabindex="-1" aria-labelledby="modalNuevaActividadLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title fs-5 text-uppercase" id="modalNuevaActividadLabel">Crear Actividad</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="guardarActividadInput" value="<?php echo $Id ?>">
            <div class="row">
              <div class="col-12">
                <label for="guardarNombreActividadInput" class="form-label mb-0">Nombre de la Actividad</label>
                <textarea type="text" name="actividad" class="form-control" id="guardarNombreActividadInput" row=3 placeholder="Ingresar nombre de actividad."></textarea>
              </div>
              <div class="col-12 mt-2">
                <label for="guardarDiagnosticoInput" class="form-label mb-0">Diagnóstico</label>
                <textarea type="text" name="diagnostico" class="form-control" ro=3 id="guardarDiagnosticoInput" placeholder="Ingresar diagnositico."></textarea>
              </div>
              <div class="col-12 mt-2">
                <label for="guardarTrabajoInput" class="form-label mb-0">Trabajos</label>
                <textarea type="text" name="trabajo" class="form-control" id="guardarTrabajoInput" row=3 placeholder="Ingresar diagnositico."></textarea>
              </div>
              <div class="col-12 mt-2">
                <label for="guardarObservacionInput" class="form-label mb-0">Observación</label>
                <textarea type="text" name="observacion" class="form-control" id="guardarObservacionInput" row=3 placeholder="Ingresar observación."></textarea>
              </div>
              <div class="col-6 col-lg-3 mt-2">
                <button id="guardarActividad" class="btn btn-primary text-uppercase pt-2 pb-2 col-12" onclick="fnCrearActividad()" ><i class="bi bi-floppy"></i> Guardar</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div><!-- END GUARDAR ACTIVIDAD - M O D A L -->

    <!-- START AGREGAR SUBACTIVIDAD - M O D A L -->
    <div class="modal fade" id="modalNuevaSubActividad" tabindex="-1" aria-labelledby="modalNuevaSubActividadLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title fs-5 text-uppercase" id="modalNuevaSubActividadLabel">Crear SubActividad</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="guardarSubActividadInput" value="<?php echo $Id ?>">
            <input type="hidden" id="cabeceraIdInput">
            <div class="row">
              <div class="col-12">
                <label for="guardarNombreSubActividadInput" class="form-label mb-0">Nombre de la Actividad</label>
                <textarea type="text" name="actividad" class="form-control" id="guardarNombreSubActividadInput" row=3 placeholder="Ingresar nombre de subactividad."></textarea>
              </div>
              <div class="col-12 mt-2">
                <label for="guardarDiagnosticoSubActividad" class="form-label mb-0">Diagnóstico</label>
                <textarea type="text" name="diagnostico" class="form-control" ro=3 id="guardarDiagnosticoSubActividadInput" placeholder="Ingresar diagnositico."></textarea>
              </div>
              <div class="col-12 mt-2">
                <label for="guardarTrabajoSubActividadInput" class="form-label mb-0">Trabajos</label>
                <textarea type="text" name="trabajo" class="form-control" id="guardarTrabajoSubActividadInput" row=3 placeholder="Ingresar diagnositico."></textarea>
              </div>
              <div class="col-12 mt-2">
                <label for="guardarObservacionSubActividadInput" class="form-label mb-0">Observación</label>
                <textarea type="text" name="observacion" class="form-control" id="guardarObservacionSubActividadInput" row=3 placeholder="Ingresar observación."></textarea>
              </div>
              <div class="col-6 mt-2">
                <button id="guardarSubActividad" class="btn btn-primary text-uppercase pt-2 pb-2 col-12" onclick="fnGuardarSubActividad()" ><i class="bi bi-floppy"></i> Guardar</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div><!-- END GUARDAR ACTIVIDAD - M O D A L -->

    <!-- START EDITAR ACTIVIDAD - M O D A L -->
    <div class="modal fade" id="modalEditarActividad" tabindex="-1" aria-labelledby="modalEditarActividadLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title fs-5 text-uppercase" id="modalEditarActividadLabel">Editar Actividad</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="editarActividadInput">
            <div class="row">
              <div class="col-12">
                <label for="editarNombreActividadInput" class="form-label mb-0">Nombre de la Actividad</label>
                <textarea type="text" name="actividad" class="form-control" id="editarNombreActividadInput" row=3></textarea>
              </div>
              <div class="col-12 mt-2">
                <label for="editarDiagnosticoInput" class="form-label mb-0">Diagnóstico</label>
                <textarea type="text" name="diagnostico" class="form-control" ro=3 id="editarDiagnosticoInput"></textarea>
              </div>
              <div class="col-12 mt-2">
                <label for="editarTrabajoInput" class="form-label mb-0">Trabajos</label>
                <textarea type="text" name="trabajo" class="form-control" id="editarTrabajoInput" row=3></textarea>
              </div>
              <div class="col-12 mt-2">
                <label for="editarObservacionInput" class="form-label mb-0">Observación</label>
                <textarea type="text" name="observacion" class="form-control" id="editarObservacionInput" row=3></textarea>
              </div>
              <div class="col-6 mt-2">
                <button id="editarActividadBtn" class="btn btn-primary text-uppercase pt-2 pb-2 col-12" onclick="FnModificarActividad()"><i class="bi bi-pencil-square"></i> Editar</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div><!-- END EDITAR ACTIVIDAD - M O D A L -->
         
    <!-- START IMAGENES - M O D A L -->
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
                <input type="text" class="form-control" id="txtTitulo">
              </div>
              <div class="col-12 mb-2">
                <label class="form-label mb-0">Descripción</label>
                <input type="text" class="form-control" id="txtDescripcion">
              </div>                        
              <div class="col-12">
                <label for="adjuntarImagenInput" class="form-label mb-0">Imagen</label>
                <input id="fileImagen" type="file" accept="image/*,.pdf" class="form-control mb-2"/>
              </div>
              <div class="col-12 m-0">
                  <div class="col-md-12 text-center" id="divImagen"><i class="fas fa-images fs-2"></i></div>
              </div>
            </div>
          </div>
          <div id="msjAgregarImagen" class="modal-body pt-1"></div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary text-uppercase pt-2 pb-2 col-12" onclick="FnAgregarImagen(); return false;"><i class="bi bi-floppy"></i>  Guardar</button>
          </div>
        </div>
      </div>
    </div><!-- END IMAGENES - M O D A L -->
	</div>

  <div class="container-loader-full">
    <div class="loader-full"></div>
  </div>

  <script src="js/actividad.js"></script>
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