<?php

declare(strict_types=1);

namespace Tests\Unit\Application;

use App\Application\UseCase\RegisterCitizen\RegisterCitizen;
use App\Application\UseCase\RegisterCitizen\RegisterCitizenInput;
use App\Domain\Exception\CitizenAlreadyExistsException;
use App\Domain\Exception\InvalidCpfException;
use App\Domain\Exception\InvalidNameException;
use App\Domain\Shared\ValueObject\Cpf;
use App\Infrastructure\Persistence\InMemoryCitizenRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RegisterCitizenTest extends TestCase
{
    private InMemoryCitizenRepository $citizens;

    private RegisterCitizen $registerCitizen;

    protected function setUp(): void
    {
        $this->citizens = new InMemoryCitizenRepository();
        $this->registerCitizen = new RegisterCitizen($this->citizens);
    }

    #[Test]
    public function itRegistersACitizen(): void
    {
        $citizen = $this->registerCitizen->handle(new RegisterCitizenInput('  Maria   Silva ', '529.982.247-25'));

        self::assertNotNull($citizen->id);
        self::assertSame('Maria Silva', $citizen->name->value());
        self::assertSame('52998224725', $citizen->cpf->value());
        self::assertEquals($citizen, $this->citizens->findByCpf(Cpf::fromString('52998224725')));
    }

    #[Test]
    public function itRejectsADuplicatedCpf(): void
    {
        $this->registerCitizen->handle(new RegisterCitizenInput('Maria Silva', '52998224725'));

        $this->expectExceptionObject(CitizenAlreadyExistsException::withCpf(Cpf::fromString('52998224725')));

        $this->registerCitizen->handle(new RegisterCitizenInput('João Souza', '529.982.247-25'));
    }

    #[Test]
    public function itRejectsAnInvalidCpf(): void
    {
        $this->expectException(InvalidCpfException::class);

        $this->registerCitizen->handle(new RegisterCitizenInput('Maria Silva', '111.111.111-11'));
    }

    #[Test]
    public function itRejectsAnInvalidName(): void
    {
        $this->expectException(InvalidNameException::class);

        $this->registerCitizen->handle(new RegisterCitizenInput('Maria', '52998224725'));
    }
}
