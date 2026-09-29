<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: auth.php');
    exit;
}
require_once __DIR__ . '/../php/config/db_connect.php';

// Получаем клиентов
$clients = $pdo->query("SELECT * FROM client ORDER BY client_id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Клиенты</title>
    <link rel="stylesheet" href="/acs/css/main.css" />
</head>
<body>
<div class="container" style="margin-top: 1rem;">
    <h1>Клиенты</h1>

    <table style="width:100%; border-collapse: collapse; background:#1f1f1f; color:#eee;">
        <thead>
            <tr>
                <th style="padding:10px; border-bottom:1px solid #333;">ID</th>
                <th style="padding:10px; border-bottom:1px solid #333;">ФИО</th>
                <th style="padding:10px; border-bottom:1px solid #333;">Телефон</th>
                <th style="padding:10px; border-bottom:1px solid #333;">Email</th>
                <th style="padding:10px; border-bottom:1px solid #333;">Адрес</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($clients as $client): ?>
                <tr>
                    <td style="padding:10px; border-bottom:1px solid #333;"><?= $client['client_id'] ?></td>
                    <td style="padding:10px; border-bottom:1px solid #333;"><?= htmlspecialchars($client['full_name']) ?></td>
                    <td style="padding:10px; border-bottom:1px solid #333;"><?= htmlspecialchars($client['phone']) ?></td>
                    <td style="padding:10px; border-bottom:1px solid #333;"><?= htmlspecialchars($client['email']) ?></td>
                    <td style="padding:10px; border-bottom:1px solid #333;"><?= htmlspecialchars($client['address']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="index.php" style="display:inline-block; margin-top:20px;">← Вернуться в панель администратора</a></p>
</div>
</body>
</html>
