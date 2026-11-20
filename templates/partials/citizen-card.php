<?php

declare(strict_types=1);

/**
 * @var string $name
 * @var string $cpf formatted (000.000.000-00)
 */
?>
<dl class="citizen-card">
    <div>
        <dt>Nome</dt>
        <dd><?= e($name) ?></dd>
    </div>
    <div>
        <dt>CPF</dt>
        <dd class="citizen-card__cpf"><?= e($cpf) ?></dd>
    </div>
</dl>
