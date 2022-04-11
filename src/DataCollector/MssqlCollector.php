<?php
declare(strict_types = 1);

namespace Dekcz\MssqlProfiler\DataCollector;

use DateTimeInterface;
use DekApps\MssqlProcedure\Event\Event as SQLEvent;
use Exception;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class MssqlCollector extends AbstractDataCollector
{

    public function __construct()
    {
        $this->data['mssql_queries'] = [];
        $this->data['mssql_exec_time'] = [];
    }

    public function registerMssqlEvents(): void
    {
        $that = $this;
        SQLEvent::instance()->bind(SQLEvent::ON_AFTER_PROC_EXECUTE, function ($connection, $stmt, $key, $procname, $executionTime) use ($that) {
            // inherited mojo
            $exeTime = (float) number_format($executionTime * 1000, 3, '.', '');
            $out = $connection->getOutputs();

            $conn = [
                'DB' => $connection->getDbName(),
                'NAME' => $connection->getName(),
                'IN' => $connection->getInputsConfig(),
                'OUT' => [],
                'TIME' => $exeTime,
                'RESULT' => sqlsrv_num_rows($stmt),
                'ENUM_PARAM_OUT' => SQLSRV_PARAM_OUT,
            ];

            if (!preg_match('/^\[[a-zA-Z0-9_]+\]\./', $conn['NAME'])) {
                $conn['NAME'] = preg_replace('/^([a-zA-Z0-9_]+)\./', '[$1].', $conn['NAME']);
            }

            foreach ($conn['IN'] as $key => &$in) {
                $in['php_type'] = gettype($in['var']);
                if (is_string($in['var'])) {
                    $in['var'] = 'N\'' . $in['var'] . '\'';
                } elseif ($in['var'] instanceof DateTimeInterface) {
                    $in['var'] = $in['var']->format(DateTimeInterface::ISO8601);
                } elseif ($in['var'] === null) {
                    $in['var'] = 'NULL';
                }
                if ($in['type'] === SQLSRV_PARAM_OUT) {
                    $conn['OUT'][$in['var_name']] = [
                        'value' => $out[$in['var_name']],
                        'param_name' => $in['param_name'],
                        'var_name' => $in['var_name'],
                        'var_type' => $in['var'],
                        'php_type' => gettype($out[$in['var_name']]),
                    ];
                }
            }

            foreach ($conn['OUT'] as $k => &$o) {
                switch ($o['var_type'])
                {
                    case SQLSRV_SQLTYPE_INT:
                        $o['var_name_text'] = 'int';
                        break;
                    case SQLSRV_SQLTYPE_VARCHAR:
                        $o['var_name_text'] = 'varchar(max)';
                        break;
                    case SQLSRV_SQLTYPE_FLOAT:
                        $o['var_name_text'] = 'float';
                        break;
                    default:
                        $o['var_name_text'] = 'other';
                        break;
                }
            }
            $that->data['mssql_queries'][] = $conn;
            $that->data['mssql_exec_time'][] = $exeTime;
        });
//        SQLEvent::instance()->bind(SQLEvent::ON_ERROR_PROC_EXECUTE, function ($conn, $error) {
//            //@todo: errors
//            throw new Exception($error[0]['message'], $error[0]['code']);
//        });
    }

    public function collect(Request $request, Response $response, ?Throwable $exception = null): void
    {
//        see mssql events
    }

    public static function getTemplate(): ?string
    {
        return '@MssqlProfiler/Collector/mssql.html.twig';
    }

    /**
     * @return array<int, array>
     */
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
