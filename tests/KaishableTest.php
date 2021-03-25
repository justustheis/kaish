<?php

// phpcs:disable PSR1.Methods.CamelCapsMethodName

namespace Tests;

class KaishableTest extends TestCase
{
    /** @test */
    public function it_gets_a_unique_cache_key_for_an_eloquent_model()
    {
        $model = $this->makePost();

        $this->assertEquals(
            'Tests\Stubs\Post/1-'.$model->updated_at->timestamp,
            $model->getCacheKey()
        );
    }
}
