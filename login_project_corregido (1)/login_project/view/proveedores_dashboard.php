<?php
require_once __DIR__ . '/../config/require_auth.php';
require_role(['proveedor','gerente']);

// ------------------------------------------------------------------
// Datos de sesión (usuario autenticado)
// ------------------------------------------------------------------
$usuario = $_SESSION["user"]["username"] ?? "Proveedor";
$rol     = ucfirst($_SESSION["rol"] ?? "proveedor");

// ------------------------------------------------------------------
// Escala de avance de una orden (mapeo de estados reales de la BD)
// ------------------------------------------------------------------
$escala = [
    "P40"  => "Pedido",
    "P80"  => "Confirmado",
    "P120" => "En producción",
    "P180" => "En tránsito",
    "P220" => "Entregado"
];
$estadoAGrano = [
    'pendiente'       => 'P40',
    'confirmado'      => 'P80',
    'en produccion'   => 'P120',
    'produccion'      => 'P120',
    'en transito'     => 'P180',
    'enviado'         => 'P180',
    'entregado'       => 'P220',
    'facturado'       => 'P220',
];

// ------------------------------------------------------------------
// Datos reales desde la base de datos
// ------------------------------------------------------------------
$stats = ["activas" => 0, "en_transito" => 0, "facturas" => 0];
$ordenes = [];
$entregas = [];
$marcas = [];

try {
    $db = (new Conexion())->conn;

    $hasOrdenes = (bool) $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'orden_compra'")->fetchColumn();

    if ($hasOrdenes) {
        $statsStmt = $db->query("SELECT
            COALESCE(SUM(ORD_estado NOT IN ('entregado','facturado','cancelado')),0) AS activas,
            COALESCE(SUM(ORD_estado IN ('en transito','enviado')),0) AS en_transito,
            COALESCE(SUM(ORD_estado = 'facturado'),0) AS facturas
            FROM orden_compra");
        $statsRow = $statsStmt->fetch(PDO::FETCH_ASSOC);
        $stats = [
            "activas"     => (int) ($statsRow['activas'] ?? 0),
            "en_transito" => (int) ($statsRow['en_transito'] ?? 0),
            "facturas"    => (int) ($statsRow['facturas'] ?? 0),
        ];

        // Órdenes con su producto asociado
        $stmt = $db->query("SELECT oc.ORD_id_orden, oc.ORD_fecha, oc.ORD_estado, oc.ORD_total, oc.ORD_retrasada, oc.ORD_notas,
                p.PRO_nombre_producto, p.PRO_marca, doc.DOC_cantidad
            FROM orden_compra oc
            LEFT JOIN detalle_orden_compra doc ON doc.ORD_id_orden = oc.ORD_id_orden
            LEFT JOIN productos p ON p.PRO_codigo = doc.PRO_codigo
            ORDER BY oc.ORD_fecha DESC");
        foreach ($stmt as $row) {
            $estado = strtolower((string) ($row['ORD_estado'] ?? 'pendiente'));
            $avance = $estadoAGrano[$estado] ?? 'P40';
            $ordenes[] = [
                "id"        => "OC-" . $row['ORD_id_orden'],
                "producto"  => $row['PRO_nombre_producto'] ?: 'Producto no disponible',
                "marca"     => $row['PRO_marca'] ?: 'C&M',
                "grano"     => $avance,
                "cant"      => (int) ($row['DOC_cantidad'] ?? 0) . " und",
                "avance"    => $avance,
                "retrasada" => (bool) ($row['ORD_retrasada'] ?? false),
                "notas"     => (string) ($row['ORD_notas'] ?? ''),
                "estado"    => $estado,
            ];
        }

        // Próximas entregas: órdenes en tránsito o entregadas recientemente + agendadas en despacho
        $stmtEntregas = $db->query("SELECT oc.ORD_id_orden, oc.ORD_fecha, oc.ORD_estado
            FROM orden_compra oc
            WHERE LOWER(oc.ORD_estado) IN ('en transito','enviado','entregado','facturado')
            ORDER BY oc.ORD_fecha DESC LIMIT 6");
        foreach ($stmtEntregas as $row) {
            $entregas[] = [
                "fecha"   => date('d M', strtotime($row['ORD_fecha'])),
                "orden"   => "OC-" . $row['ORD_id_orden'],
                "destino" => "Entrega " . ucfirst($row['ORD_estado']),
            ];
        }

        // Entregas agendadas por el proveedor (modal "Agendar")
        $tieneDespacho = (bool) $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'despacho_bodega'")->fetchColumn();
        if ($tieneDespacho) {
            $stmtDespacho = $db->query("SELECT DES_orden_bodega, DES_direccion_envio, DES_id_confirmacion, estado
                FROM despacho_bodega
                WHERE estado IN ('Agendado','Enviado','Pendiente')
                ORDER BY DES_id DESC LIMIT 8");
            foreach ($stmtDespacho as $row) {
                $entregas[] = [
                    "fecha"   => $row['estado'] === 'Enviado' ? 'Enviado' : 'Agendado',
                    "orden"   => $row['DES_orden_bodega'],
                    "destino" => $row['DES_direccion_envio'] . ' · ' . $row['DES_id_confirmacion'],
                ];
            }
        }
    }

    // Marcas disponibles en el catálogo actual
    $stmtMarcas = $db->query("SELECT DISTINCT PRO_marca FROM productos
        WHERE deleted_at IS NULL AND PRO_marca IS NOT NULL AND PRO_marca <> '' ORDER BY PRO_marca");
    $marcas = array_map(function ($row) { return $row['PRO_marca']; }, $stmtMarcas->fetchAll(PDO::FETCH_ASSOC));
} catch (Throwable $exception) {
    // Si la BD no responde, se muestran los indicadores en cero.
}

function grano_index($escala, $grano) {
    $keys = array_keys($escala);
    return array_search($grano, $keys);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel Proveedores · C&M Soluciones Abrasivas</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/lucide@latest"></script>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Open+Sans:wght@400;500;600&display=swap');
  :root{
    --grafito:#121212;
    --pizarra:#1D2D35;
    --pizarra-light:#2A3D47;
    --industrial:#FFC800;
    --dorado:#F5C72B;
    --alerta:#E62415;
    --nieve:#FFFFFF;
    --muted:#7E9099;
  }
  body{ background:var(--grafito); color:var(--nieve); font-family:'Open Sans',sans-serif; }
  .font-title{ font-family:'Montserrat',sans-serif; }
  .diamond{ width:.5rem; height:.5rem; background:var(--industrial); transform:rotate(45deg); flex:none; }
  .diamond-alerta{ background:var(--alerta); }
  .diamond-ok{ background:#3FA34D; }
  .grit-track{ display:flex; gap:2px; height:6px; }
  .grit-seg{ flex:1; background:var(--pizarra-light); }
  .grit-seg.filled{ background:var(--industrial); }
  .grit-seg.filled.retrasada{ background:var(--alerta); }
  ::selection{ background:var(--industrial); color:var(--grafito); }
</style>
</head>
<body class="min-h-screen relative">

<!-- HEADER / NAVBAR -->
<header class="border-b border-[var(--pizarra-light)] bg-[var(--grafito)] sticky top-0 z-40">
  <div class="max-w-6xl mx-auto px-6 py-4 flex flex-wrap items-center justify-between gap-4">
    <div class="flex items-center gap-4">
      <div class="w-11 h-11 rotate-45 flex items-center justify-center" style="background:var(--pizarra); border:2px solid var(--dorado);">
        <span class="font-title font-extrabold text-sm -rotate-45" style="color:var(--dorado);">C&M</span>
      </div>
      <div>
        <h1 class="font-title font-extrabold text-xl tracking-wide uppercase">Panel Proveedores</h1>
        <p class="text-xs" style="color:var(--muted);">C&M Soluciones Abrasivas S.A.S. — a tu mano</p>
      </div>
    </div>
    
    <!-- ACCIONES RÁPIDAS Y USUARIO -->
    <div class="flex items-center gap-4">
      <button onclick="abrirModalFactura()" class="flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded bg-[var(--pizarra)] border border-[var(--pizarra-light)] hover:border-[var(--industrial)] transition">
        <i data-lucide="upload" class="w-4 h-4 text-[var(--industrial)]"></i> Subir Factura
      </button>
      <div class="text-right hidden sm:block">
        <p class="font-semibold text-sm"><?php echo htmlspecialchars($usuario); ?></p>
        <p class="text-xs" style="color:var(--muted);"><?php echo htmlspecialchars($rol); ?></p>
      </div>
      <a href="index.php?action=logout" class="flex items-center gap-2 px-3 py-2 text-xs font-semibold rounded" style="background:var(--alerta);">
        <i data-lucide="log-out" class="w-4 h-4"></i>
      </a>
    </div>
  </div>
</header>

<div class="max-w-6xl mx-auto px-6 py-8">

  <?php if (isset($_GET['msg']) && trim($_GET['msg']) !== ''): ?>
  <div class="px-4 py-3 mb-6 text-sm font-semibold rounded border" style="background:var(--pizarra); border-color:var(--dorado); color:var(--nieve);" id="banner-msg">
    <?php echo htmlspecialchars($_GET['msg'], ENT_QUOTES, 'UTF-8'); ?>
  </div>
  <?php endif; ?>

  <!-- BANDA DE INDICADORES -->
  <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-[var(--pizarra-light)] mb-8 border border-[var(--pizarra-light)] bg-[var(--pizarra)]">
    <div class="px-6 py-5">
      <p class="text-4xl font-title font-extrabold" style="color:var(--industrial);"><?php echo $stats["activas"]; ?></p>
      <p class="text-sm mt-1" style="color:var(--muted);">Órdenes activas</p>
    </div>
    <div class="px-6 py-5">
      <p class="text-4xl font-title font-extrabold"><?php echo $stats["en_transito"]; ?></p>
      <p class="text-sm mt-1" style="color:var(--muted);">En tránsito esta semana</p>
    </div>
    <div class="px-6 py-5">
      <p class="text-4xl font-title font-extrabold" style="color:var(--alerta);"><?php echo $stats["facturas"]; ?></p>
      <p class="text-sm mt-1" style="color:var(--muted);">Facturas pendientes de pago</p>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

    <!-- ÓRDENES DE COMPRA -->
    <div class="lg:col-span-2">
      <div class="flex items-center justify-between gap-4 mb-4">
        <div class="flex items-center gap-2">
          <div class="diamond"></div>
          <h2 class="font-title font-bold text-lg">Órdenes de compra</h2>
        </div>
        <!-- Búsqueda / Filtro -->
        <input type="text" id="buscador" onkeyup="filtrarOrdenes()" placeholder="Buscar orden o marca..." 
               class="bg-[var(--pizarra)] border border-[var(--pizarra-light)] px-3 py-1.5 text-xs rounded focus:outline-none focus:border-[var(--industrial)] text-white w-48 sm:w-64">
      </div>

      <div id="lista-ordenes" class="border border-[var(--pizarra-light)]">
        <?php if (!$ordenes): ?>
        <div class="px-5 py-8 text-center" style="background:var(--pizarra);">
          <p class="text-sm font-semibold mb-1">Aún no hay órdenes de compra registradas.</p>
          <p class="text-xs" style="color:var(--muted);">Las órdenes aparecerán aquí cuando el gerente las registre.</p>
        </div>
        <?php endif; ?>
        <?php foreach ($ordenes as $o): $idx = grano_index($escala, $o["avance"]); ?>
        <div class="orden-item px-5 py-4 border-b border-[var(--pizarra-light)] last:border-b-0" style="background:var(--pizarra);" data-search="<?php echo strtolower($o["id"].' '.$o["producto"].' '.$o["marca"]); ?>">
          
          <div class="flex flex-wrap items-start justify-between gap-2">
            <div>
              <p class="font-semibold text-sm"><?php echo $o["id"]; ?> · <?php echo htmlspecialchars($o["producto"]); ?></p>
              <p class="text-xs mt-1" style="color:var(--muted);">
                <?php echo $o["marca"]; ?> — grano <?php echo $o["grano"]; ?> — <?php echo $o["cant"]; ?>
              </p>
              <?php if (!empty($o["notas"])): ?>
              <p class="text-xs mt-1 font-medium" style="color:var(--industrial);">📝 <?php echo htmlspecialchars($o["notas"]); ?></p>
              <?php endif; ?>
            </div>
            
            <!-- Acciones de la Orden -->
            <div class="flex items-center gap-3">
              <div class="flex items-center gap-2 text-xs font-semibold">
                <div class="diamond <?php echo $o["retrasada"] ? "diamond-alerta" : "diamond-ok"; ?>"></div>
                <?php echo $escala[$o["avance"]]; ?>
              </div>
              <button onclick="abrirModalGestion('<?php echo $o['id']; ?>', '<?php echo $o['avance']; ?>')" 
                      class="px-2 py-1 text-xs bg-[var(--pizarra-light)] hover:bg-[var(--industrial)] hover:text-black font-semibold rounded transition flex items-center gap-1">
                <i data-lucide="edit-3" class="w-3 h-3"></i> Actualizar
              </button>
            </div>
          </div>

          <!-- Barra de avance (Lija) -->
          <div class="grit-track mt-3">
            <?php foreach (array_keys($escala) as $i => $g): ?>
              <div class="grit-seg <?php echo $i <= $idx ? "filled" : ""; ?> <?php echo ($o["retrasada"] && $i <= $idx) ? "retrasada" : ""; ?>"></div>
            <?php endforeach; ?>
          </div>
          <div class="flex justify-between text-[10px] mt-1" style="color:var(--muted);">
            <?php foreach (array_keys($escala) as $g): ?><span><?php echo $g; ?></span><?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- COLUMNA LATERAL -->
    <div class="space-y-8">

      <!-- ENTREGAS -->
      <div>
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center gap-2">
            <div class="diamond"></div>
            <h2 class="font-title font-bold text-lg">Próximas entregas</h2>
          </div>
          <button onclick="abrirModalEntrega()" class="text-xs text-[var(--industrial)] hover:underline flex items-center gap-1">
            <i data-lucide="plus-circle" class="w-3 h-3"></i> Agendar
          </button>
        </div>

        <div class="border border-[var(--pizarra-light)]">
          <?php if (!$entregas): ?>
          <div class="px-5 py-6 text-center" style="background:var(--pizarra);">
            <p class="text-xs mb-1" style="color:var(--muted);">No hay entregas programadas.</p>
            <p class="text-xs" style="color:var(--muted);">Cuando una orden esté en tránsito o entregada se listará aquí.</p>
          </div>
          <?php endif; ?>
          <?php foreach ($entregas as $e): ?>
          <div class="px-5 py-3 border-b border-[var(--pizarra-light)] last:border-b-0 flex items-center justify-between" style="background:var(--pizarra);">
            <div>
              <p class="text-sm font-semibold"><?php echo $e["orden"]; ?></p>
              <p class="text-xs" style="color:var(--muted);"><?php echo $e["destino"]; ?></p>
            </div>
            <p class="text-sm font-title font-bold" style="color:var(--industrial);"><?php echo $e["fecha"]; ?></p>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- CATÁLOGO POR MARCA -->
      <div>
        <div class="flex items-center gap-2 mb-4">
          <div class="diamond"></div>
          <h2 class="font-title font-bold text-lg">Catálogo por marca</h2>
        </div>
        <div class="flex flex-wrap gap-2">
          <?php if (!$marcas): ?>
          <span class="text-xs" style="color:var(--muted);">Sin marcas registradas.</span>
          <?php endif; ?>
          <?php foreach ($marcas as $m): ?>
          <a href="index.php?action=catalogo&marca=<?php echo urlencode($m); ?>"
             class="text-xs font-semibold px-3 py-1.5 border border-[var(--pizarra-light)] hover:border-[var(--industrial)] transition"
             style="background:var(--pizarra);">
            <?php echo $m; ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- FOOTER -->
<footer class="border-t border-[var(--pizarra-light)] mt-16">
  <div class="max-w-6xl mx-auto px-6 py-6 flex flex-wrap items-center justify-between gap-4 text-xs" style="color:var(--muted);">
    <p>C&M Soluciones Abrasivas S.A.S.</p>
    <div class="flex flex-wrap gap-x-6 gap-y-1">
      <span>WhatsApp 318 763 9442</span>
      <span>(601) 739 0093 2</span>
      <span>abrasivossoluciones@gmail.com</span>
    </div>
  </div>
</footer>

<!-- MODAL: ACTUALIZAR ESTADO DE ÓRDEN -->
<div id="modal-gestion" class="fixed inset-0 bg-black/70 flex items-center justify-center hidden z-50 p-4">
  <div class="bg-[var(--pizarra)] border border-[var(--pizarra-light)] p-6 rounded max-w-md w-full">
    <div class="flex justify-between items-center mb-4">
      <h3 class="font-title font-bold text-lg">Actualizar Órden <span id="modal-orden-id" class="text-[var(--industrial)]"></span></h3>
      <button onclick="cerrarModales()" class="text-gray-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form action="index.php?action=proveedor" method="POST" class="space-y-4 text-xs">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="actualizar_orden">
      <input type="hidden" name="orden_id" id="form-orden-id">
      <div>
        <label class="block mb-1 text-[var(--muted)]">Estado / Grano de avance:</label>
        <select name="avance" id="form-avance" class="w-full bg-[var(--grafito)] border border-[var(--pizarra-light)] p-2 rounded text-white">
          <?php foreach ($escala as $grano => $etiqueta): ?>
            <option value="<?php echo $grano; ?>"><?php echo $grano; ?> - <?php echo $etiqueta; ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex items-center gap-2">
        <input type="checkbox" name="retrasada" id="form-retrasada" value="1" class="accent-[var(--alerta)]">
        <label for="form-retrasada" class="text-white">Marcar como con retraso / novedad</label>
      </div>
      <div>
        <label class="block mb-1 text-[var(--muted)]">Observaciones o Notas:</label>
        <textarea name="notas" rows="3" class="w-full bg-[var(--grafito)] border border-[var(--pizarra-light)] p-2 rounded text-white" placeholder="Escribe novedades del envío..."></textarea>
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" onclick="cerrarModales()" class="px-4 py-2 rounded bg-[var(--pizarra-light)]">Cancelar</button>
        <button type="submit" class="px-4 py-2 rounded font-semibold text-black bg-[var(--industrial)]">Guardar Cambios</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: AGENDAR ENTREGA -->
<div id="modal-entrega" class="fixed inset-0 bg-black/70 flex items-center justify-center hidden z-50 p-4">
  <div class="bg-[var(--pizarra)] border border-[var(--pizarra-light)] p-6 rounded max-w-md w-full">
    <div class="flex justify-between items-center mb-4">
      <h3 class="font-title font-bold text-lg">Agendar Próxima Entrega</h3>
      <button onclick="cerrarModales()" class="text-gray-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form action="index.php?action=proveedor" method="POST" class="space-y-4 text-xs">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="agendar_entrega">
      <div>
        <label class="block mb-1 text-[var(--muted)]">Órden asociada:</label>
        <select name="orden" class="w-full bg-[var(--grafito)] border border-[var(--pizarra-light)] p-2 rounded text-white">
          <?php foreach ($ordenes as $o): ?>
            <option value="<?php echo $o['id']; ?>"><?php echo $o['id']; ?> - <?php echo htmlspecialchars($o['producto']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block mb-1 text-[var(--muted)]">Fecha de Entrega:</label>
        <input type="date" name="fecha" required class="w-full bg-[var(--grafito)] border border-[var(--pizarra-light)] p-2 rounded text-white">
      </div>
      <div>
        <label class="block mb-1 text-[var(--muted)]">Destino / Sucursal:</label>
        <input type="text" name="destino" placeholder="Ej: Bodega Bogotá" required class="w-full bg-[var(--grafito)] border border-[var(--pizarra-light)] p-2 rounded text-white">
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" onclick="cerrarModales()" class="px-4 py-2 rounded bg-[var(--pizarra-light)]">Cancelar</button>
        <button type="submit" class="px-4 py-2 rounded font-semibold text-black bg-[var(--industrial)]">Agendar</button>
      </div>
    </form>
  </div>
</div>

<!-- MODAL: SUBIR FACTURA -->
<div id="modal-factura" class="fixed inset-0 bg-black/70 flex items-center justify-center hidden z-50 p-4">
  <div class="bg-[var(--pizarra)] border border-[var(--pizarra-light)] p-6 rounded max-w-md w-full">
    <div class="flex justify-between items-center mb-4">
      <h3 class="font-title font-bold text-lg">Subir Factura</h3>
      <button onclick="cerrarModales()" class="text-gray-400 hover:text-white"><i data-lucide="x" class="w-5 h-5"></i></button>
    </div>
    <form action="index.php?action=proveedor" method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
      <?php echo csrf_field(); ?>
      <input type="hidden" name="action" value="subir_factura">
      <div>
        <label class="block mb-1 text-[var(--muted)]">Órden de Compra:</label>
        <select name="orden_id" class="w-full bg-[var(--grafito)] border border-[var(--pizarra-light)] p-2 rounded text-white">
          <?php foreach ($ordenes as $o): ?>
            <option value="<?php echo $o['id']; ?>"><?php echo $o['id']; ?> (<?php echo $o['marca']; ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block mb-1 text-[var(--muted)]">Archivo (PDF o XML):</label>
        <input type="file" name="factura" accept=".pdf,.xml" required class="w-full bg-[var(--grafito)] border border-[var(--pizarra-light)] p-2 rounded text-white">
      </div>
      <div class="flex justify-end gap-2 pt-2">
        <button type="button" onclick="cerrarModales()" class="px-4 py-2 rounded bg-[var(--pizarra-light)]">Cancelar</button>
        <button type="submit" class="px-4 py-2 rounded font-semibold text-black bg-[var(--industrial)]">Subir Documento</button>
      </div>
    </form>
  </div>
</div>

<!-- JAVASCRIPT DE INTERACCION -->
<script>
  lucide.createIcons();

  // Filtrado de ordenes en tiempo real
  function filtrarOrdenes() {
    const input = document.getElementById('buscador').value.toLowerCase();
    const items = document.querySelectorAll('.orden-item');

    items.forEach(item => {
      const text = item.getAttribute('data-search');
      item.style.display = text.includes(input) ? '' : 'none';
    });
  }

  // Modales
  function cerrarModales() {
    document.getElementById('modal-gestion').classList.add('hidden');
    document.getElementById('modal-entrega').classList.add('hidden');
    document.getElementById('modal-factura').classList.add('hidden');
  }

  function abrirModalGestion(id, avance) {
    cerrarModales();
    document.getElementById('modal-orden-id').innerText = id;
    document.getElementById('form-orden-id').value = id;
    document.getElementById('form-avance').value = avance;
    document.getElementById('modal-gestion').classList.remove('hidden');
  }

  function abrirModalEntrega() {
    cerrarModales();
    document.getElementById('modal-entrega').classList.remove('hidden');
  }

  function abrirModalFactura() {
    cerrarModales();
    document.getElementById('modal-factura').classList.remove('hidden');
  }
</script>
</body>
</html>