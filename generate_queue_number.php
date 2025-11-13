<?php
// generate_queue_number.php
// Require koneksi.php before calling this file in other scripts
$mysqli = require 'koneksi.php';

function make_service_prefix($name) {
    $name = preg_replace('/[^A-Za-z0-9 ]/', '', $name);
    $parts = array_filter(explode(' ', strtoupper($name)));
    $prefix = '';
    foreach ($parts as $p) {
        $prefix .= substr($p, 0, 1);
        if (strlen($prefix) >= 2) break;
    }
    if (strlen($prefix) < 2) {
        $prefix = strtoupper(substr($name,0,2));
    }
    return $prefix;
}

function generate_queue_number($mysqli, $id_menu) {
    // ambil nama service
    $sql = "SELECT name FROM menu WHERE id_menu = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id_menu);
    $stmt->execute();
    $stmt->bind_result($menu_name);
    $stmt->fetch();
    $stmt->close();

    if (!$menu_name) $menu_name = 'SV';

    $prefix = make_service_prefix($menu_name); // e.g. BP

    // ambil max nomor hari ini untuk service tersebut
    $sql2 = "SELECT MAX(CAST(SUBSTR(queue_number, INSTR(queue_number,'-')+1) AS UNSIGNED)) AS max_no
             FROM queue
             WHERE id_menu = ? AND DATE(created_at) = CURDATE()";
    $stmt2 = $mysqli->prepare($sql2);
    $stmt2->bind_param("i", $id_menu);
    $stmt2->execute();
    $stmt2->bind_result($max_no);
    $stmt2->fetch();
    $stmt2->close();

    $next = ($max_no !== null) ? intval($max_no) + 1 : 1;
    $num = str_pad($next, 3, "0", STR_PAD_LEFT);
    return $prefix . '-' . $num; // e.g. BP-001
}
