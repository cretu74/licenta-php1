<?php
session_start();
include("connection.php");
include("header.php");
$where = "WHERE 1=1";

// Filtre
if (!empty($_GET['nume'])) {
    $nume = mysqli_real_escape_string($con, $_GET['nume']);
    $where .= " AND p.nume LIKE '%$nume%'";
}
if (!empty($_GET['specie'])) {
    $specie = mysqli_real_escape_string($con, $_GET['specie']);
    $where .= " AND p.specie LIKE '%$specie%'";
}
if (!empty($_GET['proprietar'])) {
    $proprietar = mysqli_real_escape_string($con, $_GET['proprietar']);
    $where .= " AND (pr.nume LIKE '%$proprietar%' OR pr.prenume LIKE '%$proprietar%')";
}
if (!empty($_GET['de_la']) && !empty($_GET['pana_la'])) {
    $de_la = mysqli_real_escape_string($con, $_GET['de_la']);
    $pana_la = mysqli_real_escape_string($con, $_GET['pana_la']);
    $where .= " AND p.data_inregistrare BETWEEN '$de_la' AND '$pana_la'";
}

// Interogare
$query = "
    SELECT p.nume AS pacient_nume, p.specie, p.rasa, p.data_inregistrare, pr.nume AS prop_nume, pr.prenume AS prop_prenume
    FROM pacienti p
    JOIN proprietari pr ON p.proprietar_id = pr.id
    $where
    ORDER BY p.data_inregistrare DESC
";
$rezultat = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Raport pacienți</title>
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
    <h2>Raport pacienți</h2>

    <!-- Form filtre -->
    <form method="GET" style="margin-bottom: 20px;">
        <input type="text" name="nume" placeholder="Nume pacient" value="<?= $_GET['nume'] ?? '' ?>">
        <input type="text" name="specie" placeholder="Specie" value="<?= $_GET['specie'] ?? '' ?>">
        <input type="text" name="proprietar" placeholder="Proprietar" value="<?= $_GET['proprietar'] ?? '' ?>">
        <label>De la:</label>
        <input type="date" name="de_la" value="<?= $_GET['de_la'] ?? '' ?>">
        <label>Până la:</label>
        <input type="date" name="pana_la" value="<?= $_GET['pana_la'] ?? '' ?>">
        <button type="submit" class="btn">Filtrează</button>
        <a href="raport_pacienti.php" class="btn red">Resetează</a>
    </form>

    <!-- Tabel rezultate -->
    <table>
        <thead>
            <tr>
                <th>Nume pacient</th>
                <th>Specie</th>
                <th>Rasă</th>
                <th>Proprietar</th>
                <th>Data înregistrării</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = mysqli_fetch_assoc($rezultat)): ?>
            <tr>
                <td><?= htmlspecialchars($row['pacient_nume']) ?></td>
                <td><?= htmlspecialchars($row['specie']) ?></td>
                <td><?= htmlspecialchars($row['rasa']) ?></td>
                <td><?= $row['prop_nume'] . ' ' . $row['prop_prenume'] ?></td>
                <td><?= date("d.m.Y", strtotime($row['data_inregistrare'])) ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
