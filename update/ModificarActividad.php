<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Authorization, Content-Type, Accept");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {    
    http_response_code(200);
    exit();
}

$data = ['res' => false, 'msg' => 'Error general.'];

include($_SERVER['DOCUMENT_ROOT'].'/gesman/connection/ConnGesmanDb.php');
require_once '../datos/InformesData.php';

try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (empty($_POST['id']) || empty($_POST['actividad'])) {
        throw new Exception("La información está incompleta.");
    }
    
    $USUARIO = date('Ymd-His (').'jhuiza'.')';

    $actividad = new stdClass();
    $actividad->id = $_POST['id'];
    $actividad->actividad = $_POST['actividad'];
    $actividad->diagnostico = empty($_POST['diagnostico']) ? null : $_POST['diagnostico'];
    $actividad->trabajos = empty($_POST['trabajos']) ? null : $_POST['trabajos'];
    $actividad->observaciones = empty($_POST['observaciones']) ? null : $_POST['observaciones'];
    $actividad->usuario = $USUARIO;

    if (FnModificarActividad($conmy, $actividad)) {
        $data['msg'] = "Se modificó la Actividad.";
        $data['res'] = true;
    } else {
        $data['msg'] = "Error modificando la Actividad.";
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
// header('Content-Type: application/json');
ob_end_clean();
echo json_encode($data);
?>
