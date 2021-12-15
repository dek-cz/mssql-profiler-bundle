# mssql-profiler-bundle
adding  mssql procedure panel to profiler
Installation
------------

Via Composer:

```sh
$ composer require --dev dek-cz/mssql-profiler-bundle
```


Usage
-----
bundles.php
```php
Dekcz\MssqlProfiler\MssqlProfilerBundle::class => ['dev' => true, 'test' => true],
```

packages/dev/mssql_profiler.yaml, packages/test/mssql_profiler.yaml
```yaml
mssql_profiler:
    web_profiler: true
    client_definition: 'App\MyMssqlConnector'
```
App/MyMssqlConnector.php
```php
use Dekcz\MssqlProfiler\DataCollector\MssqlCollectorInterface;
use Dekcz\MssqlProfiler\DataCollector\MssqlCollectorTrait;

class MyMssqlConnector implements MssqlCollectorInterface
{
..
    use MssqlCollectorTrait;
..
}
```
Toolbar

![Alt text](src/Resources/public/preview1.jpg?raw=true "Toolbar")

Profiler

![Alt text](src/Resources/public/preview2.jpg?raw=true "Profiler")
