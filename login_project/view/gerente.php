<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>C&M Soluciones Abrasivas</title>
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
    </style>
</head>
<body class="bg-gray-900 text-white min-h-screen">

    <!-- HEADER -->
    <header class="bg-gradient-to-r from-amber-600 to-amber-500 shadow-lg">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 border-2 border-white rotate-45 flex items-center justify-center bg-black/40">
                    <span class="font-industrial text-white text-lg font-bold -rotate-45">C&M</span>
                </div>
                <h1 class="font-industrial text-2xl font-bold">PANEL GERENTE</h1>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-right">
                    <p class="font-semibold"><?php echo htmlspecialchars($_SESSION["user"]["username"]); ?></p>
                    <p class="text-xs bg-red-600 px-3 py-1 rounded-full inline-block mt-1">gerente</p>
                </div>
                <a href="index.php?action=logout" class="bg-red-600 hover:bg-red-700 px-4 py-2 rounded-lg transition flex items-center gap-2">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    Cerrar Sesión
                </a>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-6 py-8">
        <!-- TARJETAS DE FUNCIONES -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 hover:border-amber-500 transition cursor-pointer">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-industrial text-lg font-bold">Usuarios</h3>
                    <i data-lucide="users" class="w-8 h-8 text-amber-400"></i>
                </div>
                <p class="text-gray-400 text-sm mb-4">Gestionar usuarios del sistema</p>
                <a href="#" class="text-amber-400 hover:text-amber-300 text-sm font-semibold">Ir →</a>
            </div>

            <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 hover:border-amber-500 transition cursor-pointer">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-industrial text-lg font-bold">Reportes</h3>
                    <i data-lucide="file-text" class="w-8 h-8 text-amber-400"></i>
                </div>
                <p class="text-gray-400 text-sm mb-4">Ver reportes del sistema</p>
                <a href="#" class="text-amber-400 hover:text-amber-300 text-sm font-semibold">Ir →</a>
            </div>

            <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 hover:border-amber-500 transition cursor-pointer">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-industrial text-lg font-bold">Configuración</h3>
                    <i data-lucide="settings" class="w-8 h-8 text-amber-400"></i>
                </div>
                <p class="text-gray-400 text-sm mb-4">Configurar el sistema</p>
                <a href="#" class="text-amber-400 hover:text-amber-300 text-sm font-semibold">Ir →</a>
            </div>

            <div class="bg-gray-800 border border-gray-700 rounded-lg p-6 hover:border-amber-500 transition cursor-pointer">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-industrial text-lg font-bold">Auditoría</h3>
                    <i data-lucide="shield-alert" class="w-8 h-8 text-amber-400"></i>
                </div>
                <p class="text-gray-400 text-sm mb-4">Historial de actividades</p>
                <a href="#" class="text-amber-400 hover:text-amber-300 text-sm font-semibold">Ir →</a>
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
                        <tr class="border-b border-gray-700 hover:bg-gray-700/50">
                            <td class="py-3"><?php echo htmlspecialchars($_SESSION["user"]["username"]); ?></td>
                            <td class="py-3"><span class="bg-red-600 px-2 py-1 rounded text-xs">Administrador</span></td>
                            <td class="py-3"><span class="bg-green-600 px-2 py-1 rounded text-xs">Activo</span></td>
                            <td class="py-3">
                                <button class="text-amber-400 hover:text-amber-300 mr-3">Editar</button>
                                <button class="text-red-400 hover:text-red-300">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
