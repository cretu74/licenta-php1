<?php
session_start();
include("connection.php");
include("header.php");


// Adăugare medic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adauga'])) {
    $stmt = $con->prepare("INSERT INTO medici (nume, prenume, telefon, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $_POST['nume'], $_POST['prenume'], $_POST['telefon'], $_POST['email']);
    $stmt->execute();
    header("Location: medici.php");
    exit();
}

// Editare medic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['salveaza'])) {
    $stmt = $con->prepare("UPDATE medici SET nume=?, prenume=?, telefon=?, email=? WHERE id=?");
    $stmt->bind_param("ssssi", $_POST['nume'], $_POST['prenume'], $_POST['telefon'], $_POST['email'], $_POST['id']);
    $stmt->execute();
    header("Location: medici.php");
    exit();
}

// Ștergere medic
if (isset($_GET['sterge'])) {
    $id = (int)$_GET['sterge'];
    mysqli_query($con, "DELETE FROM medici WHERE id = $id");
    header("Location: medici.php");
    exit();
}

// Preluare medici
$edit_id = isset($_GET['edit']) ? (int)$_GET['edit'] : null;
$rezultat = mysqli_query($con, "SELECT * FROM medici ORDER BY nume, prenume");
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Medici</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; margin: 0; padding: 20px;
            background: url("imagine.png") no-repeat center center fixed;
            background-size: cover; }
        .container { max-width: 900px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background: #f0f0f0; }
        .btn { padding: 6px 12px; background: #5c6bc0; color: white; text-decoration: none; border-radius: 4px; }
        .btn.red { background: #d9534f; }
        .btn.grey { background: #777; }
        form input { width: 100%; padding: 8px; margin: 5px 0; }
        label::after {
            content: "*";
            color: red;
            margin-left: 4px;
        }
        label.optional::after {
            content: "";
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Medici</h1>

    <!-- Formular adăugare -->
    <form method="POST">
        <label>Nume</label>
        <input type="text" name="nume" required>

        <label>Prenume</label>
        <input type="text" name="prenume" required>

        <label class="optional">Telefon</label>
        <input type="text" name="telefon">

        <label class="optional">Email</label>
        <input type="email" name="email">

        <button type="submit" name="adauga" class="btn">Adaugă medic</button>
    </form>

    <!-- Tabel medici -->
    <table>
        <thead>
        <tr>
            <th>Nume</th>
            <th>Prenume</th>
            <th>Telefon</th>
            <th>Email</th>
            <th>Acțiuni</th>
        </tr>
        </thead>
        <tbody>
        <?php while ($medic = mysqli_fetch_assoc($rezultat)): ?>
            <?php if ($edit_id === (int)$medic['id']): ?>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $medic['id'] ?>">
                    <tr>
                        <td><input type="text" name="nume" value="<?= htmlspecialchars($medic['nume']) ?>" required></td>
                        <td><input type="text" name="prenume" value="<?= htmlspecialchars($medic['prenume']) ?>" required></td>
                        <td><input type="text" name="telefon" value="<?= htmlspecialchars($medic['telefon']) ?>"></td>
                        <td><input type="email" name="email" value="<?= htmlspecialchars($medic['email']) ?>"></td>
                        <td>
                            <button type="submit" name="salveaza" class="btn">Salvează</button>
                            <a href="medici.php" class="btn grey">Renunță</a>
                        </td>
                    </tr>
                </form>
            <?php else: ?>
                <tr>
                    <td><?= htmlspecialchars($medic['nume']) ?></td>
                    <td><?= htmlspecialchars($medic['prenume']) ?></td>
                    <td><?= htmlspecialchars($medic['telefon']) ?></td>
                    <td><?= htmlspecialchars($medic['email']) ?></td>
                    <td>
                        <a href="medici.php?edit=<?= $medic['id'] ?>" class="btn">Editare</a>
                        <a href="medici.php?sterge=<?= $medic['id'] ?>" class="btn red" onclick="return confirm('Sigur vrei să ștergi acest medic?')">Șterge</a>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
