<?php

declare(strict_types=1);

/**
 * @var App\Infrastructure\View\ViewRenderer $this
 * @var string $csrfToken
 * @var array{message: string, name: string, cpf: string}|null $registered
 * @var string|null $error
 * @var App\Application\UseCase\RegisterCitizen\RegisterCitizenInput $old
 */
?>
<section class="card" aria-labelledby="register-title">
    <h1 id="register-title">Cadastrar cidadão</h1>

    <?php if ($registered !== null): ?>
        <?= $this->partial('partials/alert', [
            'type' => 'success',
            'message' => $registered['message'],
            'details' => $this->partial('partials/citizen-card', [
                'name' => $registered['name'],
                'cpf' => $registered['cpf'],
            ]),
        ]) ?>
    <?php endif ?>

    <?php if ($error !== null): ?>
        <?= $this->partial('partials/alert', ['type' => 'error', 'message' => $error]) ?>
    <?php endif ?>

    <form class="form" method="post" action="/cidadaos" novalidate>
        <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">

        <div class="field">
            <label for="name">Nome completo</label>
            <input id="name" name="name" type="text" required maxlength="150"
                   autocomplete="name" value="<?= e($old->name) ?>">
        </div>

        <div class="field">
            <label for="cpf">CPF</label>
            <input id="cpf" name="cpf" type="text" required inputmode="numeric" maxlength="14"
                   placeholder="000.000.000-00" autocomplete="off" value="<?= e($old->cpf) ?>"
                   data-cpf-input aria-describedby="cpf-feedback">
            <p id="cpf-feedback" class="field__feedback" aria-live="polite"></p>
        </div>

        <button class="button" type="submit">Cadastrar</button>
    </form>
</section>
