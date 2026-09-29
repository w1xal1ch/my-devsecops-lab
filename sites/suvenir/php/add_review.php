<?php
require_once 'db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['Role'] != 4) exit('Ошибка авторизации');
$user = $_SESSION['user'];
$res = $mysqli->query("SELECT ID_Client FROM client WHERE ID_User = {$user['ID_User']}");
$client = $res->fetch_assoc();
$client_id = $client['ID_Client'];

$rating = (int)($_POST['rating'] ?? 0);
$text = trim($_POST['text'] ?? '');
$product_id = (int)($_POST['product_id'] ?? 0);

if ($rating < 1 || $rating > 10 || !$text || !$product_id) {
    exit('Некорректные данные');
}

// Проверка, что клиент действительно покупал этот товар
$sale_check = $mysqli->query("SELECT sc.* FROM sale_contents sc 
    JOIN sale s ON sc.ID_Sale = s.ID_S 
    WHERE s.ID_Client = $client_id AND sc.ID_Product = $product_id");
if (!$sale_check->num_rows) {
    exit('Вы не покупали этот товар');
}

// Проверка, что отзыв ещё не оставлен
$rev_check = $mysqli->query("SELECT * FROM customer_reviews WHERE ID_Client=$client_id AND ID_Product=$product_id");
if ($rev_check->num_rows) exit('Вы уже оставили отзыв на этот товар');

// Добавляем отзыв
$date = date('Y-m-d');
$text = $mysqli->real_escape_string($text);
$mysqli->query("INSERT INTO customer_reviews (ID_Client, ID_Product, Rating_1_to_10, Text, Date) 
    VALUES ($client_id, $product_id, $rating, '$text', '$date')");
echo 'ok';
?>
