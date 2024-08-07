<?php
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";
  require_once 'Datos/InformesData.php';

  $Id = isset($_GET['id']) ? $_GET['id'] : '';
  $Cliid = 2;
  $isAuthorized = false;
  $errorMessage = ''; 

  $NUMERO=1;
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
  
	function FnGenerarInformeHtmlAcordeon($arbol, $imagenes,$numero, $nivel = 0, $indice ='1') {
		$html='';
		$contador=1;		

		foreach ($arbol as $key=>$nodo) {
      //ASIGNANDO VALOR A NODOGLOBAL
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

  function obtenerInforme($conmy, $Id, $Cliid) {
    $stmt = $conmy->prepare("SELECT id, ordid, equid, cliid, numero, nombre, fecha, ord_nombre, cli_nombre, cli_contacto, ubicacion, supervisor, equ_codigo, equ_nombre, equ_marca, equ_modelo, equ_serie, equ_datos, equ_km, equ_hm, actividad, estado FROM tblinforme WHERE id = :Id AND cliid = :Cliid;");
    $stmt->execute([':Id' => $Id, ':Cliid' => $Cliid]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  function obtenerDetallesInforme($conmy, $id) {
    $stmt = $conmy->prepare("SELECT id, ownid, tipo, actividad, diagnostico, trabajos, observaciones FROM tbldetalleinforme WHERE infid = :InfId;");
    $stmt->bindParam(':InfId', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  function obtenerArchivos($conmy, $ids) {
    $placeholders = implode(',', array_fill(0, count($ids), '?')); 
    $stmt = $conmy->prepare("SELECT id, refid, nombre, descripcion, titulo FROM tblarchivos WHERE refid IN ($placeholders) AND tabla = ? AND tipo = ?");
    $params = array_merge($ids, ['INFD', 'IMG']); 
    $stmt->execute($params);
    $imagenes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $imagenes[$row['refid']][] = [
          'id' => (int)$row['id'],
          'nombre' => $row['nombre'],
          'descripcion' => $row['descripcion'],
          'titulo' => $row['titulo']
        ];
    }
    return $imagenes;
  }

  try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (is_numeric($Id) && $Id > 0) {
      $informe = obtenerInforme($conmy, $Id, $Cliid);
      if ($informe) {
        $isAuthorized = true;
        $actividadesDetalles = obtenerDetallesInforme($conmy, $Id);
        $actividades = [];
        $conclusiones = [];
        $recomendaciones = [];
        $antecedentes = [];
        foreach ($actividadesDetalles as $dato) {
          switch ($dato['tipo']) {
            case 'act':
              $actividades[] = [
                  'id' => $dato['id'],
                  'ownid' => $dato['ownid'],
                  'tipo' => $dato['tipo'],
                  'actividad' => $dato['actividad'],
                  'diagnostico' => $dato['diagnostico'],
                  'trabajos' => $dato['trabajos'],
                  'observaciones' => $dato['observaciones'],
              ];
              break;
            case 'con':
              $conclusiones[] = ['actividad' => $dato['actividad']];
                break;
            case 'rec':
              $recomendaciones[] = ['actividad' => $dato['actividad']];
              break;
            case 'ant':
              $antecedentes[] = ['actividad' => $dato['actividad']];
              break;
          }
        }
        $arbol = construirArbol($actividades);
        $ids = array_column($actividades, 'id');
        $imagenes = obtenerArchivos($conmy, $ids);
      }else{
        $errorMessage = 'Informe no encontrado o acceso denegado';
      }
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
    .hijos p:first-child{
      padding-top: 10px;
      padding-left: 10px;
    }
    </style>
  <body>
      <!-- INICIO CONTAINER -->
      <div class="container">

        <div class="row mb-3 mt-3">
          <div class="col-12 btn-group" role="group" aria-label="Basic example">
            <a href="/informes/buscarInforme.php" class="col-4">
              <button type="button" class="btn btn-outline-primary col-12 fw-bold d-flex flex-column align-items-center" style="border-radius:0"><i class="bi bi-list-task"></i><span class="text-button"> Informes</span></button>
            </a>
            <a href="/informes/datoGeneral.php?id=<?php echo ($Id);?>" class="col-4">
              <button type="button" class="btn btn-outline-primary col-12 fw-bold d-flex flex-column align-items-center" style="border-radius:0; border-left:0"><i class="bi bi-pencil-square"></i><span class="text-button"> Editar</span></button>
            </a>
            <a href="#" class="col-4">
              <button type="button" class="btn btn-outline-primary col-12 fw-bold d-flex flex-column align-items-center" style="border-radius:0; border-left:0"><i class="bi bi-check-square"></i><span class="text-button"> Finalizar</span></button>
            </a>
          </div>
        </div>

        <!-- NOMBRE DE CLIENTE E INFORME -->
        <div class="row border-bottom mb-2 fs-5">
          <div class="col-12 fw-bold d-flex justify-content-between">
            <p class="m-0 p-0 text-secondary"><?php echo $isAuthorized ? $informe['cli_nombre'] : 'No autorizado' ; ?></p>
            <input type="text" class="d-none" id="txtId" value="">
            <p class="m-0 p-0 text-center text-secondary"><?php echo $isAuthorized ? $informe['nombre'] : 'No autorizado' ; ?></p>
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
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe['numero']  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Nombre Informe</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe['nombre']  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Fecha</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo   $informe['fecha']  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">OT N°</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe['ord_nombre']  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Nombre de cliente:</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe['cli_nombre']  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Contacto</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe['cli_contaco']  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Lugar</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe['ubicacion']  ; ?></p>
            </div>
            <div class="col-6 col-sm-8 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Supervisor</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe['supervisor']  ; ?></p>
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
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe['equ_nombre']  ; ?></p>              
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Modelo Equipo</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe['equ_modelo']   ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Serie Equipo</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe['equ_serie']  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Marca Equipo</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe['equ_marca']  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Kilometraje</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe['equ_km']  ; ?></p>
            </div>
            <div class="col-6 col-sm-4 col-lg-4 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Horas Motor</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe['equ_hm'] ; ?></p>
            </div>
            <div class="col-12 col-lg-6 mb-1">
              <p class="m-0 text-secondary fw-light" style="font-size: 15px;">Carateristicas</p> 
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold"><?php echo  $informe['equ_datos']  ; ?></p>
            </div>
            <div class="row m-0 mt-2 mb-2 p-0 d-flex justify-content-evenly">
              <?php foreach($archivos as $archivo): ?>
                <div class="col-5 col-lg-4 col-xl-3 border border-secondary border-opacity-50">
                  <p class="text-center text-uppercase mt-4 mb-1"><?php echo ($archivo['titulo']); ?></p>
                  <img src="/mycloud/files/<?php echo ($archivo['nombre']); ?>" class="img-fluid" alt="">
                  <p class="text-center text-uppercase"><?php echo ($archivo['descripcion']); ?></p>
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
              <p class="m-0 p-0 text-secondary text-uppercase fw-bold" style="text-align: justify;"><?php echo  $informe['actividad']  ; ?></p>          
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
            <!-- ACÁ VAN LOS ANEXOS -->
          </div>
        </div>
        
        <!-- ESTADO -->
        <div class="row p-1 mb-2 mt-2">
          <div class="col-12 mb-0">
            <p class="mt-2 mb-2 text-secondary fw-bold">ESTADO</p>
          </div>
          <div class="p-1 mb-2">
            <?php
              $Estado = 1; 
              $EstadoClass = ($Estado == 1) ? 'bg-primary' : ($Estado == 2 ? 'bg-success' : 'bg-danger');
              $EstadoText = ($Estado == 1) ? 'Abierto' : ($Estado == 2 ? 'Cerrado' : 'Anulado');
            ?>
            <div class="col-2">
              <p class="text-white text-center rounded rounded-1 pt-1 pb-1 <?php echo $EstadoClass; ?>"><?php echo ($EstadoText); ?></p>
            </div>            
          </div>
        </div>
        <?php endif ?>
      </div><!-- CIERRE CONTAINER -->
    <script src="js/vistaPreliminar.js"></script>
    <script src="/mycloud/library/bootstrap-5.1.0-dist/js/bootstrap.min.js"></script>
    <script src="/mycloud/library/SweetAlert2/js/sweetalert2.all.min.js"></script>
    <?php if ($errorMessage): ?>
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo addslashes($errorMessage); ?>',
            // timer: 2000
          });
        });
      </script>
    <?php endif; ?>
  </body>
</html>