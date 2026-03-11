<?php
// Файл: includes/auth.php

// Запускаем сессию ТОЛЬКО если она еще не запущена
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
 * Проверка авторизации и перенаправление
 */
function checkAuth() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}
?>