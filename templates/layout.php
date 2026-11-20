<?php

declare(strict_types=1);

/**
 * @var App\Infrastructure\View\ViewRenderer $this
 * @var string $title
 * @var string $content
 * @var string|null $activeTab
 */
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title) ?> · Cadastro de Cidadãos</title>
    <link rel="stylesheet" href="/assets/css/app.css">
    <script src="/assets/js/app.js" defer></script>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <p class="brand">Cadastro de Cidadãos</p>
            <?= $this->partial('partials/nav', ['activeTab' => $activeTab ?? null]) ?>
        </div>
    </header>

    <main class="container">
        <?= $content ?>
    </main>
</body>
</html>
