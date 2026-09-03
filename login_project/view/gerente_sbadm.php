<?php
require_once __DIR__ . '/../config/conexion.php';

$db = (new Conexion())->conn;
$user = $_SESSION['user'] ?? [];
$username = $user['username'] ?? 'Gerente';
$role = $_SESSION['rol'] ?? 'gerente';
$stats = ['sales' => 0, 'pending' => 0, 'products' => 0, 'low' => 0];
$weekly = array_fill(0, 7, 0);
$monthly = array_fill(0, 6, 0);
$recentSales = [];
$dashboardError = '';

try {
    $productStats = $db->query('SELECT COUNT(*) AS products, COALESCE(SUM(PRO_stock_actual), 0) AS units, COALESCE(SUM(PRO_stock_actual <= PRO_stock_minimo), 0) AS low FROM productos')->fetch(PDO::FETCH_ASSOC);
    $stats['products'] = (int) ($productStats['products'] ?? 0);
    $stats['low'] = (int) ($productStats['low'] ?? 0);
    $hasSales = (bool) $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'ventas'")->fetchColumn();
    if ($hasSales) {
        $summary = $db->query("SELECT COALESCE(SUM(CASE WHEN MONTH(fecha_venta)=MONTH(CURRENT_DATE()) AND YEAR(fecha_venta)=YEAR(CURRENT_DATE()) AND estado <> 'Cancelada' THEN total ELSE 0 END),0) AS sales, COALESCE(SUM(estado='Pendiente'),0) AS pending FROM ventas")->fetch(PDO::FETCH_ASSOC);
        $stats['sales'] = (float) ($summary['sales'] ?? 0);
        $stats['pending'] = (int) ($summary['pending'] ?? 0);
        $stmt = $db->query("SELECT WEEKDAY(fecha_venta) AS day_index, SUM(total) AS amount FROM ventas WHERE fecha_venta >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 DAY) AND estado <> 'Cancelada' GROUP BY WEEKDAY(fecha_venta)");
        foreach ($stmt as $row) { $index = (int) $row['day_index']; if ($index >= 0 && $index < 7) $weekly[$index] = (float) $row['amount']; }
        $stmt = $db->query("SELECT PERIOD_DIFF(EXTRACT(YEAR_MONTH FROM CURRENT_DATE()), EXTRACT(YEAR_MONTH FROM fecha_venta)) AS month_index, SUM(total) AS amount FROM ventas WHERE fecha_venta >= DATE_SUB(CURRENT_DATE(), INTERVAL 5 MONTH) AND estado <> 'Cancelada' GROUP BY month_index");
        foreach ($stmt as $row) { $index = (int) $row['month_index']; if ($index >= 0 && $index < 6) $monthly[5 - $index] = (float) $row['amount']; }
        $recentSales = $db->query("SELECT v.id_venta, v.fecha_venta, v.total, v.estado, CONCAT(c.nombre, ' ', c.apellido) AS cliente FROM ventas v LEFT JOIN clientes c ON c.id_cliente=v.id_cliente ORDER BY v.fecha_venta DESC LIMIT 8")->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $exception) {
    $dashboardError = 'No fue posible cargar los indicadores. Verifica la conexión y la estructura de la base de datos.';
}

$monthLabels = [];
$monthNames = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
for ($i = 5; $i >= 0; $i--) $monthLabels[] = $monthNames[(int) date('n', strtotime("-$i months")) - 1];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistema de gestión C&M Soluciones Abrasivas">
    <title>C&M SOLUCIONES ABRASIVAS</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet">
    <link href="css/styles.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
</head>
<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <a class="navbar-brand ps-3" href="index.php?action=gerente">C&M ABRASIVAS</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" type="button"><i class="fas fa-bars"></i></button>
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0"><div class="input-group"><input class="form-control" type="search" placeholder="Buscar productos, clientes..." aria-label="Buscar"><button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button></div></form>
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4"><li class="nav-item dropdown"><a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown"><i class="fas fa-user fa-fw"></i> <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></a><ul class="dropdown-menu dropdown-menu-end"><li><a class="dropdown-item" href="index.php?action=usuario&section=perfil">Mi perfil</a></li><li><hr class="dropdown-divider"></li><li><a class="dropdown-item" href="index.php?action=logout">Cerrar sesión</a></li></ul></li></ul>
    </nav>
    <div id="layoutSidenav">
        <div id="layoutSidenav_nav"><nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion"><div class="sb-sidenav-menu"><div class="nav">
            <div class="sb-sidenav-menu-heading">Menú principal</div>
            <a class="nav-link active" href="index.php?action=gerente"><div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>Dashboard</a>
            <div class="sb-sidenav-menu-heading">Gestión</div>
            <a class="nav-link" href="index.php?action=inventario"><div class="sb-nav-link-icon"><i class="fas fa-boxes"></i></div>Inventario</a>
            <a class="nav-link" href="index.php?action=reportes"><div class="sb-nav-link-icon"><i class="fas fa-chart-line"></i></div>Reportes</a>
            <?php if ($role === 'admin'): ?><a class="nav-link" href="index.php?action=usuario&section=usuarios"><div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>Usuarios</a><?php endif; ?>
            <div class="sb-sidenav-menu-heading">Mi cuenta</div><a class="nav-link" href="index.php?action=usuario&section=perfil"><div class="sb-nav-link-icon"><i class="fas fa-user-circle"></i></div>Mi perfil</a>
        </div></div><div class="sb-sidenav-footer"><div class="small">Inició sesión como:</div><?php echo htmlspecialchars(ucfirst($role), ENT_QUOTES, 'UTF-8'); ?></div></nav></div>
        <div id="layoutSidenav_content"><main><div class="container-fluid px-4">
            <h1 class="mt-4">¡Bienvenido, <?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>!</h1>
            <ol class="breadcrumb mb-4"><li class="breadcrumb-item active">Resumen general de C&M Soluciones Abrasivas SAS</li></ol>
            <?php if ($dashboardError): ?><div class="alert alert-warning" role="alert"><i class="fas fa-triangle-exclamation me-2"></i><?php echo htmlspecialchars($dashboardError, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
            <div class="row">
                <div class="col-xl-3 col-md-6"><a class="text-decoration-none" href="index.php?action=reportes"><div class="card bg-primary text-white mb-4"><div class="card-body d-flex align-items-center justify-content-between"><div><div class="text-white-50 small">Ventas del mes</div><div id="salesMetric" class="fs-4 fw-bold">$ <?php echo number_format($stats['sales'], 0, ',', '.'); ?></div></div><i class="fas fa-shopping-cart fa-2x opacity-75"></i></div><div class="card-footer"><span class="small text-white">Ver detalles</span></div></div></a></div>
                <div class="col-xl-3 col-md-6"><a class="text-decoration-none" href="index.php?action=reportes"><div class="card bg-warning text-white mb-4"><div class="card-body d-flex align-items-center justify-content-between"><div><div class="text-white-50 small">Pedidos pendientes</div><div id="pendingMetric" class="fs-4 fw-bold"><?php echo $stats['pending']; ?></div></div><i class="fas fa-box fa-2x opacity-75"></i></div><div class="card-footer"><span class="small text-white">Ver detalles</span></div></div></a></div>
                <div class="col-xl-3 col-md-6"><a class="text-decoration-none" href="index.php?action=inventario"><div class="card bg-success text-white mb-4"><div class="card-body d-flex align-items-center justify-content-between"><div><div class="text-white-50 small">Productos en stock</div><div id="productsMetric" class="fs-4 fw-bold"><?php echo $stats['products']; ?></div></div><i class="fas fa-cubes fa-2x opacity-75"></i></div><div class="card-footer"><span class="small text-white">Catálogo disponible</span></div></div></a></div>
                <div class="col-xl-3 col-md-6"><a class="text-decoration-none" href="index.php?action=inventario"><div class="card bg-danger text-white mb-4"><div class="card-body d-flex align-items-center justify-content-between"><div><div class="text-white-50 small">Alertas de stock</div><div id="lowMetric" class="fs-4 fw-bold"><?php echo $stats['low']; ?></div></div><i class="fas fa-exclamation-triangle fa-2x opacity-75"></i></div><div class="card-footer"><span class="small text-white">Stock bajo</span></div></div></a></div>
            </div>
            <div class="row"><div class="col-xl-6"><div class="card mb-4"><div class="card-header"><i class="fas fa-chart-area me-1"></i> VENTAS DE LA ÚLTIMA SEMANA</div><div class="card-body"><div style="height:240px"><canvas id="myAreaChart"></canvas></div></div></div></div><div class="col-xl-6"><div class="card mb-4"><div class="card-header"><i class="fas fa-chart-bar me-1"></i> VENTAS EN LOS ÚLTIMOS 6 MESES</div><div class="card-body"><div style="height:240px"><canvas id="myBarChart"></canvas></div></div></div></div></div>
            <div class="card mb-4"><div class="card-header d-flex align-items-center justify-content-between"><span><i class="fas fa-table me-1"></i> VENTAS RECIENTES</span><a class="btn btn-sm btn-primary" href="index.php?action=reportes"><i class="fas fa-plus me-1"></i>Registrar venta</a></div><div class="card-body table-responsive"><table class="table table-striped table-bordered"><thead><tr><th>ID Venta</th><th>Fecha</th><th>Cliente</th><th>Total</th><th>Estado</th></tr></thead><tbody><?php foreach ($recentSales as $sale): ?><tr><td><?php echo (int) $sale['id_venta']; ?></td><td><?php echo htmlspecialchars($sale['fecha_venta'], ENT_QUOTES, 'UTF-8'); ?></td><td><?php echo htmlspecialchars($sale['cliente'] ?? 'Sin cliente', ENT_QUOTES, 'UTF-8'); ?></td><td>$ <?php echo number_format((float) $sale['total'], 0, ',', '.'); ?></td><td><?php echo htmlspecialchars($sale['estado'], ENT_QUOTES, 'UTF-8'); ?></td></tr><?php endforeach; ?><?php if (!$recentSales): ?><tr><td colspan="5" class="text-center text-muted">No hay ventas registradas. Usa “Registrar venta” para alimentar las gráficas.</td></tr><?php endif; ?></tbody></table></div></div>
        </div></main><footer class="py-4 bg-light mt-auto"><div class="container-fluid px-4"><div class="small text-muted">C&M Soluciones Abrasivas SAS 2026</div></div></footer></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script><script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js"></script><script src="js/scripts.js"></script>
    <script>const chartOptions={maintainAspectRatio:false,legend:{display:false}};const areaChart=new Chart(document.getElementById('myAreaChart'),{type:'line',data:{labels:['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'],datasets:[{label:'Ventas',backgroundColor:'rgba(13,110,253,.2)',borderColor:'#0d6efd',data:<?php echo json_encode($weekly); ?>}]},options:chartOptions});const barChart=new Chart(document.getElementById('myBarChart'),{type:'bar',data:{labels:<?php echo json_encode($monthLabels); ?>,datasets:[{label:'Ventas',backgroundColor:'#ffc107',data:<?php echo json_encode($monthly); ?>}]},options:chartOptions});function refreshDashboard(){fetch('index.php?action=dashboard_data',{headers:{Accept:'application/json'},cache:'no-store'}).then(response=>response.ok?response.json():Promise.reject()).then(data=>{document.getElementById('salesMetric').textContent='$ '+Number(data.sales).toLocaleString('es-CO');document.getElementById('pendingMetric').textContent=data.pending;document.getElementById('productsMetric').textContent=data.products;document.getElementById('lowMetric').textContent=data.low;areaChart.data.datasets[0].data=data.weekly;barChart.data.datasets[0].data=data.monthly;areaChart.update();barChart.update();}).catch(()=>{});}setInterval(refreshDashboard,30000);</script>
</body>
</html>
