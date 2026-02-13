<?php
require_once 'includes/helpers.php';
$pageTitle = 'Tarjetas de Presentación';

$pdo = getConnection();

$action = $_GET['action'] ?? 'list';
$id     = (int)($_GET['id'] ?? 0);

// --- GUARDAR (CREAR/EDITAR) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $data = [
        'nombre'          => sanitize($_POST['nombre'] ?? ''),
        'cantidad'        => (int)($_POST['cantidad'] ?? 0),
        'valor_monetario' => (float)($_POST['valor_monetario'] ?? 0),
    ];

    try {
        if (!empty($_POST['id'])) {
            $sql = "UPDATE tarjetas_presentacion
                    SET nombre=:nombre, cantidad=:cantidad, valor_monetario=:valor_monetario
                    WHERE idtarjeta=:id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':nombre' => $data['nombre'],
                ':cantidad' => $data['cantidad'],
                ':valor_monetario' => $data['valor_monetario'],
                ':id' => (int)$_POST['id']
            ]);
            setFlash('success', 'Registro actualizado correctamente.');
        } else {
            $sql = "INSERT INTO tarjetas_presentacion (nombre, cantidad, valor_monetario)
                    VALUES (:nombre, :cantidad, :valor_monetario)";
            $pdo->prepare($sql)->execute($data);
            setFlash('success', 'Registro creado correctamente.');
        }
    } catch (PDOException $e) {
        setFlash('error', 'Error: ' . $e->getMessage());
    }

    redirect('tarjetas_presentacion.php');
}

// --- ELIMINAR ---
if ($action === 'delete' && $id) {
    try {
        $pdo->prepare("DELETE FROM tarjetas_presentacion WHERE idtarjeta=?")->execute([$id]);
        setFlash('success', 'Registro eliminado.');
    } catch (PDOException $e) {
        setFlash('error', 'No se puede eliminar: ' . $e->getMessage());
    }
    redirect('tarjetas_presentacion.php');
}

// --- EDITAR: cargar datos ---
$edit = null;
if ($action === 'edit' && $id) {
    $st = $pdo->prepare("SELECT * FROM tarjetas_presentacion WHERE idtarjeta=?");
    $st->execute([$id]);
    $edit = $st->fetch();
    if (!$edit) {
        setFlash('error', 'Registro no encontrado.');
        redirect('tarjetas_presentacion.php');
    }
}

// --- LISTAR ---
$search  = sanitize($_GET['search'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$perPage = 15;

$where  = $search ? "WHERE nombre LIKE :s" : "";
$params = $search ? [':s' => "%$search%"] : [];

$totalSt = $pdo->prepare("SELECT COUNT(*) FROM tarjetas_presentacion $where");
$totalSt->execute($params);
$total = (int)$totalSt->fetchColumn();

$pag = getPaginationData($total, $page, $perPage);

$stmt = $pdo->prepare("SELECT * FROM tarjetas_presentacion
                       $where
                       ORDER BY idtarjeta DESC
                       LIMIT :limit OFFSET :offset");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':limit', $pag['perPage'], PDO::PARAM_INT);
$stmt->bindValue(':offset', $pag['offset'], PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

include 'includes/header.php';
?>
