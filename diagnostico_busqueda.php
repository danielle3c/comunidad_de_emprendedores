<?php
// ============================================================
// DIAGNÓSTICO DEL BUSCADOR — subir a la raíz del proyecto
// Abrir en navegador: http://tu-servidor/proyecto/diagnostico_busqueda.php
// BORRAR este archivo después de usarlo
// ============================================================
require_once __DIR__ . '/includes/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Diagnóstico Buscador</title>
<style>
body { font-family: monospace; padding: 2rem; background: #0d1a0d; color: #e0f0e0; }
h2 { color: #43b02a; }
.ok   { color: #4ade80; }
.fail { color: #f87171; }
.warn { color: #fbbf24; }
pre  { background: #111; padding: 1rem; border-left: 3px solid #43b02a; white-space: pre-wrap; font-size: .85rem; }
table { border-collapse: collapse; width: 100%; margin: .5rem 0; }
th, td { border: 1px solid #2a4a2a; padding: .4rem .7rem; text-align: left; }
th { background: #1a3a1a; color: #43b02a; }
</style>
</head>
<body>

<h2>🔍 Diagnóstico del Buscador de Personas</h2>

<?php
try {
    $pdo = getConnection();
    echo "<p class='ok'>✔ Conexión a la base de datos: OK</p>";
} catch (Exception $e) {
    echo "<p class='fail'>✖ Conexión fallida: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// ── Función helper ──────────────────────────────────────────
function probar($pdo, $descripcion, $sql, $params = []) {
    echo "<h3>" . htmlspecialchars($descripcion) . "</h3>";
    try {
        $s = $pdo->prepare($sql);
        $s->execute($params);
        $rows = $s->fetchAll(PDO::FETCH_ASSOC);
        echo "<p class='ok'>✔ Consulta exitosa — " . count($rows) . " fila(s)</p>";
        if ($rows) {
            echo "<table><tr>";
            foreach (array_keys($rows[0]) as $col) echo "<th>" . htmlspecialchars($col) . "</th>";
            echo "</tr>";
            foreach (array_slice($rows, 0, 5) as $row) {
                echo "<tr>";
                foreach ($row as $v) echo "<td>" . htmlspecialchars((string)$v) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p class='warn'>⚠ Sin filas (la tabla existe pero está vacía o sin coincidencias)</p>";
        }
        return true;
    } catch (PDOException $e) {
        echo "<pre class='fail'>✖ ERROR: " . htmlspecialchars($e->getMessage()) . "</pre>";
        return false;
    }
}

// ── 1. Listar todas las tablas ──────────────────────────────
echo "<h2>1. Tablas en la base de datos</h2>";
probar($pdo, "Todas las tablas", "SHOW TABLES");

// ── 2. Estructura de personas ───────────────────────────────
echo "<h2>2. Columnas de cada tabla relevante</h2>";
foreach (['personas', 'emprendedores', 'Contratos', 'contratos', 'creditos', 'inscripciones_talleres'] as $t) {
    try {
        $s = $pdo->query("DESCRIBE `$t`");
        $cols = $s->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Tabla: <span style='color:#43b02a'>$t</span></h3>";
        echo "<table><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        foreach ($cols as $c) {
            echo "<tr><td><b>{$c['Field']}</b></td><td>{$c['Type']}</td><td>{$c['Null']}</td><td>{$c['Key']}</td><td>{$c['Default']}</td></tr>";
        }
        echo "</table>";
    } catch (PDOException $e) {
        echo "<p class='warn'>⚠ Tabla <b>$t</b> no existe (normal si el nombre es diferente)</p>";
    }
}

// ── 3. Consulta base de personas ────────────────────────────
echo "<h2>3. Consulta base del buscador</h2>";
$ok1 = probar($pdo,
    "SELECT personas WHERE estado=1 LIKE '%a%'",
    "SELECT idpersonas AS id, rut, CONCAT(nombres,' ',apellidos) AS nombre FROM personas WHERE estado=1 AND (nombres LIKE :q OR apellidos LIKE :q) LIMIT 5",
    [':q' => '%a%']
);

// Si la consulta base falla, intenta variantes
if (!$ok1) {
    echo "<p class='warn'>Intentando con estado='Activo'...</p>";
    probar($pdo,
        "SELECT personas WHERE estado='Activo'",
        "SELECT * FROM personas WHERE estado='Activo' LIMIT 3",
        []
    );
    echo "<p class='warn'>Intentando sin filtro de estado...</p>";
    probar($pdo, "SELECT personas sin filtro", "SELECT * FROM personas LIMIT 3");
}

// ── 4. JOIN con emprendedores ────────────────────────────────
echo "<h2>4. JOINs de badges</h2>";

// Intentar detectar nombre real de la tabla Contratos
$tablaContratos = null;
foreach (['Contratos', 'contratos', 'CONTRATOS'] as $t) {
    try { $pdo->query("SELECT 1 FROM `$t` LIMIT 1"); $tablaContratos = $t; break; }
    catch (PDOException $e) { continue; }
}
echo "<p>Tabla Contratos detectada: <b style='color:#43b02a'>" . ($tablaContratos ?? 'NO ENCONTRADA') . "</b></p>";

if ($tablaContratos) {
    probar($pdo,
        "JOIN emprendedores ↔ $tablaContratos",
        "SELECT e.idemprendedores, e.personas_idpersonas, c.estado FROM emprendedores e JOIN `$tablaContratos` c ON c.emprendedores_idemprendedores = e.idemprendedores LIMIT 5"
    );
}

probar($pdo,
    "JOIN emprendedores ↔ creditos",
    "SELECT e.idemprendedores, cr.estado FROM emprendedores e JOIN creditos cr ON cr.emprendedores_idemprendedores = e.idemprendedores LIMIT 5"
);

probar($pdo,
    "JOIN emprendedores ↔ inscripciones_talleres",
    "SELECT e.idemprendedores, it.emprendedores_idemprendedores FROM emprendedores e JOIN inscripciones_talleres it ON it.emprendedores_idemprendedores = e.idemprendedores LIMIT 5"
);

// ── 5. Consulta completa del buscador ────────────────────────
echo "<h2>5. Consulta COMPLETA del buscador (con EXISTS)</h2>";
if ($tablaContratos) {
    probar($pdo,
        "Consulta completa — buscar 'a'",
        "SELECT p.idpersonas AS id, p.rut, CONCAT(p.nombres,' ',p.apellidos) AS nombre,
            EXISTS(SELECT 1 FROM emprendedores e JOIN `$tablaContratos` c ON c.emprendedores_idemprendedores=e.idemprendedores WHERE e.personas_idpersonas=p.idpersonas AND c.estado='Activo') AS contrato_activo,
            EXISTS(SELECT 1 FROM emprendedores e JOIN creditos cr ON cr.emprendedores_idemprendedores=e.idemprendedores WHERE e.personas_idpersonas=p.idpersonas AND cr.estado='Activo') AS credito_activo,
            EXISTS(SELECT 1 FROM emprendedores e JOIN inscripciones_talleres it ON it.emprendedores_idemprendedores=e.idemprendedores WHERE e.personas_idpersonas=p.idpersonas) AS talleres
        FROM personas p WHERE p.estado=1 AND (p.nombres LIKE :q OR p.apellidos LIKE :q) LIMIT 5",
        [':q' => '%a%']
    );
}

echo "<hr><p style='color:#666;font-size:.8rem'>⚠️ Eliminar este archivo del servidor cuando termines el diagnóstico</p>";
?>
</body>
</html>
