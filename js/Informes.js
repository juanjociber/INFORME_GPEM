var vgEquipo = 0;
var vgOrden = '';
var vgFechaInicial = '';
var vgFechaFinal = '';
var vgPagina = 0;
var NextPage = false;
const vgLoader = document.querySelector('.container-loader-full');

window.onload = function() {
    document.getElementById('MenuOrdenes').classList.add('menu-activo','fw-bold');
    vgLoader.classList.add('loader-full-hidden');
};

$(document).ready(function() {
    $('#cbActivo1').select2({
        width: 'resolve', //Personalizar el alto del select, aplicar estilo.
        ajax: {
            delay: 450, //Tiempo de demora para buscar
            url: '/gesman/search/ListarActivos.php',
            type: 'POST',
            dataType: 'json',
            data: function (params) {
                return {
                    nombre: params.term // parametros a enviar al server. params.term captura lo que se escribe en el input
                };
            },
            processResults: function (data) {
                return {
                    results: data.data //Retornar el json obtenido
                }
            },
            cache: true
        },
        placeholder: 'Seleccionar',
        allowClear: true, // Permite borrar la selección
        minimumInputLength:1 //Caracteres minimos para buscar
    });
});


$(document).ready(function() {
    $('#cbActivo2').select2({
        dropdownParent: $('#modalAgregarOrden'),//Agregar el select a un modal
        width: 'resolve', //Personalizar el alto del select, aplicar estilo.
        ajax: {
            delay: 450, //Tiempo de demora para buscar
            url: '/gesman/search/ListarActivos.php',
            type: 'POST',
            dataType: 'json',
            data: function (params) {
                return {
                    nombre: params.term // parametros a enviar al server. params.term captura lo que se escribe en el input
                };
            },
            processResults: function (data) {
                return {
                    results: data.data //Retornar el json obtenido
                }
            },
            cache: true
        },
        placeholder: 'Seleccionar',
        minimumInputLength:1 //Caracteres minimos para buscar
    });/*.on('select2:select', function (e) {
        nuevaOrden.idactivo=e.params.data.id;
        nuevaOrden.activo=e.params.data.text;
        console.log(nuevaOrden);
    });*/
});

function FnModalAgregarOrden(){
    const modalAgregarOrden=new bootstrap.Modal(document.getElementById('modalAgregarOrden'), {
        keyboard: false
    }).show();
    return false;
}

async function FnAgregarOrden(){
    vgLoader.classList.remove('loader-full-hidden');
    try {        
        const formData = new FormData();
        formData.append('fecha', document.getElementById('txtFecha2').value);
        formData.append('orden', document.getElementById('txtOrden2').value);
        formData.append('idactivo', document.getElementById('cbActivo2').value);
        formData.append('activo', document.getElementById("cbActivo2").options[document.getElementById("cbActivo2").selectedIndex].text);
        formData.append('km', document.getElementById('txtKm2').value);
        formData.append('idtipo', document.getElementById('cbTipo2').value);
        formData.append('tipo', document.getElementById("cbTipo2").options[document.getElementById("cbTipo2").selectedIndex].text);        
        formData.append('idsistema', document.getElementById('cbSistema2').value);
        formData.append('sistema', document.getElementById("cbSistema2").options[document.getElementById("cbSistema2").selectedIndex].text);
        formData.append('actividad', document.getElementById('txtActividad2').value);
        const response = await fetch("/gesman/insert/AgregarOrden.php", {
            method: "POST",
            body: formData
        });/*.then(response=>response.text()).then((response)=>{console.log(response)}).catch(err=>console.log(err))*/        
        if(!response.ok){throw new Error(`${response.status} ${response.statusText}`)}
        const datos = await response.json();
        if(!datos.res){throw new Error(datos.msg);}
        setTimeout(()=>{window.location.href='/gesman/EditarOrden.php?orden='+datos.id;},1000);
    } catch (error) {
        document.getElementById('msjAgregarOrden').innerHTML=`<div class="alert alert-danger mb-2 p-1 text-center" role="alert">${error}</div>`;
        setTimeout(()=>{vgLoader.classList.add('loader-full-hidden');},500);
    }
}

function FnBuscarInformes(){
    vgOrden = document.getElementById('txtNombre').value;
    vgEquipo = document.getElementById('cbEquipo').value;
    vgFechaInicial = document.getElementById('dtpFechaInicial').value;
    vgFechaFinal = document.getElementById('dtpFechaFinal').value;
    vgPagina = 0;
    document.getElementById('tblInformes').innerHTML = '';
    FnBuscarInformes2();
    return false;
}

async function FnBuscarInformes2(){
    vgLoader.classList.remove('loader-full-hidden');
    NextPage = false;
    try {
        const formData = new FormData();
        formData.append('nombre', vgOrden);
        formData.append('equid', vgEquipo);
        formData.append('fechainicial', vgFechaInicial);
        formData.append('fechafinal', vgFechaFinal);
        formData.append('pagina', vgPagina);

        console.log(vgFechaInicial, vgFechaFinal)

        const response = await fetch('/informes/search/BuscarInformes.php', {
            method:'POST',
            body: formData
        });/*.then(response=>response.text()).then((response)=>{console.log(response)}).catch(err=>console.log(err));*/

        if(response.ok){
            const datos = await response.json();
            console.log(datos);
            if(datos.res){
                let estado = '';
                datos.data.forEach(informe => {
                    switch (informe.estado){
                        case 1:
                            estado='<span class="badge bg-danger">Anulado</span>';
                        break;
                        case 2:
                            estado='<span class="badge bg-primary">Proceso</span>';
                        break;
                        case 3:
                            estado='<span class="badge bg-success">Cerrado</span>';
                        break;
                        default:
                            estado='<span class="badge bg-light text-dark">Unknown</span>';
                    }

                    document.getElementById('tblInformes').innerHTML +=`
                    <div class="col-12 divselect border-bottom border-secondary mb-1 p-1">
                        <a class="link-colecciones" href="#" onclick="FnResumenInforme(${informe.id}); return false;">
                            <div class="row">
                                <div class="col-8"><span class="fw-bold">${informe.nombre}</span> <span style="font-size: 12px; font-style: italic;">${informe.fecha}</span></div>
                                <div class="col-4 text-end">${estado}</div>
                                <div class="col-12">${informe.actividad}</div>
                            </div>
                        </a>
                    </div>`;
                });

                vgPagina += datos.pag;
                console.log(datos.pag);
                if (datos.pag == 20) {
                    NextPage = true;
                    document.getElementById("divPaginacion").classList.remove("d-none");
                }
            }else{
                throw new Error(datos.msg);
            }
        }else{
            throw new Error(`${response.status} ${response.statusText}`);
        }
    } catch (error) {
        alert(error);
    }

    await new Promise((resolve, reject) => {
        setTimeout(function () {
            vgLoader.classList.add('loader-full-hidden');
        }, 500)
    });
}

function fnNuevaPagina() {
    if (NextPage) {
        FnBuscarOrdenes2();
    }else{
        document.getElementById("divPaginacion").classList.add("d-none");
    }
    return false;
}

function FnResumenOrden(orden){
    if(orden > 0){
        window.location.href='/gesman/ResumenOrden.php?orden='+orden;
    }
    return false;
}
