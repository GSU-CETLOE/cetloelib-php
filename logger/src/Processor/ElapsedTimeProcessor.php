<?php

declare(strict_types=1);

namespace mjfklib\Logger\Processor;

use Monolog\LogRecord;
use Monolog\Processor\ProcessorInterface;

class ElapsedTimeProcessor implements ProcessorInterface
{
    private float $logStartTime;


    public function __construct()
    {
        $this->logStartTime = microtime(true);
    }


    /**
     * @param LogRecord $record
     * @return LogRecord
     */
    public function __invoke(LogRecord $record): LogRecord
    {
        $elapsedTime = microtime(true) - $this->logStartTime;
        $elapsed = intval($elapsedTime);
        $record->extra['elapsed'] = sprintf(
            "%'.02d:%'.02d:%'.02d.%s",
            intval(gmdate("H", $elapsed)),
            intval(gmdate("i", $elapsed)),
            intval(gmdate("s", $elapsed)),
            explode(".", number_format(round($elapsedTime - $elapsed, 3), 3))[1]
        );
        return $record;
    }
}
