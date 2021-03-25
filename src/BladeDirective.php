<?php

namespace JustusTheis\Kaish;

use Exception;

class BladeDirective
{
    /**
     * The cache instance.
     *
     * @var RussianCaching
     */
    protected $cache;

    /**
     * A list of cache keys.
     *
     * @param array $keys
     */
    protected $keys = [];

    /**
     * A list of cache tags.
     *
     * @param array $tags
     */
    protected $tags = [];

    /**
     * Create a new instance.
     *
     * @param RussianCaching $cache
     */
    public function __construct(Kaishing $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Handle the @cache setup.
     *
     * @param string     $key
     * @param mixed|null $model
     * @param string     $tags
     *
     * @return bool
     * @throws \Exception
     */
    public function setUp(string $key, $model = null, string $tags = 'views')
    {
        $this->keys[] = $key = $this->normalizeKey($key, $model);
        $this->tags[] = $tags;
        ob_start();

        return $this->cache->has($key, $tags);
    }

    /**
     * Handle the @endcache teardown.
     *
     * @return mixed
     */
    public function tearDown()
    {
        return $this->cache->put(
            array_pop($this->keys),
            ob_get_clean(),
            array_pop($this->tags)
        );
    }

    /**
     * Normalize the cache key.
     *
     * @param string $key
     * @param null   $model
     *
     * @return string
     * @throws \Exception
     */
    protected function normalizeKey(string $key, $model = null) :string
    {
        if (! is_string($key)) {
            throw new Exception('The key must be of type string');
        }

        // If the user wants to provide their own cache
        // key, we'll opt for that.
        if (is_string($key) && $model == null) {
            return $key;
        }

        // Otherwise we'll try to use the model to calculate
        // the cache key, itself.
        if (is_object($model) && method_exists($model, 'getCacheKey')) {
            return $key.$model->getCacheKey();
        }

        // If we're dealing with a collection, we'll
        // use a hashed version of its contents.
        if ($model instanceof \Illuminate\Support\Collection) {
            return $key.md5($model);
        }

        throw new Exception('Could not determine an appropriate cache key.');
    }
}
