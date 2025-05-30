<?php
session_start();
include("connection.php");
include("header.php");


function zi_romana($data) {
    $zile = ['Duminică','Luni','Marți','Miercuri','Joi','Vineri','Sâmbătă'];
    $zi = date('w', strtotime($data));
    return $zile[$zi];
}

// Filtrare
$where = "WHERE 1=1";
if (!empty($_GET['de_la']) && !empty($_GET['pana_la'])) {
    $de_la = mysqli_real_escape_string($con, $_GET['de_la']);
    $pana_la = mysqli_real_escape_string($con, $_GET['pana_la']);
    $where .= " AND p.data BETWEEN '$de_la' AND '$pana_la'";
}
if (!empty($_GET['pacient'])) {
    $pacient = mysqli_real_escape_string($con, $_GET['pacient']);
    $where .= " AND pa.nume LIKE '%$pacient%'";
}
if (!empty($_GET['medic'])) {
    $medic = mysqli_real_escape_string($con, $_GET['medic']);
    $where .= " AND (m.nume LIKE '%$medic%' OR m.prenume LIKE '%$medic%')";
}

// Acțiuni
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adauga'])) {
    $stmt = $con->prepare("INSERT INTO programari (pacient_id, medic_id, data, interval_orar, motiv) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $_POST['pacient_id'], $_POST['medic_id'], $_POST['data'], $_POST['interval_orar'], $_POST['motiv']);
    $stmt->execute();
    header("Location: programari.php");
    exit();
}
if (isset($_GET['confirma'])) {
    $id = (int)$_GET['confirma'];
    mysqli_query($con, "UPDATE programari SET status = 'confirmată' WHERE id = $id");
    header("Location: programari.php");
    exit();
}
if (isset($_GET['anuleaza'])) {
    $id = (int)$_GET['anuleaza'];
    mysqli_query($con, "UPDATE programari SET status = 'anulată' WHERE id = $id");
    header("Location: programari.php");
    exit();
}
if (isset($_GET['sterge'])) {
    $id = (int)$_GET['sterge'];
    mysqli_query($con, "DELETE FROM programari WHERE id = $id");
    header("Location: programari.php");
    exit();
}

// Finalizare programare
if (isset($_GET['finalizeaza'])) {
    $id = (int)$_GET['finalizeaza'];
    mysqli_query($con, "UPDATE programari SET status = 'finalizată' WHERE id = $id");
    header("Location: programari.php");
    exit();
}


// Preluare programări
$programari = mysqli_query($con, "
    SELECT p.*, pa.nume AS pacient_nume, m.nume AS medic_nume, m.prenume AS medic_prenume
    FROM programari p
    JOIN pacienti pa ON p.pacient_id = pa.id
    JOIN medici m ON p.medic_id = m.id
    $where
    ORDER BY p.data, p.interval_orar
");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Programări</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px;
            background: url("imagine.png") no-repeat center center fixed;
            background-size: cover }
        .container { background: #fff; padding: 20px; border-radius: 8px; max-width: 1100px; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background: #eee; }
        .btn { padding: 6px 12px; margin-right: 4px; border-radius: 4px; text-decoration: none; }
        .btn.green { background: #5cb85c; color: white; }
        .btn.red { background: #d9534f; color: white; }
        .btn.gray { background: #999; color: white; }
        .btn.purple { background: #5c6bc0; color: white; }
        form input, form select { padding: 6px; margin: 4px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Programări</h1>

    <!-- Filtru -->
    <form method="GET" style="margin-bottom: 20px;">
        <label>De la:</label>
        <input type="date" name="de_la" value="<?= $_GET['de_la'] ?? '' ?>">
        <label>Până la:</label>
        <input type="date" name="pana_la" value="<?= $_GET['pana_la'] ?? '' ?>">
        <input type="text" name="pacient" placeholder="Pacient" value="<?= $_GET['pacient'] ?? '' ?>">
        <input type="text" name="medic" placeholder="Medic" value="<?= $_GET['medic'] ?? '' ?>">
        <button type="submit" class="btn purple">Caută</button>
        <a href="programari.php" class="btn red">Resetează</a>
    </form>

    <!-- Adăugare programare -->
    <form method="POST">
        <select name="pacient_id" required>
            <option value="">-- Pacient --</option>
            <?php
            $pacienti = mysqli_query($con, "SELECT id, nume FROM pacienti");
            while ($p = mysqli_fetch_assoc($pacienti)) {
                echo "<option value='{$p['id']}'>{$p['nume']}</option>";
            }
            ?>
        </select>
        <select name="medic_id" required>
            <option value="">-- Medic --</option>
            <?php
            $medici = mysqli_query($con, "SELECT id, nume, prenume FROM medici");
            while ($m = mysqli_fetch_assoc($medici)) {
                echo "<option value='{$m['id']}'>{$m['nume']} {$m['prenume']}</option>";
            }
            ?>
        </select>
        <input type="date" name="data" required>
        <select name="interval_orar" required>
            <option value="">-- Interval orar --</option>
            <option>09:00 - 10:00</option>
            <option>10:00 - 11:00</option>
            <option>11:00 - 12:00</option>
            <option>13:00 - 14:00</option>
            <option>14:00 - 15:00</option>
        </select>
        <input type="text" name="motiv" placeholder="Motiv (opțional)">
        <button type="submit" name="adauga" class="btn purple">Adaugă</button>
    </form>

    <!-- Tabel -->
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
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($p = mysqli_fetch_assoc($programari)): ?>
            <tr>
                <td><?= zi_romana($p['data']) ?></td>
                <td><?= date("d.m.Y", strtotime($p['data'])) ?></td>
                <td><?= $p['interval_orar'] ?></td>
                <td><?= $p['pacient_nume'] ?></td>
                <td><?= $p['medic_nume'] . ' ' . $p['medic_prenume'] ?></td>
                <td><?= ucfirst($p['status']) ?></td>
                <td><?= $p['motiv'] ?></td>
                <td>
         <?php if ($p['status'] == 'neconfirmată'): ?>
            <a href="?confirma=<?= $p['id'] ?>" class="btn green">Confirmă</a>
         <?php endif; ?>

          <?php if ($p['status'] != 'anulată' && $p['status'] != 'finalizată'): ?>
            <a href="?anuleaza=<?= $p['id'] ?>" class="btn gray">Anulează</a>
          <?php endif; ?>

          <?php if ($p['status'] == 'confirmată'): ?>
            <a href="?finalizeaza=<?= $p['id'] ?>" class="btn purple">Finalizează</a>
          <?php endif; ?>

            <a href="?sterge=<?= $p['id'] ?>" class="btn red" onclick="return confirm('Ștergi această programare?')">Șterge</a>
</td>

            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
