<?php
  require_once $_SERVER['DOCUMENT_ROOT']."/gesman/connection/ConnGesmanDb.php";

?>
<!doctype html>
<html lang="es">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Buscador | GPEM SAC</title>
    <link rel="shortcut icon" href="/mycloud/logos/favicon.ico">
    <link rel="stylesheet" href="/mycloud/library/bootstrap-icons-1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/mycloud/library/SweetAlert2/css/sweetalert2.min.css">
    <link rel="stylesheet" href="/mycloud/library/bootstrap-5.1.0-dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="/mycloud/library/select-gpem-1.0/css/select-gpem-1.0.css">
    <style>
      .custom-select-arrow { top: 50%;right: 20px; }
    </style>
  </head>
  <body>
    <!-- CONTENEDOR -->
    <div class="container">
      <!-- CABECERA -->
      <div class="row border-bottom mb-3 fs-5">
        <div class="col-12 fw-bold d-flex justify-content-between">
          <p class="m-0 p-0 text-secondary text-uppercase fw-bold">CLIENTE</p>
        </div>
      </div>

      <!-- FILTRO -->
      <div class="row mb-1 border-bottom">
        <div class="col-6 col-lg-6 col-xl-3">
          <label for="informeInput" class="form-label mb-0">Informe</label>
          <input type="text" class="form-control text-secondary text-uppercase fw-bold" id="informeInput">
        </div>
        
        <div class="col-6 col-lg-6 col-xl-3 custom-select-wrapper">
          <label for="equipoInput" class="form-label mb-0">Equipo</label>
          <input type="text" id="equipoInput" class="form-control text-secondary text-uppercase fw-bold" autocomplete="off" placeholder="Ingrese 1 o más caracteres">
          <span class="custom-select-arrow"><i class="bi bi-chevron-down"></i></span>
          <div id="equipoList" class="custom-select-list"></div>
          <div class="fullscreen-spinner" id="spinner" style="display: none;">
            <div class="spinner"></div>
          </div>
        </div>

        <input type="hidden" id="idActivoInput">
        
        <div class="col-6 col-lg-6 col-xl-3">
          <label for="fechaInicialInput" class="form-label mb-0">Fecha inicial</label>
          <input type="date" class="form-control text-secondary text-uppercase fw-bold" id="fechaInicialInput" value=""/>
        </div>
        <div class="col-6 col-lg-6 col-xl-3">
          <label for="fechaFinalInput" class="form-label mb-0">Fecha final</label>
          <input type="date" class="form-control text-secondary text-uppercase fw-bold" id="fechaFinalInput" value=""/>
        </div>
        
        <div class="col-6 col-lg-3 mt-2 mb-2">
          <button type="button" class="btn btn-primary text-uppercase col-12 col-lg-6" onclick="fnBuscarInforme();"><i class="bi bi-search"></i> Buscar</button>
        </div>  
      </div>

      <!-- INFORMES -->
      <div id="contenedor-lista"></div>
    </div>

    <script src="js/informes.js"></script>
    <script src="/mycloud/library/bootstrap-5.1.0-dist/js/bootstrap.min.js"></script>
    <script src="/mycloud/library/SweetAlert2/js/sweetalert2.all.min.js"></script>
  </body>
</html>
