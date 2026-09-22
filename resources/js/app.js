'use strict';
document.addEventListener('click',event=>{
    const sidebar=document.querySelector('[data-sidebar]');
    if(event.target.closest('[data-sidebar-toggle]')){sidebar?.classList.toggle('open');document.body.classList.toggle('menu-open',sidebar?.classList.contains('open'));}
    if(event.target.closest('[data-sidebar-backdrop]')||event.target.closest('.menu-group-items a')){sidebar?.classList.remove('open');document.body.classList.remove('menu-open');}
    const group=event.target.closest('[data-menu-group-trigger]');
    if(group){const parent=group.closest('[data-menu-group]');const open=!parent.classList.contains('is-open');parent.classList.toggle('is-open',open);group.setAttribute('aria-expanded',String(open));}
    const opener=event.target.closest('[data-dialog-open]');
    if(opener){const dialog=document.getElementById(opener.dataset.dialogOpen);if(dialog&&!dialog.open)dialog.showModal();}
    const closer=event.target.closest('[data-dialog-close]');
    if(closer)closer.closest('dialog')?.close();
    if(event.target instanceof HTMLDialogElement)event.target.close();
});
document.addEventListener('keydown',event=>{if(event.key==='Escape'){document.querySelector('[data-sidebar]')?.classList.remove('open');document.body.classList.remove('menu-open');}});
document.querySelector('.logout')?.closest('form')?.addEventListener('submit',async event=>{event.preventDefault();try{await tessaApi(event.currentTarget.action,{accion:'logout'});location.href=window.TESSA_BASE+'capacliente/app/login.php';}catch{location.href=window.TESSA_BASE+'capacliente/app/login.php';}});
