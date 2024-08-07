<?php 
include($_SERVER['DOCUMENT_ROOT'].'/gesman/connection/ConnGesmanDb.php');
require_once '../Datos/InformesData.php';

$data = [
    'data' => [],
    'res' => false,
    'msg' => 'Error general.'
];

try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if(empty($_POST['id'])){
        throw new Exception("La informacion esta incompleta.");
    }

    $actividad = FnBuscarActividadPorInforme($conmy, $_POST['id']);
    
    if ($actividad) {
        $data['data'] = $actividad;
        $data['res'] = true;
        $data['msg'] = 'Ok.';
    } else {
        $data['msg'] = 'No se encontró la actividad.';
    }

} catch(PDOException $ex){
    $data['msg'] = $ex->getMessage();
} catch (Exception $ex) {
    $data['msg'] = $ex->getMessage();
} finally {
    $conmy = null;
}

header('Content-Type: application/json');
echo json_encode($data);
?>


