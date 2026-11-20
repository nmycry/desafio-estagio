<?php

declare(strict_types=1);

/**
 * @var App\Infrastructure\View\ViewRenderer $this
 * @var string|null $activeTab
 */

$tabs = [
    'register' => ['href' => '/', 'label' => 'Cadastrar cidadão', 'icon' => 'user-plus'],
    'search' => ['href' => '/pesquisa', 'label' => 'Pesquisar cidadão', 'icon' => 'search'],
];
?>
<nav class="tabs" aria-label="Seções">
    <?php foreach ($tabs as $key => $tab): ?>
        <a class="tab" href="<?= e($tab['href']) ?>"<?= $key === $activeTab ? ' aria-current="page"' : '' ?>>
            <?= $this->partial('partials/icon', ['name' => $tab['icon']]) ?>
            <?= e($tab['label']) ?>
        </a>
    <?php endforeach ?>
</nav>
