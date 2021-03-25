<?php

// phpcs:disable PSR1.Methods.CamelCapsMethodName

namespace Tests;

use JustusTheis\Kaish\Kaishing;

class KaishingTest extends TestCase
{
    /** @test */
    public function it_caches_the_given_key_based_on_a_model()
    {
        $post = $this->makePost();

        $cache = new \Illuminate\Cache\Repository(
            new \Illuminate\Cache\ArrayStore
        );

        $cache = new Kaishing($cache);

        $cache->put($post, '<div>view fragment</div>');

        $this->assertTrue($cache->has($post->getCacheKey()));
        $this->assertTrue($cache->has($post));
    }

    /** @test */
    public function it_caches_the_given_key_based_on_a_string()
    {
        $cache = new \Illuminate\Cache\Repository(
            new \Illuminate\Cache\ArrayStore
        );

        $cache = new Kaishing($cache);

        $cache->put('someKey', '<div>view fragment</div>');

        $this->assertTrue($cache->has('someKey'));
    }
}
