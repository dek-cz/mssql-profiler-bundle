<?php

declare(strict_types = 1);

namespace Dekcz\MssqlProfiler\DataCollector;

trait MssqlCollectorTrait
{

    /**
     * @var MssqlCollector
     */
    private MssqlCollector $mssqlCollector;

    public function setMssqlCollector(MssqlCollector $mssqlCollector): void
    {
        $this->mssqlCollector = $mssqlCollector;
    }

}
