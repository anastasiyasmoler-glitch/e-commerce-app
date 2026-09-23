<?php

declare(strict_types=1);

namespace Auth\Http;

final class Inertia
{
    public static function render(string $component, array $props = []): never
    {
        $page = [
            'component' => $component,
            'props' => $props,
            'url' => $_SERVER['REQUEST_URI'] ?? '/',
            'version' => '1',
        ];

        $isInertia = ($_SERVER['HTTP_X_INERTIA'] ?? '') === 'true';

        if ($isInertia) {
            header('Content-Type: application/json; charset=utf-8');
            header('X-Inertia: true');
            header('X-Inertia-Version: 1');
            echo json_encode($page, JSON_THROW_ON_ERROR);
            exit;
        }

        $pageJson = htmlspecialchars(
            json_encode($page, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ENT_QUOTES,
            'UTF-8'
        );

        require dirname(__DIR__, 2) . '/views/app.php';
        exit;
    }
}
