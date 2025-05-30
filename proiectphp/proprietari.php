<?php
include("connection.php");
include("functions.php");
session_start();

// Adaugă proprietar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adauga'])) {
    $stmt = $con->prepare("INSERT INTO proprietari (nume, prenume, adresa, telefon, email) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $_POST['nume'], $_POST['prenume'], $_POST['adresa'], $_POST['telefon'], $_POST['email']);
    $stmt->execute();
    header("Location: proprietari.php");
    exit();
}

// Șterge proprietar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sterge'])) {
    $id = (int)$_POST['sterge'];
    mysqli_query($con, "DELETE FROM proprietari WHERE id = $id");
    header("Location: proprietari.php");
    exit();
}

// Salvează modificări
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salveaza'])) {
    $stmt = $con->prepare("UPDATE proprietari SET nume=?, prenume=?, adresa=?, telefon=?, email=? WHERE id=?");
    $stmt->bind_param("sssssi", $_POST['nume'], $_POST['prenume'], $_POST['adresa'], $_POST['telefon'], $_POST['email'], $_POST['id']);
    $stmt->execute();
    header("Location: proprietari.php");
    exit();
}

$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : null;

// Preluare filtre
$nume_filter = isset($_GET['nume']) ? trim($_GET['nume']) : '';
$prenume_filter = isset($_GET['prenume']) ? trim($_GET['prenume']) : '';
$animal_filter = isset($_GET['animal']) ? trim($_GET['animal']) : '';

$where = [];
if ($nume_filter !== '') $where[] = "p.nume LIKE '%" . mysqli_real_escape_string($con, $nume_filter) . "%'";
if ($prenume_filter !== '') $where[] = "p.prenume LIKE '%" . mysqli_real_escape_string($con, $prenume_filter) . "%'";
if ($animal_filter !== '') $where[] = "(SELECT COUNT(*) FROM pacienti WHERE pacienti.proprietar_id = p.id AND pacienti.nume LIKE '%" . mysqli_real_escape_string($con, $animal_filter) . "%') > 0";

$filter_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";
$proprietari = mysqli_query($con, "SELECT * FROM proprietari p $filter_sql ORDER BY nume ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Proprietari</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 30px; margin: 0; 
            background: url("imagine.png") no-repeat center center fixed;
            background-size: cover}
        .container { max-width: 1100px; margin: auto; background: white; padding: 20px; border-radius: 8px; margin-top: 20px; }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: left; vertical-align: top; }
        th { background-color: #f0f0f0; }
        .btn { padding: 6px 12px; background-color: #5c6bc0; color: white; border: none; border-radius: 4px; text-decoration: none; cursor: pointer; margin-right: 5px; }
        .btn-danger { background-color: #e53935; }
        form input, textarea { width: 100%; padding: 8px; margin: 5px 0; }
        .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-top: 20px; }
        .filter-form { display: flex; gap: 20px; margin-top: 10px; align-items: flex-end; }
        .filter-form div { flex: 1; }
        .required::after { content: " *"; color: red; }
    </style>
</head>
<body>
<?php include("header.php"); ?>
<div class="container">
    <h1>Lista Proprietari</h1>

    <form method="GET" class="filter-form">
        <div>
            <label>Nume</label>
            <input type="text" name="nume" value="<?= htmlspecialchars($nume_filter) ?>">
        </div>
        <div>
            <label>Prenume</label>
            <input type="text" name="prenume" value="<?= htmlspecialchars($prenume_filter) ?>">
        </div>
        <div>
            <label>Animal</label>
            <input type="text" name="animal" value="<?= htmlspecialchars($animal_filter) ?>">
        </div>
        <div>
            <button class="btn" type="submit">Caută</button>
            <a class="btn btn-danger" href="proprietari.php">Resetează</a>
        </div>
    </form>

    <form method="POST">
        <h3>Adaugă proprietar</h3>
        <div class="form-grid">
            <div>
                <label class="required">Nume</label>
                <input type="text" name="nume" required>
            </div>
            <div>
                <label class="required">Prenume</label>
                <input type="text" name="prenume" required>
            </div>
            <div>
                <label>Adresă</label>
                <textarea name="adresa"></textarea>
            </div>
            <div>
                <label>Telefon</label>
                <input type="text" name="telefon">
            </div>
            <div>
                <label>Email</label>
                <input type="email" name="email">
            </div>
        </div>
        <button class="btn" name="adauga">Adaugă</button>
    </form>

    <table>
        <thead>
        <tr>
            <th>Nume complet</th>
            <th>Adresă</th>
            <th>Telefon</th>
            <th>Email</th>
            <th>Pacienți</th>
            <th>Acțiuni</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($prop = mysqli_fetch_assoc($proprietari)):
            $id = $prop['id'];
            $pacienti = mysqli_query($con, "SELECT nume FROM pacienti WHERE proprietar_id = $id");
            $lista_pacienti = [];
            while ($row = mysqli_fetch_assoc($pacienti)) {
                $lista_pacienti[] = htmlspecialchars($row['nume']);
            }
        ?>
            <tr>
            <?php if ($edit_id == $id): ?>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    <td><input type="text" name="nume" value="<?= htmlspecialchars($prop['nume']) ?>" required>
                        <input type="text" name="prenume" value="<?= htmlspecialchars($prop['prenume']) ?>" required></td>
                    <td><input type="text" name="adresa" value="<?= htmlspecialchars($prop['adresa']) ?>"></td>
                    <td><input type="text" name="telefon" value="<?= htmlspecialchars($prop['telefon']) ?>"></td>
                    <td><input type="email" name="email" value="<?= htmlspecialchars($prop['email']) ?>"></td>
                    <td><?= $lista_pacienti ? implode(", ", $lista_pacienti) : '<em>Niciun pacient</em>' ?></td>
                    <td>
                        <button class="btn" name="salveaza">Salvează</button>
                        <a href="proprietari.php" class="btn btn-danger">Renunță</a>
                    </td>
                </form>
            <?php else: ?>
                <td><?= htmlspecialchars($prop['nume'] . ' ' . $prop['prenume']) ?></td>
                <td><?= htmlspecialchars($prop['adresa']) ?></td>
                <td><?= htmlspecialchars($prop['telefon']) ?></td>
                <td><?= htmlspecialchars($prop['email']) ?></td>
                <td><?= $lista_pacienti ? implode(", ", $lista_pacienti) : '<em>Niciun pacient</em>' ?></td>
                <td>
                    <a class="btn" href="proprietari.php?edit=<?= $id ?>">Editează</a>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="sterge" value="<?= $id ?>">
                        <button class="btn btn-danger" onclick="return confirm('Esti sigur ca vrei sa stergi acest proprietar?');">Sterge</button>
                    </form>
                </td>
            <?php endif; ?>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
