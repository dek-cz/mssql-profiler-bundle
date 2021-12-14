<?php

declare(strict_types = 1);

namespace Dekcz\MssqlProfiler\DataCollector;

interface MssqlCollectorInterface
{

    public function setMssqlCollector(MssqlCollector $mssqlCollector): void;

}
