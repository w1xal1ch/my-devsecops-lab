<?php
// Нормализация телефона в +7XXXXXXXXXX
function formatPhoneForDB($phone) {
    $clean = preg_replace('/[^0-9]/', '', $phone);
    if (preg_match('/^8(\d{10})$/', $clean, $matches)) {
        return '+7' . $matches[1];
    }
    if (preg_match('/^7(\d{10})$/', $clean, $matches)) {
        return '+7' . $matches[1];
    }
    if (preg_match('/^(\d{10})$/', $clean, $matches)) {
        return '+7' . $matches[1];
    }
    if (preg_match('/^\+7(\d{10})$/', $clean, $matches)) {
        return '+7' . $matches[1];
    }
    return false;
}

// Валидация ФИО
function validateName($name) {
    return preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', $name);
}
?>