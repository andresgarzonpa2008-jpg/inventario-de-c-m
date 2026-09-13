<?php
require_once __DIR__ . '/../config/conexion.php';

$db = (new Conexion())->conn;
$currentUser = $_SESSION['user'] ?? [];
$username = $currentUser['username'] ?? 'Usuario';
$role = $_SESSION['rol'] ?? 'usuario';
$stats = ['sales' => 0, 'pending' => 0, 'products' => 0, 'stock' => 0, 'low' => 0];
$recentUsers = [];

try {
    $stats['users'] = (int) $db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    $productStats = $db->query('SELECT COUNT(*) AS products, COALESCE(SUM(PRO_stock_actual), 0) AS stock, SUM(PRO_stock_actual <= PRO_stock_minimo) AS low FROM productos')->fetch(PDO::FETCH_ASSOC);
    $stats['products'] = (int) ($productStats['products'] ?? 0);
    $stats['stock'] = (int) ($productStats['stock'] ?? 0);
    $stats['low'] = (int) ($productStats['low'] ?? 0);
    $salesStats = $db->query("SELECT COALESCE(SUM(CASE WHEN MONTH(fecha_venta) = MONTH(CURRENT_DATE()) AND YEAR(fecha_venta) = YEAR(CURRENT_DATE()) AND estado <> 'Cancelada' THEN total ELSE 0 END), 0) AS sales, COALESCE(SUM(estado = 'Pendiente'), 0) AS pending FROM ventas")->fetch(PDO::FETCH_ASSOC);
    $stats['sales'] = (float) ($salesStats['sales'] ?? 0);
    $stats['pending'] = (int) ($salesStats['pending'] ?? 0);
    $recentUsers = $db->query('SELECT id, nombre, apellido, username, rol FROM usuarios ORDER BY id DESC LIMIT 8')->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $exception) {
    $dashboardError = 'No fue posible cargar todos los indicadores desde la base de datos.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Oswald:wght@500;600;700&family=Roboto:wght@300;400;500;700&display=swap');
        body {
            font-family: 'Roboto', sans-serif;
        }
        .font-industrial {
            font-family: 'Oswald', sans-serif;
        }
        .gerente-sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 20;
            width: 225px;
            padding-top: 5rem;
            background: #212529;
        }
        .gerente-content {
            margin-left: 225px;
            padding-top: 5rem;
        }
        @media (max-width: 991px) {
            .gerente-sidebar { transform: translateX(-100%); transition: transform .2s ease; }
            .gerente-sidebar.open { transform: translateX(0); }
            .gerente-content { margin-left: 0; }
        }
        .manager-tools { display: none; }
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen gerente-dashboard">

    <!-- HEADER -->
    <header class="fixed-top bg-dark shadow-lg">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 border-2 border-white rotate-45 flex items-center justify-center bg-black/40">
                    <span class="font-industrial text-white text-lg font-bold -rotate-45">C&M</span>
                </div>
                <h1 class="font-industrial text-2xl font-bold">PANEL GERENTE</h1>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="font-semibold"><?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="text-xs bg-red-600 px-3 py-1 rounded-full inline-block mt-1"><?php echo htmlspecialchars(strtoupper($role), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <a href="index.php?action=logout" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg transition flex items-center gap-2">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    Cerrar Sesión
                </a>
            </div>
        </div>
    </header>

    <aside class="gerente-sidebar" id="gerenteSidebar">
        <div class="p-3 text-uppercase text-white-50 small fw-bold">Menú principal</div>
        <a class="nav-link active text-white px-3 py-3" href="index.php?action=gerente"><i class="fas fa-tachometer-alt fa-fw me-2"></i>Dashboard</a>
        <div class="p-3 text-uppercase text-white-50 small fw-bold">Gestión</div>
        <a class="nav-link text-white px-3 py-3" href="index.php?action=inventario"><i class="fas fa-boxes fa-fw me-2"></i>Inventario</a>
        <a class="nav-link text-white px-3 py-3" href="index.php?action=reportes"><i class="fas fa-chart-line fa-fw me-2"></i>Reportes</a>
        <?php if ($role !== 'inventario'): ?><a class="nav-link text-white px-3 py-3" href="index.php?action=usuario&section=usuarios"><i class="fas fa-users fa-fw me-2"></i>Usuarios</a><?php endif; ?>
        <div class="p-3 text-uppercase text-white-50 small fw-bold">Mi cuenta</div>
        <a class="nav-link text-white px-3 py-3" href="index.php?action=usuario&section=perfil"><i class="fas fa-user-circle fa-fw me-2"></i>Mi perfil</a>
    </aside>

    <div class="max-w-7xl mx-auto px-6 py-8 gerente-content">
        <?php if (!empty($dashboardError)): ?>
            <div class="mb-6 rounded-lg border border-red-500/50 bg-red-950/40 px-4 py-3 text-red-200">
                <?php echo htmlspecialchars($dashboardError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <div class="rounded-lg border border-blue-500/40 bg-blue-600 p-5 text-white">
                <p class="text-sm text-blue-100">Ventas del mes</p>
                <p class="mt-2 text-3xl font-bold">$ <?php echo number_format($stats['sales'], 0, ',', '.'); ?></p>
            </div>
            <div class="rounded-lg border border-amber-300/40 bg-amber-500 p-5 text-white">
                <p class="text-sm text-amber-100">Pedidos pendientes</p>
                <p class="mt-2 text-3xl font-bold"><?php echo $stats['pending']; ?></p>
            </div>
            <div class="rounded-lg border border-emerald-300/40 bg-emerald-600 p-5 text-white">
                <p class="text-sm text-emerald-100">Productos en stock</p>
                <p class="mt-2 text-3xl font-bold"><?php echo $stats['products']; ?> <span class="text-sm font-normal">(<?php echo $stats['stock']; ?> unidades)</span></p>
            </div>
            <div class="rounded-lg border border-red-300/40 bg-red-600 p-5 text-white">
                <p class="text-sm text-red-100">Alertas de stock</p>
                <p class="mt-2 text-3xl font-bold"><?php echo $stats['low']; ?></p>
            </div>
        </div>
        <!-- TARJETAS DE FUNCIONES -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 manager-tools">
            <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 hover:border-amber-500 transition cursor-pointer">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-industrial text-lg font-bold">Usuarios</h3>
                    <i data-lucide="users" class="w-8 h-8 text-amber-400"></i>
                </div>
                <p class="text-gray-400 text-sm mb-4">Gestionar usuarios del sistema</p>
                <a href="index.php?action=usuario&section=usuarios" class="text-amber-400 hover:text-amber-300 text-sm font-semibold">Ir →</a>
            </div>

            <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 hover:border-amber-500 transition cursor-pointer">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-industrial text-lg font-bold">Reportes</h3>
                    <i data-lucide="file-text" class="w-8 h-8 text-amber-400"></i>
                </div>
                <p class="text-gray-400 text-sm mb-4">Ver reportes del sistema</p>
                <a href="index.php?action=reportes" class="text-amber-400 hover:text-amber-300 text-sm font-semibold">Ir →</a>
            </div>

            <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 hover:border-amber-500 transition cursor-pointer">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-industrial text-lg font-bold">Configuración</h3>
                    <i data-lucide="settings" class="w-8 h-8 text-amber-400"></i>
                </div>
                <p class="text-gray-400 text-sm mb-4">Configurar el sistema</p>
                <a href="index.php?action=inventario" class="text-amber-400 hover:text-amber-300 text-sm font-semibold">Ir →</a>
            </div>

            <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 hover:border-amber-500 transition cursor-pointer">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-industrial text-lg font-bold">Auditoría</h3>
                    <i data-lucide="shield-alert" class="w-8 h-8 text-amber-400"></i>
                </div>
                <p class="text-gray-400 text-sm mb-4">Historial de actividades</p>
                <a href="index.php?action=auditoria" class="text-amber-400 hover:text-amber-300 text-sm font-semibold">Ir →</a>
            </div>
        </div>

        <!-- TABLA DE USUARIOS -->
        <div class="bg-gray-800 border border-gray-700 rounded-lg p-6">
            <h2 class="font-industrial text-xl font-bold mb-4 flex items-center gap-2">
                <i data-lucide="users" class="w-6 h-6 text-amber-400"></i>
                Gestión de Usuarios
            </h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-700">
                        <tr>
                            <th class="pb-3 font-semibold text-amber-400">Usuario</th>
                            <th class="pb-3 font-semibold text-amber-400">Rol</th>
                            <th class="pb-3 font-semibold text-amber-400">Estado</th>
                            <th class="pb-3 font-semibold text-amber-400">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentUsers as $user): ?>
                            <tr class="border-b border-gray-700 hover:bg-gray-700/50">
                                <td class="py-3"><?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td class="py-3"><span class="bg-red-600 px-2 py-1 rounded text-xs"><?php echo htmlspecialchars(ucfirst($user['rol']), ENT_QUOTES, 'UTF-8'); ?></span></td>
                                <td class="py-3"><span class="bg-green-600 px-2 py-1 rounded text-xs">Activo</span></td>
                                <td class="py-3">
                                    <a href="index.php?action=usuario&section=editar_usuario&id=<?php echo (int) $user['id']; ?>" class="text-amber-400 hover:text-amber-300 mr-3">Editar</a>
                                    <?php if ((int) $user['id'] !== (int) ($currentUser['id'] ?? 0)): ?>
                                        <span class="text-gray-500">Gestionar en Usuarios</span>
                                    <?php else: ?>
                                        <span class="text-gray-500">Tu cuenta</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$recentUsers): ?>
                            <tr><td colspan="4" class="py-6 text-center text-gray-400">No hay usuarios registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        document.getElementById('sidebarToggle')?.addEventListener('click', () => document.getElementById('gerenteSidebar').classList.toggle('open'));
    </script>
</body>
</html>
</html>
