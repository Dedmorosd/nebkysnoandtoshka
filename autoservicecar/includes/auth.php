<?php
// Файл: includes/auth.php

// Запускаем сессию, если еще не запущена
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Проверка авторизации пользователя
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Проверка роли администратора
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Проверка роли механика
 */
function isMechanic() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'mechanic';
}

/**
 * Проверка роли клиента
 */
function isClient() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'client';
}

/**
 * Проверка авторизации и перенаправление на login.php если не авторизован
 */
function checkAuth() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Проверка прав администратора
 */
function checkAdmin() {
    checkAuth(); // Сначала проверяем авторизацию
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}

/**
 * Проверка прав механика или администратора
 */
function checkMechanic() {
    checkAuth();
    if (!isMechanic() && !isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}
?>