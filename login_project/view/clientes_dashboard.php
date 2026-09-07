<?php
require_once __DIR__ . '/../config/conexion.php';
$catalogProducts = [];
try {
    $catalogProducts = (new Conexion())->conn->query("SELECT PRO_codigo, PRO_nombre_producto, PRO_descripcion, PRO_marca, PRO_imagen_url, PRO_precio_unitario, PRO_stock_actual FROM productos ORDER BY PRO_codigo DESC")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $exception) {
    $catalogProducts = [];
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Portal de Cliente - C&M Soluciones Abrasivas" />
        <title>Portal Cliente - C&M SOLUCIONES ABRASIVAS</title>
        
        <!-- CSS -->
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <link href="css/styles.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    </head>
    <body class="sb-nav-fixed">
        <!-- Top Navbar -->
        <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
            <a class="navbar-brand ps-3" href="index.html">C&M CLIENTES</a>
            <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Navbar Search -->
            <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
                <div class="input-group">
                    <input class="form-control" type="text" placeholder="Buscar abrasivos, discos..." aria-label="Buscar..." aria-describedby="btnNavbarSearch" />
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
                        <li><a class="dropdown-item" href="#!">Mi Perfil</a></li>
                        <li><a class="dropdown-item" href="#!">Medios de Pago</a></li>
                        <li><hr class="dropdown-divider" /></li>
                        <li><a class="dropdown-item" href="index.php?action=logout">Cerrar sesión</a></li>
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
                            <a class="nav-link active" href="index.html">
                                <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                                Mi Panel
                            </a>
                            
                            <div class="sb-sidenav-menu-heading">Tienda y Pedidos</div>
                            <a class="nav-link" href="#catalogo">
                                <div class="sb-nav-link-icon"><i class="fas fa-store"></i></div>
                                Ver el Catálogo
                            </a>
                            <a class="nav-link" href="#carrito">
                                <div class="sb-nav-link-icon"><i class="fas fa-shopping-cart"></i></div>
                                Carrito de Compras <span class="badge bg-danger ms-2" id="cartBadge">2</span>
                            </a>
                            <a class="nav-link" href="#facturas">
                                <div class="sb-nav-link-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                                Mis Facturas
                            </a>
                            <a class="nav-link" href="#pagos">
                                <div class="sb-nav-link-icon"><i class="fas fa-credit-card"></i></div>
                                Medios de Pago
                            </a>
                            <a class="nav-link" href="#quejas">
                                <div class="sb-nav-link-icon"><i class="fas fa-headset"></i></div>
                                Quejas y PQRS
                            </a>
                        </div>
                    </div>
                    <div class="sb-sidenav-footer">
                        <div class="small">Inició sesión como:</div>
                        Cliente / Industrial
                    </div>
                </nav>
            </div>

            <!-- Main Content -->
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">¡Bienvenido, Cliente C&M!</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Panel de control y autogestión de compras</li>
                        </ol>

                        <!-- Dashboard Cards -->
                        <div class="row">
                            <!-- Pedidos Activos -->
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-primary text-white mb-4">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="text-white-50 small">Mis Compras del Mes</div>
                                            <div class="fs-4 fw-bold">$ 2.080.000</div>
                                        </div>
                                        <i class="fas fa-shopping-bag fa-2x opacity-75"></i>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="#facturas">Ver Facturas</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Carrito actual -->
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-warning text-white mb-4">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="text-white-50 small">Artículos en Carrito</div>
                                            <div class="fs-4 fw-bold" id="cardItemCount">2 ítems</div>
                                        </div>
                                        <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="#carrito">Ir al Carrito</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Facturas Pendientes -->
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-danger text-white mb-4">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="text-white-50 small">Facturas por Pagar</div>
                                            <div class="fs-4 fw-bold">1</div>
                                        </div>
                                        <i class="fas fa-file-invoice fa-2x opacity-75"></i>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="#pagos">Pagar Ahora</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Estado de Quejas -->
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-success text-white mb-4">
                                    <div class="card-body d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="text-white-50 small">PQRS Resueltas</div>
                                            <div class="fs-4 fw-bold">3 / 3</div>
                                        </div>
                                        <i class="fas fa-check-circle fa-2x opacity-75"></i>
                                    </div>
                                    <div class="card-footer d-flex align-items-center justify-content-between">
                                        <a class="small text-white stretched-link" href="#quejas">Ver PQRS</a>
                                        <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 1: CARRITO DE COMPRAS (Sumar y Eliminar Productos) -->
                        <div class="card mb-4" id="carrito">
                            <div class="card-header bg-dark text-white">
                                <i class="fas fa-shopping-cart me-1"></i>
                                <strong>CARRITO DE COMPRAS ACTUAL</strong>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Producto Abrasivo</th>
                                                <th>Precio Unitario</th>
                                                <th style="width: 180px;">Cantidad</th>
                                                <th>Subtotal</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="cartTableBody">
                                            <tr>
                                                <td>Disco de Corte Fino 7 Pulgadas (C&M)</td>
                                                <td>$ 45.000</td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="changeQty(this, -1)">-</button>
                                                        <input type="text" class="form-control text-center item-qty" value="2" readonly>
                                                        <button class="btn btn-outline-secondary" type="button" onclick="changeQty(this, 1)">+</button>
                                                    </div>
                                                </td>
                                                <td class="item-subtotal">$ 90.000</td>
                                                <td>
                                                    <button class="btn btn-danger btn-sm" onclick="removeItem(this)"><i class="fas fa-trash-alt"></i> Eliminar</button>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>Lija al Agua Grano 120 (Paquete x 10)</td>
                                                <td>$ 25.000</td>
                                                <td>
                                                    <div class="input-group input-group-sm">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="changeQty(this, -1)">-</button>
                                                        <input type="text" class="form-control text-center item-qty" value="1" readonly>
                                                        <button class="btn btn-outline-secondary" type="button" onclick="changeQty(this, 1)">+</button>
                                                    </div>
                                                </td>
                                                <td class="item-subtotal">$ 25.000</td>
                                                <td>
                                                    <button class="btn btn-danger btn-sm" onclick="removeItem(this)"><i class="fas fa-trash-alt"></i> Eliminar</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3 border-top pt-3">
                                    <h4>Total Carrito: <span id="cartTotal" class="text-primary">$ 115.000</span></h4>
                                    <button class="btn btn-success btn-lg"><i class="fas fa-lock me-2"></i> Proceder al Pago</button>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 2: VER EL CATÁLOGO DE PRODUCTOS -->
                        <div class="card mb-4" id="catalogo">
                            <div class="card-header bg-dark text-white">
                                <i class="fas fa-store me-1"></i>
                                <strong>CATÁLOGO DE PRODUCTOS ABRASIVOS</strong>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($catalogProducts as $product):
                                        $productName = htmlspecialchars($product['PRO_nombre_producto'], ENT_QUOTES, 'UTF-8');
                                        $description = htmlspecialchars($product['PRO_descripcion'] ?: 'Producto abrasivo para aplicaciones industriales.', ENT_QUOTES, 'UTF-8');
                                        $brand = htmlspecialchars($product['PRO_marca'] ?: 'C&M', ENT_QUOTES, 'UTF-8');
                                        $price = (float) $product['PRO_precio_unitario'];
                                        $stock = (int) $product['PRO_stock_actual'];
                                        $image = trim((string) ($product['PRO_imagen_url'] ?? ''));
                                    ?>
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100 shadow-sm border-0">
                                                <?php if ($image): ?><img src="<?php echo htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo $productName; ?>" class="card-img-top" style="height:170px;object-fit:cover"><?php else: ?><div class="d-flex align-items-center justify-content-center bg-light text-warning" style="height:170px"><i class="fas fa-compact-disc fa-4x"></i></div><?php endif; ?>
                                                <div class="card-body d-flex flex-column">
                                                    <span class="small text-muted text-uppercase"><?php echo $brand; ?></span>
                                                    <h5 class="card-title mt-1"><?php echo $productName; ?></h5>
                                                    <p class="card-text text-muted small flex-grow-1"><?php echo $description; ?></p>
                                                    <div class="d-flex justify-content-between align-items-center mb-3"><strong class="text-success">$ <?php echo number_format($price, 0, ',', '.'); ?></strong><span class="badge <?php echo $stock > 0 ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $stock > 0 ? $stock . ' disponibles' : 'Agotado'; ?></span></div>
                                                    <button class="btn btn-outline-primary btn-sm" <?php echo $stock > 0 ? '' : 'disabled'; ?> onclick="addToCart('<?php echo addslashes($product['PRO_nombre_producto']); ?>', <?php echo $price; ?>)"><i class="fas fa-cart-plus"></i> <?php echo $stock > 0 ? 'Agregar al Carrito' : 'Sin existencias'; ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <?php if (!$catalogProducts): ?><div class="col-12"><div class="alert alert-info mb-0"><i class="fas fa-info-circle me-2"></i>El catálogo está esperando nuevos productos del gerente.</div></div><?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 3: FACTURAS Y MEDIOS DE PAGO -->
                        <div class="row">
                            <!-- Tabla de Facturas -->
                            <div class="col-xl-6">
                                <div class="card mb-4" id="facturas">
                                    <div class="card-header bg-dark text-white">
                                        <i class="fas fa-file-invoice-dollar me-1"></i>
                                        <strong>MIS FACTURAS</strong>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-striped table-bordered">
                                            <thead>
                                                <tr>
                                                    <th>Factura</th>
                                                    <th>Fecha</th>
                                                    <th>Total</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>FAC-000891</td>
                                                    <td>10/08/2026</td>
                                                    <td>$ 1.250.000</td>
                                                    <td><span class="badge bg-success">Pagada</span></td>
                                                </tr>
                                                <tr>
                                                    <td>FAC-000892</td>
                                                    <td>02/09/2026</td>
                                                    <td>$ 830.000</td>
                                                    <td><span class="badge bg-danger">Pendiente</span></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Medios de Pago -->
                            <div class="col-xl-6">
                                <div class="card mb-4" id="pagos">
                                    <div class="card-header bg-dark text-white">
                                        <i class="fas fa-credit-card me-1"></i>
                                        <strong>MEDIOS DE PAGO REGISTRADOS</strong>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group mb-3">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="fab fa-cc-visa text-primary fa-lg me-2"></i> Tarjeta Crédito terminada en <strong>4321</strong>
                                                </div>
                                                <span class="badge bg-primary rounded-pill">Principal</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="fas fa-university text-secondary fa-lg me-2"></i> PSE / Cuenta Bancolombia
                                                </div>
                                                <button class="btn btn-sm btn-outline-secondary">Editar</button>
                                            </li>
                                        </ul>
                                        <button class="btn btn-outline-dark btn-sm w-100"><i class="fas fa-plus"></i> Agregar Nuevo Medio de Pago</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SECCIÓN 4: QUEJAS O PQRS -->
                        <div class="card mb-4" id="quejas">
                            <div class="card-header bg-dark text-white">
                                <i class="fas fa-headset me-1"></i>
                                <strong>CENTRO DE SOPORTE Y QUEJAS (PQRS)</strong>
                            </div>
                            <div class="card-body">
                                <form>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Tipo de Solicitud</label>
                                            <select class="form-select">
                                                <option>Queja sobre producto</option>
                                                <option>Reclamo por entrega</option>
                                                <option>Sugerencia</option>
                                            </select>
                                        </div>
                                        <div class="col-md-8 mb-3">
                                            <label class="form-label">Descripción del caso</label>
                                            <input type="text" class="form-control" placeholder="Detalle su solicitud...">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-paper-plane"></i> Enviar PQRS</button>
                                </form>
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
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
        <script src="js/scripts.js"></script>
        <script src="js/datatables-simple-demo.js"></script>

        <!-- SCRIPT INTERACTIVO PARA EL CARRITO (SUMAR Y ELIMINAR) -->
        <script>
            function updateCartTotal() {
                let rows = document.querySelectorAll('#cartTableBody tr');
                let total = 0;
                let count = 0;

                rows.forEach(row => {
                    let priceText = row.children[1].innerText.replace('$', '').replace(/\./g, '').trim();
                    let qty = parseInt(row.querySelector('.item-qty').value);
                    let price = parseFloat(priceText);
                    
                    let subtotal = price * qty;
                    row.querySelector('.item-subtotal').innerText = '$ ' + subtotal.toLocaleString('es-CO');
                    total += subtotal;
                    count += qty;
                });

                document.getElementById('cartTotal').innerText = '$ ' + total.toLocaleString('es-CO');
                document.getElementById('cardItemCount').innerText = count + ' ítems';
                document.getElementById('cartBadge').innerText = count;
            }

            function changeQty(btn, delta) {
                let input = btn.parentElement.querySelector('.item-qty');
                let currentVal = parseInt(input.value);
                let newVal = currentVal + delta;
                if (newVal >= 1) {
                    input.value = newVal;
                    updateCartTotal();
                }
            }

            function removeItem(btn) {
                let row = btn.closest('tr');
                row.remove();
                updateCartTotal();
            }

            function addToCart(productName, price) {
                let tbody = document.getElementById('cartTableBody');
                
                // Verificar si ya existe para sumarle 1
                let existingRow = Array.from(tbody.querySelectorAll('tr')).find(row => row.children[0].innerText === productName);
                if (existingRow) {
                    let qtyInput = existingRow.querySelector('.item-qty');
                    qtyInput.value = parseInt(qtyInput.value) + 1;
                } else {
                    let newRow = document.createElement('tr');
                    newRow.innerHTML = `
                        <td>${productName}</td>
                        <td>$ ${price.toLocaleString('es-CO')}</td>
                        <td>
                            <div class="input-group input-group-sm">
                                <button class="btn btn-outline-secondary" type="button" onclick="changeQty(this, -1)">-</button>
                                <input type="text" class="form-control text-center item-qty" value="1" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="changeQty(this, 1)">+</button>
                            </div>
                        </td>
                        <td class="item-subtotal">$ ${price.toLocaleString('es-CO')}</td>
                        <td>
                            <button class="btn btn-danger btn-sm" onclick="removeItem(this)"><i class="fas fa-trash-alt"></i> Eliminar</button>
                        </td>
                    `;
                    tbody.appendChild(newRow);
                }
                updateCartTotal();
                alert('¡Producto agregado al carrito exitosamente!');
            }
        </script>
    </body>
</html>