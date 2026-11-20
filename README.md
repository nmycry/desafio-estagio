# Cadastro de Cidadãos

Aplicação em PHP 8.5 puro (sem framework) para cadastrar cidadãos brasileiros
(nome completo e CPF) e pesquisá-los por nome ou CPF.

## Requisitos

- Docker e Docker Compose (no macOS e no Windows, via Docker Desktop).
- Git, para clonar o repositório.

PHP, Composer e MySQL **não** precisam estar instalados: rodam dentro dos containers.

## Como rodar

```bash
cp .env.example .env
docker compose up -d --build
docker compose exec app composer install
```

Acesse <http://localhost:8080>.

Na primeira subida o MySQL leva alguns segundos para inicializar. O container
`app` só sobe depois que o healthcheck do banco passa.

> **UID/GID (Linux e macOS):** o container roda com o mesmo UID/GID do seu
> usuário, para que `vendor/` e os caches não fiquem com dono root. O padrão é
> 1000. Se `id -u` e `id -g` retornarem outros valores, ajuste `UID` e `GID` no
> `.env` antes do build.

### No Windows

O comando `cp` não existe no Prompt de Comando (cmd). Para criar o `.env`:

```bat
copy .env.example .env
```

No PowerShell, `cp .env.example .env` funciona, porque `cp` é um atalho para
`Copy-Item`. Os demais comandos (`docker compose ...`) são os mesmos, com o
Docker Desktop aberto. `UID` e `GID` não precisam ser alterados, porque o
Docker Desktop cuida das permissões.

**Recomendado:** clone e rode o projeto **dentro do WSL2** (por exemplo, em
`~/desafio-estagio` no terminal do Ubuntu), seguindo os mesmos comandos de
Linux. Também funciona a partir de uma pasta do Windows (`C:\...`), mas o bind
mount entre os sistemas de arquivos deixa a aplicação e os testes bem mais
lentos.

### Por que `composer install` é um passo separado

O código é montado no container por bind mount (`.:/var/www/html`), o que
esconde qualquer `vendor/` instalado durante o build da imagem. Entre um
entrypoint que instala as dependências e um comando explícito, escolhi o
comando. É um passo a mais, só que visível e sem mágica: quem lê o README sabe
exatamente o que acontece.

## Testes e qualidade

Tudo roda dentro do container:

```bash
docker compose exec app composer test              # todas as suítes
docker compose exec app composer test:unit         # unitários (sem banco)
docker compose exec app composer test:integration  # integração (banco citizens_test)
docker compose exec app composer analyse           # PHPStan, nível max
docker compose exec app composer cs:check          # PHP-CS-Fixer (PER-CS), sem alterar arquivos
docker compose exec app composer cs:fix            # aplica as correções de estilo
```

- **Unitários:** cobrem os value objects, os casos de uso e a camada HTTP.
  Os testes HTTP passam requisições pelo `Kernel` e verificam status, redirect,
  flash message, CSRF, escape da saída e páginas de erro, usando um
  repositório em memória e uma sessão em array, sem servidor nem banco.
- **Integração:** usam um banco separado, `citizens_test`, criado pelo mesmo
  script de inicialização. A tabela é zerada antes de cada teste.

## Portas e banco de dados

- Se a 8080 ou a 3306 já estiverem em uso, troque `APP_PORT` e `DB_PORT` no
  `.env` e rode `docker compose up -d` de novo. `DB_PORT` muda só a porta
  publicada no host: dentro da rede do Docker a aplicação sempre fala com
  `db:3306`.
- Para resetar o banco (apaga todos os dados e recria o schema na próxima
  subida):

  ```bash
  docker compose down -v
  docker compose up -d
  ```

- O schema está em `database/init/01-schema.sql`. O MySQL o executa
  automaticamente quando o volume é criado. Como um arquivo `.sql` não lê
  variáveis de ambiente, os nomes `citizens` (banco e usuário) e
  `citizens_test` estão fixos nele. Se mudar `DB_NAME`, `DB_TEST_NAME` ou
  `DB_USER` no `.env`, ajuste o script também.

## Estrutura

```
src/
  Domain/          regras de negócio puras: Citizen, CitizenRepository (interface),
                   value objects Cpf e FullName, exceções de domínio
  Application/     casos de uso: RegisterCitizen (+ DTO de entrada) e SearchCitizens
  Infrastructure/  detalhes técnicos: PDO/MySQL, repositório em memória,
                   HTTP (Request, Response, Router, Session), views, config, container
  Presentation/    Kernel (rotas + tratamento de erros) e CitizenController,
                   que traduz HTTP <-> casos de uso
public/            front controller (index.php), .htaccess e assets
templates/         views em PHP puro (layout, páginas e partials)
database/init/     schema criado na inicialização do MySQL
docker/app/        Dockerfile (PHP 8.5 + Apache)
tests/             Unit/ (domínio, casos de uso e HTTP, sem banco) e Integration/ (MySQL)
```

As dependências apontam sempre para dentro: o domínio não conhece nenhuma
outra camada, a aplicação conhece só o domínio, e a infraestrutura implementa
as interfaces definidas por eles. Os detalhes, com diagramas do fluxo de uma
requisição, estão em [docs/ARQUITETURA.md](docs/ARQUITETURA.md).

## Decisões e padrões

- **Value Object:** `Cpf` e `FullName` são imutáveis e validam a si mesmos na
  criação. Um CPF inválido não chega a existir como objeto, então o resto do
  código não precisa revalidar. O CPF é guardado só com dígitos e formatado
  (`000.000.000-00`) apenas na exibição.
- **Repository:** `CitizenRepository` é uma interface do domínio com duas
  implementações. `PdoCitizenRepository` serve a aplicação e os testes de
  integração. `InMemoryCitizenRepository` permite testar os casos de uso sem
  banco.
- **Use Case / Application Service:** cada ação do sistema é uma classe com um
  único método `handle()`. A regra "CPF é único" fica no caso de uso
  (`findByCpf` antes de inserir). A constraint `UNIQUE` do banco é a segunda
  barreira: o repositório converte o erro 1062 em
  `CitizenAlreadyExistsException`, o que cobre duas requisições simultâneas.
- **DTO:** `RegisterCitizenInput` leva os dados crus da requisição até o caso
  de uso sem acoplá-lo ao HTTP.
- **Dependency Injection (via construtor):** nenhuma classe cria as próprias
  dependências. `Container` é o único lugar que monta o grafo de objetos, de
  forma manual e explícita.
- **Front Controller:** `public/index.php` é o único ponto de entrada. O
  `.htaccess` reescreve as rotas para ele, e ele entrega a requisição ao
  `Kernel`, onde ficam o roteamento e o tratamento centralizado de erros. Como
  o `Kernel` é uma classe comum, os testes o exercitam sem servidor HTTP.
- **Post/Redirect/Get:** depois de um cadastro bem-sucedido, a resposta é um
  `303` para `/`. A mensagem de sucesso, com nome e CPF formatado, viaja numa
  flash message na sessão. Recarregar a página não reenvia o formulário.
- **Tratamento de erros:** exceções de domínio (CPF inválido, nome inválido,
  duplicidade, busca vazia) viram mensagens amigáveis no próprio formulário.
  Erros inesperados são registrados no log e exibidos como uma página 500
  genérica, sem stack trace.
- **Segurança:**
  - Toda saída passa pelo helper `e()` (`htmlspecialchars`).
  - Os formulários POST têm token CSRF, comparado com `hash_equals`.
  - O SQL usa somente prepared statements.
  - Na busca por nome, `%` e `_` são escapados e tratados como texto literal.
  - As credenciais vêm de variáveis de ambiente.
- **Validação:** o servidor é a fonte da verdade. O JavaScript repete o mesmo
  algoritmo de CPF apenas para dar feedback enquanto o usuário digita.
- **Busca:**
  - Um termo com 11 dígitos (com ou sem máscara) é tratado como CPF; qualquer
    outro, como parte do nome.
  - A collation `utf8mb4_unicode_ci` torna a busca por nome insensível a
    maiúsculas e acentos ("joao" encontra "João").
  - Os resultados vêm ordenados por nome.
- **PHP 8.5:**
  - O pipe operator aparece na normalização do nome, que é de fato uma cadeia
    de transformações.
  - `clone with` e `#[\NoDiscard]` aparecem em `Citizen::withId()`, um wither
    imutável cujo retorno nunca deve ser ignorado.
- **Estilo:** PER-CS, a evolução oficial do PSR-12 mantida pelo PHP-FIG. Ele
  inclui todas as regras do PSR-12 e acrescenta as de sintaxe mais nova, como
  arrow functions.

## Possíveis melhorias

- Paginação na busca por nome, para bases grandes.
- Edição e exclusão de cadastros.
- Busca por nome com índice FULLTEXT, ou por prefixo (`LIKE 'termo%'`), para
  aproveitar índice. O `LIKE '%termo%'` atual não usa o índice em `name`.
- Testes de ponta a ponta no navegador (por exemplo, com Playwright), cobrindo
  também a máscara e a validação de CPF em JavaScript.
- Pipeline de CI rodando testes, PHPStan e PHP-CS-Fixer a cada push.
- Migrations versionadas no lugar do script único de inicialização.
- Cabeçalhos de segurança (Content-Security-Policy, X-Frame-Options).
