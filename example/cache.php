<?php
/*
 * This file is part of the Cache package.
 *
 * (c) Unit6 <team@unit6websites.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require realpath(dirname(__FILE__) . '/../autoload.php');
require realpath(dirname(__FILE__) . '/../vendor/autoload.php');

use Unit6\Cache\Adapter\Predis\Pool as PredisPool;
use Unit6\Cache;

$key = 'foobar';
$value = ['example.com', uniqid()];
$ttl = 5;

$settings = [
    'scheme' => 'tcp',
    'host'   => 'redis.lan',
    'port'   => 6379
];

$client = new Predis\Client($settings);

$cache = new PredisPool($client);

$item = new Cache\Item($key, $value);

$item->expiresAfter($ttl);

$cache->save($item);

$item = $cache->getItem($key);

var_dump($item->get()); exit;