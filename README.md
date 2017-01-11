# Cache for Redis

A simple [PSR-6 compliant](http://www.php-fig.org/psr/psr-6/) cache adapter for Predis.

```php
use Unit6\Cache\Adapter\Predis\Pool;
use Unit6\Cache;

$directory = realpath(dirname(__FILE__) . '/storage');
$key = 'foobar';
$value = ['example.com', uniqid()];
$ttl = 5;

$settings = [
    'scheme' => 'tcp',
    'host'   => 'localhost',
    'port'   => '6379'
];

$client = new Predis\Client( $settings );

$cache = new Pool($client);

$item = new Cache\Item($key, $value);

$item->expiresAfter($ttl);

$cache->save($item);

var_dump($cache->getItem($key));
```

### License

This project is licensed under the MIT license -- see the `LICENSE.txt` for the full license details.
