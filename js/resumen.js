// INICIALIZANDO VARIABLES PARA MODAL GLOBAL
let modalEditarActividad;

document.addEventListener('DOMContentLoaded', () => {
  modalEditarActividad = new bootstrap.Modal(document.getElementById('modalActividad'), { keyboard: false });
});

const fnEditarActividad = async (id) => {
  // MOSTRAR MODAL
  modalEditarActividad.show();
  const formData = new FormData();
  formData.append('id', id);

  try {
    const response = await fetch('http://localhost/informes/search/BuscarActividadInforme.php', {
      method: 'POST',
      body: formData
    });

    if (!response.ok) { 
      throw new Error(response.status + ' ' + response.statusText); 
    }
    const datos = await response.json();
    if (!datos.res) { 
      throw new Error(datos.msg); 
    }
    console.log(datos);
    // MOSTRANDO DATA RECIBIDA DE SERVIDOR
    document.getElementById('modalActividadInput').value = datos.data.actividad;
  } 
  catch (error) { console.error('Error:', error); }
};

//MODIFICAR ACTIVIDAD
const fnModificarActividadInforme = async () => {
  const id = document.getElementById('txtIdInforme').value;
  const actividad = document.getElementById('modalActividadInput').value;

  if (isNaN(id)) {
      Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'ID debe ser un número',
          timer: 2000
      });
      return;
  }
  const formData = new FormData();
  formData.append('id', id);
  formData.append('actividad', actividad);
  try {
      const response = await fetch('http://localhost/informes/update/ModificarActividadInforme.php', {
          method: 'POST',
          body: formData
      });

      if (!response.ok) {
          const errorText = await response.text();
          Swal.fire({
              icon: 'error',
              title: 'Error',
              text: errorText,
              timer: 2000
          });
          return;
      }
      const datos = await response.json();
      if (!datos.res) {
          Swal.fire({
              icon: 'error',
              title: 'Error',
              text: datos.msg,
              timer: 2000
          });
          return;
      }
      // Mensaje de éxito
      Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: datos.msg,
          timer: 3000
      });
      // ACTUALIZANDO CAMPO : ACTIVIDAD
      document.querySelector('#actividadId').textContent = actividad;
      // CERRAR MODAL
      const modalEditarActividad = bootstrap.Modal.getInstance(document.getElementById('modalActividad'));
      if (modalEditarActividad) {
          modalEditarActividad.hide();
      }
  } catch (error) {
      Swal.fire({
          icon: 'error',
          title: 'Error',
          text: `Se produjo un error inesperado: ${error.message}`,
          timer: 3000
      });
  }
};

/**------------------------------------------------------------
 * FUNCIONES : ANTECEDENNTES - CONSLUSIONES - RECOMENDACIONES
 * ------------------------------------------------------------
 */
let tipoSeleccionado = '';

// FUNCIÓN AGREGAR
const abrirModalAgregar = async (cabecera,tipo) => {
  tipoSeleccionado = tipo;
  const modal = new bootstrap.Modal(document.getElementById('agregarActividadModal'), { keyboard: false });
  modal.show();

  // ACTUALIZAR EL TEXTO DEL H5 SEGÚN EL TIPO
  const modalTitle = document.getElementById('cabeceraRegistrarModal');
  switch(cabecera) {
    case 'antecedente':
      modalTitle.textContent = 'Registrar Antecedente';
      break;
    case 'conclusion':
      modalTitle.textContent = 'Registrar Conclusión';
      break;
    case 'recomendacion':
      modalTitle.textContent = 'Registrar Recomendación';
      break;
    default:
      modalTitle.textContent = 'Registrar';
  }
}

const fnRegistrarActividadDetalle = async()=>{
  const actividad = document.getElementById('registroActividadInput').value.trim();
  const diagnostico = document.getElementById('registroDiagnosticoInput').value;
  const trabajos = document.getElementById('registroTrabajoInput').value;
  const observaciones = document.getElementById('registroObservacionInput').value;
  const infid = document.getElementById('txtIdInforme').value;

  
  if (!actividad) {
    Swal.fire({
      title: 'Aviso',
      text: 'Por favor, ingrese el nombre de la actividad.',
      icon: 'info',
      confirmButtonText: 'OK',
      timer: 2000
    });
    return;
  }

  const formData = new FormData();
  formData.append('infid', infid);
  formData.append('actividad', actividad);
  formData.append('diagnostico', diagnostico);
  formData.append('trabajos', trabajos);
  formData.append('observaciones', observaciones);
  formData.append('tipo', tipoSeleccionado);

  console.log(infid,actividad,diagnostico,trabajos,observaciones,tipoSeleccionado);

  try {
    const response = await fetch('http://localhost/informes/insert/AgregarActividadDetalle.php', {
      method: 'POST',
      body: formData
    });
    if (!response.ok) {
      throw new Error(`HTTP error! Status: ${response.status}`);
    }

    const result = await response.json();

    if (result.res) {
      Swal.fire({
        title: 'Éxito',
        text: result.msg,
        icon: 'success',
        confirmButtonText: 'OK',
        timer: 2000
      }).then(() => {
        const modalInstance = bootstrap.Modal.getInstance(document.getElementById('agregarActividadModal'));
        if (modalInstance) {
          modalInstance.hide();
        }
        location.reload();
      });
    } else {
      Swal.fire({
        title: 'Error',
        text: result.msg,
        icon: 'error',
        confirmButtonText: 'OK',
        timer: 2000
      });
    }
  } catch (error) {
    Swal.fire({
      title: 'Error',
      text: `Se produjo un error inesperado: ${error.message}`,
      icon: 'error',
      confirmButtonText: 'OK',
      timer: 2000
    });
  }
};

// FUNCIÓN ABRIR MODAL Y BUSCA DATA ENVIADA POR EL SERVIDOR
const abrirModalEditar = async (id, cabecera) => {

  const modal = new bootstrap.Modal(document.getElementById('modalGeneral'), {keyboard: false});
  modal.show();
  //console.log(id,tipo);
  document.getElementById('txtIdtblDetalleInf').value = id;
    
  const formData = new FormData();
  formData.append('id', id);
  try {
    const response = await fetch('http://localhost/informes/search/BuscarActividad.php', {
      method: 'POST',
      body: formData
    });
    if (!response.ok) { 
      throw new Error(response.status + ' ' + response.statusText); 
    }
    const datos = await response.json();
    if (!datos.res) { 
      throw new Error(datos.msg); 
    }
    console.log('RESPUESTA DEL SERVIDOR', datos);
    // MOSTRANDO DATA RECIBIDA DE SERVIDOR
    document.getElementById('actividadModalInput').value = datos.data.actividad;
    document.getElementById('diagnosticoModalInput').value = datos.data.diagnostico;
    document.getElementById('trabajoModalInput').value = datos.data.trabajos;
    document.getElementById('observacionModalInput').value = datos.data.observaciones;

    // ACTUALIZAR EL TEXTO DEL H5 SEGÚN EL TIPO
    const modalTitle = document.getElementById('cabeceraModal');
    switch(cabecera) {
      case 'antecedente':
        modalTitle.textContent = 'Modificar Antecedente';
        break;
      case 'conclusion':
        modalTitle.textContent = 'Modificar Conclusión';
        break;
      case 'recomendacion':
        modalTitle.textContent = 'Modificar Recomendación';
        break;
      default:
        modalTitle.textContent = 'Modificar';
    }
  } 
  catch (error) {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error,
      timer: 2000
    });
  }
}

// FUNCIÓN MODIFICAR ACTIVIDAD DETALLE
const FnModificarActividad = async () => {
  const id = document.getElementById('txtIdtblDetalleInf').value;
  const actividad = document.getElementById('actividadModalInput').value;
  const diagnostico = document.getElementById('diagnosticoModalInput').value;
  const trabajos = document.getElementById('trabajoModalInput').value;
  const observaciones = document.getElementById('observacionModalInput').value;

  if (isNaN(id)) {
      Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'ID debe ser un número',
          timer: 2000
      });
      return;
  }

  const formData = new FormData();
  formData.append('id', id);
  formData.append('actividad', actividad);
  formData.append('diagnostico', diagnostico);
  formData.append('trabajos', trabajos);
  formData.append('observaciones', observaciones);

  try {
      const response = await fetch('http://localhost/informes/update/ModificarActividad.php', {
          method: 'POST',
          body: formData
      });

      if (!response.ok) {
          const errorText = await response.text();
          Swal.fire({
              icon: 'error',
              title: 'Error',
              text: errorText,
              timer: 2000
          });
          return;
      }

      const datos = await response.json();
      if (!datos.res) {
          Swal.fire({
              icon: 'error',
              title: 'Error',
              text: datos.msg,
              timer: 2000
          });
          return;
      }

      Swal.fire({
          icon: 'success',
          title: 'Éxito',
          text: datos.msg,
          timer: 2000
      });
      setTimeout(() => {
        location.reload();
      }, 1000);

      // CERRAR MODAL
      const modalEditarActividad = bootstrap.Modal.getInstance(document.getElementById('modalGeneral'));
      if (modalEditarActividad) {
          modalEditarActividad.hide();
      }
  } catch (error) {
      Swal.fire({
          icon: 'error',
          title: 'Error',
          text: `Se produjo un error inesperado: ${error.message}`,
          timer: 2000
      });
  }
};

// FUNCIÓN ELIMINAR
const abrirModalEliminar = async (id) => {
  const formData = new FormData();
  formData.append('id', id);
  console.log(id);
  try {
    const response = await fetch('http://localhost/informes/delete/EliminarActividad.php', {
      method: 'POST',
      body: formData
    });

    if (!response.ok) {
      throw new Error(`Error en la respuesta del servidor: ${response.statusText}`);
    }
    const result = await response.json();
    if (result.res) {
      Swal.fire({
        title: "Éxito",
        text: result.msg,
        icon: "success",
        timer: 2000
      }).then(() => {
        location.reload();
      });
    } else {
      Swal.fire({
        title: "Error",
        text: result.msg,
        icon: "error",
        timer: 2000
      });
    }
  } catch (error) {
    Swal.fire({
      title: "Error",
      text: error.message,
      icon: "error",
      timer: 2000
    });
  }
};

