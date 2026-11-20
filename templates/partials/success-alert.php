<?php

declare(strict_types=1);

/**
 * @var App\Infrastructure\View\ViewRenderer $this
 * @var string $title
 * @var string $message
 */
?>
<div class="alert alert--success" role="alert">
    <?= $this->partial('partials/icon', ['name' => 'check-circle', 'size' => 20]) ?>
    <div>
        <strong><?= e($title) ?></strong>
        <span><?= e($message) ?></span>
    </div>
</div>
