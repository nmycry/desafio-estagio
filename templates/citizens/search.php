<?php

declare(strict_types=1);

/**
 * @var App\Infrastructure\View\ViewRenderer $this
 * @var string $term
 * @var list<App\Domain\Citizen\Citizen>|null $citizens null when no search was made
 * @var string|null $error
 */
?>
<section class="card" aria-labelledby="search-title">
    <h1 id="search-title">Pesquisar cidadão</h1>

    <form class="form form--inline" method="get" action="/pesquisa" role="search">
        <div class="field">
            <label for="termo">Nome ou CPF</label>
            <input id="termo" name="termo" type="search" value="<?= e($term) ?>"
                   placeholder="Ex.: Maria ou 000.000.000-00" data-cpf-search>
        </div>

        <button class="button" type="submit">Pesquisar</button>
    </form>

    <?php if ($error !== null): ?>
        <?= $this->partial('partials/alert', ['type' => 'error', 'message' => $error]) ?>
    <?php elseif ($citizens === []): ?>
        <?= $this->partial('partials/alert', ['type' => 'info', 'message' => 'Cidadão não encontrado']) ?>
    <?php elseif ($citizens !== null): ?>
        <h2 class="results-title">
            <?= count($citizens) === 1 ? '1 cidadão encontrado' : count($citizens) . ' cidadãos encontrados' ?>
        </h2>
        <ul class="results">
            <?php foreach ($citizens as $citizen): ?>
                <li>
                    <?= $this->partial('partials/citizen-card', [
                        'name' => $citizen->name->value(),
                        'cpf' => $citizen->cpf->formatted(),
                    ]) ?>
                </li>
            <?php endforeach ?>
        </ul>
    <?php endif ?>
</section>
