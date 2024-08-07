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

    if (!isset($_POST['nombre'])) {
        throw new Exception("Los datos no están completos.");
    }
    // $cliId = $_SESSION[''];
    $cliId = 2; 
    $nombre = $_POST['nombre'];

    // OBTENIENDO DATOS DEL EQUIPO
    $equipos = FnBuscarEquipos($conmy, $nombre, $cliId);
    if ($equipos) {
        $data['data'] = $equipos;
        $data['res'] = true;
        $data['msg'] = 'Ok.';
    } else {
        $data['msg'] = 'No se encontraron equipos.';
    }
} catch(PDOException $ex) {
    $data['msg'] = 'Error de base de datos: ' . $ex->getMessage();
} catch (Exception $ex) {
    $data['msg'] = 'Error: ' . $ex->getMessage();
} finally {
    $conmy = null;
}

header('Content-Type: application/json');
echo json_encode($data);
?>

