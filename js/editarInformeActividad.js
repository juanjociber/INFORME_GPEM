// INICIALIZANDO VARIABLES PARA MODAL GLOBAL
let modalEditarActividad;

document.addEventListener('DOMContentLoaded', () => {
  modalEditarActividad = new bootstrap.Modal(document.getElementById('modalEditarActividad'), { keyboard: false });
});

//FUNCIÓN CREA ACTIVIDAD
const fnCrearActividad = async () => {
  const actividad = document.getElementById('guardarNombreActividadInput').value.trim();
  const diagnostico = document.getElementById('guardarDiagnosticoInput').value.trim();
  const trabajos = document.getElementById('guardarTrabajoInput').value.trim();
  const observaciones = document.getElementById('guardarObservacionInput').value.trim();
  if (!actividad) {
      Swal.fire({
          title: 'Aviso',
          text: 'Por favor, ingrese el nombre de la actividad.',
          icon: 'info',
          confirmButtonText: 'OK',
          timer:2000
      });
      return;
  }
  const infid = document.getElementById('guardarActividadInput').value;
  const formData = new FormData();
  formData.append('infid', infid);
  formData.append('actividad', actividad);
  formData.append('diagnostico', diagnostico);
  formData.append('trabajos', trabajos);
  formData.append('observaciones', observaciones);

  try {
    const response = await fetch('http://localhost/informes/insert/AgregarActividad.php', {
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
            timer:2000
        });
        const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaActividad'));
        if (modal) {
            modal.hide();
        }
        setTimeout(() => {
          location.reload();
        }, 2000);
    } else {
        Swal.fire({
          title: 'Error',
          text: result.msg,
          icon: 'error',
          confirmButtonText: 'OK',
          timer:2000
        });
    }
  } catch (error) {
      Swal.fire({
        title: 'Error',
        text: `Se produjo un error inesperado: ${error.message}`,
        icon: 'error',
        confirmButtonText: 'OK',
        timer:2000
      });
  }
};

// CREAR SUBACTIVIDAD
const fnCrearSubActividad = async (id) => {
  const modal = new bootstrap.Modal(document.getElementById('modalNuevaSubActividad'), { keyboard: false });
  modal.show();
  document.getElementById('cabeceraIdInput').value = id;
};

// GUARDAR SUB-ACTIVIDAD
const fnGuardarSubActividad = async () =>{
  const actividad = document.getElementById('guardarNombreSubActividadInput').value.trim();
  const diagnostico = document.getElementById('guardarDiagnosticoSubActividadInput').value.trim();
  const trabajos = document.getElementById('guardarTrabajoSubActividadInput').value.trim();
  const observaciones = document.getElementById('guardarObservacionSubActividadInput').value.trim(); 
  // VERIFICAR SI SE HA INGRESADO NOMBRE DE ACTIVIDAD
  if (!actividad) {
    Swal.fire({
      title: 'Aviso',
      text: 'Debe ingresar el nombre de la subactividad.',
      icon: 'info',
      confirmButtonText: 'OK',
      timer:2000
  });
  return;
  }
  // OBTENIENDO ID DEL INFORME
  const infid = document.getElementById('guardarSubActividadInput').value; 
  const ownid = document.getElementById('cabeceraIdInput').value; 
  // CREAR FORM-DATA
  const formData = new FormData();
  formData.append('infid', infid);
  formData.append('ownid', ownid);
  formData.append('actividad', actividad);
  formData.append('diagnostico', diagnostico);
  formData.append('trabajos', trabajos);
  formData.append('observaciones', observaciones);
  console.log(infid,ownid, actividad, diagnostico, trabajos, observaciones);

  try {
    const response = await fetch('http://localhost/informes/insert/AgregarActividad.php', {
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
        confirmButtonText: 'OK'
      });
      
      const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaSubActividad'));
      if (modal) {
          modal.hide();
      }
      setTimeout(() => {
        location.reload();
      }, 2000);
    } else {
      Swal.fire({
        title: 'Error',
        text: result.msg,
        icon: 'error',
        confirmButtonText: 'OK',
        timer:2000
      });
    }
  } catch (error) {
    Swal.fire({
      title: 'Error',
      text: `Se produjo un error inesperado: ${error.message}`,
      icon: 'error',
      confirmButtonText: 'OK',
      timer:2000
    });
  }
};

//BUSCAR ACTIVIDAD
const fnEditarActividad = async (id) => {
  modalEditarActividad.show();
  const formData = new FormData();
  formData.append('id', id);

  try {
    const response = await fetch('http://localhost/informes/search/buscarActividad.php', {
        method: 'POST',
        body: formData
    });

    if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
    }
    const datos = await response.json();

    if (!datos.res) {
        throw new Error(datos.msg);
    }
    document.getElementById('editarActividadInput').value = datos.data.id;
    document.getElementById('editarNombreActividadInput').value = datos.data.actividad;
    document.getElementById('editarDiagnosticoInput').value = datos.data.diagnostico;
    document.getElementById('editarTrabajoInput').value = datos.data.trabajos;
    document.getElementById('editarObservacionInput').value = datos.data.observaciones;
  } catch (error) {
    Swal.fire({
        title: 'Error',
        text: error.message,
        icon: 'error',
        confirmButtonText: 'OK',
        timer:2000
    });
  }
};


//MODIFICAR ACTIVIDAD
const FnModificarActividad = async () => {
  const id = document.getElementById('editarActividadInput').value;
  const actividad = document.getElementById('editarNombreActividadInput').value;
  const diagnostico = document.getElementById('editarDiagnosticoInput').value;
  const trabajo = document.getElementById('editarTrabajoInput').value;
  const observacion = document.getElementById('editarObservacionInput').value;

  const formData = new FormData();
  formData.append('id', id);
  formData.append('actividad', actividad);
  formData.append('diagnostico', diagnostico);
  formData.append('trabajos', trabajo);
  formData.append('observaciones', observacion);

  try {
    const response = await fetch('http://localhost/informes/update/ModificarActividad.php', {
      method: 'POST',
      body: formData
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status} ${response.statusText}`);
    }

    const datos = await response.json();

    if (!datos.res) {
      throw new Error(datos.msg);
    }
    document.querySelector(`#accordion-header-${id} .accordion-button`).textContent = actividad;
    document.getElementById(`diagnostico-${id}`).textContent = diagnostico;
    document.getElementById(`trabajo-${id}`).textContent = trabajo;
    document.getElementById(`observacion-${id}`).textContent = observacion;
    Swal.fire({
      title: 'Éxito',
      text: datos.msg,
      icon: 'success',
      confirmButtonText: 'OK',
      timer:2000
    });

    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalEditarActividad'));
    if (modalInstance) {
      modalInstance.hide();
    }

  } catch (error) {
    Swal.fire({
      title: 'Error',
      text: error.message,
      icon: 'error',
      confirmButtonText: 'OK',
      timer:2000
    });
  }
};

// ABRIR MODAL REGISTRAR IMAGEN
const fnAbrirModalRegistrarImagen = (id) => {
  const modal = new bootstrap.Modal(document.getElementById('modalAgregarImagen'), { keyboard: false });
  modal.show();
  document.getElementById('cabeceraIdInput').value = id;

};

/**================================
 FUNCIONES PARA CARGA DE IMÁGENES
===================================* 
*/
const MAX_WIDTH = 1080;
const MAX_HEIGHT = 720;
const MIME_TYPE = "image/jpeg";
const QUALITY = 0.7;

const $divImagen = document.getElementById("divImagen");

document.getElementById('fileImagen').addEventListener('change', function(event) {
  // vgLoader.classList.remove('loader-full-hidden');
  
  const file = event.target.files[0];

  if (!isValidFileType(file)) {
      console.log('El archivo', file.name, 'Tipo de archivo no permitido.');
  }

  if (!isValidFileSize(file)) {
      console.log('El archivo', file.name, 'El tamaño del archivo excede los 3MB.');
  }

  while ($divImagen.firstChild) {
      $divImagen.removeChild($divImagen.firstChild);
  }

  if (file.type.startsWith('image/')) {
      displayImage(file);
  }

  console.log('Nombre del archivo:', file.name);
  console.log('Tipo del archivo:', file.type);
  console.log('Tamaño del archivo:', file.size, 'bytes');

  setTimeout(function() {
    //vgLoader.classList.add('loader-full-hidden');
  }, 1000)
});

function isValidFileType(file) {
  const acceptedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
  return acceptedTypes.includes(file.type);
}

function isValidFileSize(file) {
  const maxSize = 3 * 1024 * 1024; // 4MB en bytes
  return file.size <= maxSize;
}

function displayImage(file) {
  const reader = new FileReader();
  reader.onload = function(event) {
      const imageUrl = event.target.result;
      const canvas = document.createElement('canvas');
      canvas.style.border = '1px solid black';

      $divImagen.appendChild(canvas);
      const context = canvas.getContext('2d');

      const image = new Image();
      image.onload = function() {
          const [newWidth, newHeight] = calculateSize(image, MAX_WIDTH, MAX_HEIGHT);
          canvas.width = newWidth;
          canvas.height = newHeight;
          canvas.id="canvas";
          context.drawImage(image, 0, 0, newWidth, newHeight);

          // Agregar texto como marca de agua
          context.strokeStyle = 'rgba(216, 216, 216, 0.7)';// color del texto (blanco con opacidad)
          context.font = '15px Verdana'; // fuente y tamaño del texto
          context.strokeText("GPEM SAC", 10, newHeight-10);// texto y posición

          canvas.toBlob(
              (blob) => {
                  // Handle the compressed image. es. upload or save in local state
                  displayInfo('Original: ', file);
                  displayInfo('Comprimido: ', blob);
              },
              MIME_TYPE,
              QUALITY
          );

      };
      image.src = imageUrl;
  };
  reader.readAsDataURL(file);
}

function displayInfo(label, file) {
  const p = document.createElement('p');
  p.classList.add('text-secondary', 'm-0', 'fs-6');
  p.innerText = `${label} ${readableBytes(file.size)}`;
  $divImagen.append(p);
}

function readableBytes(bytes) {
  const i = Math.floor(Math.log(bytes) / Math.log(1024)),
  sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
  return (bytes / Math.pow(1024, i)).toFixed(2) + ' ' + sizes[i];
}

function calculateSize(img, maxWidth, maxHeight) {
  let width = img.width;
  let height = img.height;
  // calculate the width and height, constraining the proportions
  if (width > height) {
      if (width > maxWidth) {
          height = Math.round((height * maxWidth) / width);
          width = maxWidth;
      }
  } else {
      if (height > maxHeight) {
          width = Math.round((width * maxHeight) / height);
          height = maxHeight;
      }
  }
  return [width, height];
}

async function FnAgregarImagen(){
  //vgLoader.classList.remove('loader-full-hidden');
  try {
      var archivo;

      if(document.getElementById('canvas')){
          archivo = document.querySelector("#canvas").toDataURL("image/jpeg");
      }else if(document.getElementById('fileImagen').files.length == 1){
          archivo = fileOrCanvasData = document.getElementById('fileImagen').files[0];
      }else{
          throw new Error('No se reconoce el archivo');
      }

      const formData = new FormData();
      formData.append('refid', document.getElementById('cabeceraIdInput').value);
      formData.append('titulo', document.getElementById('txtTitulo').value);
      formData.append('descripcion', document.getElementById('txtDescripcion').value);
      formData.append('archivo', archivo);
      formData.append('tabla', document.getElementById('tabla').value);

      const response = await fetch('http://localhost/informes/insert/AgregarArchivo.php', {
          method:'POST',
          body: formData
      });

      if(!response.ok){throw new Error(`${response.status} ${response.statusText}`);}
      const datos = await response.json();
      if(!datos.res){throw new Error(datos.msg);}
      Swal.fire({
        title: "Éxito",
        text: datos.msg,
        icon: "success",
        timer:2000
      });
      setTimeout(function() {location.reload();}, 1000)

  } catch (error) {
      document.getElementById('msjAgregarImagen').innerHTML = `<div class="alert alert-danger m-0 p-1 text-center" role="alert">${error.message}</div>`;
      setTimeout(function() {
        //vgLoader.classList.add('loader-full-hidden');
      }, 1000)
  }
}

//ELIMINAR ARCHIVO
const fnEliminarImagen = async (id) => {
  const formData = new FormData();
  formData.append('id', id);
  try {
    const response = await fetch('http://localhost/informes/delete/EliminarArchivo.php', {
      method: 'POST',
      body: formData,
      headers: {
        'Accept': 'application/json'
      }
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status} ${response.statusText}`);
    }

    const result = await response.json();

    if (result.res) {
      const elemento = document.getElementById(id);
      if (elemento) {
        elemento.remove();
      }
      Swal.fire({
        title: "Éxito",
        text: result.msg,
        icon: "success",
        timer:2000
      }).then(() => {
        location.reload();
      });
    } else {
      Swal.fire({
        title: "Error",
        text: result.msg,
        icon: "error",
        timer:2000
      });
    }
  } catch (error) {
    Swal.fire({
      title: "Error",
      text: error.message,
      icon: "error",
      timer:2000
    });
  }
};


// FUNCIÓN ELIMINAR ACTIVIDAD
const fnEliminarActividad = async (id) => {
  const formData = new FormData();
  formData.append('id', id);

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
        const actividadDiv = document.getElementById(id);
        if (actividadDiv) {
          actividadDiv.remove();
        } 
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

















