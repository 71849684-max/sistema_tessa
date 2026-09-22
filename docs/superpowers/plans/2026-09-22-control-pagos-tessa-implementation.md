# Control de pagos Tessa Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox syntax for tracking.

**Goal:** Construir una aplicación PHP independiente y segura para gestionar pagos, contratos, egresos y acceso interno de Tessa.

**Architecture:** Las vistas PHP y JavaScript consumen controladores delgados, que autorizan y delegan a Services. Los Services usan exclusivamente mysqli preparado y CALL sp_* contra MariaDB; los procedimientos ejecutan las mutaciones financieras transaccionales.

**Tech Stack:** PHP 8.1+ estricto, MariaDB/MySQL, Apache/Laragon, HTML5, CSS3 y JavaScript vanilla.

**Spec:** docs/superpowers/specs/2026-09-22-control-pagos-tessa-design.md

## Global Constraints

- No usar Laravel, otro framework ni librerías de aplicación.
- Todo PHP nuevo declara strict_types=1; los controladores no contienen SQL.
- Toda operación de base usa prepared statements y CALL sp_*; la respuesta es {exito,mensaje,datos,errores}.
- No migrar ni incluir datos comerciales, usuarios ni credenciales predeterminadas.
- Contraseñas: password_hash()/password_verify(); login exitoso usa session_regenerate_id(true).
- Comprobantes únicamente en storage/vouchers/; CSS en resources/css/, JS en resources/js/ y marca en assets/recursos/.
- El permiso efectivo prioriza denegación individual, permiso individual y permiso heredado de cargo, en ese orden.

## Review Focus

- Una denegación individual vence un permiso heredado del cargo para la misma acción y módulo.
- Un pago simultáneo o superior al saldo de una cuota falla sin alterar importes ya aplicados.
- Anular una cobranza sin motivo o ya anulada falla y no revierte dos veces.
- Un archivo con extensión permitida pero MIME/firma inválida no llega a storage/vouchers.
- Un captcha vencido o reutilizado falla aun con usuario y contraseña correctos.

---

## Estructura de archivos

~~~
capaconexion/{datos.local.example.php,Conexion.php}
capalogica/core/{BaseService.php,RespuestaHelper.php,SessionService.php}
capalogica/seguridad/{CaptchaService.php,AuthService.php,PermisoService.php,AuditoriaService.php,VoucherService.php}
capalogica/ventas/{ClienteService.php,ServicioService.php,ContratoService.php,CobranzaService.php,EgresoService.php}
capalogica/rrhh/{PersonalService.php,CargoService.php,UsuarioService.php}
controllers/{core,seguridad,ventas,rrhh}/
capacliente/{app,ventas,rrhh}/
resources/{css,js}/
assets/recursos/{imagenes,videos,fuentes}/
storage/vouchers/
bd/tessa_control_pagos.sql
scripts/inicializar_admin.php
tests/
docs/PRODUCCION.md
~~~

### Task 1: Foundation, configuration and common API contract

**Files:**
- Create: .gitignore, capaconexion/datos.local.example.php, capaconexion/Conexion.php, capalogica/core/{RespuestaHelper.php,BaseService.php,SessionService.php}, controllers/core/ControllerHelper.php, tests/{bootstrap.php,RespuestaHelperTest.php}.

**Interfaces:**
- Produces: RespuestaHelper::ok(string,mixed): array, RespuestaHelper::error(string,array,mixed): array, Conexion::call(string,string,array): array, ControllerHelper::input(): array.

- [ ] **Step 1: Write the failing response-contract test.**

~~~php
<?php declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
assert(RespuestaHelper::ok('Correcto', ['id' => 7]) === [
    'exito'=>true, 'mensaje'=>'Correcto', 'datos'=>['id'=>7], 'errores'=>[]
]);
~~~

- [ ] **Step 2: Run test to verify it fails.**

Run: php tests/RespuestaHelperTest.php

Expected: fatal error because RespuestaHelper does not exist.

- [ ] **Step 3: Write the minimal implementation.**

~~~php
public static function ok(string $mensaje, mixed $datos = null): array {
    return ['exito'=>true,'mensaje'=>$mensaje,'datos'=>$datos,'errores'=>[]];
}
public function call(string $sql, string $tipos = '', array $parametros = []): array {
    $stmt = $this->mysqli->prepare($sql);
    // bind, execute, collect result sets, release pending CALL results
}
~~~

Load only environment variables or datos.local.php, use utf8mb4, and return generic technical errors.

- [ ] **Step 4: Run test and syntax validation.**

Run: php tests/RespuestaHelperTest.php; Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }

Expected: test pass and all files report no syntax errors.

- [ ] **Step 5: Commit.**

~~~bash
git add .gitignore capaconexion capalogica/core controllers/core tests
git commit -m "feat: add secure PHP foundation"
~~~

### Task 2: Clean schema, procedures and first-admin initializer

**Files:**
- Create: bd/tessa_control_pagos.sql, scripts/inicializar_admin.php, docs/PRODUCCION.md, tests/InicializadorAdminTest.php.

**Interfaces:**
- Consumes: Conexion::call().
- Produces: tables from the approved design and sp_login_obtener_hash, sp_login_registrar_intento, sp_admin_inicializar.

- [ ] **Step 1: Write the failing CLI guard test.**

~~~php
<?php declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
assert(validarPasswordInicial('corta') === 'La contraseña debe tener al menos 12 caracteres.');
assert(validarPasswordInicial('UnaClaveSegura!2026') === null);
~~~

- [ ] **Step 2: Run test to verify it fails.**

Run: php tests/InicializadorAdminTest.php

Expected: undefined validarPasswordInicial.

- [ ] **Step 3: Write schema and initializer.**

Create all entities, foreign keys, indexes, audit fields and authorization metadata from the design. Write procedures for login, users, permissions, catalogues, contracts, milestones, collections and expenses. The CLI initializer inserts first person/cargo/admin/user in one transaction and hashes with PASSWORD_DEFAULT.

~~~php
if (PHP_SAPI !== 'cli') { fwrite(STDERR, "Solo CLI\n"); exit(1); }
$hash = password_hash($password, PASSWORD_DEFAULT);
$db->call('CALL sp_admin_inicializar(?,?,?,?,?)', 'sssss', [$usuario,$hash,$nombres,$apellidos,$dni]);
~~~

- [ ] **Step 4: Run test and import a clean database.**

Run: php tests/InicializadorAdminTest.php; mysql -u root tessa_control_pagos < bd/tessa_control_pagos.sql

Expected: test passes and import has no SQL errors. If root needs a password, use local configuration without adding it to source.

- [ ] **Step 5: Commit.**

~~~bash
git add bd scripts docs/PRODUCCION.md tests/InicializadorAdminTest.php
git commit -m "feat: add clean payment control schema"
~~~

### Task 3: Captcha, sessions, login and effective authorization

**Files:**
- Create: capalogica/seguridad/{CaptchaService.php,AuthService.php,PermisoService.php,AuditoriaService.php}, controllers/seguridad/{auth_controller.php,captcha_controller.php}, capacliente/app/login.php, tests/{CaptchaServiceTest.php,PermisoServiceTest.php,AuthServiceTest.php}.

**Interfaces:**
- Produces: CaptchaService::generar(): string, CaptchaService::validar(string): bool, AuthService::login(array): array, PermisoService::autorizar(string,string): bool.

- [ ] **Step 1: Write failing captcha and permission precedence tests.**

~~~php
$captcha = new CaptchaService($session);
$svg = $captcha->generar();
assert(str_starts_with($svg, '<svg'));
assert($captcha->validar($session['captcha_codigo']) === true);
assert($captcha->validar($session['captcha_codigo']) === false);
$service = new PermisoService([['modulo'=>'cobranzas','accion'=>'anular','permitido'=>1]],
    [['modulo'=>'cobranzas','accion'=>'anular','permitido'=>0]]);
assert($service->autorizar('cobranzas', 'anular') === false);
~~~

- [ ] **Step 2: Run tests to verify they fail.**

Run: php tests/CaptchaServiceTest.php; php tests/PermisoServiceTest.php

Expected: missing service classes.

- [ ] **Step 3: Implement services and controller guards.**

Generate randomized one-use SVG captcha with expiry; consume it after validation. AuthService checks captcha, calls login SP, runs password_verify, records failure/lockout, regenerates session on success and audits it. Resolve deny/allow/cargo precedence from SP result and check it before controller actions.

- [ ] **Step 4: Run tests and manual login checks.**

Run: php tests/CaptchaServiceTest.php; php tests/PermisoServiceTest.php; php tests/AuthServiceTest.php

Expected: PASS. Test captcha refresh, invalid captcha, invalid password and valid login in browser.

- [ ] **Step 5: Commit.**

~~~bash
git add capalogica/seguridad controllers/seguridad capacliente/app/login.php tests
git commit -m "feat: add secure login and permissions"
~~~

### Task 4: Tessa assets, responsive shell and video login

**Files:**
- Create: assets/recursos/{imagenes,videos,fuentes}/, resources/css/{app.css,login.css}, resources/js/app.js, capacliente/app/{_bootstrap.php,layout.php,dashboard.php}, tests/AssetLayoutTest.php.

**Interfaces:**
- Produces: renderizarLayout(string,string,callable): void and sidebar trigger [data-sidebar-toggle].

- [ ] **Step 1: Write the failing asset/accessibility test.**

~~~php
assert(is_file(__DIR__ . '/../assets/recursos/imagenes/tessa-logo.png'));
assert(is_file(__DIR__ . '/../assets/recursos/videos/llamagozu.mp4'));
assert(str_contains(file_get_contents(__DIR__ . '/../resources/css/login.css'), 'prefers-reduced-motion'));
~~~

- [ ] **Step 2: Run test to verify it fails.**

Run: php tests/AssetLayoutTest.php

Expected: missing asset and CSS files.

- [ ] **Step 3: Copy authorized materials and implement the shell.**

Copy logo/favicons, llamagozu.mp4, Outfit and title font. Use video only in login: muted, looped, playsinline, dark overlay, poster/static fallback and disabled autoplay for mobile/reduced motion. Build accessible sidebar, stacked filters, responsive modals and horizontal table scroll. Keep uploads out of public assets.

- [ ] **Step 4: Run test and inspect at 375px/1280px.**

Run: php tests/AssetLayoutTest.php

Expected: PASS. Confirm readable form over video, collapsible sidebar, filters stack, table scroll and modal fits screen.

- [ ] **Step 5: Commit.**

~~~bash
git add assets resources capacliente/app tests/AssetLayoutTest.php
git commit -m "feat: add Tessa responsive application shell"
~~~

### Task 5: Personal, cargo, users and individual permissions

**Files:**
- Create: capalogica/rrhh/{PersonalService.php,CargoService.php,UsuarioService.php}, controllers/rrhh/{personal_controller.php,cargo_controller.php,usuario_controller.php,permiso_controller.php,auditoria_controller.php}, capacliente/rrhh/{personal.php,cargos.php,usuarios.php,auditoria.php}, resources/js/{personal.js,usuarios.js}, tests/PersonalValidationTest.php.

**Interfaces:**
- Consumes: sp_personal_*, sp_cargo_*, sp_usuario_*, sp_permiso_*.
- Produces: CRUD actions guarded by ver, crear, editar and administrar.

- [ ] **Step 1: Write a failing staff validation test.**

~~~php
$service = new PersonalService($fakeDb);
$resultado = $service->guardar(['dni'=>'123','nombres'=>'Ana','apellidos'=>'Rojas','id_cargo'=>1]);
assert($resultado['exito'] === false);
assert(isset($resultado['errores']['dni']));
~~~

- [ ] **Step 2: Run test to verify it fails.**

Run: php tests/PersonalValidationTest.php

Expected: PersonalService missing.

- [ ] **Step 3: Implement personnel, user and permission administration.**

Validate DNI/names/cargo, hash passwords only in UsuarioService, persist cargo templates and person allow/deny overrides with SPs. Audit user/password/permission changes. Render safe responsive forms and permission matrices.

- [ ] **Step 4: Run test and authorization walkthrough.**

Run: php tests/PersonalValidationTest.php

Expected: PASS. Create cargo grant, deny same action for one person, and verify protected endpoint returns 403.

- [ ] **Step 5: Commit.**

~~~bash
git add capalogica/rrhh controllers/rrhh capacliente/rrhh resources/js tests
git commit -m "feat: add personal and granular permissions"
~~~

### Task 6: Clients, services and contracts with milestones

**Files:**
- Create: capalogica/ventas/{ClienteService.php,ServicioService.php,ContratoService.php}, controllers/ventas/{cliente_controller.php,servicio_controller.php,contrato_controller.php}, capacliente/ventas/{clientes.php,servicios.php,contratos.php}, resources/js/{api.js,clientes.js,servicios.js,contratos.js}, tests/{ClienteServiceTest.php,ContratoServiceTest.php}.

**Interfaces:**
- Produces: ContratoService::guardar(array): array and ContratoService::anular(int,string): array.

- [ ] **Step 1: Write failing contract-total test.**

~~~php
$resultado = (new ContratoService($fakeDb))->guardar([
  'tipo'=>'INDIVIDUAL','bruto'=>'100','descuento'=>'10','hitos'=>[['monto'=>80]]
]);
assert($resultado['exito'] === false);
assert($resultado['errores']['hitos'] === 'La suma de cuotas debe ser igual al monto neto.');
~~~

- [ ] **Step 2: Run tests to verify they fail.**

Run: php tests/ClienteServiceTest.php; php tests/ContratoServiceTest.php

Expected: missing service classes.

- [ ] **Step 3: Implement catalogues and contract workflow.**

Validate natural/legal documents, service catalogues, individual/group requirements, group members, dates and exact milestone total. Stored procedures generate number and transactionally save contract, members and milestones. Void requires reason and rejects active collections. Audit mutations.

- [ ] **Step 4: Run tests and browser contract walkthrough.**

Run: php tests/ClienteServiceTest.php; php tests/ContratoServiceTest.php

Expected: PASS. Create client, service, individual and group contract, verify details/history.

- [ ] **Step 5: Commit.**

~~~bash
git add capalogica/ventas controllers/ventas capacliente/ventas resources/js tests bd
git commit -m "feat: add catalogues contracts and milestones"
~~~

### Task 7: Collections, protected vouchers and reversals

**Files:**
- Create: capalogica/seguridad/VoucherService.php, capalogica/ventas/CobranzaService.php, controllers/{ventas/cobranza_controller.php,seguridad/archivo_controller.php}, capacliente/ventas/cobranzas.php, resources/js/cobranzas.js, tests/{VoucherServiceTest.php,CobranzaServiceTest.php}.

**Interfaces:**
- Produces: VoucherService::guardar(array): array, CobranzaService::registrar(array): array, CobranzaService::anular(int,string): array.

- [ ] **Step 1: Write failing upload and void tests.**

~~~php
$file = ['tmp_name'=>__DIR__.'/fixtures/falso.jpg','name'=>'falso.jpg','size'=>10,'error'=>UPLOAD_ERR_OK];
assert((new VoucherService($storage))->guardar($file)['exito'] === false);
assert((new CobranzaService($fakeDb))->anular(8, '')['errores']['motivo'] === 'El motivo de anulación es obligatorio.');
~~~

- [ ] **Step 2: Run tests to verify they fail.**

Run: php tests/VoucherServiceTest.php; php tests/CobranzaServiceTest.php

Expected: undefined classes.

- [ ] **Step 3: Implement storage and collection procedures.**

Check detected JPEG/PNG/PDF MIME and signature, enforce 5 MiB, generate random server filename/hash and move into storage/vouchers. Serve it only through authorized controller. Register allocations transactionally while locking milestone balances; void once with reason through sp_cobranza_anular and recalculate states.

- [ ] **Step 4: Run tests and essential collections check.**

Run: php tests/VoucherServiceTest.php; php tests/CobranzaServiceTest.php

Expected: PASS. Upload valid voucher, make partial payment, void it and confirm restored balance/history.

- [ ] **Step 5: Commit.**

~~~bash
git add capalogica controllers capacliente/ventas/cobranzas.php resources/js/cobranzas.js storage tests bd
git commit -m "feat: add collections and secure vouchers"
~~~

### Task 8: Expenses, audit view and final essential verification

**Files:**
- Create: capalogica/ventas/EgresoService.php, controllers/ventas/egreso_controller.php, capacliente/ventas/egresos.php, resources/js/egresos.js, tests/EgresoServiceTest.php.

**Interfaces:**
- Consumes: sp_egreso_* and sp_auditoria_listar.
- Produces: filtered expense list, registration and reasoned void.

- [ ] **Step 1: Write failing expense validation test.**

~~~php
$r = (new EgresoService($fakeDb))->registrar(['fecha'=>'2026-09-22','concepto'=>'Taxi','monto'=>'0']);
assert($r['exito'] === false);
assert($r['errores']['monto'] === 'El monto debe ser mayor que cero.');
~~~

- [ ] **Step 2: Run test to verify it fails.**

Run: php tests/EgresoServiceTest.php

Expected: missing EgresoService.

- [ ] **Step 3: Implement expenses and audit listing.**

Validate date, category/concept, beneficiary and positive amount; filter by inclusive date/state; void only with a reason. Record sensitive events through the audit service and list them only with audit view permission.

- [ ] **Step 4: Run all tests, syntax and browser checks.**

Run: Get-ChildItem tests -Filter *Test.php | ForEach-Object { php $_.FullName }; Get-ChildItem -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }

Expected: every test and syntax check pass. Verify expense create/filter/void, audit trail and 375px/1280px responsive behavior.

- [ ] **Step 5: Commit.**

~~~bash
git add capalogica controllers capacliente resources tests bd docs
git commit -m "feat: complete Tessa payment control"
~~~

## Self-review

- Spec coverage: Tasks 1–2 implement configuration, schema, deployment and first admin; Task 3 implements login, authorization and audit; Task 4 implements supplied brand materials, login video and responsive layout; Tasks 5–6 implement personnel, catalogues and contracts; Tasks 7–8 implement collections, vouchers, expenses and audit filters.
- Placeholder scan: no deferred work markers; every task has explicit files, a failing test, command, implementation behavior and passing verification.
- Type consistency: Services return the same response array; controllers consume arrays; authorization always receives (modulo, accion).
- Review-focus coverage: task 3 tests deny precedence/captcha reuse; task 7 tests file spoofing, payment totals and cancellation; task 8 tests expense invalid amount.

