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
  
    $USUARIO = date('Ymd-His (').'jhuiza'.')'; // Identificador de usuario o acción

    $informe = new stdClass();
    $informe->id = $_POST['id'];
    $informe->equnombre = empty($_POST['equnombre']) ? null : $_POST['equnombre'];
    $informe->equmarca = empty($_POST['equmarca']) ? null : $_POST['equmarca'];
    $informe->equmodelo = empty($_POST['equmodelo']) ? null : $_POST['equmodelo'];
    $informe->equserie = empty($_POST['equserie']) ? null :$_POST['equserie'];
    $informe->equkm = empty($_POST['equkm']) ? 0 : $_POST['equkm'];
    $informe->equhm = empty($_POST['equhm']) ? 0 : $_POST['equhm'];
    $informe->actualizacion = $USUARIO;

    // LOG DATOS RECIBIDOS
    error_log("Datos recibidos: " . json_encode($informe));

    // Llama a la función para modificar los datos
    if (FnModificarInformeDatosEquipos($conmy, $informe)) {
        $data['msg'] = "Se modificaron los datos del equipo.";
        $data['res'] = true;
    } else {
        $data['msg'] = "Error modificando los datos del equipo.";
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

// Limpia el buffer de salida y envía la respuesta
ob_end_clean();
echo json_encode($data);
?>

