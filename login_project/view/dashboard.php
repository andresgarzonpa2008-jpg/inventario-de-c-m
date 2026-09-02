<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Sistema de Gestión - C&M Soluciones Abrasivas" />
        <title>C&M SOLUCIONES ABRASIVAS</title>
        
        <!-- CSS -->
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
        <!-- Top Navbar -->
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <a class="navbar-brand ps-3" href="index.html">C&M ABRASIVAS</a>
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Navbar Search -->
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Buscar productos, clientes..." aria-label="Buscar..." aria-describedby="btnNavbarSearch" />
                    <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
                </div>
            </form>
            
            <!-- Navbar User Menu -->
            <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user fa-fw"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="#!">Configuración</a></li>
                        <li><a class="dropdown-item" href="#!">Registro de actividad</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <a href="index.php?action=logout">Cerrar Sesión</a>
                    </ul>
                </li>
            </ul>
        </nav>

        <div id="layoutSidenav">
            <!-- Sidebar Navigation -->
            <div id="layoutSidenav_nav">
                <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
                    <div class="sb-sidenav-menu">
                        <div class="nav">
                            <div class="sb-sidenav-menu-heading">Menú Principal</div>
                            <a class="nav-link active" href="index.php?action=usuario&section=home">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Dashboard
                            </a>
                            
                            <div class="sb-sidenav-menu-heading">Mi Cuenta</div>
                            
                            <a class="nav-link" href="index.php?action=usuario&section=perfil">
                                <div class="sb-nav-link-icon"><i class="fas fa-user-circle"></i></div>
                                Mi Perfil
                            </a>
                            <a class="nav-link" href="index.php?action=usuario&section=configuracion">
                                <div class="sb-nav-link-icon"><i class="fas fa-cog"></i></div>
                                Configuración
                            </a>
                            
                            <?php if ($_SESSION["rol"] === "admin"): ?>
                            <div class="sb-sidenav-menu-heading">Administración</div>
                            
                            <a class="nav-link" href="index.php?action=usuario&section=usuarios">
                                <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                                Gestión de Usuarios
                            </a>
                            <?php endif; ?>
                            
                            <div class="sb-sidenav-menu-heading">Gestión</div>
                            
                            <a class="nav-link" href="index.php?action=usuario&section=productos">
                                <div class="sb-nav-link-icon"><i class="fas fa-box-open"></i></div>
                                Productos
                            </a>
                            <a class="nav-link" href="index.php?action=usuario&section=inventario">
                                <div class="sb-nav-link-icon"><i class="fas fa-boxes"></i></div>
                                Inventario
                            </a>
                            <a class="nav-link" href="index.php?action=usuario&section=ventas">
                                <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                                Ventas
                            </a>
                            <a class="nav-link" href="index.php?action=usuario&section=alertas">
                                <div class="sb-nav-link-icon"><i class="fas fa-exclamation-triangle"></i></div>
                                Alertas de Stock
                            </a>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Inició sesión como:</div>
                        Gerente
                    </div>
                </nav>
            </div>

            <!-- Main Content -->
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">¡Bienvenido, Gerente!</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Resumen general de C&M Soluciones Abrasivas SAS</li>
                        </ol>

                        <!-- Dashboard Cards con Iconos (Marcas amarillas) -->
                        <div class="row">
                            <!-- Ventas del mes -->
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="text-white-50 small">Ventas del mes</div>
                                            <div class="fs-4 fw-bold">$ 24.850.000</div>
                                        </div>
                                        <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="#">Ver Detalles</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pedidos pendientes -->
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-warning text-white mb-4">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="text-white-50 small">Pedidos pendientes</div>
                                            <div class="fs-4 fw-bold">18</div>
                                        </div>
                                        <i class="fas fa-box fa-2x opacity-75"></i>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="#">Ver Detalles</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Productos en stock -->
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="text-white-50 small">Productos en stock</div>
                                            <div class="fs-4 fw-bold">342</div>
                                        </div>
                                        <i class="fas fa-cubes fa-2x opacity-75"></i>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="#">Ver Detalles</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Alertas de stock -->
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-danger text-white mb-4">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="text-white-50 small">Alertas de stock</div>
                                            <div class="fs-4 fw-bold">12</div>
                                        </div>
                                        <i class="fas fa-exclamation-triangle fa-2x opacity-75"></i>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="#">Ver Detalles</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Charts Row (Estructuras de las Gráficas) -->
                        <div class="row">
                            <!-- Gráfica de Línea/Área: Ventas Recientes (Última Semana) -->
                            <div class="col-xl-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-chart-area me-1"></i>
                                        VENTAS DE LA ÚLTIMA SEMANA (EN MILES COP)
                                    </div>
                                    <div class="card-body"><canvas id="myAreaChart" width="100%" height="40"></canvas></div>
                                </div>
                            </div>

                            <!-- Gráfica de Barras: Ventas Históricas de Abrasivos (Últimos 6 Meses) -->
                            <div class="col-xl-6">
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <i class="fas fa-chart-bar me-1"></i>
                                        VENTAS EN LOS ÚLTIMOS 6 MESES (MILLONES COP)
                                    </div>
                                    <div class="card-body"><canvas id="myBarChart" width="100%" height="40"></canvas></div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Table -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                VENTAS RECIENTES
                            </div>
                            <div class="card-body">
                                <table id="datatablesSimple" class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID Venta</th>
                                            <th>Fecha</th>
                                            <th>Cliente</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>VEN-000145</td>
                                            <td>10/08/2026</td>
                                            <td>Industrias Metalúrgicas S.A.S</td>
                                            <td>$1.250.000</td>
                                            <td><span class="badge bg-success">Completada</span></td>
                                        </tr>
                                        <tr>
                                            <td>VEN-000144</td>
                                            <td>09/08/2026</td>
                                            <td>Taller Mecánico del Norte</td>
                                            <td>$830.000</td>
                                            <td><span class="badge bg-success">Completada</span></td>
                                        </tr>
                                        <tr>
                                            <td>VEN-000143</td>
                                            <td>08/08/2026</td>
                                            <td>Constructora Horizonte</td>
                                            <td>$2.450.000</td>
                                            <td><span class="badge bg-success">Completada</span></td>
                                        </tr>
                                        <tr>
                                            <td>VEN-000142</td>
                                            <td>07/08/2026</td>
                                            <td>Metal Solutions Ltda.</td>
                                            <td>$1.180.000</td>
                                            <td><span class="badge bg-warning text-dark">Pendiente</span></td>
                                        </tr>
                                        <tr>
                                            <td>VEN-000141</td>
                                            <td>06/08/2026</td>
                                            <td>Servicios Industriales J&R</td>
                                            <td>$950.000</td>
                                            <td><span class="badge bg-warning text-dark">Pendiente</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </main>

                <footer class="py-4 bg-light mt-auto">
                    <div class="container-fluid px-4">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="text-muted">Copyright &copy; C&M Soluciones Abrasivas SAS 2026</div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>

        <!-- Scripts JavaScript JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="js/datatables-simple-demo.js"></script>

        <!-- SCRIPT PARA RENDERIZAR LAS GRÁFICAS -->
        <script>
            // Configuración global de fuentes Chart.js
            Chart.defaults.global.defaultFontFamily = '-apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif';
            Chart.defaults.global.defaultFontColor = '#292b2c';

            // 1. Gráfica de Área: Ventas diarias de la última semana
            var ctxArea = document.getElementById("myAreaChart");
            var myAreaChart = new Chart(ctxArea, {
              type: 'line',
              data: {
                labels: ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado", "Domingo"],
                datasets: [{
                  label: "Ventas ($)",
                  lineTension: 0.3,
                  backgroundColor: "rgba(2,117,216,0.2)",
                  borderColor: "rgba(2,117,216,1)",
                  pointRadius: 5,
                  pointBackgroundColor: "rgba(2,117,216,1)",
                  pointBorderColor: "rgba(255,255,255,0.8)",
                  pointHoverRadius: 5,
                  pointHoverBackgroundColor: "rgba(2,117,216,1)",
                  pointHitRadius: 50,
                  pointBorderWidth: 2,
                  data: [1200, 2100, 1800, 3100, 2600, 3900, 1500],
                }],
              },
              options: {
                scales: {
                  xAxes: [{ time: { unit: 'date' }, gridLines: { display: false }, ticks: { maxTicksLimit: 7 } }],
                  yAxes: [{ ticks: { min: 0, max: 5000, maxTicksLimit: 5 }, gridLines: { color: "rgba(0, 0, 0, .125)" } }],
                },
                legend: { display: false }
              }
            });

            // 2. Gráfica de Barras: Ventas de los últimos 6 meses (Mar - Ago)
            var ctxBar = document.getElementById("myBarChart");
            var myBarChart = new Chart(ctxBar, {
              type: 'bar',
              data: {
                labels: ["Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto"],
                datasets: [{
                  label: "Ingresos (Millones COP)",
                  backgroundColor: "rgba(255,193,7,1)",
                  borderColor: "rgba(255,193,7,1)",
                  data: [11.5, 15.2, 18.0, 28.5, 22.1, 24.8],
                }],
              },
              options: {
                scales: {
                  xAxes: [{ gridLines: { display: false }, ticks: { maxTicksLimit: 6 } }],
                  yAxes: [{ ticks: { min: 0, max: 35, maxTicksLimit: 5 }, gridLines: { color: "rgba(0, 0, 0, .125)" } }],
                },
                legend: { display: false }
              }
            });
        </script>
    </body>
</html>
   