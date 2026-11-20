<?php

declare(strict_types=1);

/**
 * @var string $title
 * @var string $message
 */
?>
<section class="page" aria-labelledby="error-title">
    <div class="card">
        <div class="card__header card__header--borderless">
            <h1 class="card__title" id="error-title"><?= e($title) ?></h1>
            <p class="card__description"><?= e($message) ?></p>
        </div>
        <div class="card__body">
            <a class="link" href="/">Voltar ao cadastro</a>
        </div>
    </div>
</section>
