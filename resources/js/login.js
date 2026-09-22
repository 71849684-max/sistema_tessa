'use strict';
const form=document.querySelector('#login-form'),
    mensaje=document.querySelector('#login-message'),
    captcha=document.querySelector('#captcha');
document.querySelector('#refresh-captcha')?.addEventListener('click',()=>{captcha.src=window.TESSA_BASE+'controllers/seguridad/captcha_controller.php?x='+Date.now();});
form?.addEventListener('submit',async event=>{event.preventDefault();mensaje.textContent='';
    const datos=Object.fromEntries(new FormData(form));
    try{const respuesta=await fetch(form.action,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json',Accept:'application/json'},body:JSON.stringify(datos)});const json=await respuesta.json();if(json.exito){location.href=window.TESSA_BASE+json.datos.redirigir;}else{mensaje.textContent=json.mensaje;captcha.src=window.TESSA_BASE+'controllers/seguridad/captcha_controller.php?x='+Date.now();}}catch{mensaje.textContent='No se pudo comunicar con el servidor.';}});
