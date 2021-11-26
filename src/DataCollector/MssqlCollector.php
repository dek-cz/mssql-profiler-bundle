<?php

namespace Dekcz\MssqlProfiler\DataCollector;

use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use DekApps\MssqlProcedure\Event\Event as SQLEvent;

class MssqlCollector extends AbstractDataCollector
{

    public function __construct()
    {
        $that = $this;
        $that->data['mssql_queries'] = [];
        $that->data['mssql_exec_time'] = [];
        SQLEvent::instance()->bind(SQLEvent::ON_AFTER_PROC_EXECUTE, function ($connection, $stmt, $key, $procname, $executionTime) use ($that)
        {
            $exeTime = (float) number_format($executionTime * 1000, 3, '.', '');
            $out = $connection->getOutputs();
            foreach ($out as $o) {
                switch ($o['var_type'])
                {
                    case SQLSRV_SQLTYPE_INT :
                        $o['var_name'] = 'int';
                        break;
                    case SQLSRV_SQLTYPE_VARCHAR :
                        $o['var_name'] = 'varchar(max)';
                        break;
                    case SQLSRV_SQLTYPE_FLOAT :
                        $o['var_name'] = 'float';
                        break;
                    default;
                        $o['var_name'] = 'other';
                        break;
                }
            }
            $conn = array(
                'DB' => $connection->getDbName(),
                'NAME' => $connection->getName(),
                'IN' => $connection->getInputsConfig(),
                'OUT' => $out,
                'TIME' => $exeTime,
                'RESULT' => sqlsrv_num_rows($stmt),
                'ENUM_PARAM_OUT' => SQLSRV_PARAM_OUT
            );

            if (!preg_match('/^\[[a-zA-Z0-9_]+\]\./', $conn['NAME'])) {
                $conn['NAME'] = preg_replace('/^([a-zA-Z0-9_]+)\./', '[$1].', $conn['NAME']);
            }

            foreach ($conn['IN'] as $key => &$in) {
                $in['php_type'] = gettype($in['var']);
                if (is_string($in['var'])) {
                    $in['var'] = 'N\'' . $in['var'] . '\'';
                } elseif ($in['var'] instanceof \DateTimeInterface) {
                    $in['var'] = $in['var']->format(\DateTimeInterface::ISO8601);
                } elseif (is_null($in['var'])) {
                    $in['var'] = 'NULL';
                }

                if ($in['type'] === SQLSRV_PARAM_OUT) {
                    $conn['OUT'][$in['var_name']] = [
                        'value' => $conn['OUT'][$in['var_name']],
                        'param_name' => $in['param_name'],
                        'var_name' => $in['var_name'],
                        'var_type' => $in['var'],
                    ];
                }
            }

            $output = [];
            foreach ($conn['OUT'] as $key => &$out) {
                if (is_string($out)) {
                    $out = htmlspecialchars($out);
                }

                $output[] = $key . ': <em>(' . gettype($out['value']) . ')</em> ' . $out['value'];
            }

            $that->data['mssql_queries'][] = $conn;
            $that->data['mssql_exec_time'][] = $exeTime;
        });
        SQLEvent::instance()->bind(SQLEvent::ON_ERROR_PROC_EXECUTE, function ($conn, $error) use ($that)
        {
            throw new \Exception($error[0]['message'], $error[0]['code']);
        });
    }

    public function collect(Request $request, Response $response, \Throwable $exception = null)
    {
        
    }

    public static function getTemplate(): ?string
    {
//        var_dump(__DIR__ . '/../Resources/views/Collector/mssql.html.twig');exit;
        return "@MssqlProfiler/Collector/mssql.html.twig";
    }

    public function getDump(): array
    {
        return $this->data['mssql_queries'];
    }

    public function getCountConn(): int
    {
        return count($this->data['mssql_queries']);
    }

    public function getTime(): float
    {
        return array_sum($this->data['mssql_exec_time']);
    }

}
