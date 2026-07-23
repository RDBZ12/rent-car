/*!
    * Start Bootstrap - SB Admin v7.0.3 (https://startbootstrap.com/template/sb-admin)
    * Copyright 2013-2021 Start Bootstrap
    * Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-sb-admin/blob/master/LICENSE)
    */
    // 
// Scripts
// 

window.addEventListener('DOMContentLoaded', event => {

    // Toggle the side navigation
    const sidebarToggle = document.body.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', event => {
            event.preventDefault();
            document.body.classList.toggle('sb-sidenav-toggled');
            localStorage.setItem('sb|sidebar-toggle', document.body.classList.contains('sb-sidenav-toggled'));
        });
    }

    // Corregir comportamiento de submenús (Toggle Real)
    // Buscamos todos los enlaces que controlan colapsables en el sidebar
    const collapseLinks = document.querySelectorAll('.nav-link[data-bs-toggle="collapse"]');
    collapseLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            const targetSelector = this.getAttribute('data-bs-target');
            const targetEl = document.querySelector(targetSelector);
            
            if (targetEl && targetEl.classList.contains('show')) {
                // Si ya está abierto, detenemos el comportamiento por defecto de Bootstrap 
                // y forzamos el cierre manual para lograr el "toggle"
                e.stopPropagation();
                e.preventDefault();
                const bsCollapse = bootstrap.Collapse.getOrCreateInstance(targetEl);
                bsCollapse.hide();
            }
        });
    });

});
