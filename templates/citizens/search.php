<?php

declare(strict_types=1);

/**
 * @var App\Infrastructure\View\ViewRenderer $this
 * @var string $term
 * @var App\Application\UseCase\ListCitizens\CitizenPage|null $listing set when no search was made
 * @var list<App\Domain\Citizen\Citizen>|null $citizens set when a search was made
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
                           placeholder="Ex.: Maria ou 000.000.000-00" data-search-input
                           <?= $error !== null ? 'aria-invalid="true" aria-describedby="term-message"' : '' ?>>
                </div>
                <?php if ($error !== null): ?>
                    <span class="field__message" id="term-message" role="alert"><?= e($error) ?></span>
                <?php endif ?>
            </div>
            <button class="button button--primary" type="submit">Pesquisar</button>
        </form>

        <?php if ($listing === null): ?>
            <p class="search__reset"><a class="link" href="/pesquisa">Ver todos os cidadãos</a></p>
        <?php endif ?>

        <?php if ($listing !== null): ?>
            <div class="results">
                <?php if ($listing->total === 0): ?>
                    <?= $this->partial('partials/empty-state', [
                        'title' => 'Nenhum cidadão cadastrado ainda',
                        'message' => 'Os cidadãos cadastrados aparecerão aqui.',
                    ]) ?>
                <?php else: ?>
                    <p class="results__summary">
                        <?= $listing->total === 1 ? '1 cidadão cadastrado' : $listing->total . ' cidadãos cadastrados' ?>
                    </p>
                    <?= $this->partial('partials/citizens-table', ['citizens' => $listing->citizens]) ?>
                    <?php if ($listing->totalPages > 1): ?>
                        <?= $this->partial('partials/pagination', ['listing' => $listing]) ?>
                    <?php endif ?>
                <?php endif ?>
            </div>
        <?php elseif ($citizens === []): ?>
            <div class="results">
                <?= $this->partial('partials/empty-state', [
                    'title' => 'Cidadão não encontrado',
                    'message' => 'Confira a grafia ou tente buscar só pelo primeiro nome ou pelos números do CPF.',
                ]) ?>
            </div>
        <?php elseif ($citizens !== null): ?>
            <div class="results">
                <p class="results__summary" role="status">
                    <?= count($citizens) === 1 ? '1 cidadão encontrado' : count($citizens) . ' cidadãos encontrados' ?>
                </p>
                <?= $this->partial('partials/citizens-table', ['citizens' => $citizens]) ?>
            </div>
        <?php endif ?>
    </div>
</section>
