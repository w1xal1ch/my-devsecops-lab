<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: auth.php');
    exit;
}
require_once __DIR__ . '/../php/config/db_connect.php';

$error = '';
$success = '';

// Добавление или обновление производителя
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $name = trim($_POST['name'] ?? '');

    if ($name === '') {
        $error = 'Введите название производителя';
    } else {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE manufacturer SET name = ? WHERE manufacturer_id = ?");
            $stmt->execute([$name, $id]);
            $success = 'Производитель обновлён';
        } else {
            $stmt = $pdo->prepare("INSERT INTO manufacturer (name) VALUES (?)");
            $stmt->execute([$name]);
            $success = 'Производитель добавлен';
        }
    }
}

// Удаление производителя
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM manufacturer WHERE manufacturer_id = ?");
    $stmt->execute([$id]);
    header('Location: manufacturers.php');
    exit;
}

// Получаем производителей
$manufacturers = $pdo->query("SELECT * FROM manufacturer ORDER BY name")->fetchAll();

// Для редактирования
$editmanufacturer = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM manufacturer WHERE manufacturer_id = ?");
    $stmt->execute([$editId]);
    $editmanufacturer = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Производители</title>
    <link rel="stylesheet" href="/acs/css/main.css" />
</head>
<body>
<div class="container" style="margin-top:1rem;">
    <h1>Производители</h1>

    <?php if ($error): ?>
        <div style="color:#e74c3c; margin-bottom:1rem;"><?=htmlspecialchars($error)?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div style="color:#2ecc71; margin-bottom:1rem;"><?=htmlspecialchars($success)?></div>
    <?php endif; ?>

    <form method="post" style="max-width:400px; margin-bottom:2rem;">
        <input type="hidden" name="id" value="<?= htmlspecialchars($editmanufacturer['manufacturer_id'] ?? '') ?>" />
        <input type="text" name="name" placeholder="Название производителя" required value="<?= htmlspecialchars($editmanufacturer['name'] ?? '') ?>" style="width:100%; padding:8px; margin-bottom:10px;" />
        <button type="submit" style="background:#e74c3c; color:#fff; padding:10px 20px; border:none; border-radius:6px; cursor:pointer;">
            <?= $editmanufacturer ? 'Обновить' : 'Добавить' ?>
        </button>
        <?php if ($editmanufacturer): ?>
            <a href="manufacturers.php" style="margin-left:15px; color:#999;">Отмена</a>
        <?php endif; ?>
    </form>

    <table style="width:100%; border-collapse: collapse; background:#1f1f1f; color:#eee;">
        <thead>
            <tr>
                <th style="padding:10px; border-bottom:1px solid #333; width:10%;">ID</th>
                <th style="padding:10px; border-bottom:1px solid #333;">Название</th>
                <th style="padding:10px; border-bottom:1px solid #333; width:20%;">Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($manufacturers as $manuf): ?>
                <tr>
                    <td style="padding:10px; border-bottom:1px solid #333;"><?= $manuf['manufacturer_id'] ?></td>
                    <td style="padding:10px; border-bottom:1px solid #333;"><?= htmlspecialchars($manuf['name']) ?></td>
                    <td style="padding:10px; border-bottom:1px solid #333;">
                        <a href="manufacturers.php?edit=<?= $manuf['manufacturer_id'] ?>" style="color:#2ecc71; margin-right:10px;">Редактировать</a>
                        <a href="manufacturers.php?delete=<?= $manuf['manufacturer_id'] ?>" onclick="return confirm('Удалить производителя?');" style="color:#e74c3c;">Удалить</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p><a href="index.php" style="display:inline-block; margin-top:20px;">← Вернуться в панель администратора</a></p>
</div>
</body>
</html>
