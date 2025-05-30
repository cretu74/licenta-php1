<?php
session_start();
include("connection.php");
include("header.php");

function zi_romana($data) {
  $zile = ['Duminică','Luni','Marți','Miercuri','Joi','Vineri','Sâmbătă'];
  return $zile[date('w', strtotime($data))];
}

// Filtrare
$where = "WHERE 1=1";
if (!empty($_GET['status'])) {
  $status = mysqli_real_escape_string($con, $_GET['status']);
  $where .= " AND p.status = '$status'";
}
if (!empty($_GET['pacient'])) {
  $pacient = mysqli_real_escape_string($con, $_GET['pacient']);
  $where .= " AND pa.nume LIKE '%$pacient%'";
}
if (!empty($_GET['medic'])) {
  $medic = mysqli_real_escape_string($con, $_GET['medic']);
  $where .= " AND (m.nume LIKE '%$medic%' OR m.prenume LIKE '%$medic%')";
}
if (!empty($_GET['de_la']) && !empty($_GET['pana_la'])) {
  $de_la = mysqli_real_escape_string($con, $_GET['de_la']);
  $pana_la = mysqli_real_escape_string($con, $_GET['pana_la']);
  $where .= " AND p.data BETWEEN '$de_la' AND '$pana_la'";
}

$query = "
  SELECT p.*, pa.nume AS pacient_nume, m.nume AS medic_nume, m.prenume AS medic_prenume
  FROM programari p
  JOIN pacienti pa ON p.pacient_id = pa.id
  JOIN medici m ON p.medic_id = m.id
  $where
  ORDER BY p.data DESC
";
$rezultat = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Raport programări</title>
  <style>
    body { font-family: Arial; background: #f4f4f4; padding: 20px;background: url("imagine.png") no-repeat center center fixed;
            background-size: cover }
    .container { max-width: 1100px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { padding: 10px; border: 1px solid #ccc; }
    th { background: #eee; }
    .btn { padding: 6px 12px; border-radius: 4px; text-decoration: none; background-color: #5c6bc0; color: white; }
    form input, form select { padding: 6px; margin: 4px; }
    .btn.red {background-color: #e53935; color: white;}
  </style>
</head>
<body>
<div class="container">
  <h2>Raport programări</h2>

  <form method="GET">
    <select name="status">
      <option value="">-- Toate statusurile --</option>
      <option value="neconfirmată" <?= ($_GET['status'] ?? '') == 'neconfirmată' ? 'selected' : '' ?>>Neconfirmată</option>
      <option value="confirmată" <?= ($_GET['status'] ?? '') == 'confirmată' ? 'selected' : '' ?>>Confirmată</option>
      <option value="finalizată" <?= ($_GET['status'] ?? '') == 'finalizată' ? 'selected' : '' ?>>Finalizată</option>
      <option value="anulată" <?= ($_GET['status'] ?? '') == 'anulată' ? 'selected' : '' ?>>Anulată</option>
    </select>
    <input type="text" name="pacient" placeholder="Pacient" value="<?= $_GET['pacient'] ?? '' ?>">
    <input type="text" name="medic" placeholder="Medic" value="<?= $_GET['medic'] ?? '' ?>">
    <label>De la:</label>
    <input type="date" name="de_la" value="<?= $_GET['de_la'] ?? '' ?>">
    <label>Până la:</label>
    <input type="date" name="pana_la" value="<?= $_GET['pana_la'] ?? '' ?>">
    <button type="submit" class="btn">Filtrează</button>
     <a href="raport_programari.php" class="btn red" style="margin-left: 8px;">Resetează</a>

  </form>

  <table>
    <thead>
      <tr>
        <th>Zi</th>
        <th>Data</th>
        <th>Interval</th>
        <th>Pacient</th>
        <th>Medic</th>
        <th>Status</th>
        <th>Motiv</th>
      </tr>
    </thead>
    <tbody>
    <?php while ($p = mysqli_fetch_assoc($rezultat)): ?>
      <tr>
        <td><?= zi_romana($p['data']) ?></td>
        <td><?= date("d.m.Y", strtotime($p['data'])) ?></td>
        <td><?= htmlspecialchars($p['interval_orar']) ?></td>
        <td><?= htmlspecialchars($p['pacient_nume']) ?></td>
        <td><?= htmlspecialchars($p['medic_nume'] . ' ' . $p['medic_prenume']) ?></td>
        <td><?= ucfirst($p['status']) ?></td>
        <td><?= htmlspecialchars($p['motiv']) ?></td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>
</body>
</html>
