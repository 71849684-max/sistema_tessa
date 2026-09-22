'use strict';
window.tessaApi=async function(url,datos={},archivo=false)
{const opciones={method:'POST',credentials:'same-origin',headers:{Accept:'application/json'}
};
    const csrf=document.querySelector('meta[name="csrf-token"]')?.content;
    if(csrf){opciones.headers['X-CSRF-Token']=csrf;}if(archivo){opciones.body=datos;}
    else{opciones.headers['Content-Type']='application/json';opciones.body=JSON.stringify(datos);}
    const respuesta=await fetch(url,opciones);
    const texto=await respuesta.text();let json;try{json=JSON.parse(texto);}
catch{throw new Error('Respuesta inválida del servidor.');}if(respuesta.status===401){location.href=window.TESSA_BASE+'capacliente/app/login.php';}return json;};
