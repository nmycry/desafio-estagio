<?php

declare(strict_types=1);

namespace App\Infrastructure\View;

use Throwable;

final readonly class ViewRenderer
{
    public function __construct(private string $templatesPath) {}

    /**
     * Renders a page template wrapped in the layout.
     *
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        return $this->partial('layout', [...$data, 'content' => $this->partial($template, $data)]);
    }

    /**
     * Renders a single template; inside it, $this->partial() includes others.
     *
     * @param array<string, mixed> $data
     */
    public function partial(string $template, array $data = []): string
    {
        extract($data, EXTR_SKIP);
        ob_start();

        try {
            require $this->templatesPath . '/' . $template . '.php';
        } catch (Throwable $exception) {
            ob_end_clean();

            throw $exception;
        }

        return (string) ob_get_clean();
    }
}
