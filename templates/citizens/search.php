<?php

declare(strict_types=1);

/**
 * @var App\Infrastructure\View\ViewRenderer $this
 * @var string $term
 * @var list<App\Domain\Citizen\Citizen>|null $citizens null when no search was made
 * @var string|null $error
 */
?>
<section class="page" aria-labelledby="search-title">
    <div class="card">
        <div class="card__header card__header--borderless">
            <h1 class="card__title" id="search-title">Pesquisar cidadão</h1>
            <p class="card__description">Busque por parte do nome ou pelo CPF, com ou sem pontuação.</p>
        </div>

        <form class="search" method="get" action="/pesquisa" role="search">
            <div class="field search__field<?= $error !== null ? ' field--error' : '' ?>">
                <label for="term">Nome ou CPF</label>
                <div class="input-icon">
                    <?= $this->partial('partials/icon', ['name' => 'search']) ?>
                    <input id="term" name="termo" type="search" value="<?= e($term) ?>"
                           placeholder="Ex.: Maria ou 000.000.000-00" data-cpf-search
                           <?= $error !== null ? 'aria-invalid="true" aria-describedby="term-message"' : '' ?>>
                </div>
                <?php if ($error !== null): ?>
                    <span class="field__message" id="term-message" role="alert"><?= e($error) ?></span>
                <?php endif ?>
            </div>
            <button class="button button--primary" type="submit">Pesquisar</button>
        </form>

        <?php if ($citizens === []): ?>
            <div class="results">
                <div class="empty-state">
                    <div class="empty-state__icon"><?= $this->partial('partials/icon', ['name' => 'search-empty', 'size' => 22]) ?></div>
                    <strong role="alert">Cidadão não encontrado</strong>
                    <span>Confira a grafia ou tente buscar só pelo primeiro nome ou pelos números do CPF.</span>
                    <a class="link" href="/">Cadastrar novo cidadão</a>
                </div>
            </div>
        <?php elseif ($citizens !== null): ?>
            <div class="results">
                <p class="results__summary" role="status">
                    <?= count($citizens) === 1 ? '1 cidadão encontrado' : count($citizens) . ' cidadãos encontrados' ?>
                </p>

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
                                    <td>
                                        <?php if ($citizen->state === null): ?>
                                            <span class="table__empty">Não informado</span>
                                        <?php else: ?>
                                            <span class="state-tag"><?= e($citizen->state->value) ?></span>
                                        <?php endif ?>
                                    </td>
                                </tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif ?>
    </div>
</section>
