<?php
session_start();
if (!isset($_SESSION['usuario_id'])) { exit; }
include 'config.php';

if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conexion, $_GET['id']);
    $res = mysqli_query($conexion, "SELECT creditos_idcreditos, monto FROM cobranzas WHERE idcobranzas = '$id'");
    if ($c = mysqli_fetch_assoc($res)) {
        mysqli_query($conexion, "UPDATE creditos SET saldo_inicial = saldo_inicial + {$c['monto']}, estado = 1 WHERE idcreditos = '{$c['creditos_idcreditos']}'");
        mysqli_query($conexion, "DELETE FROM cobranzas WHERE idcobranzas = '$id'");
    }
}
header("Location: historial_cobros.php");
