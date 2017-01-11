<?php
/*
 * This file is part of the Cache package.
 *
 * (c) Unit6 <team@unit6websites.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Unit6\Cache\Adapter\Predis;

use Predis\Client;

use Unit6\Cache\Item;
use Unit6\Cache\AbstractCacheItemPool;

/**
 * File Cache Adapter
 *
 * Using the filesystem as a persistence layer.
 */
class Pool extends AbstractCacheItemPool
{
    /**
     * Cache Client
     *
     * Directory used as storage engine.
     *
     * @var Predis\Client
     */
    protected $client;

    /**
     * Cache default options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Setup Cache Pool
     *
     * Configure a Predis storage engine.
     *
     * @param Client $client  Predis client connection.
     * @param array  $options List of parameters.
     *
     * @return void
     */
    public function __construct(Client $client, array $options = [])
    {
        $this->options = array_merge($this->options, $options);
        $this->setClient($client);
    }

    /**
     * Get Predis Client
     *
     * @return Predis\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set Predis Client
     *
     * @param Predis\Client $client
     *
     * @return void
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Send Redis Commands
     *
     * @return string
     */
    public function command(/* name[, arguments] */)
    {
        $arguments = func_get_args();
        $arguments_num = func_num_args();

        if ( ! $arguments_num) {
            throw new InvalidArgumentException('Use a defined command name followed by any arguments');
        }

        $name = array_shift($arguments);
        return call_user_func_array([$this->getClient(), $name], $arguments);
    }

    /**
     * Persist cache item in cache.
     *
     * @param string             $key  Cache item identifier.
     * @param CacheItemInterface $item Cache item to store.
     * @param int|null           $ttl  Time-to-Live in seconds from now.
     *
     * @return bool true if saved
     */
    protected function storeItemInCache($key, Item $item, $ttl)
    {
        //if ($this->command('get', $key)) {
        //    $this->command('del', $key);
        //}

        $data = [
            ($ttl === null ? null : time() + $ttl),
            $item->get()
        ];

        $value = $this->encode($data);

        if ($ttl) {
            return 'OK' === $this->command('setex', $key, $ttl, $value)->getPayload();
        } else {
            return 'OK' === $this->command('set', $key, $value)->getPayload();
        }
    }

    /**
     * Fetch an object from the cache implementation.
     *
     * @param string $key
     *
     * @return array with [isHit, value]
     */
    protected function fetchObjectFromCache($key)
    {
        $value = $this->command('get', $key);

        if (null === $value) {
            return [false, null];
        }

        $data = $this->decode($value);

        if ($data[0] !== null && time() > $data[0]) {
            $this->clearOneObjectFromCache($key);
            return [false, null];
        }

        return [true, $data[1]];
    }

    /**
     * Flush all objects from cache.
     *
     * @return bool false if error
     */
    protected function clearAllObjectsFromCache()
    {
        return 'OK' === $this->command('flushdb')->getPayload();
    }

    /**
     * Remove one object from cache.
     *
     * @param string $key
     *
     * @return bool
     */
    protected function clearOneObjectFromCache($key)
    {
        return $this->command('del', $key) >= 0;
    }

    /**
     * Encode the string
     *
     * @param mixed $value Cache value to serialize.
     *
     * @return string
     */
    private function encode($value)
    {
        return json_encode($value);
    }

    /**
     * Decode the string
     *
     * @param string $value Cache value to serialize.
     *
     * @return array
     */
    private function decode($value)
    {
        return json_decode($value, $assoc = true);
    }
}