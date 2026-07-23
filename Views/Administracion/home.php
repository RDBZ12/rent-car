<?php include "Views/Templates/header.php"; ?>

<div class="row g-4 mb-5">
    <!-- Usuarios -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-summary border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div>
                    <p class="text-muted mb-1 fw-medium">Usuarios</p>
                    <h2 class="mb-0 fw-bold"><?php echo $data['usuarios']['total']; ?></h2>
                </div>
                <div class="icon-box bg-light-primary">
                    <i class="fas fa-user"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0 px-4 pb-4">
                <a class="small text-primary fw-semibold stretched-link text-decoration-none" href="<?php echo base_url; ?>Usuarios">
                    Ver detalles <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Clientes -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-summary border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div>
                    <p class="text-muted mb-1 fw-medium">Clientes</p>
                    <h2 class="mb-0 fw-bold"><?php echo $data['clientes']['total']; ?></h2>
                </div>
                <div class="icon-box bg-light-success">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0 px-4 pb-4">
                <a class="small text-success fw-semibold stretched-link text-decoration-none" href="<?php echo base_url; ?>Clientes">
                    Ver detalles <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Vehículos -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-summary border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div>
                    <p class="text-muted mb-1 fw-medium">Vehículos</p>
                    <h2 class="mb-0 fw-bold"><?php echo $data['vehiculos']['total']; ?></h2>
                </div>
                <div class="icon-box bg-light-danger">
                    <i class="fas fa-car"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0 px-4 pb-4">
                <a class="small text-danger fw-semibold stretched-link text-decoration-none" href="<?php echo base_url; ?>Vehiculos">
                    Ver detalles <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Gamas -->
    <div class="col-xl-3 col-md-6">
        <div class="card card-summary border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div>
                    <p class="text-muted mb-1 fw-medium">Gamas</p>
                    <h2 class="mb-0 fw-bold"><?php echo $data['gamas']['total']; ?></h2>
                </div>
                <div class="icon-box bg-light-warning">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-top-0 px-4 pb-4">
                <a class="small text-warning fw-semibold stretched-link text-decoration-none" href="<?php echo base_url; ?>Gamas">
                    Ver detalles <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold text-dark">Nuestra Flota</h5>
            </div>
            <div class="card-body p-0">
                <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner rounded-bottom">
                        <div class="carousel-item active">
                            <img src="<?php echo base_url; ?>Assets/img/1.jpg" class="d-block w-100" alt="..." style="height: 400px; object-fit: cover;">
                            <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.3); backdrop-filter: blur(5px); border-radius: 15px;">
                                <h3 class="fw-bold">Confort y Seguridad</h3>
                                <p>Vehículos de última generación para tus viajes.</p>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="<?php echo base_url; ?>Assets/img/2.jpg" class="d-block w-100" alt="..." style="height: 400px; object-fit: cover;">
                            <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.3); backdrop-filter: blur(5px); border-radius: 15px;">
                                <h3 class="fw-bold">Variedad de Modelos</h3>
                                <p>Encuentra el auto perfecto para cada ocasión.</p>
                            </div>
                        </div>
                        <div class="carousel-item">
                            <img src="<?php echo base_url; ?>Assets/img/3.jpg" class="d-block w-100" alt="..." style="height: 400px; object-fit: cover;">
                            <div class="carousel-caption d-none d-md-block" style="background: rgba(0,0,0,0.3); backdrop-filter: blur(5px); border-radius: 15px;">
                                <h3 class="fw-bold">Los Mejores Precios</h3>
                                <p>Alquila fácil, rápido y seguro.</p>
                            </div>
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "Views/Templates/footer.php"; ?>