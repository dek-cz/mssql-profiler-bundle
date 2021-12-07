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
services:
        Dekcz\MssqlProfiler\DataCollector\MssqlCollector:
            autoconfigure: true
            autowire: true
            tags:
                -
                    name: mssql_collector
    #                 must match the value returned by the getName() method
                    id: 'mssql'
    #                 optional template (it has more priority than the value returned by getTemplate())
#                    template: 'mssql_collector/template.html.twig'
                    template: "@MssqlProfiler/Collector/mssql.html.twig"
    #                 optional priority (positive or negative integer; default = 0)
    #                 priority: 300

mssql_profiler:
    web_profiler: true
```