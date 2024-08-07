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

    if (empty($_POST['id']) || empty($_POST['fecha']) || empty($_POST['clicontacto']) || empty($_POST['supervisor'])) {
        throw new Exception("La información está incompleta.");
    }

        
    $USUARIO = date('Ymd-His (').'jhuiza'.')';

    $informe = new stdClass();
    $informe->id = $_POST['id'];
    $informe->fecha = $_POST['fecha'];
    $informe->clicontacto = $_POST['clicontacto'];
    $informe->ubicacion = empty($_POST['ubicacion']) ? NULL : $_POST['ubicacion'] ;
    $informe->supervisor = $_POST['supervisor'];
    $informe->actualizacion = $USUARIO;

    // LOG DATOS RECIBIDOS
    //error_log("Datos recibidos: " . json_encode($informe));

    if (FnModificarInformeDatosGenerales($conmy, $informe)) {
        $data['msg'] = "Se modificó los datos generales.";
        $data['res'] = true;
    } else {
        $data['msg'] = "Error modificando datos generales.";
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

//ob_end_clean();
echo json_encode($data);
?>
