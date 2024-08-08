<?php
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once 'Datos/InformesData.php';

  //$Id = isset($_GET['id']) ? $_GET['id'] : '';
  $Id = $_GET['id'];
  $Cliid = 2;
  $isAuthorized = false;
  $errorMessage = ''; 
  $Estado=0;
  $NUMERO=1;
  $Nombre='';
  $ClienteNombre='';
	$tablaHTML ='';

	function construirArbol($registros, $padreId = 0) {
		$arbol = array();
		foreach ($registros as $registro) {
			if ($registro['ownid'] == $padreId) {
				$hijos = construirArbol($registros, $registro['id']);
				if (!empty($hijos)) {
					$registro['hijos'] = $hijos;
				}
        if ($padreId != 0) {
          $registro['actividad'] = strtoupper($registro['actividad']);
          $registro['diagnostico'] = strtoupper($registro['diagnostico']);
          $registro['trabajos'] = strtoupper($registro['trabajos']);
          $registro['observaciones'] = strtoupper($registro['observaciones']);
        }
        					
				$arbol[] = $registro;
			}
		}			
		return $arbol;
	}
  
	function FnGenerarInformeHtmlAcordeon($arbol, $imagenes,$numero, $nivel = 0, $indice ='1') {
		$html='';
		$contador=1;		

		foreach ($arbol as $key=>$nodo) {
			$indiceActual = $nivel==0?$contador++:$indice.'.'.($key+1);
			$html.='
            <div class="col-12 mb-0 border-bottom bg-light">
              <p class="mt-2 mb-2 fw-bold text-secondary">'.$numero.'.'.$indiceActual.' - '.$nodo['actividad'].'</p>
            </div>
						<div class="row p-1 m-0 border border-opacity-10">
							<div class="col-12 mb-1">
                <label class="form-label mb-0">Diagnóstico</label>
                <p class="mb-0 text-secondary text-uppercase fw-bold" style="font-size=15px" id="diagnostico-'.$nodo['id'].'">'.$nodo['diagnostico'].'</p>
              </div>
							<div class="col-12 mb-1">
                <label class="form-label mb-0">Trabajos</label>
                <p class="mb-0 text-secondary text-uppercase fw-bold" style="font-size=15px" id="trabajo-'.$nodo['id'].'">'.$nodo['trabajos'].'</p>
              </div>
							<div class="col-12 mb-1">
                <label class="form-label mb-0">Observaciones</label>
                <p class="mb-0 text-secondary text-uppercase fw-bold" style="font-size=15px" id="observacion-'.$nodo['id'].'">'.$nodo['observaciones'].'</p>
              </div>
						  <div class="row m-0 mt-2 mb-2 p-0 d-flex justify-content-evenly" id="'.$nodo['id'].'">';
							  if(isset($imagenes[$nodo['id']])){
								  foreach($imagenes[$nodo['id']] as $elemento){
									  $html.='
                    <div class="col-5 col-lg-4 col-xl-3 border border-secondary border-opacity-50" id="archivo-'.$elemento['id'].'">
                      <p class="text-center text-uppercase mt-4 mb-1">'.$elemento['titulo'].'</p>
                        <img src="/mycloud/files/'.$elemento['nombre'].'" class="img-fluid" alt="">
                      <p class="text-center text-uppercase">'.$elemento['descripcion'].'</p>
                    </div>';
								  }
							  }
			$html.='</div>';
			if (!empty($nodo['hijos'])) {
				$html.='<div class="p-0 hijos">';
				$html.=FnGenerarInformeHtmlAcordeon($nodo['hijos'], $imagenes,$numero, $nivel+1, $indiceActual);
				$html.='</div>';
			}
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
    
    $informe = FnBuscarInformeMatriz($conmy, $Id, $Cliid);
    if (!empty($informe) && $informe->estado != 3) {
      $isAuthorized = true;
      $Nombre = $informe->nombre;
      $ClienteNombre = $informe->clinombre;
      $Estado = $informe->estado;
      $archivos = FnBuscarArchivos($conmy, $Id);
      $datos = FnBuscarActividades($conmy, $Id);
      if (!empty($datos)) {
        $actividades = array();
        $conclusiones = array();
        $recomendaciones = array();
        $antecedentes = array();
        
        foreach ($datos as $dato) {
          if ($dato['tipo'] == 'act') {
            $actividades[] = array(
              'id' => $dato['id'],
              'ownid' => $dato['ownid'],
              'tipo' => $dato['tipo'],
              'actividad' => $dato['actividad'],
              'diagnostico' => $dato['diagnostico'],
              'trabajos' => $dato['trabajos'],
              'observaciones' => $dato['observaciones'],
            );
          } else if ($dato['tipo'] == 'con') {
            $conclusiones[] = array('actividad' => $dato['actividad']);
          } else if ($dato['tipo'] == 'rec') {
            $recomendaciones[] = array('actividad' => $dato['actividad']);
          } else if ($dato['tipo'] == 'ant') {
            $antecedentes[] = array('actividad' => $dato['actividad']);
          }    
        };

        $imagenInformes = array();
        $imagenAnexos = array();
        foreach ($archivos as $archivo) {
          if ($archivo['tabla'] == "INF") {
            $imagenInformes[] = array(
              'titulo' => $archivo['titulo'],
              'nombre' => $archivo['nombre'],
              'descripcion' => $archivo['descripcion'],
            ); 
          } else if ($archivo['tabla'] == "INFA") {
            $imagenAnexos[] = array(
              'titulo' => $archivo['titulo'],
              'nombre' => $archivo['nombre'],
              'descripcion' => $archivo['descripcion'],
            );
          }
        };
        $arbol = construirArbol($actividades);
        $ids = array_map(function($elemento) {
          return $elemento['id'];
        }, $actividades);
        
        if (count($ids) > 0) {
          $placeholders = implode(',', array_fill(0, count($ids), '?'));
          $imagenes = array();

          $stmt3 = $conmy->prepare("SELECT id, refid, nombre, descripcion, titulo FROM tblarchivos WHERE refid IN ($placeholders) AND tabla = ? AND tipo = ?");
          $params = array_merge($ids, ['INFD', 'IMG']);
          $stmt3->execute($params);

          while ($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)) {
            $imagenes[$row3['refid']][] = array(
              'id' => (int)$row3['id'],
              'nombre' => $row3['nombre'],
              'descripcion' => $row3['descripcion'],
              'titulo' => $row3['titulo'],
            );
          }
        }
      }
    }
  } catch (PDOException $ex) {
      $errorMessage = $ex->getMessage();
  } catch (Exception $e) {
      $errorMessage = $e->getMessage();
  } finally {
      $conmy = null;
  }

  $claseHabilitado = "btn-outline-secondary";
  $atributoHabilitado = " disabled";
  if($Estado == 1){
      $claseHabilitado = "btn-outline-primary";
      $atributoHabilitado = "";
  }
?>
<!doctype html>
<html lang="es">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vista Preliminar | GPEM SAC</title>
    <link rel="shortcut icon" href="/mycloud/logos/favicon.ico">
    <link rel="stylesheet" href="/mycloud/library/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/mycloud/library/SweetAlert2/css/sweetalert2.min.css">
    <link rel="stylesheet" href="/mycloud/library/bootstrap-5.1.0-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/mycloud/library/select-gpem-1.0/css/select-gpem-1.0.css">
  </head>
  <style>
    .hijos p:first-child{ padding-top: 10px;padding-left: 10px; }
    </style>
  <body>
    <!-- INICIO CONTAINER -->
    <div class="container">

      <div class="row mb-3 mt-3">
        <div class="col-12 btn-group" role="group" aria-label="Basic example">
          <button type="button" class="btn btn-outline-primary fw-bold" onclick="FnListarInformes(); return false;"><i class="fas fa-list"></i><span class="d-none d-sm-block"> Informes</span></button>
          <button type="button" class="btn btn-outline-primary fw-bold" <?php echo !$isAuthorized ? 'disabled' : ''; ?> onclick="FnEditarInforme(); return false;"><i class="fas fa-edit"></i><span class="d-none d-sm-block"> Editar</span></button>
          <button type="button" class="btn btn-outline-primary fw-bold" <?php echo !$isAuthorized ? 'disabled' : ''; ?> onclick="FnModalFinalizarInforme(); return false;"><i class="fas fa-check-square"></i><span class="d-none d-sm-block"> Finalizar</span></button>
        </div>
      </div>

      <!-- NOMBRE DE CLIENTE E INFORME -->
      <div class="row border-bottom mb-2 fs-5">
        <div class="col-12 fw-bold d-flex justify-content-between">
          <p class="m-0 p-0 text-secondary"><?php echo $isAuthorized ? $ClienteNombre : ''; ?></p>
          <input type="text" class="d-none" id="idInforme" value="<?php echo $Id; ?>">
          <p class="m-0 p-0 text-center text-secondary"><?php echo $isAuthorized ? $Nombre :'' ; ?></p>
        </div>
      </div>

      <?php if ($isAuthorized): ?>

        <!-- BOTON DESCARGAR INFORME -->
        <div class="row">
          <div id="generarInforme" class="col-6 col-lg-3 mt-4 mb-4">
            <button id="guardarActividad" class="btn bg-primary bg-gradient text-uppercase pt-2 pb-2 col-12 text-white fw-bold" onclick=fnGenerarInforme()><i class="bi bi-cloud-download"></i> Descargar </button>
          </div>
        </div>

        <!-- DATOS GENERALES -->
        <div class="row p-1 mb-2 mt-2">
          <div class="col-12 m-0 border-bottom bg-light" >
            <p class="mt-2 mb-2 fw-bold text-secondary"><?php echo $NUMERO; ?>- DATOS GENERALES</p>
          </div>
          <div class="row p-1 m-0 border border-opacity-10">
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Nro. Informe</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe->numero  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Nombre Informe</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe->nombre  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Fecha</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo   $informe->fecha  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">OT N°</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe->ordnombre  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Nombre de cliente:</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe->clinombre  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Contacto</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe->clicontacto  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Lugar</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe->clidireccion  ; ?></p>
            </div>
            <div class="col-6 col-sm-8 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Supervisor</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe->supervisor  ; ?></p>
            </div>
          </div>
        </div>
        <?php $NUMERO+=1; ?>
          
        <!-- DATOS DEL EQUIPO -->
        <div class="row p-1 mb-2 mt-2">
          <div class="col-12 mb-0 border-bottom bg-light">
            <p class="mt-2 mb-2 fw-bold text-secondary"><?php echo $NUMERO; ?>- DATOS DEL EQUIPO</p>
          </div>
          <div class="row p-1 m-0 border border-opacity-10">
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Nombre Equipo</p>
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe->equnombre  ; ?></p>              
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Modelo Equipo</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe->equmodelo   ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Serie Equipo</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe->equserie  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Marca Equipo</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe->equmarca  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Kilometraje</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe->equkm  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Horas Motor</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe->equhm ; ?></p>
            </div>
            <div class="col-12 col-lg-6 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Carateristicas</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe->equdatos  ; ?></p>
            </div>
            <div class="row m-0 mt-2 mb-2 p-0 d-flex justify-content-evenly">
              <?php foreach($imagenInformes as $imagenInforme): ?>
                <div class="col-5 col-lg-4 col-xl-3 border border-secondary border-opacity-50">
                  <p class="text-center text-uppercase mt-4 mb-1"><?php echo ($imagenInforme['titulo']); ?></p>
                  <img src="/mycloud/files/<?php echo ($imagenInforme['nombre']); ?>" class="img-fluid" alt="">
                  <p class="text-center text-uppercase"><?php echo ($imagenInforme['descripcion']); ?></p>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <?php $NUMERO+=1; ?>

        <!-- SOLICITUD DEL CLIENTE-->
        <div class="row p-1 mb-2 mt-2">
          <div class="col-12 mb-0 border-bottom bg-light">
            <p class="mt-2 mb-2 fw-bold text-secondary"><?php echo $NUMERO; ?>- SOLICITUD DEL CLIENTE</p>
          </div>
          <div class="row p-1 m-0 border border-opacity-10">
            <div class="col-12 mb-2 mt-2">
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold" style="text-align: justify;"><?php echo  $informe->actividad  ; ?></p>          
            </div>
          </div>
        </div>
        <?php $NUMERO+=1; ?>

        <!-- ANTECEDENTES-->
        <div class="row p-1 mb-2 mt-2">
          <div class="col-12 mb-0 border-bottom bg-light">
            <p class="mt-2 mb-2 fw-bold  text-secondary"><?php echo $NUMERO; ?>- ANTECEDENTES</p>
          </div>
          <div class="row p-1 m-0 border border-opacity-10">
            <?php foreach($antecedentes as $antecedente) :?>
                <div class="d-flex">
                  <i class="bi bi-stop" style="margin-right:10px"></i>
                  <p class="m-0 p-0 text-secondary text-uppercase fw-bold" style="text-align: justify;"><?php echo $antecedente['actividad'];?></p>
                </div>
            <?php endforeach ;?>
          </div>
        </div>
        <?php $NUMERO+=1; ?>
    
        <!-- ACTIVIDADES -->
        <div class="row p-1 mb-2 mt-2">
          <div class="col-12 mb-0 border-bottom bg-light">
            <p class="mt-2 mb-2 fw-bold text-secondary"><?php echo $NUMERO; ?>- ACTIVIDADES</p>
          </div>
            <?php
              $html = FnGenerarInformeHtmlAcordeon($arbol, $imagenes,$NUMERO);
              echo $html;
            ?>
        </div>
        <?php $NUMERO+=1; ?>

        <!-- CONCLUSIONES -->
        <div class="row p-1 mb-2 mt-2">
          <div class="col-12 mb-0 border-bottom bg-light">
            <p class="mt-2 mb-2 fw-bold  text-secondary"><?php echo $NUMERO; ?>- CONCLUSIONES</p>
          </div>
          <div class="row p-1 m-0 border border-opacity-10">
            <?php foreach($conclusiones as $conclusion) :?>
                <div class="d-flex">
                  <i class="bi bi-stop" style="margin-right:10px"></i>
                  <p class="m-0 p-0 text-secondary text-uppercase fw-bold" style="text-align: justify;"><?php echo $conclusion['actividad'];?></p>
                </div>
            <?php endforeach ;?>
          </div>
        </div>
        <?php $NUMERO+=1; ?>

        <!-- RECOMENDACIONES -->
        <div class="row p-1 mb-2 mt-2">
          <div class="col-12 mb-0 border-bottom bg-light">
            <p class="mt-2 mb-2 fw-bold  text-secondary"><?php echo $NUMERO; ?>- RECOMENDACIONES</p>
          </div>
          <div class="row p-1 m-0 border border-opacity-10">
            <?php foreach($recomendaciones as $recomendacion) :?>
              <div class="d-flex">
              <i class="bi bi-stop" style="margin-right:10px"></i> 
                <p class="m-0 p-0 text-secondary text-uppercase fw-bold" style="text-align: justify;"><?php echo $recomendacion['actividad'];?></p>
              </div>
            <?php endforeach ;?>
          </div>
        </div>
        <?php $NUMERO+=1; ?>

        <!-- ANEXOS -->
        <div class="row p-1 mb-2 mt-2">
          <div class="col-12 mb-0 border-bottom bg-light">
            <p class="mt-2 mb-2 fw-bold  text-secondary"><?php echo $NUMERO; ?>- ANEXOS</p>
          </div>
          <div class="row p-1 m-0 border border-opacity-10">
            <div class="row m-0 mt-2 mb-2 p-0 d-flex justify-content-evenly">
              <?php foreach($imagenAnexos as $imagenAnexo): ?>
                  <div class="col-12 col-md-10 col-xl-8 mb-4 border border-secondary border-opacity-50">
                    <p class="text-center text-uppercase mt-4 mb-1"><?php echo ($imagenAnexo['titulo']); ?></p>
                    <img src="/mycloud/files/<?php echo ($imagenAnexo['nombre']); ?>" class="img-fluid" alt="">
                    <p class="text-center text-uppercase"><?php echo ($imagenAnexo['descripcion']); ?></p>
                  </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
          
        <!-- ESTADO -->
        <div class="row p-1 mb-2 mt-2">
          <div class="col-12 mb-0">
            <p class="mt-2 mb-2 text-secondary fw-bold">ESTADO</p>
          </div>
          <div class="col-12 p-1 mb-2">
            <?php 
              $estadoClass = $informe->estado == 1 ? 'bg-secondary' : ($informe->estado == 2 ? 'bg-primary' : ($informe->estado == 3 ? 'bg-success' : 'bg-light'));
              $estadoText = $informe->estado == 1 ? 'Anulado' : ($informe->estado == 2 ? 'Abierto' : ($informe->estado == 3 ? 'Cerrado' : 'Desconocido'));
            ?>
            <span class="p-2 text-white <?php echo $estadoClass; ?>"  Style="border-radius:4px">
              <?php echo $estadoText; ?>
            </span>
          </div>
        </div>
        <div class="modal fade" id="modalFinalizarInforme" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Finalizar Órden de Trabajo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>                
              <div class="modal-body pb-1">
                <div class="row text-center fw-bold pt-3">                        
                  <p class="text-center">Para finalizar la Órden <?php echo $Ot;?> haga clic en el botón CONFIRMAR.</p>                    
                </div>
              </div>
              <div class="modal-body pt-1" id="msjFinalizarInforme"></div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="FnFinalizarInforme(); return false;">CONFIRMAR</button>
              </div>              
            </div>
          </div>
        </div>

      <?php endif ?>

    </div><!-- CIERRE CONTAINER -->
  </body>
  <script src="js/informe.js"></script>
    <script src="/mycloud/library/bootstrap-5.1.0-dist/js/bootstrap.min.js"></script>
    <script src="/mycloud/library/SweetAlert2/js/sweetalert2.all.min.js"></script>
    <?php if ($errorMessage): ?>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo addslashes($errorMessage); ?>',
            timer: 1000,
          });
        });
      </script>
    <?php endif; ?>
</html>