<?php

require_once __DIR__ . '/session.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function old(string $key, string $default = ''): string
{
    return e($_POST[$key] ?? $default);
}

function normalize_role(?string $role): string
{
    return strtolower(trim((string) $role));
}
