<?php 
    session_start();

	$res=false;
    $id=0;
	$msg='Error general creando el Informe.';

    include($_SERVER['DOCUMENT_ROOT'].'/gesman/connection/ConnGesmanDb.php');
    require_once '../datos/InformesData.php';

    try {
        $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if(!isset($_SESSION)){throw new Exception("Se ha perdido la conexión.");}

        if(empty($_POST['ordid']) || empty($_POST['fecha']) || empty($_POST['actividad'])){throw new Exception("La información esta incompleta.");} 

        $orden=FnBuscarOrden($conmy, $_POST['ordid']);
        if(empty($orden->id)){ throw new Exception("No se encontró la Orden."); }
        
        $equipo=FnBuscarEquipo($conmy, $orden->equid);
        if(empty($orden->id)){ throw new Exception("No se encontró el Equipo."); }
        
        $cliente=FnBuscarCliente($conmy, $orden->cliid);
        if(empty($orden->id)){ throw new Exception("No se encontró el Cliente."); }

        $usuario=date('Ymd-His (').$_SESSION['UserName'].')';

        $id=FnRegistrarInforme($conmy, $orden, $cliente, $equipo, $_POST['fecha'], $_POST['actividad'], $usuario);
        if($id>0){
            $res=true;
            $msg='Se generó el Informe';
        }else{
            throw new Exception("Error generando el Informe.");  
        }
    } catch(PDOException $ex){
        $msg=$ex->getMessage();
    } catch (Exception $ex) {
        $msg=$ex->getMessage();
    }finally{
        $conmy=null;
    }

    echo json_encode(array('res'=>$res, 'id'=>$id, 'msg'=>$msg));
?>