<?php

declare(strict_types=1);

namespace Cetloe\Tests\Container;

use Cetloe\Console\ConsoleDefinitionSource;
use Cetloe\Container\ClassRepository;
use Cetloe\Container\ContainerFactory;
use Cetloe\Container\Env;
use Cetloe\Tests\Fixtures\App\FixtureDefinitionSource;
use Cetloe\Tests\Fixtures\Shared\SharedDefinitionSource;
use Cetloe\Tests\Support\EnvironmentTestCase;
use Cetloe\Tests\Support\FixtureApp;
use Cetloe\Tests\Support\TempDir;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application;

final class ContainerFactoryTest extends EnvironmentTestCase
{
    private string $logDir;


    protected function setUp(): void
    {
        parent::setUp();
        $this->logDir = TempDir::create();
    }


    protected function tearDown(): void
    {
        TempDir::remove($this->logDir);
        parent::tearDown();
    }


    public function testContainerExposesEnvAndClassRepository(): void
    {
        $env = FixtureApp::createEnv();
        $container = (new ContainerFactory($env))->create();

        self::assertSame($env, $container->get(Env::class));
        self::assertSame($env->classRepo, $container->get(ClassRepository::class));
    }


    public function testDefinitionSourcesInTheApplicationAreDiscoveredAutomatically(): void
    {
        $container = (new ContainerFactory(FixtureApp::createEnv()))->create();

        self::assertSame(FixtureApp::NAME, $container->get(FixtureDefinitionSource::APP_ONLY));
    }


    public function testSourcesReferencedByGetSourcesAreIncludedAndApplicationDefinitionsWin(): void
    {
        $container = (new ContainerFactory(FixtureApp::createEnv()))->create();

        self::assertSame('shared value', $container->get(SharedDefinitionSource::SHARED_ONLY));
        self::assertSame('hello from app', $container->get(FixtureDefinitionSource::GREETING));
    }


    public function testGlobalSourcesAreAddedWithTheirOwnDependencies(): void
    {
        $env = FixtureApp::createEnv(['LOG_DIR' => $this->logDir]);
        $container = (new ContainerFactory($env))->create([ConsoleDefinitionSource::class]);

        self::assertInstanceOf(Application::class, $container->get(Application::class));
        self::assertInstanceOf(LoggerInterface::class, $container->get(LoggerInterface::class));
    }
}
