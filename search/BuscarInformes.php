<?php 
    include($_SERVER['DOCUMENT_ROOT'].'/gesman/connection/ConnGesmanDb.php');
    require_once '../Datos/InformesData.php';

    $data = [
        'data' => [],
        'res' => false,
        'pag' => 0,
        'msg' => 'Error general.'
    ];

    try {
        $conmy->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if(empty($_POST['fechainicial']) || empty($_POST['fechafinal'])) {
            throw new Exception("Las fechas de busqueda están incompletas.");
        }

        $CLIID = 2;

        $informe = new stdClass();
        $informe->cliid = $CLIID;
        $informe->nombre = empty($_POST['nombre']) ? null : $_POST['nombre'];
        $informe->equid = empty($_POST['equid']) ? 0 : $_POST['equid'];
        $informe->fechainicial = $_POST['fechainicial'];
        $informe->fechafinal = $_POST['fechafinal'];
        $informe->pagina = empty($_POST['pagina']) ? 0 : $_POST['pagina'];

        $informes = FnBuscarInformes($conmy, $informe);
        if ($informes['pag'] > 0) {
            $data['res'] = true;
            $data['msg'] = 'Ok.';
            $data['data'] = $informes['data'];
            $data['pag'] = $informes['pag'];
        } else {
            $data['msg'] = 'No se encontraron resultados.';
        }

    } catch(PDOException $ex) {
        $data['msg'] = $ex->getMessage();
    } catch (Exception $ex) {
        $data['msg'] = $ex->getMessage();
    } finally {
        $conmy = null;
    }

    echo json_encode($data);

?>