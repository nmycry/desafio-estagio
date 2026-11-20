<?php

declare(strict_types=1);

namespace Tests\Unit\Presentation;

use App\Application\UseCase\RegisterCitizen\RegisterCitizen;
use App\Application\UseCase\SearchCitizens\SearchCitizens;
use App\Infrastructure\Http\Request;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\Session;
use App\Infrastructure\Persistence\InMemoryCitizenRepository;
use App\Infrastructure\View\ViewRenderer;
use App\Presentation\Controller\CitizenController;
use App\Presentation\Kernel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class KernelTest extends TestCase
{
    /** @var array<array-key, mixed> */
    private array $sessionStorage = [];

    private Session $session;

    private Kernel $kernel;

    private ViewRenderer $view;

    protected function setUp(): void
    {
        $this->session = new Session($this->sessionStorage);
        $this->view = new ViewRenderer(dirname(__DIR__, 3) . '/templates');

        $citizens = new InMemoryCitizenRepository();
        $controller = new CitizenController(
            new RegisterCitizen($citizens),
            new SearchCitizens($citizens),
            $this->view,
            $this->session,
        );

        $this->kernel = new Kernel(static fn(): CitizenController => $controller, $this->view);
    }

    #[Test]
    public function itShowsTheRegistrationForm(): void
    {
        $response = $this->get('/');

        self::assertSame(200, $response->status);
        self::assertStringContainsString('<form class="form" method="post" action="/cidadaos"', $response->body);
        self::assertStringContainsString('value="' . $this->session->csrfToken() . '"', $response->body);
    }

    #[Test]
    public function itRedirectsAfterRegisteringAndShowsTheFlashMessageOnce(): void
    {
        $response = $this->register('Maria Silva', '52998224725');

        self::assertSame(303, $response->status);
        self::assertSame('/', $response->headers['Location']);

        $page = $this->get('/')->body;
        self::assertStringContainsString('Cidadão cadastrado com sucesso!', $page);
        self::assertStringContainsString('Maria Silva', $page);
        self::assertStringContainsString('529.982.247-25', $page);

        self::assertStringNotContainsString('Cidadão cadastrado com sucesso!', $this->get('/')->body);
    }

    #[Test]
    public function itRejectsARegistrationWithoutAValidCsrfToken(): void
    {
        $response = $this->kernel->handle(new Request('POST', '/cidadaos', body: [
            '_token' => 'forged',
            'name' => 'Maria Silva',
            'cpf' => '52998224725',
        ]));

        self::assertSame(400, $response->status);
        self::assertStringContainsString('Sua sessão expirou', $response->body);
        self::assertStringNotContainsString('Maria Silva', $this->search('Maria')->body);
    }

    #[Test]
    public function itShowsValidationErrorsKeepingTheEscapedInput(): void
    {
        $response = $this->register('<script>alert(1)</script>', '123.456.789-00');

        self::assertSame(422, $response->status);
        self::assertStringContainsString('role="alert"', $response->body);
        self::assertStringContainsString('O nome deve conter apenas letras', $response->body);
        self::assertStringContainsString('value="&lt;script&gt;alert(1)&lt;/script&gt;"', $response->body);
        self::assertStringContainsString('value="123.456.789-00"', $response->body);
        self::assertStringNotContainsString('<script>alert(1)</script>', $response->body);
    }

    #[Test]
    public function itShowsAFriendlyErrorForADuplicatedCpf(): void
    {
        $this->register('Maria Silva', '52998224725');

        $response = $this->register('João Souza', '529.982.247-25');

        self::assertSame(422, $response->status);
        self::assertStringContainsString('Já existe um cidadão cadastrado com o CPF 529.982.247-25.', $response->body);
    }

    #[Test]
    public function itShowsOnlyTheSearchFormBeforeSearching(): void
    {
        $response = $this->get('/pesquisa');

        self::assertSame(200, $response->status);
        self::assertStringContainsString('<form class="form form--inline" method="get" action="/pesquisa"', $response->body);
        self::assertStringNotContainsString('role="alert"', $response->body);
    }

    #[Test]
    public function itListsEveryCitizenFoundByNameOrderedByName(): void
    {
        $this->register('Maria Silva', '52998224725');
        $this->register('Ana Silveira', '11144477735');

        $page = $this->search('silv')->body;

        self::assertStringContainsString('2 cidadãos encontrados', $page);
        self::assertMatchesRegularExpression('/Ana Silveira.*111\.444\.777-35.*Maria Silva.*529\.982\.247-25/s', $page);
    }

    #[Test]
    public function itFindsACitizenByCpf(): void
    {
        $this->register('Maria Silva', '52998224725');

        $page = $this->search('529.982.247-25')->body;

        self::assertStringContainsString('1 cidadão encontrado', $page);
        self::assertStringContainsString('Maria Silva', $page);
    }

    #[Test]
    public function itShowsTheNotFoundMessage(): void
    {
        self::assertStringContainsString(
            '<p class="alert__message">Cidadão não encontrado</p>',
            $this->search('Pedro')->body,
        );
    }

    #[Test]
    public function itShowsAValidationMessageForAnEmptySearch(): void
    {
        self::assertStringContainsString('Informe um nome ou CPF para pesquisar.', $this->search('   ')->body);
    }

    #[Test]
    public function itRespondsNotFoundForAnUnknownPath(): void
    {
        $response = $this->get('/nao-existe');

        self::assertSame(404, $response->status);
        self::assertStringContainsString('Erro 404', $response->body);
    }

    #[Test]
    public function itRespondsMethodNotAllowedForAKnownPathWithTheWrongMethod(): void
    {
        $response = $this->kernel->handle(new Request('DELETE', '/'));

        self::assertSame(405, $response->status);
    }

    #[Test]
    public function itHidesUnexpectedErrorsBehindAGenericPage(): void
    {
        $kernel = new Kernel(static fn(): CitizenController => throw new RuntimeException('SQLSTATE secret details'), $this->view);
        $previousErrorLog = ini_set('error_log', '/dev/null');

        try {
            $response = $kernel->handle(new Request('GET', '/'));
        } finally {
            ini_set('error_log', (string) $previousErrorLog);
        }

        self::assertSame(500, $response->status);
        self::assertStringContainsString('Ocorreu um erro inesperado', $response->body);
        self::assertStringNotContainsString('SQLSTATE', $response->body);
    }

    private function get(string $path): Response
    {
        return $this->kernel->handle(new Request('GET', $path));
    }

    private function search(string $term): Response
    {
        return $this->kernel->handle(new Request('GET', '/pesquisa', query: ['termo' => $term]));
    }

    private function register(string $name, string $cpf): Response
    {
        return $this->kernel->handle(new Request('POST', '/cidadaos', body: [
            '_token' => $this->session->csrfToken(),
            'name' => $name,
            'cpf' => $cpf,
        ]));
    }
}
