<?php

declare(strict_types=1);

/**
 * @var App\Infrastructure\View\ViewRenderer $this
 * @var string $title
 * @var string $message
 */
?>
<div class="empty-state">
    <div class="empty-state__icon"><?= $this->partial('partials/icon', ['name' => 'search-empty', 'size' => 22]) ?></div>
    <strong role="alert"><?= e($title) ?></strong>
    <span><?= e($message) ?></span>
    <a class="link" href="/">Cadastrar novo cidadão</a>
</div>
