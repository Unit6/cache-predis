<?php
/*
 * This file is part of the Cache package.
 *
 * (c) Unit6 <team@unit6websites.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Unit6\Cache\Adapter\Predis\Pool as PredisPool;
use Unit6\Cache;

/**
 * Test Predis Pool
 *
 * Check for correct operation of the standard features.
 */
class PoolTest extends PHPUnit_Framework_TestCase
{
    private $cache;
    private $client;

    public function setUp()
    {
        $settings = [
            'scheme' => 'tcp',
            'host'   => REDIS_HOST,
            'port'   => REDIS_PORT
        ];

        $this->client = new Predis\Client($settings);

        $this->cache = new PredisPool($this->client);
    }

    public function tearDown()
    {
        $this->cache->clear();

        unset($this->client);
        unset($this->cache);
    }

    public function testSaveCacheItem()
    {
        $this->assertInstanceOf('Unit6\Cache\PoolInterface', $this->cache);

        $value = uniqid();

        $item = new Cache\Item('foobar', $value);

        #$this->assertEquals($value, $item->get());

        $result = $this->cache->save($item);

        $this->assertTrue($result);

        return $item->getKey();
    }

    /**
     * @depends testSaveCacheItem
     */
    public function testGetCachedItems($key)
    {
        $result = $this->cache->getItems([$key]);

        $this->assertCount(1, $result);
        $this->assertArrayHasKey($key, $result);
    }

    public function testDeferredSaveOfCacheItem()
    {
        $value = uniqid();

        $item = new Cache\Item('raboof', $value);

        $cache = new PredisPool($this->client);

        $result = $cache->saveDeferred($item);

        $this->assertTrue($result);

        return [$cache, $item];
    }

    /**
     * @depends testDeferredSaveOfCacheItem
     */
    public function testCommitDeferredSave(array $deferred)
    {
        list($cache, $item) = $deferred;

        #$this->assertFalse($cache->command('exists', $item->getKey()));

        $result = $cache->commit();

        $this->assertTrue($result);

        #$this->assertTrue($cache->command('exists', $item->getKey()));
    }
}