<?php
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['Role'] != 1 && $_SESSION['user']['Role'] != 2)) {
    header('Location: ../index.php');
    exit;
}

require_once 'db.php';

$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

if (!$date_from || !$date_to) {
    die('Ошибка: не указан период');
}

// Получаем данные
$stmt = $mysqli->prepare("
    SELECT p.Souvenir_Name, SUM(sc.Quantity) as total_sold, SUM(sc.Quantity * sc.Price) as revenue
    FROM sale_contents sc
    JOIN sale s ON sc.ID_Sale = s.ID_S
    JOIN products p ON sc.ID_Product = p.ID_Products
    WHERE s.Sale_Date BETWEEN ? AND ?
    GROUP BY sc.ID_Product
    ORDER BY total_sold DESC
");
$stmt->bind_param("ss", $date_from, $date_to);
$stmt->execute();
$reportData = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Общая статистика
$stmt = $mysqli->prepare("
    SELECT COUNT(DISTINCT s.ID_S) as total_orders, SUM(sc.Quantity * sc.Price) as total_revenue
    FROM sale s
    JOIN sale_contents sc ON s.ID_S = sc.ID_Sale
    WHERE s.Sale_Date BETWEEN ? AND ?
");
$stmt->bind_param("ss", $date_from, $date_to);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

$totalOrders = $stats['total_orders'] ?? 0;
$totalRevenue = $stats['total_revenue'] ?? 0;

// ОЧИЩАЕМ БУФЕР ПЕРЕД ОТПРАВКОЙ ФАЙЛА
if (ob_get_level()) ob_end_clean();

// Отправляем CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="report_' . $date_from . '_to_' . $date_to . '.csv"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM для Excel

fputcsv($output, ['Отчёт о продажах']);
fputcsv($output, ['Период: ' . $date_from . ' - ' . $date_to]);
fputcsv($output, []);
fputcsv($output, ['Товар', 'Продано, шт', 'Выручка, руб']);

foreach ($reportData as $row) {
    fputcsv($output, [
        $row['Souvenir_Name'],
        $row['total_sold'],
        number_format($row['revenue'], 2, '.', '')
    ]);
}

fputcsv($output, []);
fputcsv($output, ['Итого заказов:', $totalOrders]);
fputcsv($output, ['Общая выручка:', number_format($totalRevenue, 2, '.', '') . ' руб']);

fclose($output);
exit;
?>