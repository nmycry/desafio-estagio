<?php

declare(strict_types=1);

/**
 * @var string $title
 * @var string $message
 */
?>
<section class="card" aria-labelledby="error-title">
    <h1 id="error-title"><?= e($title) ?></h1>
    <p><?= e($message) ?></p>
    <p><a href="/">Voltar ao cadastro</a></p>
</section>
