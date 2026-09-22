'use strict';

(()=>{
    const form=document.querySelector('[data-crud-form]');
    if(!form)return;
    const contract=form.querySelector('[data-cobranza-contrato]');
    const load=form.querySelector('[data-cargar-hitos]');
    const target=form.querySelector('[data-hitos-cobranza]');
    const hidden=form.querySelector('[data-detalles-json]');
    fetch(window.TESSA_BASE+'controllers/ventas/cobranza_controller.php?accion=contratos',{credentials:'same-origin',headers:{Accept:'application/json'}}).then(response=>response.json()).then(json=>{
        if(!json.exito)throw new Error(json.mensaje);
        contract.replaceChildren();
        const empty=document.createElement('option');empty.value='';empty.textContent='Seleccione un contrato…';contract.append(empty);
        (json.datos||[]).filter(item=>item.estado==='ACTIVO').forEach(item=>{const option=document.createElement('option');option.value=String(item.id_contrato);option.textContent=`${item.con_numero} · ${item.cliente_nombre} · saldo S/ ${Number(item.saldo).toFixed(2)}`;contract.append(option);});
    }).catch(()=>{contract.replaceChildren();const option=document.createElement('option');option.value='';option.textContent='No se pudieron cargar contratos.';contract.append(option);});
    const render=rows=>{target.textContent='';if(!rows.length){target.textContent='No hay cuotas pendientes para este contrato.';return;}rows.forEach(row=>{const item=document.createElement('label');item.className='payment-line';const check=document.createElement('input');check.type='checkbox';check.dataset.hito=String(row.id_hito);const name=document.createElement('span');name.textContent=`${row.numero}. ${row.descripcion} · saldo S/ ${Number(row.saldo).toFixed(2)}`;const amount=document.createElement('input');amount.type='number';amount.step='0.01';amount.min='0.01';amount.max=String(row.saldo);amount.placeholder='Monto';amount.disabled=true;check.addEventListener('change',()=>{amount.disabled=!check.checked;if(check.checked)amount.focus();});item.append(check,name,amount);target.append(item);});};
    load.addEventListener('click',async()=>{const id=Number(contract.value);if(!id){target.textContent='Indique un contrato válido.';return;}try{const response=await fetch(window.TESSA_BASE+'controllers/ventas/cobranza_controller.php?'+new URLSearchParams({accion:'hitos',id_contrato:String(id)}),{credentials:'same-origin',headers:{Accept:'application/json'}});const json=await response.json();render(json.datos||[]);}catch{target.textContent='No se pudieron cargar las cuotas.';}});
    form.addEventListener('submit',()=>{hidden.value=JSON.stringify([...target.querySelectorAll('input[data-hito]:checked')].map(check=>({id_hito:Number(check.dataset.hito),monto:Number(check.parentElement.querySelector('input[type="number"]').value)})));});
    contract.addEventListener('change',()=>{target.textContent='Pulse “Cargar cuotas” para consultar los saldos.';hidden.value='';});
    document.querySelector('[data-dialog-open="crud-editor"]')?.addEventListener('click',()=>{target.textContent='Seleccione el contrato y cargue sus cuotas pendientes.';hidden.value='';const now=new Date();form.elements.namedItem('fecha').value=new Date(now.getTime()-now.getTimezoneOffset()*60000).toISOString().slice(0,16);});
})();
