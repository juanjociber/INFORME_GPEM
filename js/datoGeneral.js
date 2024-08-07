// FUNCIÓN SELECT PERSONALIZADO
const cargaSelect = () => {
  const initCustomSelect = (inputId, listId) => {
    const selectInput = document.getElementById(inputId);
    const selectList = document.getElementById(listId);
    const selectItems = selectList.getElementsByClassName('custom-select-item');
    // MOSTRAR / OCULTAR LISTA AL HACER CLIC EN INPUT
    selectInput.addEventListener('click', function() {
        selectList.style.display = selectList.style.display === 'block' ? 'none' : 'block';
    });
    // SELECCIONAR UN ELEMENTO DE LA LISTA
    Array.from(selectItems).forEach(item => {
        item.addEventListener('click', function() {
            selectInput.value = this.textContent.trim();
            //GUARDANDO VALOR ASOCIONADO AL SELECCIONAR
            selectInput.dataset.value = this.dataset.value;
            selectList.style.display = 'none';
        });
    });
    // OCULTAR LISTA SI SE HACE CLIC FUERA DE LISTA
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.custom-select-wrapper')) {
            selectList.style.display = 'none';
        }
    });
    // FILTRAR ELEMENTOS DE LA LISTA AL ESCRIBIR EN EL INPUT
    selectInput.addEventListener('input', function() {
        const filter = selectInput.value.toLowerCase();
        let textoEncontrado = false;
        Array.from(selectItems).forEach(item => {
            const text = item.textContent.toLowerCase();
            if (text.includes(filter)) {
                item.style.display = '';
                textoEncontrado = true;
            } else {
                item.style.display = 'none';
            }
        });
        selectList.style.display = 'block';

        // LIMPIAR EL INPUT SI NO HAY CONCIDENCIAS
        if (!textoEncontrado) {
            selectInput.value = '';
            // MOSTRAR TODAS LAS OPCIONES DE LA LISTA
            Array.from(selectItems).forEach(item => {
                item.style.display = '';
            });
        }
    });
  }
  // INICIALIZANDO SELECT
  initCustomSelect('contactoInput', 'contactoList');
  initCustomSelect('supervisorInput', 'supervisorList');
};

// LLAMANDO FUNCIÓN CARGA DE SELECT
document.addEventListener('DOMContentLoaded', cargaSelect);

// FUNCIÓN GUARDAR DATOS GENERALES
const fnGuardarDatosGenerales = async () => {
  const formData = new FormData();      
  formData.append('id', document.querySelector('#idInforme').value);
  formData.append('fecha', document.querySelector('#fechaInformeInput').value.trim());
  formData.append('clicontacto', document.querySelector('#contactoInput').value.trim()); 
  formData.append('ubicacion', document.querySelector('#ubicacionInput').value.trim()); 
  formData.append('supervisor', document.querySelector('#supervisorInput').value.trim());
  //console.log('Datos a enviar: ', {id, fecha, clicontacto, ubicacion, supervisor });
  try {
    const response = await fetch('http://localhost/informes/update/ModificarDatosGenerales.php', {
      method: 'POST',
      body: formData
    });

    if (!response.ok) {
      throw new Error(response.status + ' ' + response.statusText);
    }
    const datos = await response.json();

    if (datos.res) {
      Swal.fire({
        title: "Información de servidor",
        text: datos.msg,
        icon: "success",
        timer:2000,
      });
    } else {
      Swal.fire({
        title: "Respuesta del servidor",
        text: datos.msg,
        icon: "info",
        timer:2000
      });
    }
  } catch (error) {
    Swal.fire({
      title: "Información de servidor",
      text: error,
      icon: "info",
      timer:2000,
    });
  }
}
  

