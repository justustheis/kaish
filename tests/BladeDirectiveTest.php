<?php

// phpcs:disable PSR1.Methods.CamelCapsMethodName

namespace Tests;

use JustusTheis\Kaish\Kaishing;
use JustusTheis\Kaish\BladeDirective;

class BladeDirectiveTest extends TestCase
{
    protected $kaish;

    /** @test */
    public function it_sets_up_the_opening_cache_directive()
    {
        $directive = $this->createNewCacheDirective();

        $isCached = $directive->setUp('testView', $post = $this->makePost());

        $this->assertFalse($isCached);

        echo '<div>fragment</div>';

        $cachedFragment = $directive->tearDown();

        $this->assertEquals('<div>fragment</div>', $cachedFragment);
        $this->assertTrue($this->kaish->has('testView'.$post->getCacheKey()));
    }

    /** @test */
    public function it_can_use_a_string_as_the_cache_key()
    {
        $kaish = $this->prophesize(Kaishing::class);
        $directive = new BladeDirective($kaish->reveal());

        $kaish->has('foo', 'views')->shouldBeCalled();
        $directive->setUp('foo');

        ob_end_clean(); // Since we're not doing teardown.
    }

    /** @test */
    public function it_can_use_a_collection_as_the_cache_key()
    {
        $kaish = $this->prophesize(Kaishing::class);
        $directive = new BladeDirective($kaish->reveal());

        $collection = collect(['one', 'two']);
        $kaish->has('testKey'.md5($collection), 'views')->shouldBeCalled();
        $directive->setUp('testKey', $collection);

        ob_end_clean(); // Since we're not doing teardown.
    }

    /** @test */
    public function it_can_use_the_model_to_determine_the_cache_key()
    {
        $kaish = $this->prophesize(Kaishing::class);
        $directive = new BladeDirective($kaish->reveal());

        $post = $this->makePost();
        $kaish->has('testKey'.'Tests\Stubs\Post/1-'.$post->updated_at->timestamp, 'views')->shouldBeCalled();
        $directive->setUp('testKey', $post);

        ob_end_clean(); // Since we're not doing teardown.
    }

    /** @test */
    public function it_throws_an_exception_if_it_cannot_determine_the_cache_key()
    {
        $this->expectException(\Exception::class);
        $directive = $this->createNewCacheDirective();

        $directive->setUp('testKey', new \Tests\Stubs\UnCacheablePost);
    }

    protected function createNewCacheDirective()
    {
        $cache = new \Illuminate\Cache\Repository(
            new \Illuminate\Cache\ArrayStore
        );

        $this->kaish = new \JustusTheis\Kaish\Kaishing($cache);

        return new BladeDirective($this->kaish);
    }
}
