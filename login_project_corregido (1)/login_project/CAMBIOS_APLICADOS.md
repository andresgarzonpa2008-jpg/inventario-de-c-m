# Cambios aplicados — C&M Soluciones Abrasivas SAS

## Cómo poner esto a andar
1. Restaura tu base de datos `proyecto_2` como siempre.
2. Corre **una sola vez** `database/migracion_seguridad.sql` sobre esa base (ideal: primero sobre una copia).
3. Reemplaza tu carpeta `login_project` por esta (o copia archivo por archivo si prefieres revisar antes).
4. Verifica que tu servidor PHP tenga la extensión `finfo` habilitada (ya se usaba para las imágenes) — no se agregó ninguna dependencia nueva vía Composer, todo es PHP puro.

## 1. Base de datos (`database/migracion_seguridad.sql`)
- Se agregó **PRIMARY KEY** a `productos`, `categoria` y `catalogo` (no la tenían en el dump original — riesgo de duplicados).
- El `ENUM` de `usuarios.rol` ahora incluye `admin` e `inventario` (antes solo `cliente`, `proveedor`, `gerente`; el código ya los usaba pero la BD los rechazaba).
- Se agregó columna `deleted_at` a `usuarios`, `productos` y `ventas` (soft delete).
- Se creó la tabla `auditoria_productos`, que **no existía** aunque los triggers `tr_auditoria_producto` y `tr_auditoria_producto_delete` ya intentaban insertar ahí — esto hacía que el DELETE físico de un producto fallara con error. Se eliminó el trigger duplicado y se agregó uno nuevo para auditar también el borrado lógico.
- Índices nuevos para que búsqueda/orden en usuarios y productos no hagan table scan.

## 2. Seguridad
- **CSRF** (`config/csrf.php`): token de sesión verificado en TODOS los formularios POST (login, registro, usuarios, editar usuario, inventario, reportes/ventas).
- **Expiración de sesión** (`config/session_guard.php`): cierra sesión automáticamente tras 15 minutos de inactividad.
- **Módulo legado `crud/crud` eliminado por completo**: tenía inyección SQL explotable (concatenación directa en queries), XSS reflejado/almacenado y contraseñas guardadas en texto plano. No se recomienda recuperarlo; si necesitas esa funcionalidad, dímelo y la reconstruimos de forma segura sobre el patrón MVC ya usado en `login_project`.
- `config/conexion.php`: corregido el nombre de la base de datos (`proyecto_v2` → `proyecto_2`, coincidiendo con tu dump real).

## 3. Bug funcional corregido
- El router (`index.php`) pedía `view/editar_usuario.php`, pero ese archivo vivía en `crud/crud/editar_usuario.php`. Hoy en tu app original, el botón "Editar" de un usuario lanza error fatal. Se movió al lugar correcto y se completó el `<select>` de roles (faltaban `cliente`, `proveedor`, `inventario`).

## 4. Eliminación lógica (soft delete)
Usuarios, productos y ventas ahora se marcan con `deleted_at = NOW()` en vez de borrarse físicamente. Todos los listados (`usuarios.php`, `inventario.php`, `reportes.php`, el dashboard y el endpoint `dashboard_data`) filtran `WHERE deleted_at IS NULL`.

## 5. Gestión de usuarios (`view/usuarios.php`)
- Caja de búsqueda por nombre/usuario/correo.
- Encabezados de tabla ordenables (ID, Nombre, Correo, Rol).
- Paginación de 10 en 10.
- Tarjetas de estadísticas corregidas para contar el total real (antes solo contaban lo cargado en pantalla).

## 6. Reportes (`view/reportes.php`)
- Filtro por rango de fechas (desde/hasta).
- **Exportar CSV**: incluye BOM UTF-8 (tildes/ñ se ven bien en Excel), respeta el filtro de fechas aplicado.
- **Exportar PDF**: usa la librería libre FPDF (`vendor/fpdf/fpdf.php`, sin dependencias externas ni Composer). Incluye encabezado con el nombre de la empresa y el periodo consultado, tabla de ventas y numeración de página ("Página X de Y").

## 7. Control de acceso (hallazgos de la revisión de sustentación)
Encontrados al validar el punto 1.2 del checklist (RBAC): los archivos de `view/`
no tenían guardia propia y se podía acceder a ellos directo por URL, saltándose
`index.php` por completo.

- **`.htaccess` con `Deny from all`** en `view/`, `model/`, `controller/`, `config/`,
  `vendor/` y `database/`: bloquea el acceso HTTP directo a esas carpetas. El
  router sigue funcionando igual porque usa `require_once` (inclusión de
  archivo en PHP), que no pasa por Apache/.htaccess.
- **`config/require_auth.php`** (nuevo): segunda capa de protección, por si el
  `.htaccess` fallara o el proyecto se moviera a un hosting que lo ignore. Cada
  vista sensible ahora empieza con `require_role([...])`, verificando sesión y
  rol por sí misma, sin depender solo del enrutador:
  - `gerente_sbadm.php` → admin, gerente, inventario
  - `clientes_dashboard.php` → cliente, admin
  - `proveedores_dashboard.php` → proveedor, admin
  - `inventario.php` → inventario, gerente, admin
  - `reportes.php` → gerente, admin
  - `usuarios.php`, `editar_usuario.php` → admin
  - `perfil.php` → cualquier rol autenticado
- **Enrutador (`index.php`)**: el `else` final antes cargaba `view/gerente_sbadm.php`
  para *cualquier* `action` no reconocida, sin chequear el rol (un `cliente`
  pidiendo `?action=reportes` terminaba viendo el dashboard gerencial). Ahora
  cae en `vista_home_para_rol($rol)`, que manda a cada quien a SU propia vista,
  nunca a una con más privilegios.
- **`controller/register_user.php` eliminado**: era un script de prueba
  olvidado, accesible por URL, que registraba un usuario con `rol = "usuario"`
  — valor que ni siquiera existe en el `ENUM` de `usuarios.rol` — y podía
  lanzar una excepción no controlada.
- **XSS defensivo**: `$error`/`$mensaje` en `login.php` y `register.php` ahora
  se escapan con `htmlspecialchars()` en el punto de salida (antes se
  escapaban solo al construirse en `index.php`; funcionaba, pero era frágil).
- **`utf8_decode()` reemplazado** por `mb_convert_encoding(..., 'ISO-8859-1', 'UTF-8')`
  en la exportación a PDF de `reportes.php`: `utf8_decode()` está deprecada
  desde PHP 8.2 y generaba warnings.

## Pendiente / recomendado revisar tú mismo
- No pude levantar un MySQL/MariaDB completo en este entorno para probar el flujo de principio a fin contra datos reales; sí verifiqué que **todos los archivos PHP pasan `php -l`** (sin errores de sintaxis) y revisé la lógica a mano. Aun así, prueba el flujo completo (login, crear/editar/eliminar en cada módulo, exportar CSV y PDF) antes de tu sustentación.
- Considera correr `ANALYZE TABLE` después de la migración si tu tabla de productos/usuarios crece mucho, por los índices nuevos.
- El responsive y los tiempos de respuesta (<2s) no se pudieron medir sin un servidor corriendo — pruébalo en tu entorno local.
