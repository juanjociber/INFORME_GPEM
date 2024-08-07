<?php 
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
        $id = $_POST['id']; // Obtener el ID del POST
        if (FnEliminarActividad($conmy, $id)) {
            $msg = "Se eliminó la Actividad.";
            $res = true;
        } else {
            $msg = "Error eliminando la Actividad.";
        }
    } catch (PDOException $ex) {
        $msg = $ex->getMessage();
    } catch (Exception $ex) {
        $msg = $ex->getMessage();
    } finally {
        $conmy = null;
    }

    echo json_encode(['res' => $res, 'msg' => $msg]);
?>