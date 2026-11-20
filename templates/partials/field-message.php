<?php

declare(strict_types=1);

/**
 * Help text below a field, replaced by the error message when there is one.
 *
 * @var string $id
 * @var string $help
 * @var string|null $error
 */
?>
<span class="field__message" id="<?= e($id) ?>" data-help="<?= e($help) ?>"<?= $error !== null ? ' role="alert"' : '' ?>>
    <?= e($error ?? $help) ?>
</span>
