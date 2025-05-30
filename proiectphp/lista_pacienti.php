<?php
include("connection.php");
include("functions.php");
session_start();

// Adăugare pacient nou
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adauga'])) {
    $stmt = $con->prepare("INSERT INTO pacienti (numar_fisa, data_inregistrare, proprietar_id, nume, specie, rasa, sex, greutate, culoare, varsta, microcip, boli_cronice) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisssssssss",
        $_POST['numar_fisa'], $_POST['data_inregistrare'], $_POST['proprietar_id'], $_POST['nume'],
        $_POST['specie'], $_POST['rasa'], $_POST['sex'], $_POST['greutate'],
        $_POST['culoare'], $_POST['varsta'], $_POST['microcip'], $_POST['boli_cronice']
    );
    $stmt->execute();
    header("Location: lista_pacienti.php");
    exit();
}

// Ștergere pacient
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sterge'])) {
    $id = (int)$_POST['sterge'];
    mysqli_query($con, "DELETE FROM pacienti WHERE id = $id");
    header("Location: lista_pacienti.php");
    exit();
}

// Preluare filtre
$nume = isset($_GET['nume']) ? trim($_GET['nume']) : '';
$proprietar = isset($_GET['proprietar']) ? trim($_GET['proprietar']) : '';
$nr_fisa = isset($_GET['numar_fisa']) ? trim($_GET['numar_fisa']) : '';

$where = [];
if ($nume !== '') $where[] = "p.nume LIKE '%" . mysqli_real_escape_string($con, $nume) . "%'";
if ($proprietar !== '') $where[] = "CONCAT(pr.nume, ' ', pr.prenume) LIKE '%" . mysqli_real_escape_string($con, $proprietar) . "%'";
if ($nr_fisa !== '') $where[] = "p.numar_fisa LIKE '%" . mysqli_real_escape_string($con, $nr_fisa) . "%'";

$filter_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";
$result = mysqli_query($con, "SELECT p.*, pr.nume AS prop_nume, pr.prenume AS prop_prenume FROM pacienti p LEFT JOIN proprietari pr ON p.proprietar_id = pr.id $filter_sql ORDER BY p.nume ASC");
$pacienti = mysqli_fetch_all($result, MYSQLI_ASSOC);

$proprietari = mysqli_query($con, "SELECT id, nume, prenume FROM proprietari ORDER BY nume ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Lista Pacienți</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #f4f4f4; margin: 0;
            background: url("imagine.png") no-repeat center center fixed;
            background-size: cover; }
        .container { max-width: 1100px; margin: auto; background: white; padding: 20px; border-radius: 8px; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: left; }
        th { background-color: #f0f0f0; }
        .btn { padding: 6px 12px; background-color: #5c6bc0; color: white; border: none; border-radius: 4px; text-decoration: none; margin-right: 5px; cursor: pointer; }
        .btn-danger { background-color: #e53935; }
        form input, form select, form label { width: 100%; }
        .form-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-top: 10px; }
        .filter-group { margin-bottom: 10px; }
        .filter-label { font-weight: bold; display: block; margin-bottom: 5px; }
        .required::after { content: " *"; color: red; }
    </style>
</head>
<body>
<?php include("header.php"); ?>
<div class="container">
    <h1>Lista Pacienți</h1>

    <h3>Filtrează după:</h3>
    <form method="GET">
        <div class="form-grid">
            <div class="filter-group">
                <label class="filter-label">Nr. fișă</label>
                <input type="text" name="numar_fisa" value="<?= htmlspecialchars($nr_fisa) ?>">
            </div>
            <div class="filter-group">
                <label class="filter-label">Proprietar</label>
                <input type="text" name="proprietar" value="<?= htmlspecialchars($proprietar) ?>">
            </div>
            <div class="filter-group">
                <label class="filter-label">Nume pacient</label>
                <input type="text" name="nume" value="<?= htmlspecialchars($nume) ?>">
            </div>
            <div class="filter-group">
                <label class="filter-label">&nbsp;</label>
                <button type="submit" class="btn">Caută</button>
                <a href="lista_pacienti.php" class="btn btn-danger">Resetează</a>
            </div>
        </div>
    </form>

    <form method="POST">
        <h3>Adaugă pacient nou</h3>
        <div class="form-grid" style="grid-template-columns: repeat(2, 1fr);">
            <div>
                <label class="required">Nr. fișă</label>
                <input type="text" name="numar_fisa" required>
            </div>
            <div>
                <label class="required">Data înregistrare</label>
                <input type="date" name="data_inregistrare" required>
            </div>
            <div>
                <label class="required">Proprietar</label>
                <select name="proprietar_id" required>
                    <option value="">-- Selectează proprietar --</option>
                    <?php while ($row = mysqli_fetch_assoc($proprietari)): ?>
                        <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['nume'] . ' ' . $row['prenume']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="required">Nume animal</label>
                <input type="text" name="nume" required>
            </div>
            <div>
                <label>Specie</label>
                <input type="text" name="specie">
            </div>
            <div>
                <label>Rasă</label>
                <input type="text" name="rasa">
            </div>
            <div>
                <label>Sex</label>
                <select name="sex">
                    <option value="M">M</option>
                    <option value="F">F</option>
                </select>
            </div>
            <div>
                <label>Greutate (kg)</label>
                <input type="text" name="greutate">
            </div>
            <div>
                <label>Culoare</label>
                <input type="text" name="culoare">
            </div>
            <div>
                <label>Vârstă</label>
                <input type="number" name="varsta">
            </div>
            <div>
                <label>Microcip</label>
                <input type="text" name="microcip">
            </div>
            <div>
                <label>Boli cronice</label>
                <input type="text" name="boli_cronice">
            </div>
        </div>
        <button type="submit" name="adauga" class="btn" style="margin-top: 10px;">Adaugă</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Nr. fișă</th>
                <th>Nume</th>
                <th>Specie</th>
                <th>Rasa</th>
                <th>Proprietar</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($pacienti as $p): ?>
            <tr>
                <td><?= htmlspecialchars($p['numar_fisa']) ?></td>
                <td><?= htmlspecialchars($p['nume']) ?></td>
                <td><?= htmlspecialchars($p['specie']) ?></td>
                <td><?= htmlspecialchars($p['rasa']) ?></td>
                <td><?= htmlspecialchars($p['prop_nume'] . ' ' . $p['prop_prenume']) ?></td>
                <td>
                    <a class="btn" href="pacienti.php?id=<?= $p['id'] ?>">Detalii</a>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="sterge" value="<?= $p['id'] ?>">
                        <button class="btn btn-danger" onclick="return confirm('Esti sigur că vrei să stergi acest pacient?');">Șterge</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>