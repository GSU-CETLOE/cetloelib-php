<?php

declare(strict_types=1);

namespace mjfklib\Container;

use mjfklib\Utils\ArrayValue;
use Symfony\Component\Dotenv\Dotenv;

/** @extends \ArrayObject<string,string> */
class Env extends \ArrayObject
{
    public const APP_DIR = 'APP_DIR';
    public const APP_NAMESPACE = 'APP_NAMESPACE';
    public const APP_ENV = 'APP_ENV';
    public const APP_NAME = 'APP_NAME';
    public const APP_DEBUG = 'APP_DEBUG';


    public string $appDir;
    public string $appNamespace;
    public string $appEnv;
    public string $appName;
    public bool $appDebug;
    public ClassRepository $classRepo;


    /**
     * @param string|Env|null $appDirEnv
     * @param string|null $appNamespace
     * @param string|null $appName
     * @param string|null $appEnv
     */
    final public function __construct(
        string|Env|null $appDirEnv = null,
        string|null $appNamespace = null,
        string|null $appName = null,
        string|null $appEnv = null
    ) {
        $appDirEnv = $this->preLoad($appDirEnv, $appNamespace, $appName, $appEnv);

        parent::__construct($this->load($appDirEnv));

        $this->postLoad($appDirEnv instanceof Env ? $appDirEnv : null);
    }


    /**
     * @param string|Env|null $appDirEnv
     * @param string|null $appNamespace
     * @param string|null $appName
     * @param string|null $appEnv
     * @return string|Env|null
     */
    protected function preLoad(
        string|Env|null $appDirEnv,
        string|null $appNamespace,
        string|null $appName,
        string|null $appEnv,
    ): string|Env|null {
        if ($appDirEnv instanceof Env) {
            return $appDirEnv;
        }

        $appDirEnv ??= $this->getEnv(self::APP_DIR);
        $appDirEnv = is_string($appDirEnv) ? realpath($appDirEnv) : false;
        $appDirEnv = is_string($appDirEnv) && is_dir($appDirEnv) ? $appDirEnv : null;
        $this->setEnv(self::APP_DIR, $appDirEnv);

        $appNamespace ??= $this->getEnv(self::APP_NAMESPACE);
        $this->setEnv(self::APP_NAMESPACE, $appNamespace);

        $appName ??= $this->getEnv(self::APP_NAME);
        $appName ??= is_string($appNamespace) ? str_replace('\\', '-', strtolower($appNamespace)) : null;
        $this->setEnv(self::APP_NAME, $appName);

        $appEnv ??= $this->getEnv(self::APP_ENV);
        $this->setEnv(self::APP_ENV, $appEnv);

        return $appDirEnv;
    }


    /**
     * @param string|Env|null $appDirEnv
     * @return array<string,string>
     */
    protected function load(string|Env|null $appDirEnv): array
    {
        if ($appDirEnv instanceof Env) {
            return $appDirEnv->getArrayCopy();
        }

        if (is_string($appDirEnv) && is_dir($appDirEnv)) {
            (new Dotenv())->loadEnv($appDirEnv . "/.env");
        }

        return ArrayValue::convertToStringArray($_ENV);
    }


    /**
     * @param Env|null $env
     * @return void
     */
    protected function postLoad(Env|null $env): void
    {
        if ($env !== null) {
            $this->appDir = $env->appDir;
            $this->appNamespace = $env->appNamespace;
            $this->appName = $env->appName;
            $this->appEnv = $env->appEnv;
            $this->appDebug = $env->appDebug;
            $this->classRepo = $env->classRepo;
        } else {
            $this->appDir = $this[self::APP_DIR] ?? throw new \RuntimeException(sprintf(
                "Missing or invalid env var: %s",
                self::APP_DIR
            ));
            $this->appNamespace = $this[self::APP_NAMESPACE] ?? throw new \RuntimeException(sprintf(
                "Missing or invalid env var: %s",
                self::APP_NAMESPACE
            ));
            $this->appName = $this[self::APP_NAME] ?? throw new \RuntimeException(sprintf(
                "Missing or invalid env var: %s",
                self::APP_NAME
            ));
            $this->appEnv = $this[self::APP_ENV] ?? throw new \RuntimeException(sprintf(
                "Missing or invalid env var: %s",
                self::APP_ENV
            ));
            $this->appDebug = ($this[self::APP_DEBUG] ?? '0') === '1';
            $this->classRepo = new ClassRepository($this->appDir, $this->appNamespace);
        }
    }


    /**
     * @param string $name
     * @return string|null
     */
    protected function getEnv(string $name): ?string
    {
        $value = $_SERVER[$name] ?? $_ENV[$name] ?? null;
        return match (true) {
            is_string($value) => $value,
            is_scalar($value) => strval($value),
            default => null
        };
    }


    /**
     * @param string $name
     * @param string|null $value
     * @return void
     */
    protected function setEnv(
        string $name,
        ?string $value = null
    ): void {
        if (is_string($value)) {
            $_SERVER[$name] = $_ENV[$name] = $value;
        } else {
            unset($_SERVER[$name], $_ENV[$name]);
        }
    }


    /**
     * @param string $name
     * @param string|null $default
     * @return string
     */
    public function getDirPath(
        string $name,
        string|null $default = null
    ): string {
        $dirPath = realpath(
            $this[$name]
                ?? $default
                ?? throw new \RuntimeException(sprintf("'%s' is not set", $name))
        );

        return is_string($dirPath) && is_dir($dirPath)
            ? $dirPath
            : throw new \RuntimeException(sprintf("'%s' is not a valid directory", $name));
    }


    /**
     * @param string $name
     * @param string|null $default
     * @return string
     */
    public function getFilePath(
        string $name,
        string $default = null
    ): string {
        $filePath = realpath(
            $this[$name]
                ?? $default
                ?? throw new \RuntimeException(sprintf("'%s' is not set", $name))
        );

        return is_string($filePath) && is_file($filePath)
            ? $filePath
            : throw new \RuntimeException(sprintf("'%s' is not a valid file path", $filePath));
    }


    /**
     * @param string $name
     * @param mixed[]|object $defaultValues
     * @return array<string,string>
     */
    public function getStringArray(
        string $name,
        array|object $defaultValues = []
    ): array {
        return array_merge(
            ArrayValue::convertToStringArray($this[$name] ?? []),
            ArrayValue::convertToStringArray($defaultValues)
        );
    }
}
