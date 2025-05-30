<?php
session_start();
include("connection.php");
include("header.php");

$user_data = check_login($con);

// Adăugare utilizator
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $stmt = $con->prepare("INSERT INTO users (user_id, user_name, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $_POST['user_id'], $_POST['user_name'], $_POST['password']);
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

// Editare utilizator
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    $stmt = $con->prepare("UPDATE users SET user_id = ?, user_name = ?, password = ? WHERE id = ?");
    $stmt->bind_param("sssi", $_POST['user_id'], $_POST['user_name'], $_POST['password'], $_POST['id']);
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

// Ștergere utilizator
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    mysqli_query($con, "DELETE FROM users WHERE id = $id");
    header("Location: admin.php");
    exit();
}

// Selectare utilizatori
$rezultat = mysqli_query($con, "SELECT * FROM users");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Administrare utilizatori</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px;
               background: url("imagine.png") no-repeat center center fixed;
               background-size: cover; }
        .container { max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background: #eee; }
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            background-color: #5c6bc0;
            color: white;
            border: none;
            cursor: pointer;
        }
        .btn.cancel {
            background-color: #999;
        }
        .btn.red {
            background-color: #e53935;
        }
        .form-inline input {
            width: 100%;
            padding: 6px;
        }
        .form-row { display: flex; gap: 10px; margin-bottom: 10px; }
        .form-row input { flex: 1; }
    </style>
</head>
<body>
<div class="container">
    <h2>Utilizatori</h2>

    <!-- Formular adăugare -->
    <form method="POST" style="margin-bottom: 20px;">
        <div class="form-row">
            <input type="text" name="user_id" placeholder="User ID" required>
            <input type="text" name="user_name" placeholder="Username" required>
            <input type="password" name="password" placeholder="Parolă" required>
        </div>
        <button type="submit" name="add_user" class="btn">Adaugă utilizator</button>
    </form>

    <!-- Tabel utilizatori -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>User ID</th>
                <th>Username</th>
                <th>Parolă</th>
                <th>Acțiuni</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($r = mysqli_fetch_assoc($rezultat)): ?>
            <?php if (isset($_GET['edit']) && $_GET['edit'] == $r['id']): ?>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $r['id'] ?>">
                    <tr>
                        <td><?= $r['id'] ?></td>
                        <td><input name="user_id" value="<?= htmlspecialchars($r['user_id']) ?>"></td>
                        <td><input name="user_name" value="<?= htmlspecialchars($r['user_name']) ?>"></td>
                        <td><input name="password" value="<?= htmlspecialchars($r['password']) ?>"></td>
                        <td>
                            <button type="submit" name="save_user" class="btn">Salvează</button>
                            <a href="admin.php" class="btn cancel">Renunță</a>
                        </td>
                    </tr>
                </form>
            <?php else: ?>
                <tr>
                    <td><?= $r['id'] ?></td>
                    <td><?= htmlspecialchars($r['user_id']) ?></td>
                    <td><?= htmlspecialchars($r['user_name']) ?></td>
                    <td><?= htmlspecialchars($r['password']) ?></td>
                    <td>
                        <a href="admin.php?edit=<?= $r['id'] ?>" class="btn">Editare</a>
                        <a href="admin.php?delete_id=<?= $r['id'] ?>" class="btn red" onclick="return confirm('Ștergi utilizatorul?')">Șterge</a>
                    </td>
                </tr>
            <?php endif; ?>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
