<?php
include($_SERVER['DOCUMENT_ROOT'].'/gesman/connection/ConnGesmanDb.php');
require_once '../Datos/InformesData.php';

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {    
    http_response_code(200);
    exit();
}

$data = ['data' => [],'res' => false, 'msg' => 'Error general.'];

try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (empty($_POST['id'])) {
        throw new Exception("El ID del equipo es requerido.");
    }
    
    $id = $_POST['id'];
    $cliid = 2;
    
    $equipo = FnBuscarInformeMatriz($conmy, $id, $cliid);

    if ($equipo) {
        $data['data'] = $equipo;
        $data['res'] = true;
        $data['msg'] = "Ok.";
    } else {
        $data['msg'] = "No se encontró el equipo.";
    }
} catch (PDOException $ex) {
    $data['msg'] = $ex->getMessage();
    error_log("PDOException: " . $data['msg']);
} catch (Exception $ex) {
    $data['msg'] = $ex->getMessage();
    error_log("Exception: " . $data['msg']);
} finally {
    $conmy = null;
}
header('Content-Type: application/json');
echo json_encode($data);
?>
