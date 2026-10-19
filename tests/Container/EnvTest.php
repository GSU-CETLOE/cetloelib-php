<?php

declare(strict_types=1);

namespace Cetloe\Tests\Container;

use Cetloe\Container\Env;
use Cetloe\Tests\Support\EnvironmentTestCase;
use Cetloe\Tests\Support\FixtureApp;

final class EnvTest extends EnvironmentTestCase
{
    public function testConstructorArgumentsPopulateApplicationProperties(): void
    {
        $env = FixtureApp::createEnv();

        self::assertSame(FixtureApp::dir(), $env->appDir);
        self::assertSame(FixtureApp::NAMESPACE, $env->appNamespace);
        self::assertSame(FixtureApp::NAME, $env->appName);
        self::assertSame('test', $env->appEnv);
        self::assertFalse($env->appDebug);
        self::assertArrayHasKey(FixtureApp::NAMESPACE . '\\FixtureDefinitionSource', $env->classRepo->classes);
    }


    public function testConstructorMirrorsApplicationValuesIntoProcessEnvironment(): void
    {
        FixtureApp::createEnv();

        self::assertSame(FixtureApp::dir(), $_ENV[Env::APP_DIR]);
        self::assertSame(FixtureApp::dir(), $_SERVER[Env::APP_DIR]);
        self::assertSame(FixtureApp::NAMESPACE, $_ENV[Env::APP_NAMESPACE]);
        self::assertSame(FixtureApp::NAME, $_ENV[Env::APP_NAME]);
        self::assertSame('test', $_ENV[Env::APP_ENV]);
    }


    public function testDotenvFileInApplicationDirectoryIsLoaded(): void
    {
        $env = FixtureApp::createEnv();

        self::assertSame('from-dotenv', $env['FIXTURE_VALUE']);
        self::assertSame('from-dotenv', $_ENV['FIXTURE_VALUE']);
    }


    public function testExistingProcessEnvironmentWinsOverDotenv(): void
    {
        $env = FixtureApp::createEnv(['FIXTURE_VALUE' => 'preset']);

        self::assertSame('preset', $env['FIXTURE_VALUE']);
    }


    public function testMissingArgumentsFallBackToProcessEnvironment(): void
    {
        $_ENV[Env::APP_DIR] = FixtureApp::dir();
        $_ENV[Env::APP_NAMESPACE] = FixtureApp::NAMESPACE;
        $_ENV[Env::APP_NAME] = 'from-env';
        $_ENV[Env::APP_ENV] = 'prod';

        $env = new Env();

        self::assertSame(FixtureApp::dir(), $env->appDir);
        self::assertSame('from-env', $env->appName);
        self::assertSame('prod', $env->appEnv);
    }


    public function testScalarProcessValuesAreStringified(): void
    {
        $_SERVER[Env::APP_NAME] = 42;

        $env = new Env(FixtureApp::dir(), FixtureApp::NAMESPACE, null, 'test');

        self::assertSame('42', $env->appName);
    }


    public function testApplicationNameDefaultsToLowercasedNamespace(): void
    {
        $env = new Env(FixtureApp::dir(), FixtureApp::NAMESPACE, null, 'test');

        self::assertSame('cetloe-tests-fixtures-app', $env->appName);
    }


    public function testEnvironmentDefaultsToDevWhenNothingSetsIt(): void
    {
        $env = new Env(FixtureApp::dir(), FixtureApp::NAMESPACE, FixtureApp::NAME);

        self::assertSame('dev', $env->appEnv);
    }


    public function testDebugFlagIsReadFromProcessEnvironment(): void
    {
        self::assertTrue(FixtureApp::createEnv([Env::APP_DEBUG => '1'])->appDebug);
    }


    public function testDebugFlagRequiresExactlyOne(): void
    {
        self::assertFalse(FixtureApp::createEnv([Env::APP_DEBUG => 'true'])->appDebug);
    }


    public function testCopyConstructorSharesLoadedStateWithoutReloading(): void
    {
        $original = FixtureApp::createEnv(['FIXTURE_COPY' => 'kept']);
        $_ENV['FIXTURE_COPY'] = 'changed after load';

        $copy = new Env($original);

        self::assertSame('kept', $copy['FIXTURE_COPY']);
        self::assertSame($original->appDir, $copy->appDir);
        self::assertSame($original->appNamespace, $copy->appNamespace);
        self::assertSame($original->appName, $copy->appName);
        self::assertSame($original->appEnv, $copy->appEnv);
        self::assertSame($original->appDebug, $copy->appDebug);
        self::assertSame($original->classRepo, $copy->classRepo);
    }


    public function testInvalidApplicationDirectoryIsRejected(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Missing or invalid env var: APP_DIR');
        new Env(FixtureApp::dir() . '/does-not-exist', FixtureApp::NAMESPACE);
    }


    public function testMissingNamespaceIsRejected(): void
    {
        unset($_ENV[Env::APP_NAMESPACE], $_SERVER[Env::APP_NAMESPACE]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Missing or invalid env var: APP_NAMESPACE');
        new Env(FixtureApp::dir());
    }


    public function testGetDirPathResolvesVariableOrDefault(): void
    {
        $env = FixtureApp::createEnv(['FIXTURE_DIR' => FixtureApp::dir() . '/src/']);

        self::assertSame(FixtureApp::dir() . '/src', $env->getDirPath('FIXTURE_DIR'));
        self::assertSame(FixtureApp::dir(), $env->getDirPath('UNSET_DIR', FixtureApp::dir()));
    }


    public function testGetDirPathRejectsUnsetVariable(): void
    {
        $env = FixtureApp::createEnv();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("'UNSET_DIR' is not set");
        $env->getDirPath('UNSET_DIR');
    }


    public function testGetDirPathRejectsFiles(): void
    {
        $env = FixtureApp::createEnv(['FIXTURE_DIR' => FixtureApp::dir() . '/.env']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("'FIXTURE_DIR' is not a valid directory");
        $env->getDirPath('FIXTURE_DIR');
    }


    public function testGetFilePathResolvesVariableOrDefault(): void
    {
        $dotenv = FixtureApp::dir() . '/.env';
        $env = FixtureApp::createEnv(['FIXTURE_FILE' => $dotenv]);

        self::assertSame($dotenv, $env->getFilePath('FIXTURE_FILE'));
        self::assertSame($dotenv, $env->getFilePath('UNSET_FILE', $dotenv));
    }


    public function testGetFilePathRejectsUnsetVariable(): void
    {
        $env = FixtureApp::createEnv();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("'UNSET_FILE' is not set");
        $env->getFilePath('UNSET_FILE');
    }


    public function testGetFilePathRejectsDirectories(): void
    {
        $env = FixtureApp::createEnv(['FIXTURE_FILE' => FixtureApp::dir()]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('is not a valid file path');
        $env->getFilePath('FIXTURE_FILE');
    }


    public function testGetStringArrayMergesDefaultsOverJsonVariable(): void
    {
        $env = FixtureApp::createEnv();

        self::assertSame(['alpha' => '1', 'beta' => '2'], $env->getStringArray('FIXTURE_JSON'));
        self::assertSame(
            ['alpha' => '1', 'beta' => 'override', 'gamma' => '3'],
            $env->getStringArray('FIXTURE_JSON', ['beta' => 'override', 'gamma' => 3])
        );
        self::assertSame(['x' => 'y'], $env->getStringArray('UNSET_JSON', (object)['x' => 'y']));
    }
}
