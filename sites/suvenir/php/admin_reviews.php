S<?php
if (isset($_GET['del'])) {
    $id = (int)$_GET['del'];
    $mysqli->query("DELETE FROM customer_reviews WHERE Code=$id");
    echo '<div class="success">🗑️ Отзыв удалён!</div>';
}
?>

<h2>⭐ Отзывы</h2>
<table class="admin-table">
    <thead><tr><th>Товар</th><th>Клиент</th><th>Оценка</th><th>Текст</th><th>Дата</th><th></th></tr></thead>
    <tbody>
    <?php
    $res = $mysqli->query("SELECT customer_reviews.*, client.Full_Name, products.Souvenir_Name FROM customer_reviews
        LEFT JOIN client ON customer_reviews.ID_Client=client.ID_Client
        LEFT JOIN products ON customer_reviews.ID_Product=products.ID_Products
        ORDER BY Date DESC");
    while ($row = $res->fetch_assoc()) {
        $stars = str_repeat('⭐', $row['Rating_1_to_10'] / 2);
        echo "<tr>
            <td>{$row['Souvenir_Name']}</td>
            <td>{$row['Full_Name']}</td>
            <td>$stars ({$row['Rating_1_to_10']}/10)</td>
            <td>{$row['Text']}</td>
            <td>{$row['Date']}</td>
            <td><a href='?tab=reviews&del={$row['Code']}' onclick='return confirm(\"Удалить отзыв?\")' class='btn-sm btn-delete'>🗑️</a></td>
        </tr>";
    }
    ?>
    </tbody>
</table>