<?php

declare(strict_types=1);

/**
 * @var 'success'|'error'|'info' $type
 * @var string $message
 * @var string|null $details optional HTML, already escaped by the caller
 */
?>
<div class="alert alert--<?= e($type) ?>" role="alert">
    <p class="alert__message"><?= e($message) ?></p>
    <?= $details ?? '' ?>
</div>
