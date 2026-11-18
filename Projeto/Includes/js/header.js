(function () {
    var btnPerfil = document.getElementById('topo-botao');
    var menuPerfil = document.getElementById('topo-menu');
    
    var btnNotif = document.getElementById('topo-sino');
    var menuNotif = document.getElementById('topo-notificacoes');

    // Evento do Perfil (topbar unificada)
    if (btnPerfil && menuPerfil) {
        btnPerfil.addEventListener('click', function (e) {
            e.stopPropagation();
            menuPerfil.classList.toggle('topo-menu--aberto');
            if (menuNotif) menuNotif.classList.remove('topo-notificacoes--aberto');
        });
    }

    // Evento da Notificação (topbar unificada)
    if (btnNotif && menuNotif) {
        btnNotif.addEventListener('click', function (e) {
            e.stopPropagation();
            menuNotif.classList.toggle('topo-notificacoes--aberto');
            if (menuPerfil) menuPerfil.classList.remove('topo-menu--aberto');
        });
    }

    // --- Eventos do Dashboard (header.php antigo) ---
    var dashboardProfileButton = document.getElementById('profileButton');
    var dashboardProfileMenu = document.getElementById('profileMenu');
    var dashboardNotificationButton = document.getElementById('notificationButton');
    var dashboardNotificationMenu = document.getElementById('notificationMenu');

    // Perfil do dashboard
    if (dashboardProfileButton && dashboardProfileMenu) {
        dashboardProfileButton.addEventListener('click', function (e) {
            e.stopPropagation();
            dashboardProfileMenu.classList.toggle('aberto');
            if (dashboardNotificationMenu) dashboardNotificationMenu.classList.remove('aberto');
        });
    }

    // Notificações do dashboard
    if (dashboardNotificationButton && dashboardNotificationMenu) {
        dashboardNotificationButton.addEventListener('click', function (e) {
            e.stopPropagation();
            dashboardNotificationMenu.classList.toggle('aberto');
            if (dashboardProfileMenu) dashboardProfileMenu.classList.remove('aberto');
        });
    }

    // Fechar ao clicar fora (ambos os menus)
    document.addEventListener('click', function () {
        if (menuPerfil) menuPerfil.classList.remove('topo-menu--aberto');
        if (menuNotif) menuNotif.classList.remove('topo-notificacoes--aberto');
        if (dashboardProfileMenu) dashboardProfileMenu.classList.remove('aberto');
        if (dashboardNotificationMenu) dashboardNotificationMenu.classList.remove('aberto');
    });
})();