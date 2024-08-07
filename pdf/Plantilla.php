<?php
	include($_SERVER['DOCUMENT_ROOT'].'/gesman/connection/ConnGesmanDb.php');

	$Id=$_GET['informe'];
	$Nombre='';
	$fecha='';
	$OrdNombre='';
	$CliNombre='';
	$CliContacto='';
	$Ubicacion='';
	$Supervisor='';
	$EquCodigo='';
	$EquNombre='';
	$EquMarca='';
	$EquModelo='';
	$EquSerie='';
	$EquDatos='';
	$EquKm='';
	$EquHm='';
	$Actividad='';

	$data=array('informe'=>array(), 'actividades'=>array(), 'imagenes'=>array());
	
	//$Antecedentes='';
	//$Diagnostico='';
	//$Conclusiones='';
	//$Recomendaciones='';


		require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";

		//if(!empty($_GET['informe'])){

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
		
		function FnGenerarInformeHtml($arbol, $imagenes, $nivel = 0, $indice ='1') {
			$html='';
			$contador=1;		

			foreach ($arbol as $key=>$nodo) {
				$indiceActual = $nivel==0?$contador++:$indice.'.'.($key+1);
				$html.='<tr><td colspan="2" style="border: black 1px solid">'.$indiceActual.' - '.$nodo['actividad'].'</td></tr>';
				
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

				$html.='<tr><td colspan="2" style="border: black 1px solid">';
            	$html.='<table width="100%" style="border: #b2b2b2 1px solid">';

				if (!empty($nodo['hijos'])) {
					$html.=FnGenerarInformeHtml($nodo['hijos'], $imagenes, $nivel+1, $indiceActual);
				}
	
				$html.='</table>';
	            $html.='</td></tr>';
			}
			return $html;
		}
		
			try{
				$conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				
				$stmt=$conmy->prepare("select id, nombre, fecha, ord_nombre, cli_nombre, cli_contacto, ubicacion, supervisor, equ_codigo, equ_nombre, equ_marca, equ_modelo, equ_serie, equ_datos, equ_km, equ_hm, actividad from tblinforme where id=:Id;");
				$stmt->execute(array('Id'=>$Id));
				$row=$stmt->fetch();
				if($row){
					$data['informe']['Id'] = $row['id'];
					$data['informe']['Nombre'] = $row['nombre'];
					$data['informe']['Fecha'] = $row['fecha'];
					$data['informe']['OrdNombre'] = $row['ord_nombre'];
					$data['informe']['CliNombre'] = $row['cli_nombre'];
					$data['informe']['CliContacto'] = $row['cli_contacto'];
					$data['informe']['Ubicacion'] = $row['ubicacion'];
					$data['informe']['Supervisor'] = $row['supervisor'];
					$data['informe']['EquCodigo'] = $row['equ_codigo'];
					$data['informe']['EquNombre'] = $row['equ_nombre'];
					$data['informe']['EquMarca'] = $row['equ_marca'];
					$data['informe']['EquModelo'] = $row['equ_modelo'];
					$data['informe']['EquSerie'] = $row['equ_serie'];
					$data['informe']['EquDatos'] = $row['equ_datos'];
					$data['informe']['EquKm'] = $row['equ_km'];
					$data['informe']['EquHm'] = $row['equ_hm'];
					$data['informe']['Actividad'] = $row['actividad'];
				}

				$stmt2 = $conmy->prepare("select id, ownid, tipo, actividad, diagnostico, trabajos, observaciones from tbldetalleinforme where infid=:InfId;");
				$stmt2->bindParam(':InfId', $data['informe']['Id'], PDO::PARAM_INT);
				$stmt2->execute();
				$actividades = $stmt2->fetchAll(PDO::FETCH_ASSOC);

				$arbol = construirArbol($actividades);

				$ids = array_map(function($elemento) {
					return $elemento['id'];
				}, $actividades);

				$cadenaIds = implode(',', $ids);

				$stmt3 = $conmy->prepare("select id, refid, nombre, descripcion from tblarchivos where refid IN(".$cadenaIds.") and tabla=:Tabla and tipo=:Tipo;");				
				$stmt3->execute(array(':Tabla'=>'INF', ':Tipo'=>'IMG'));
				//$imagenes = $stmt3->fetchAll(PDO::FETCH_ASSOC);
				while($row3=$stmt3->fetch(PDO::FETCH_ASSOC)){
					$imagenes[$row3['refid']][]=array(
						'id'=>(int)$row3['id'],
						'nombre'=>$row3['nombre'],
						'descripcion'=>$row3['descripcion']
					);
				}

				//echo '<pre>';
				//print_r($imagenes);
				$tablaHTML ='';
				$tablaHTML .='<table width="100%" style="border: #b2b2b2 1px solid">';
					$tabla=FnGenerarInformeHtml($arbol, $imagenes);
					$tablaHTML .=$tabla;
				$tablaHTML .='</table>';

				echo $tablaHTML ;


				/*while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
					$actividades[$row2['id']]=array(
						'OwnId' => (int)$row2['ownid'],
						'Tipo' => $row2['tipo'],
						'Actividad' => $row2['actividad'],
						'Diagnostico' => $row2['diagnostico'],
						'Trabajos' => $row2['trabajos'],
						'Observaciones' => $row2['observaciones']
					);*/
					/*$data['actividades'][$row2['id']] = array(
						'OwnId' => (int)$row2['ownid'],
						'Tipo' => $row2['tipo'],
						'Actividad' => $row2['actividad'],
						'Diagnostico' => $row2['diagnostico'],
						'Trabajos' => $row2['trabajos'],
						'Observaciones' => $row2['observaciones']
					);*/
				//}
				
				

				/*foreach ($actividades as $key=>$actividad) {
					if($actividad['OwnId']==0){
						$actividades2[$key]=array(
							'OwnId' => (int)$actividad['OwnId'],
							'Tipo' => $actividad['Tipo'],
							'Actividad' => $actividad['Actividad'],
							'Diagnostico' => $actividad['Diagnostico'],
							'Trabajos' => $actividad['Trabajos'],
							'Observaciones' => $actividad['Observaciones']
						);
					}					
					//echo $imagen['id'] . "<br>";
				}

				foreach ($actividades as $key=>$actividad) {
					if($actividad['OwnId']!=0){
						echo $actividad['OwnId'];
						$actividades2[$actividad['OwnId']]['imagenes'][]=array(
							'OwnId' => (int)$actividad['OwnId'],
							'Tipo' => $actividad['Tipo'],
							'Actividad' => $actividad['Actividad'],
							'Diagnostico' => $actividad['Diagnostico'],
							'Trabajos' => $actividad['Trabajos'],
							'Observaciones' => $actividad['Observaciones']
						);
					}					
					//echo $imagen['id'] . "<br>";
				}*/

				//echo '<pre>';
				//print_r($arbol);

				//echo '<pre>';
				//print_r($actividades);

				//$indices_cadena = implode(',', array_keys($data['actividades']));

				//$imagenes=array();

				

				// Convertir el array de 'id' a una cadena separada por comas
				

				/*$idBuscado=2;
				$elementoEncontrado = array();

				foreach ($imagenes as $elemento) {
					if ($elemento['refid'] == $idBuscado) {
						$elementoEncontrado[] = $elemento;
						//break; // Terminar el bucle una vez que se encuentre el elemento
					}
				}

				echo '<pre>';
				print_r($elementoEncontrado);*/


				//echo '<pre>';
				//print_r($imagenes);

				
				/*while ($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)) {
					$data['actividades'][$row3['refid']]['imagenes'][$row3['id']] = array(
						'RefId' => (int)$row3['refid'],
						'Nombre' => $row3['nombre'],
						'Descripcion' => $row3['descripcion']
					);
				}*/

				//foreach ($imagenes as $imagen) {
				//	echo $imagen['id'] . "<br>";
				//}


				//echo '<pre>';
				//print_r($imagenes);

			}catch(PDOException $ex){
				$conmy=null;
				//$html5='<h5>Error procesando el archivo.</h5>';
			};

			/*
			if($Id>0){
				$html5='
				<!DOCTYPE html>
				<html lang="es">
					<head>
						<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
						<title>Cotizacion</title>
						<style>
							@page {
								margin: 0cm 0cm;
							}

							*{
								font-family: DejaVu Sans, sans-serif;
								font-size: 11px;
							}

							.fs-9{
								font-size:9px;
							}

							.fs-12{
								font-size: 12px;
							}

							.fw-bold{
								font-weight:bold;
							}

							body {
								margin-top: 3cm;
								margin-left: 1cm;
								margin-right: 1cm;
								margin-bottom: 3.2cm;
							}

							header {
								position: fixed;
								top: 0.5cm;
								left: 1cm;
								right: 1cm;
								height: 2.3cm;
							}

							main{
								left:1cm;
								right:1cm;
							}

							footer {
								position: fixed;
								bottom:   0px;
								left:     0px;
								width:    21.8cm;
								height:   3.2cm;
								border-top:1px solid #B4B4B4;
								z-index:  -1000;
							}

						</style>
					</head>
					<body>';
						
						//$imagenBase64 = "data:image/png;base64," . base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT']."/mycloud/logos/logo-gpem.png")); 
						//$html=file_get_contents_curl("https://gpemsac.com/portal/cotizador/print/GenerarPdfCotizacion.php?cotizacion=13");
						
						$html5.='
						<footer>
							<p style="text-align:center; padding:0px; margin:0px;">AV. Los Incas 4ta Cuadra S/N - Comas - Lima - Per&uacute; - Telf. (511) 7130629 Anexo 308</p>
							<p style="text-align:center; padding:0px; margin:0px;">e-mail: g.administracion@gpemsac.com</p>
							<img src="'.$_SERVER['DOCUMENT_ROOT']."/mycloud/portal/empresa/footer/gpemsac.jpg".'" width="100%" style="height:2.3cm;"/>
						</footer>';

						$html5.='
						<header>
							<table style="border-spacing:0; width: 100%;">
								<tr>
									<td rowspan="2" style="width: 25%;"><img src="'.$_SERVER['DOCUMENT_ROOT']."/mycloud/logos/logo-gpem.png".'" style="height: 50px;"></td>
									<td style="text-align:center; font-weight: bold; font-size:14px; padding:3px; width:50%;">GESTION DE PROCESOS EFICIENTES DE MANTENIMIENTO S.A.C.</td>                     
									<td style="border-right:1px solid; border-top: 1px solid; border-left:1px solid; padding:5px; text-align:center; font-weight:bold; font-size:16px; width:25%;">INFORME</td>                     
								</tr>
								<tr>
									<td style="text-align:center;">Av. Los Incas S/N - Comas <br> Telf: 01-7130628</td>
									<td style="border-bottom: 1px solid; border-right: 1px solid; border-left:1px solid; padding:5px; text-align:center; font-weight:bold; font-size:16px;">'.$Nombre.'</td>
								</tr>
							</table>
						</header>';
						
						
						$html5.='
						<footer>
							<table style="width: 100%; border-top:1px solid #B4B4B4;">
								<tr>
									<td style="text-align:center;">AV. Los Incas 4ta Cuadra S/N - Comas - Lima - Per&uacute; - Telf. (511) 7130629 Anexo 308</td>
								</tr>
								<tr>
									<td style="text-align: center">e-mail: g.administracion@gpemsac.com</td>
								</tr>
								<tr>
									<td style="width: 100%;"><img src="'.$_SERVER['DOCUMENT_ROOT']."/mycloud/portal/empresa/footer/gpemsac.jpg".'" width="100%"></td>
								</tr>
							</table>
						</footer>';
						

						$html5.='<main>';					
							
						$html5.='
						<table style="width:100%; border-spacing:0; margin-bottom: 20px;">
							<tr><td colspan="7" style="text-align: right; padding-bottom: 0px; padding-right: 20px;">'.$Fecha.'</tr></td>
							<tr><td colspan="7">Se&ntilde;ores:</td></tr>
							<tr><td colspan="7" style="font-weight:bold; padding:0px;">'.utf8_encode($CliNombre).'</td></tr>
							<tr><td colspan="7" style="padding:0px;">RUC: '.$CliRuc.'</td></tr>
							<tr><td colspan="7" style="padding:0px;">Direcci&oacute;n: '.utf8_encode($CliDireccion).'</td></tr>';
							
						if(!empty($CliContacto)){
							$html5.='<tr><td colspan="7" style="padding:0px;">Contacto: '.utf8_encode($CliContacto).'</td></tr>';
						}
	
						if(!empty($CliTelefono)){
							$html5.='<tr><td colspan="7" style="padding:0px;">Tel&eacute;fono: '.utf8_encode($CliTelefono).'</td></tr>';
						}
	
						if(!empty($CliCorreo)){
							$html5.='<tr><td colspan="7" style="padding:0px;">Correo: '.utf8_encode($CliCorreo).'</td></tr>';
						}

						$html5.='
						<tr><td colspan="7" style="padding-top: 10px; padding-bottom: 8px;">De nuestra especial consideraci&oacute;n, es grato dirigirnos a Uds. para remitir la siguiente cotizaci&oacute;n.</td></tr>';
							
						$html5.='
						<tr style="background-color:#001466; color:white; text-align:center;">
							<td style="padding:8px; text-align:right; width: 5%">Item</td>
							<td style="padding:8px; width: 40%;">Descripci&oacute;n</td>
							<td style="padding:8px; text-align:right;">Cantidad</td>
							<td style="padding:8px;">Medida</td>
							<td style="padding:8px; text-align:right;">Valor Unitario</td>
							<td style="padding:8px; text-align:right;">Precio Unitario</td>
							<td style="padding:8px; text-align:right;">Valor Venta</td>
						</tr>';

						try{
							$conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
							$stmt=$conmy->prepare("select id, ownid, tipo, actividad, diagnostico, trabajos, observaciones from tbldetalleinforme where infid=:InfId;");
							$stmt->execute(array('InfId'=>$Id));
							if($stmt->rowCount()>0){
								$i = 1;
								$PrecioUnitario = 0;
								$ValorVenta = 0;
								$Estado = '';              
								while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
									//0: Seleccionar, 1:STOCK, 2:IMPORTACION
									switch ($row['estado']) {
										case 1:
											$Estado = ' [Stock]';
											break;
										case 2:
											$Estado = ' [Importación]';
											break;
										default:
											$Estado = '';
											break;
									}
									$PrecioUnitario=$row['precio']*(1+($Igv/100));
									$ValorVenta=$row['cantidad']*$row['precio'];
									$TotValorVenta+=$ValorVenta;

											
									$html5.='
									<tr>
										<td style="padding:8px 5px 8px 5px; text-align:right; border-bottom: 1px solid #B4B4B4;">'.$i.'</td>
										<td style="padding:8px 5px 8px 5px; border-bottom: 1px solid #B4B4B4;">'.utf8_encode($row['nombre'].$Estado).'</td>
										<td style="padding:8px 5px 8px 5px; text-align: right; border-bottom: 1px solid #B4B4B4;">'.sprintf('%.2f', $row['cantidad']).'</td>
										<td style="padding:8px 5px 8px 5px; border-bottom: 1px solid #B4B4B4;">'.utf8_encode($row['medida']).'</td>
										<td style="padding:8px 5px 8px 5px; text-align: right; border-bottom: 1px solid #B4B4B4;">'.sprintf('%.2f', $row['precio']).'</td>
										<td style="padding:8px 5px 8px 5px; text-align: right; border-bottom: 1px solid #B4B4B4;">'.sprintf('%.2f', $PrecioUnitario).'</td>
										<td style="padding:8px 5px 8px 5px; text-align: right; border-bottom: 1px solid #B4B4B4;">'.sprintf('%.2f', $ValorVenta).'</td>
									</tr>';
									
									$i+=1;
								}
										
								$TotDescuentos = $TotValorVenta * ($Descuento/100);
								$BaseImponible = $TotValorVenta - $TotDescuentos;
								$TotImpuestos = $BaseImponible * ($Igv/100);
								$TotPrecioVenta = $BaseImponible + $TotImpuestos;

								$html5.='
								<tr>
									<td colspan="6" style="padding:5px 5px 5px 5px; text-align: right;">Total Venta '.$MonedaSimbolo.'</td>
									<td style="padding:5px 5px 5px 5px; text-align: right;">'.sprintf('%.2f', $TotValorVenta).'</td>
								</tr>';

								if($Descuento>0){
									$html5.='
									<tr>
										<td colspan="6" style="padding:5px 5px 5px 5px; text-align: right;">Total con Descuento('.$Descuento.'%) '.$MonedaSimbolo.'</td>
										<td style="padding:5px 5px 5px 5px; text-align: right;">'.sprintf('%.2f', $BaseImponible).'</td>
									</tr>';
								}

								$html5.='											
								<tr>
									<td colspan="6" style="padding:5px 5px 5px 5px; text-align: right;">Total IGV('.$Igv.'%) '.$MonedaSimbolo.'</td>
									<td style="padding:5px 5px 5px 5px; text-align: right;">'.sprintf('%.2f', $TotImpuestos).'</td>
								</tr>
								<tr>
									<td colspan="6" style="padding:5px 5px 5px 5px; text-align: right; font-weight:bold;">Monto Total '.$MonedaSimbolo.'</td>
									<td style="padding:5px 5px 5px 5px; text-align:right; font-weight:bold;">'.sprintf('%.2f', $TotPrecioVenta).'</td>
								</tr>';
							}
							
							$stmt=null;
						}catch(PDOException $e){
							$stmt=null;
						};

						$html5.='<tr><td colspan="7" style="font-weight:bold;">CONDICIONES COMERCIALES</td></tr>';

						$html5.='<tr><td colspan="7">&#10003 Moneda: '.$MonedaNombre.'</td></tr>';

						if($Descuento>0){
							$html5.='<tr><td colspan="7">&#10003 Descuento: '.$Descuento.'%</td></tr>';
						}

						if(!empty($CotPago)){
							$html5.='<tr><td colspan="7">&#10003 Condici&oacute;n de pago: '.utf8_encode($CotPago).'.</td></tr>';
						}

						if(!empty($CotTiempo)){
							$html5.='<tr><td colspan="7">&#10003 Oferta v&aacute;lida por '.utf8_encode($CotTiempo).' d&iacute;as.</td></tr>';
						}

						//$html5.='
						//<tr>
						//	<td colspan="7">&#10003 '.utf8_encode('Para transferencias desde otras plazas, el banco realizará un cobro del 0,5% sobre el monto total, el cual deberá ser asumido por el cliente.').'</td>
						//</tr>
						//<tr>
						//	<td colspan="7">&#10003 Envio a provincias por SHALOM o MARVISUR.</td>
						//</tr>';

						try{
							$conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
							$stmt=$conmy->prepare("select id, descripcion from tblnotascotizacion where cotid=:CotId;");
							$stmt->execute(array('CotId'=>$Id));
							if($stmt->rowCount()>0){                   
								while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
									$html5.='<tr><td colspan="7">&#10003 '.utf8_encode($row['descripcion']).'</td></tr>';
								}
							}
							$stmt=null;
						}catch(PDOException $e){
							$stmt=null;
						}

						$html5.='</table>';

						try{
							$conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
							$stmt=$conmy->prepare("select nombre, descripcion from tblcotizacionimagenes where cotid=:CotId;");
							$stmt->execute(array('CotId'=>$Id));
							if($stmt->rowCount()>0){
								$html5.='<table style="border-spacing:0; width: 100%; margin-bottom: 20px;"><tr><td style="font-weight:bold;">IMAGENES DE REFERENCIA</td></tr>';      
								while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){									
									$html5.='<tr><td style="width: 100%; text-align:center; padding-bottom: 5px;"><p style="margin-bottom:2px;">'.$row['descripcion'].'</p><img src="'.$_SERVER['DOCUMENT_ROOT']."/mycloud/portal/cotizador/anexos/".$row['nombre'].'" style="width: 400px; border:1px solid #000;"></td></tr>';
								}
								$html5.='</table>';
							}
							$stmt=null;
						}catch(PDOException $e){
							$stmt=null;
						};

						if($VerCuentas){
							$html5.='
							<table style="width: 100%; border-collapse: collapse; text-align:center; margin-bottom: 15px;">
								<tr>
									<td class="fs-9 fw-bold" style="border: 1px solid; padding: 5px; text-align: center;" colspan="6">GESTION DE PROCESOS EFICIENTES DE MANTENIMIENTO S.A.C.</td>
								</tr>
								<tr>
									<td class="fs-9" style="border: 1px solid; padding: 5px;" colspan="2">RUC: 20566384826</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;" colspan="4">DIRECCION: AV. LOS INCAS 4TA CUADRA S/N -COMAS</td>
								</tr>
								<tr>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">ITEM</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">BANCO</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">MONEDA</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">TIPO DE CUENTA</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">NRO DE CUENTA</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">CODIGO INTERBANCARIO</td>
								</tr>
								<tr>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">1</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">BBVA BANCO CONTINENTAL</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">MN</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">CUENTA CORRIENTE</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">0011 0333 0100108365</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">011 333 000100108365 26</td>
								</tr>
								<tr>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">2</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">BBVA BANCO CONTINENTAL</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">ME</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">CUENTA CORRIENTE</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">0011 0333 0100108373</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">011 333 000100108373 20</td>
								</tr>
								<tr>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">3</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">BANCO DE LA NACION</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">MN</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">CUENTA DETRACCION</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;">00-003-163202</td>
									<td class="fs-9" style="border: 1px solid; padding: 5px;"></td>
								</tr>
							</table>';
						}						

						$html5.='
						<p>Sin otro tema en particular nos despedimos de Ud, esperando la confirmaci&oacute;n de nuestros servicios.</p>
						<p>Saludos cordiales.</p>
						<p class="fs-12 fw-bold" style="text-decoration: underline; padding:0px; margin:0px;">'.utf8_encode($VendNombre).'</p>
						<p style="padding:0px; margin:0px;">Asesor Comercial</p>
						<p style="padding:0px; margin:0px;">GPEM SAC.</p>
					</main>
				</body>
			</html>';
		}else{
			$html5='<h5>No se encontr&oacute; la Cotizaci&oacute;n.</h5>';
		}
	}else{
		$html5='<h5>No se reconoce el n&uacute;mero de Cotizaci&oacute;n.</h5>';
	}
}else{
	$html5='<h5>Acci&oacute;n no autorizada.</h5>';
}


	require_once $_SERVER['DOCUMENT_ROOT']."/mycloud/library/dompdf_0-8-3/autoload.inc.php";
	use Dompdf\Dompdf;

	// Instanciamos un objeto de la clase DOMPDF.
	$pdf = new DOMPDF();
	$pdf->set_option('enable_html5_parser', TRUE);
	$pdf->set_paper("letter", "portrait");//Definimos el tamaño y orientación del papel que queremos.
	//$pdf->set_paper(array(0,0,104,250));
	$pdf->load_html(utf8_decode($html5));
	$pdf->render();//Renderizamos el documento PDF.
	$pdf->stream('COTIZACION '.$Cotizacion.'.pdf');// Enviamos el fichero PDF al navegador.

	function file_get_contents_curl($url) {
		$crl = curl_init();
		$timeout = 5;
		curl_setopt($crl, CURLOPT_URL, $url);
		curl_setopt($crl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($crl, CURLOPT_CONNECTTIMEOUT, $timeout);
		$ret = curl_exec($crl);
		curl_close($crl);
		return $ret;
	}*/
	//echo '<pre>';
	//print_r($data);
?>