<?php

declare(strict_types=1);

/**
 * @var App\Application\UseCase\ListCitizens\CitizenPage $listing
 */
?>
<nav class="pagination" aria-label="Paginação">
    <?php if ($listing->hasPrevious()): ?>
        <a class="button button--secondary button--small" href="/pesquisa?pagina=<?= $listing->page - 1 ?>" rel="prev">Anterior</a>
    <?php endif ?>

    <span class="pagination__status">Página <?= $listing->page ?> de <?= $listing->totalPages ?></span>

    <?php if ($listing->hasNext()): ?>
        <a class="button button--secondary button--small" href="/pesquisa?pagina=<?= $listing->page + 1 ?>" rel="next">Próxima</a>
    <?php endif ?>
</nav>
