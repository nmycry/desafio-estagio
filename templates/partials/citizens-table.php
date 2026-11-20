<?php

declare(strict_types=1);

/**
 * @var list<App\Domain\Citizen\Citizen> $citizens
 */
?>
<div class="table-wrapper">
    <table class="table">
        <thead>
            <tr>
                <th scope="col">Nome</th>
                <th scope="col">CPF</th>
                <th scope="col">Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($citizens as $citizen): ?>
                <tr>
                    <td>
                        <div class="person">
                            <span class="avatar" aria-hidden="true"><?= e(initials($citizen->name->value())) ?></span>
                            <span class="person__name"><?= e($citizen->name->value()) ?></span>
                        </div>
                    </td>
                    <td class="table__cpf"><?= e($citizen->cpf->formatted()) ?></td>
                    <td><span class="state-tag"><?= e($citizen->state->value) ?></span></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>
