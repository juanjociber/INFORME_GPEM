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

$res = false;
$msg = 'Error general.';
$imagenRegistrada = null;

include($_SERVER['DOCUMENT_ROOT'].'/gesman/connection/ConnGesmanDb.php');
require_once '../datos/InformesData.php';

try {
    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (empty($_POST['refid']) || empty($_POST['archivo'])) {
        throw new Exception("La información está incompleta.");
    }

    $USUARIO = date('Ymd-His (').'jhuiza'.')';

    $FileName = 'INF'.'_'.$_POST['refid'].'_'.uniqid().'.jpeg';
    $FileType = 'IMG';
    $FileEncoded = str_replace("data:image/jpeg;base64,", "", $_POST['archivo']);
    $FileDecoded = base64_decode($FileEncoded);

    if (file_put_contents($_SERVER['DOCUMENT_ROOT']."/mycloud/files/".$FileName, $FileDecoded) === false) {
        throw new Exception("Error guardando el archivo en el servidor.");
    }

    $imagen = new stdClass();
    $imagen->refid = $_POST['refid'];
    $imagen->tabla = 'INF';
    $imagen->nombre = $FileName;
    $imagen->titulo = empty($_POST['titulo']) ? $FileName : $_POST['titulo'];
    $imagen->descripcion = empty($_POST['descripcion']) ? null : $_POST['descripcion'];
    $imagen->usuario = $USUARIO;
    $imagen->tipo = 'IMG';

    if (FnRegistrarImagen($conmy, $imagen)) {
        $msg = "Archivo registrado con éxito.";
        $res = true;
        $imagenRegistrada = $imagen; // Retornar imagen registrada
    } else {
        throw new Exception("Error registrando el archivo en la base de datos.");
    }
} catch (PDOException $ex) {
    $msg = "Error en la base de datos: " . $ex->getMessage();
} catch (Exception $ex) {
    $msg = "Error: " . $ex->getMessage();
} finally {
    $conmy = null;
}

echo json_encode(array('res' => $res, 'msg' => $msg, 'imagen' => $imagenRegistrada));
?>
