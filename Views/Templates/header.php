<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Panel Administrativo | Alquiler</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo base_url; ?>Assets/css/styles.css?v=1.1" rel="stylesheet" />
    <link href="<?php echo base_url; ?>Assets/DataTables/datatables.min.css?v=2" rel="stylesheet" />
    <link href="<?php echo base_url; ?>Assets/css/jquery-ui.min.css" rel="stylesheet" />
    <link href="<?php echo base_url; ?>Assets/css/estilos.css?v=1.1" rel="stylesheet" />
    <script src="<?php echo base_url; ?>Assets/js/all.min.js"></script>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-light">
        <!-- Navbar Brand-->
        <a class="navbar-brand ps-3" href="<?php echo base_url; ?>Administracion/home">
            <i class="fas fa-car-side"></i> RENT<span class="text-secondary">CAR</span>
        </a>
        <!-- Sidebar Toggle-->
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>


        <!-- Navbar-->
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
                <a class="nav-link" href="<?php echo base_url; ?>Usuarios/perfil" title="Mi Perfil">
                    <span class="badge bg-success">En Línea</span>
                    <i class="fas fa-user-tie"></i> <?php echo $_SESSION['usuario']; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="btn btn-outline-danger btn-sm mt-1 ms-2" href="<?php echo base_url; ?>Usuarios/salir" title="Cerrar Sesión">
                    <i class="fas fa-sign-out-alt"></i> Salir
                </a>
            </li>
        </ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <a class="nav-link" href="<?php echo base_url; ?>Usuarios/perfil">
                            <div class="sb-nav-link-icon">
                                <img class="rounded-circle shadow-sm" src="<?php echo base_url . 'Assets/img/users/' . $_SESSION['perfil']; ?>" width="32" height="32" style="object-fit: cover;">
                            </div>
                            Mi Perfil
                        </a>
                        <?php if ($_SESSION['id_usuario'] == 1) { ?>
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                            <div class="sb-nav-link-icon"><i class="fas fa-cogs fa-2x"></i></div>
                            Administración
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="<?php echo base_url; ?>Usuarios">Usuarios</a>
                                <a class="nav-link" href="<?php echo base_url; ?>Administracion/moneda">Monedas</a>
                                <a class="nav-link" href="<?php echo base_url; ?>Administracion">Configuración</a>
                            </nav>
                        </div>
                        <?php } ?>
                        <a class="nav-link" href="<?php echo base_url; ?>Clientes">
                            <div class="sb-nav-link-icon"><i class="fas fa-users fa-2x"></i></div>
                            Clientes
                        </a>

                        <?php if ($_SESSION['id_usuario'] == 1) { ?>
                            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseVehiculos" aria-expanded="false" aria-controls="collapseVehiculos">
                                <div class="sb-nav-link-icon"><i class="fas fa-truck-moving fa-2x"></i></div>
                                Vehículos
                                <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                            </a>
                            <div class="collapse" id="collapseVehiculos" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                                <nav class="sb-sidenav-menu-nested nav">
                                    <a class="nav-link" href="<?php echo base_url; ?>Marcas">Marcas</a>
                                    <a class="nav-link" href="<?php echo base_url; ?>Modelos">Modelos</a>
                                    <a class="nav-link" href="<?php echo base_url; ?>Gamas">Gamas</a>
                                    <a class="nav-link" href="<?php echo base_url; ?>TiposDia">Tipos de Día</a>
                                    <a class="nav-link" href="<?php echo base_url; ?>Vehiculos">Vehiculos</a>
                                    <a class="nav-link text-primary fw-bold" href="<?php echo base_url; ?>Feriados"><i class="fas fa-calendar-day me-1"></i> Feriados</a>
                                </nav>
                            </div>
                        <?php } ?>
                        <a class="nav-link" href="<?php echo base_url; ?>Reservas">
                            <div class="sb-nav-link-icon"><i class="fas fa-hourglass-start fa-2x"></i></div>
                            Gestión de Reservas
                        </a>
                        <a class="nav-link" href="<?php echo base_url; ?>Pagos">
                            <div class="sb-nav-link-icon"><i class="fas fa-money-bill fa-2x"></i></div>
                            Pagos Realizados
                        </a>
                        <a class="nav-link" href="<?php echo base_url; ?>Recepciones">
                            <div class="sb-nav-link-icon"><i class="fas fa-undo-alt fa-2x"></i></div>
                            Recepción de Vehículos
                        </a>
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseConsultas" aria-expanded="false" aria-controls="collapseConsultas">
                            <div class="sb-nav-link-icon"><i class="fas fa-search fa-2x"></i></div>
                            Consultas
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseConsultas" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="<?php echo base_url; ?>Consultas/vehiculos_disponibles">Vehículos Disponibles</a>
                                <a class="nav-link" href="<?php echo base_url; ?>Consultas/alquileres_activos">Alquileres Activos</a>
                                <a class="nav-link" href="<?php echo base_url; ?>Consultas/historial_cliente">Historial por Cliente</a>
                                <a class="nav-link" href="<?php echo base_url; ?>Consultas/estado_cuenta">Estado de Cuenta</a>
                                <a class="nav-link" href="<?php echo base_url; ?>Consultas/vehiculos_feriados">Vehículos en Feriados</a>
                            </nav>
                        </div>
                        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseReportes" aria-expanded="false" aria-controls="collapseReportes">
                            <div class="sb-nav-link-icon"><i class="fas fa-chart-bar fa-2x"></i></div>
                            Reportes
                            <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                        </a>
                        <div class="collapse" id="collapseReportes" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                            <nav class="sb-sidenav-menu-nested nav">
                                <a class="nav-link" href="<?php echo base_url; ?>Reportes/ingresos_fecha">Ingresos por Fecha</a>
                                <a class="nav-link" href="<?php echo base_url; ?>Reportes/vehiculos_rentados">Vehículos más Rentados</a>
                                <a class="nav-link" href="<?php echo base_url; ?>Reportes/clientes_frecuentes">Clientes Frecuentes</a>
                            </nav>
                        </div>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small text-capitalize"><?php echo $_SESSION['nombre']; ?></div>
                    <?php echo $_SESSION['usuario']; ?>
                </div>
            </nav>
        </div>
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4 mt-4">