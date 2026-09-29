<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: auth.php');
    exit;
}
require_once __DIR__ . '/../php/config/db_connect.php';

// Удаление отзыва
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE review_id = ?");
    $stmt->execute([$id]);
    header('Location: reviews.php');
    exit;
}

// Массовое удаление
if (isset($_POST['mass_delete']) && isset($_POST['delete_ids'])) {
    $ids = array_map('intval', $_POST['delete_ids']);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("DELETE FROM reviews WHERE review_id IN ($placeholders)");
    $stmt->execute($ids);
    header('Location: reviews.php');
    exit;
}

// Получаем список отзывов с информацией о товаре и клиенте
$reviews = $pdo->query("
    SELECT r.*, c.full_name, c.email, p.name as product_name, p.product_id
    FROM reviews r
    JOIN client c ON r.client_id = c.client_id
    JOIN product p ON r.product_id = p.product_id
    ORDER BY r.created_at DESC
")->fetchAll();

// Статистика
$stats = $pdo->query("
    SELECT 
        COUNT(*) as total_reviews,
        ROUND(AVG(rating), 1) as avg_rating,
        SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as rating_5,
        SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as rating_4,
        SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as rating_3,
        SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as rating_2,
        SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as rating_1
    FROM reviews
")->fetch();

// Фильтрация по рейтингу
$currentRating = isset($_GET['rating']) ? (int)$_GET['rating'] : 0;
$filteredReviews = $reviews;
if ($currentRating > 0 && $currentRating <= 5) {
    $filteredReviews = array_filter($reviews, function($r) use ($currentRating) {
        return $r['rating'] == $currentRating;
    });
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" />
    <title>Управление отзывами</title>
    <link rel="stylesheet" href="/acs/css/main.css" />
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #1e1e1e;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            border-bottom: 3px solid #e74c3c;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #e74c3c;
        }
        .stat-label {
            color: #aaa;
            margin-top: 5px;
            font-size: 0.85rem;
        }
        .rating-stars {
            color: #f1c40f;
            letter-spacing: 2px;
        }
        .review-card {
            background: #1e1e1e;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #333;
        }
        .review-user {
            font-weight: bold;
            color: #e74c3c;
        }
        .review-product {
            font-size: 0.9rem;
            color: #aaa;
        }
        .review-product a {
            color: #2ecc71;
            text-decoration: none;
        }
        .review-product a:hover {
            text-decoration: underline;
        }
        .review-date {
            font-size: 0.8rem;
            color: #666;
        }
        .review-comment {
            margin: 15px 0;
            line-height: 1.5;
            background: #2a2a2a;
            padding: 12px;
            border-radius: 8px;
        }
        .btn-delete {
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
        }
        .btn-delete:hover {
            background: #c0392b;
        }
        .empty-state {
            text-align: center;
            padding: 60px;
            color: #aaa;
        }
        .rating-filter {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-btn {
            background: #2a2a2a;
            color: #fff;
            padding: 6px 12px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.85rem;
        }
        .filter-btn.active, .filter-btn:hover {
            background: #e74c3c;
        }
        .checkbox-select {
            margin-right: 15px;
            transform: scale(1.2);
            cursor: pointer;
        }
        .mass-actions {
            background: #2a2a2a;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        .btn-mass-delete {
            background: #e74c3c;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
        }
    </style>
</head>
<body>
<div class="container" style="margin-top: 1rem;">
    <h1>⭐ Управление отзывами</h1>

    <!-- Статистика -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?= $stats['total_reviews'] ?></div>
            <div class="stat-label">Всего отзывов</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= $stats['avg_rating'] ?? 0 ?></div>
            <div class="stat-label">Средний рейтинг</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">⭐5: <?= $stats['rating_5'] ?></div>
            <div class="stat-label">Отличные отзывы</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">⭐1: <?= $stats['rating_1'] ?></div>
            <div class="stat-label">Плохие отзывы</div>
        </div>
    </div>

    <!-- Фильтр по рейтингу -->
    <div class="rating-filter">
        <span style="color:#aaa;">Фильтр по рейтингу:</span>
        <a href="reviews.php" class="filter-btn <?= $currentRating == 0 ? 'active' : '' ?>">Все</a>
        <?php for ($i = 5; $i >= 1; $i--): ?>
            <a href="?rating=<?= $i ?>" class="filter-btn <?= $currentRating == $i ? 'active' : '' ?>">⭐<?= $i ?></a>
        <?php endfor; ?>
    </div>

    <?php if (empty($filteredReviews)): ?>
        <div class="empty-state">
            <p>📭 Отзывы не найдены</p>
        </div>
    <?php else: ?>
        <!-- Массовые действия -->
        <form method="post" id="massDeleteForm">
            <div class="mass-actions">
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" id="selectAll" class="checkbox-select">
                    <strong>Выбрать все</strong>
                </label>
                <button type="submit" name="mass_delete" class="btn-mass-delete" onclick="return confirmDeleteSelected()">
                    🗑️ Удалить выбранные
                </button>
                <span id="selectedCount" style="color: #aaa;">Выбрано: 0</span>
            </div>

            <?php foreach ($filteredReviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div>
                            <input type="checkbox" name="delete_ids[]" value="<?= $review['review_id'] ?>" class="review-checkbox" style="margin-right: 10px; transform: scale(1.2);">
                            <span class="review-user">👤 <?= htmlspecialchars($review['full_name']) ?></span>
                            <span class="review-product">
                                (товар: <a href="/acs/product.php?id=<?= $review['product_id'] ?>" target="_blank"><?= htmlspecialchars($review['product_name']) ?></a>)
                            </span>
                        </div>
                        <div>
                            <span class="rating-stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?= $i <= $review['rating'] ? '⭐' : '☆' ?>
                                <?php endfor; ?>
                            </span>
                            <span class="review-date">📅 <?= date('d.m.Y H:i', strtotime($review['created_at'])) ?></span>
                        </div>
                    </div>
                    <div class="review-comment">
                        <?= nl2br(htmlspecialchars($review['comment'])) ?>
                    </div>
                    <div style="display: flex; justify-content: flex-end;">
                        <a href="?delete=<?= $review['review_id'] ?>" class="btn-delete" onclick="return confirm('Вы уверены, что хотите удалить этот отзыв?')">
                            🗑️ Удалить
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </form>
    <?php endif; ?>

    <p><a href="index.php" style="display:inline-block; margin-top:20px;">← Вернуться в панель администратора</a></p>
</div>

<script>
    // Выделение всех чекбоксов
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.review-checkbox');
    const selectedCountSpan = document.getElementById('selectedCount');
    
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.review-checkbox:checked').length;
        if (selectedCountSpan) {
            selectedCountSpan.textContent = `Выбрано: ${checked}`;
        }
    }
    
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
            updateSelectedCount();
        });
    }
    
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
    
    function confirmDeleteSelected() {
        const checked = document.querySelectorAll('.review-checkbox:checked').length;
        if (checked === 0) {
            alert('Выберите хотя бы один отзыв для удаления');
            return false;
        }
        return confirm(`Вы уверены, что хотите удалить ${checked} отзыв(ов)? Это действие необратимо.`);
    }
    
    updateSelectedCount();
</script>
</body>
</html>