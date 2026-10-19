<?php

declare(strict_types=1);

namespace mjfklib\Logger;

trait LoggerAwareTrait
{
    use \Psr\Log\LoggerAwareTrait;


    /**
     * @param float $start
     * @return string
     */
    protected function getElapsedTime(float $start): string
    {
        $elapsed = microtime(true) - $start;
        $elapsedTime = intval($elapsed);
        return sprintf(
            "%'.02d:%'.02d:%'.02d.%s",
            intval(gmdate("H", $elapsedTime)),
            intval(gmdate("i", $elapsedTime)),
            intval(gmdate("s", $elapsedTime)),
            explode(".", number_format(round($elapsed - $elapsedTime, 3), 3))[1]
        );
    }


    /**
     * @param mixed[] $results
     * @return string
     */
    protected function formatLogResults(array $results): string
    {
        $results = array_map(
            fn ($v) => strval($v),
            array_filter(
                $results,
                fn ($v) => is_scalar($v)
            )
        );

        return implode(
            "; ",
            array_map(
                fn ($k, $v) => "{$k}: {$v}",
                array_keys($results),
                array_values($results)
            )
        );
    }
}
