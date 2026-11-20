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
        $this->view = new ViewRenderer(dirname(__DIR__, 3) . '/templates', dirname(__DIR__, 3) . '/public');

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
        self::assertStringContainsString('method="post" action="/cidadaos"', $response->body);
        self::assertStringContainsString('value="' . $this->session->csrfToken() . '"', $response->body);
        self::assertStringContainsString('<select id="state" name="state"', $response->body);
        self::assertStringNotContainsString('field--error', $response->body);
    }

    #[Test]
    public function itVersionsTheAssetsByTheirModificationTime(): void
    {
        $page = $this->get('/')->body;

        self::assertMatchesRegularExpression('#href="/assets/css/app\.css\?v=[1-9]\d*"#', $page);
        self::assertMatchesRegularExpression('#src="/assets/js/app\.js\?v=[1-9]\d*"#', $page);
    }

    #[Test]
    public function itRedirectsAfterRegisteringAndShowsTheFlashMessageOnce(): void
    {
        $response = $this->register('Maria Silva', '52998224725', 'SP');

        self::assertSame(303, $response->status);
        self::assertSame('/', $response->headers['Location']);

        $page = $this->get('/')->body;
        self::assertStringContainsString('Cidadão cadastrado com sucesso', $page);
        self::assertStringContainsString('Maria Silva · CPF 529.982.247-25 · SP', $page);

        self::assertStringNotContainsString('Cidadão cadastrado com sucesso', $this->get('/')->body);
    }

    #[Test]
    public function itRegistersACitizenWithoutState(): void
    {
        $response = $this->register('Maria Silva', '52998224725', '');

        self::assertSame(303, $response->status);
        self::assertStringContainsString('<span>Maria Silva · CPF 529.982.247-25</span>', $this->get('/')->body);
    }

    #[Test]
    public function itRejectsARegistrationWithoutAValidCsrfToken(): void
    {
        $response = $this->kernel->handle(new Request('POST', '/cidadaos', body: [
            '_token' => 'forged',
            'name' => 'Maria Silva',
            'cpf' => '52998224725',
            'state' => 'SP',
        ]));

        self::assertSame(400, $response->status);
        self::assertStringContainsString('Sua sessão expirou', $response->body);
        self::assertStringNotContainsString('Maria Silva', $this->search('Maria')->body);
    }

    #[Test]
    public function itShowsTheErrorNextToTheFieldKeepingTheEscapedInput(): void
    {
        $response = $this->register('<script>alert(1)</script>', '123.456.789-00', 'RJ');

        self::assertSame(422, $response->status);
        $this->assertFieldError($response->body, 'name', 'O nome deve conter apenas letras, espaços, apóstrofo e hífen.');
        self::assertStringContainsString('value="&lt;script&gt;alert(1)&lt;/script&gt;"', $response->body);
        self::assertStringContainsString('value="123.456.789-00"', $response->body);
        self::assertStringContainsString('data-selected="RJ"', $response->body);
        self::assertStringNotContainsString('<script>alert(1)</script>', $response->body);
    }

    #[Test]
    public function itShowsTheErrorOnTheStateFieldForAnUnknownState(): void
    {
        $response = $this->register('Maria Silva', '52998224725', 'XX');

        self::assertSame(422, $response->status);
        $this->assertFieldError($response->body, 'state', 'Selecione um estado válido.');
    }

    #[Test]
    public function itShowsAFriendlyErrorOnTheCpfFieldForADuplicatedCpf(): void
    {
        $this->register('Maria Silva', '52998224725', 'SP');

        $response = $this->register('João Souza', '529.982.247-25', 'RJ');

        self::assertSame(422, $response->status);
        $this->assertFieldError($response->body, 'cpf', 'Já existe um cidadão cadastrado com o CPF 529.982.247-25.');
    }

    #[Test]
    public function itShowsOnlyTheSearchFormBeforeSearching(): void
    {
        $this->register('Maria Silva', '52998224725', 'SP');

        $response = $this->get('/pesquisa');

        self::assertSame(200, $response->status);
        self::assertStringContainsString('method="get" action="/pesquisa"', $response->body);
        self::assertStringNotContainsString('<table', $response->body);
        self::assertStringNotContainsString('Maria Silva', $response->body);
        self::assertStringNotContainsString('role="alert"', $response->body);
    }

    #[Test]
    public function itListsEveryCitizenFoundByNameOrderedByNameWithTheirState(): void
    {
        $this->register('Maria Silva', '52998224725', 'SP');
        $this->register('Ana Silveira', '11144477735', 'MG');

        $page = $this->search('silv')->body;

        self::assertStringContainsString('2 cidadãos encontrados', $page);
        self::assertStringContainsString('<th scope="col">Estado</th>', $page);
        self::assertMatchesRegularExpression(
            '/AS.*Ana Silveira.*111\.444\.777-35.*MG.*MS.*Maria Silva.*529\.982\.247-25.*SP/s',
            $page,
        );
    }

    #[Test]
    public function itShowsWhenTheStateWasNotInformed(): void
    {
        $this->register('Maria Silva', '52998224725', '');

        $page = $this->search('Maria')->body;

        self::assertStringContainsString('<span class="table__empty">Não informado</span>', $page);
        self::assertStringNotContainsString('class="state-tag"', $page);
    }

    #[Test]
    public function itFindsACitizenByCpf(): void
    {
        $this->register('Maria Silva', '52998224725', 'SP');

        $page = $this->search('529.982.247-25')->body;

        self::assertStringContainsString('1 cidadão encontrado', $page);
        self::assertStringContainsString('Maria Silva', $page);
    }

    #[Test]
    public function itShowsTheNotFoundMessage(): void
    {
        $page = $this->search('Pedro')->body;

        self::assertStringContainsString('<strong role="alert">Cidadão não encontrado</strong>', $page);
        self::assertStringNotContainsString('<table', $page);
    }

    #[Test]
    public function itShowsTheEmptySearchErrorOnTheSearchField(): void
    {
        $this->register('Maria Silva', '52998224725', 'SP');

        $page = $this->search('   ')->body;

        self::assertStringNotContainsString('<table', $page);
        self::assertStringContainsString('field search__field field--error', $page);
        self::assertStringContainsString(
            '<span class="field__message" id="term-message" role="alert">Informe um nome ou CPF para pesquisar.</span>',
            $page,
        );
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

    private function register(string $name, string $cpf, string $state): Response
    {
        return $this->kernel->handle(new Request('POST', '/cidadaos', body: [
            '_token' => $this->session->csrfToken(),
            'name' => $name,
            'cpf' => $cpf,
            'state' => $state,
        ]));
    }

    /**
     * The field is marked as invalid and its help text is replaced by the error.
     */
    private function assertFieldError(string $page, string $field, string $message): void
    {
        self::assertMatchesRegularExpression(
            sprintf('/<div class="field field--error[^"]*">\s*<label for="%s">/', $field),
            $page,
        );
        self::assertMatchesRegularExpression(
            sprintf('/id="%s-message"[^>]*role="alert">\s*%s\s*</', $field, preg_quote(e($message), '/')),
            $page,
        );
        self::assertSame(1, substr_count($page, 'field--error'));
    }
}
