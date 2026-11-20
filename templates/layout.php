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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="<?= e($this->asset('/assets/css/app.css')) ?>">
    <script src="<?= e($this->asset('/assets/js/app.js')) ?>" defer></script>
</head>
<body>
    <header class="site-header">
        <div class="container site-header__inner">
            <div class="brand">
                <div class="brand__icon"><?= $this->partial('partials/icon', ['name' => 'users', 'size' => 22]) ?></div>
                <div>
                    <p class="brand__title">Cadastro de Cidadãos</p>
                    <p class="brand__subtitle">Cadastre e consulte cidadãos pelo nome ou CPF</p>
                </div>
            </div>

            <?= $this->partial('partials/nav', ['activeTab' => $activeTab ?? null]) ?>
        </div>
    </header>

    <main class="container content">
        <?= $content ?>
    </main>
</body>
</html>
