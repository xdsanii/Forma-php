<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "user_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Savienojums neizdevās: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($username) || empty($email) || empty($password)) {
        echo "<div class='error'>Lūdzu, aizpildiet visus laukus.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<div class='error'>Nepareizs e-pasta formāts.</div>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        if ($stmt->execute()) {
            echo "<div class='success'>Lietotājs veiksmīgi pievienots.</div>";
        } else {
            echo "<div class='error'>Kļūda: " . $stmt->error . "</div>";
        }

        $stmt->close();
    }
}

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $sql = "DELETE FROM users WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        echo "<div class='success'>Lietotājs veiksmīgi izdzēsts.</div>";
    } else {
        echo "<div class='error'>Kļūda: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

$sql = "SELECT id, username, email FROM users";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reģistrācijas forma</title>
    <style>
        body { font-family: 'Arial', sans-serif; background-color: #f4f4f4; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 400px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h1 { text-align: center; color: #007bff; }
        label { margin-top: 10px; font-weight: bold; }
        input { padding: 10px; width: calc(100% - 22px); border: 1px solid #ddd; border-radius: 5px; margin-bottom: 15px; }
        button { width: 100%; padding: 10px; background-color: #007bff; border: none; color: white; font-size: 16px; border-radius: 5px; cursor: pointer; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        table, th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #007bff; color: white; }
        a { text-decoration: none; color: red; }
        .success { color: #5cb85c; margin: 10px 0; text-align: center; }
        .error { color: #d9534f; margin: 10px 0; text-align: center; }
    </style>
</head>
<body>

<div class="container">
    <h1>Reģistrācijas forma</h1>
    <form method="POST" action="">
        <label for="username">Lietotājvārds:</label>
        <input type="text" name="username" id="username" required>
        <label for="email">Epasts:</label>
        <input type="email" name="email" id="email" required>
        <label for="password">Parole:</label>
        <input type="password" name="password" id="password" required>
        <button type="submit">Pievienot</button>
    </form>
</div>

<div class="container">
    <h2>Esošie lietotāji</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Lietotājvārds</th>
                <th>E-pasts</th>
                <th>Darbības</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id']; ?></td>
                        <td><?= htmlspecialchars($row['username']); ?></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td>
                            <a href="?delete=<?= $row['id']; ?>" onclick="return confirm('Vai tiešām vēlaties dzēst šo lietotāju?')">Dzēst</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Nav pieejami lietotāji.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
