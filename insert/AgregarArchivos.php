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

try {
    include($_SERVER['DOCUMENT_ROOT'].'/gesman/connection/ConnGesmanDb.php');
    require_once '../datos/InformesData.php';

    $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $tabla = $_POST['tabla'];
    if (!in_array($tabla, ['INF', 'INFA', 'INFD'])) {
        throw new Exception('Tabla no válida.');
    }

    $USUARIO = date('Ymd-His (').'jhuiza'.')';

    $FileName = $tabla.'_'.$_POST['refid'].'_'.uniqid().'.jpeg';
    $FileType = 'IMG';
    $FileEncoded = str_replace("data:image/jpeg;base64,", "", $_POST['archivo']);
    $FileDecoded = base64_decode($FileEncoded);
    file_put_contents($_SERVER['DOCUMENT_ROOT']."/mycloud/files/".$FileName, $FileDecoded);

    $imagen = new stdClass();
    $imagen->refid = $_POST['refid'];
    $imagen->tabla = $tabla;
    $imagen->nombre = $FileName;
    $imagen->titulo = empty($_POST['titulo']) ? $FileName : $_POST['titulo'];
    $imagen->descripcion = empty($_POST['descripcion']) ? null : $_POST['descripcion'];
    $imagen->usuario = $USUARIO;
    $imagen->tipo = 'IMG';

    if (FnRegistrarImagen($conmy, $imagen)) {
        $msg = "Archivo registrado con éxito.";
        $res = true;
        $imagenRegistrada = $imagen; 
    } else {
        $msg = "Error registrando el Archivo.";
    }
} catch (PDOException $ex) {
    $msg = $ex->getMessage();
} catch (Exception $ex) {
    $msg = $ex->getMessage();
} finally {
    $conmy = null;
}

echo json_encode(array('res' => $res, 'msg' => $msg, 'imagen' => $imagenRegistrada));
?>