
// const modalFinalizarInforme=new bootstrap.Modal(document.getElementById('modalFinalizarInforme'), {
//     keyboard: false
// });

function FnListarInformes(){
  window.location.href='/informes/buscarInforme.php';
  return false;
}

function FnEditarInforme(){
  let informe = document.getElementById('idInforme').value;
  if(informe > 0){
      window.location.href='/informes/datoGeneral.php?id='+informe;
  }
  return false;
}

// function FnModalFinalizarInforme(){
//   document.getElementById('msjFinalizarInforme').innerHTML = '';
//   modalFinalizarInforme.show();
// };

async function FnFinalizarInforme(){
  //vgLoader.classList.remove('loader-full-hidden');
  try {
      const formData = new FormData();
      formData.append('id', document.getElementById('idInforme').value);
      const response = await fetch('http://localhost/informes/update/FinalizarInforme.php', {
          method:'POST',
          body: formData
      });

      if(!response.ok){
          throw new Error(`${response.status} ${response.statusText}`)
      }

      const datos = await response.json();
      
      if(datos.res){
          location.reload();
      }else{
          throw new Error(datos.msg)
      }
  } catch (error) {
      document.getElementById('msjFinalizarOrden').innerHTML = `<div class="alert alert-danger m-0 p-1 text-center" role="alert">${error}</div>`;
  }
  
  setTimeout(function () {
      vgLoader.classList.add('loader-full-hidden');
  }, 500);

  return false;
}