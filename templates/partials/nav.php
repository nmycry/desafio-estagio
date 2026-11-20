<?php

declare(strict_types=1);

/**
 * @var string|null $activeTab
 */

$tabs = [
    'register' => ['href' => '/', 'label' => 'Cadastrar cidadão'],
    'search' => ['href' => '/pesquisa', 'label' => 'Pesquisar cidadão'],
];
?>
<nav class="tabs" aria-label="Seções">
    <?php foreach ($tabs as $key => $tab): ?>
        <a class="tab" href="<?= e($tab['href']) ?>"<?= $key === $activeTab ? ' aria-current="page"' : '' ?>>
            <?= e($tab['label']) ?>
        </a>
    <?php endforeach ?>
</nav>
