<?php

declare(strict_types=1);

/**
 * @var App\Infrastructure\View\ViewRenderer $this
 * @var string $csrfToken
 * @var array{name: string, cpf: string, state: string}|null $registered
 * @var array<'name'|'cpf'|'state', string> $errors
 * @var App\Application\UseCase\RegisterCitizen\RegisterCitizenInput $old
 */

$fieldClass = static fn(string $field): string => isset($errors[$field]) ? 'field field--error' : 'field';
$invalid = static fn(string $field): string => isset($errors[$field]) ? ' aria-invalid="true"' : '';
?>
<section class="page" aria-labelledby="register-title">
    <?php if ($registered !== null): ?>
        <?= $this->partial('partials/success-alert', [
            'title' => 'Cidadão cadastrado com sucesso',
            'message' => implode(' · ', array_filter([
                $registered['name'],
                'CPF ' . $registered['cpf'],
                $registered['state'],
            ])),
        ]) ?>
    <?php endif ?>

    <div class="card">
        <div class="card__header">
            <h1 class="card__title" id="register-title">Novo cidadão</h1>
            <p class="card__description">Campos marcados com <span class="required">*</span> são obrigatórios.</p>
        </div>

        <form class="card__body" method="post" action="/cidadaos" novalidate>
            <input type="hidden" name="_token" value="<?= e($csrfToken) ?>">

            <div class="form-grid">
                <div class="<?= $fieldClass('name') ?> field--full">
                    <label for="name">Nome completo <span class="required">*</span></label>
                    <input id="name" name="name" type="text" required maxlength="150" autocomplete="name"
                           placeholder="Ex.: Maria Aparecida Souza" value="<?= e($old->name) ?>"
                           aria-describedby="name-message"<?= $invalid('name') ?>>
                    <?= $this->partial('partials/field-message', [
                        'id' => 'name-message',
                        'help' => 'Como consta no documento.',
                        'error' => $errors['name'] ?? null,
                    ]) ?>
                </div>

                <div class="<?= $fieldClass('cpf') ?>">
                    <label for="cpf">CPF <span class="required">*</span></label>
                    <input id="cpf" name="cpf" type="text" required inputmode="numeric" maxlength="14"
                           placeholder="000.000.000-00" autocomplete="off" value="<?= e($old->cpf) ?>"
                           aria-describedby="cpf-message" data-cpf-input<?= $invalid('cpf') ?>>
                    <?= $this->partial('partials/field-message', [
                        'id' => 'cpf-message',
                        'help' => 'Somente números. A máscara é aplicada automaticamente.',
                        'error' => $errors['cpf'] ?? null,
                    ]) ?>
                </div>

                <div class="<?= $fieldClass('state') ?>">
                    <label for="state">Estado</label>
                    <select id="state" name="state" aria-describedby="state-message"
                            data-states-select data-selected="<?= e($old->state) ?>"<?= $invalid('state') ?>>
                        <option value="">Selecione o estado</option>
                    </select>
                    <?= $this->partial('partials/field-message', [
                        'id' => 'state-message',
                        'help' => 'Opcional. Estado onde o cidadão reside.',
                        'error' => $errors['state'] ?? null,
                    ]) ?>
                </div>
            </div>

            <div class="form-actions">
                <a class="button button--secondary" href="/">Limpar</a>
                <button class="button button--primary" type="submit">
                    <?= $this->partial('partials/icon', ['name' => 'check']) ?>
                    Cadastrar cidadão
                </button>
            </div>
        </form>
    </div>
</section>
