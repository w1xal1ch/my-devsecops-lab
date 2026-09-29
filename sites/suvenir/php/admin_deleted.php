<?php
if (isset($_GET['restore'])) {
    $id = (int)$_GET['restore'];
    $stmt = $mysqli->prepare("UPDATE products SET is_deleted = 0 WHERE ID_Products = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo '<div class="success">♻️ Товар восстановлен!</div>';
}

if (isset($_GET['force_delete'])) {
    $id = (int)$_GET['force_delete'];
    $stmt = $mysqli->prepare("DELETE FROM products WHERE ID_Products = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo '<div class="success">🔥 Товар удалён навсегда!</div>';
}
?>

<h2>📦 Архив удалённых товаров</h2>
<table class="admin-table">
    <thead><tr><th>ID</th><th>Фото</th><th>Название</th><th>Цена</th><th>Действия</th></tr></thead>
    <tbody>
    <?php
    $res = $mysqli->query("SELECT * FROM products WHERE is_deleted = 1 ORDER BY ID_Products DESC");
    while ($row = $res->fetch_assoc()):
        $photoExists = ($row['Photo'] && file_exists($_SERVER['DOCUMENT_ROOT'] . '/img/' . $row['Photo'])) ? $row['Photo'] : 'no-image.jpg';
    ?>
    <tr>
        <td><?=$row['ID_Products']?></td>
        <td><img src="/img/<?=$photoExists?>" width="50" style="border-radius:8px;"></td>
        <td><?=htmlspecialchars($row['Souvenir_Name'])?></td>
        <td><?=$row['Price']?> ₽</td>
        <td>
            <a href="?tab=deleted&restore=<?=$row['ID_Products']?>" class="btn-sm btn-restore">♻️ Восстановить</a>
            <a href="?tab=deleted&force_delete=<?=$row['ID_Products']?>" onclick="return confirm('Удалить навсегда? Это необратимо!')" class="btn-sm btn-force">🔥 Удалить навсегда</a>
        </td>
    </tr>
    <?php endwhile; ?>
    </tbody>
</table>