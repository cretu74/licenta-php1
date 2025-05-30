<?php
session_start();
include("connection.php");
include("header.php");
$where = "WHERE 1=1";

if (!empty($_GET['serviciu'])) {
    $serviciu = mysqli_real_escape_string($con, $_GET['serviciu']);
    $where .= " AND s.denumire LIKE '%$serviciu%'";
}

if (!empty($_GET['pacient'])) {
    $pacient = mysqli_real_escape_string($con, $_GET['pacient']);
    $where .= " AND pa.nume LIKE '%$pacient%'";
}

if (!empty($_GET['medic'])) {
    $medic = mysqli_real_escape_string($con, $_GET['medic']);
    $where .= " AND (m.nume LIKE '%$medic%' OR m.prenume LIKE '%$medic%')";
}

if (!empty($_GET['suma'])) {
    $suma = (float)$_GET['suma'];
    $where .= " AND p.suma = $suma";
}

if (!empty($_GET['de_la']) && !empty($_GET['pana_la'])) {
  $de_la = mysqli_real_escape_string($con, $_GET['de_la']);
  $pana_la = mysqli_real_escape_string($con, $_GET['pana_la']);
  $where .= " AND p.data_plata BETWEEN '$de_la' AND '$pana_la'";
}

$query = "
    SELECT p.*, s.denumire AS serviciu, pa.nume AS pacient, m.nume AS medic_nume, m.prenume AS medic_prenume
    FROM plati p
    JOIN servicii s ON p.serviciu_id = s.id
    JOIN pacienti pa ON s.pacient_id = pa.id
    JOIN medici m ON s.medic_id = m.id
    $where
    ORDER BY p.data_plata DESC
";

$rezultat = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Raport încasări</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px;background: url("imagine.png") no-repeat center center fixed;
            background-size: cover }
        .container { max-width: 1100px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; }
        th { background: #eee; }
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            background-color: #5c6bc0;
            color: white;
        }
        .btn.red {
            background-color: #e53935;
            color: white;
        }
        form input {
            padding: 6px;
            margin: 4px;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Raport încasări</h2>

    <form method="GET" style="margin-bottom: 20px;">
        <input type="text" name="serviciu" placeholder="Serviciu" value="<?= $_GET['serviciu'] ?? '' ?>">
        <input type="text" name="pacient" placeholder="Pacient" value="<?= $_GET['pacient'] ?? '' ?>">
        <input type="text" name="medic" placeholder="Medic" value="<?= $_GET['medic'] ?? '' ?>">
        <input type="number" name="suma" step="0.01" placeholder="Sumă (exact)" value="<?= $_GET['suma'] ?? '' ?>">
        <label>De la:</label>
        <input type="date" name="de_la" value="<?= $_GET['de_la'] ?? '' ?>">
        <label>Până la:</label>
        <input type="date" name="pana_la" value="<?= $_GET['pana_la'] ?? '' ?>">
        <button type="submit" class="btn">Filtrează</button>
        <a href="raport_incasari.php" class="btn red" style="margin-left: 8px;">Resetează</a>
    </form>

    <table>
        <thead>
            <tr>
                <th>Denumire serviciu</th>
                <th>Pacient</th>
                <th>Medic</th>
                <th>Suma</th>
                <th>Data plății</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total = 0;
            while ($r = mysqli_fetch_assoc($rezultat)):
                $total += (float)$r['suma'];
            ?>
                <tr>
                    <td><?= htmlspecialchars($r['serviciu']) ?></td>
                    <td><?= htmlspecialchars($r['pacient']) ?></td>
                    <td><?= htmlspecialchars($r['medic_nume'] . ' ' . $r['medic_prenume']) ?></td>
                    <td><?= number_format($r['suma'], 2) ?> lei</td>
                    <td><?= date("d.m.Y", strtotime($r['data_plata'])) ?></td>
                </tr>
            <?php endwhile; ?>
            <tr>
                <td colspan="3" style="text-align:right;"><strong>Total:</strong></td>
                <td><strong><?= number_format($total, 2) ?> lei</strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>
</div>
</body>
</html>
