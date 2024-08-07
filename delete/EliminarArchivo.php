<?php 
declare(strict_types=1);

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Authorization, Content-Type, Accept");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {    
    http_response_code(200);
    exit();
}

$data = [
    'res' => false,
    'msg' => 'Error general.',
];

include($_SERVER['DOCUMENT_ROOT'].'/gesman/connection/ConnGesmanDb.php');
require_once '../datos/InformesData.php';

try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if (empty($_POST['id'])) {
        throw new Exception("La información está incompleta.");
    }

    $id = (int)$_POST['id'];

    if (FnEliminarArchivo($conmy, $id)) {
        $data['msg'] = "Se eliminó el Archivo.";
        $data['res'] = true;
    } else {
        $data['msg'] = "Error eliminando el Archivo.";
    }
} catch (PDOException $ex) {
    $data['msg'] = $ex->getMessage();
} catch (Exception $ex) {
    $data['msg'] = $ex->getMessage();
} finally {
    $conmy = null;
    ob_end_clean();
    echo json_encode($data);
    exit(); 
}
?>
