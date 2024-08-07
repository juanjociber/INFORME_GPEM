<?php
    session_start();
    require_once $_SERVER['DOCUMENT_ROOT'].'/gesman/connection/ConnGesmanDb.php';

    $Bandera = false;
	if(isset($_SESSION['CliId']) && isset($_SESSION['UserName'])){
		$Bandera = true;
	}

    $data = array();
    $data['res'] = false;
    $data['msg'] = 'Error del sistema.';

    if($Bandera==true && $_SERVER['REQUEST_METHOD']==='POST'){
        if(!empty($_POST['id'])){
            $Usuario = date('Ymd-His').' ('.$_SESSION['UserName'].')';
            try{
                $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $stmt=$conmy->prepare("UPDATE tblinforme SET estado=:Estado, actualizacion=:Actualizacion WHERE id=:Id AND cliid=:Cliid AND estado IN(1,2,4);");
                $stmt->execute(array(
                    ':Estado' => 3,
                    ':Actualizacion' => $Usuario,
                    ':Id' => $_POST['id'],
                    ':Cliid' => $_SESSION['CliId']             
                ));

                if($stmt->rowCount()>0){
                    $data['res'] = true;
                    $data['msg'] = 'Se finalizó el Informe.';                    
                }else{
                    $data['msg'] = 'No se pudo finalizar el Informe.';
                }
                $stmt = null;                
            }catch(PDOException $e){
                $stmt=null;
                $data['msg'] = $e->getMessage();
            }
        }else{
            $data['msg'] = 'La información esta incompleta.';
        }
    }else{
        $data['msg'] = 'Usuario no autorizado.';
    }

    echo json_encode($data);
?>