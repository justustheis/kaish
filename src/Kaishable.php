<?php

namespace JustusTheis\Kaish;

trait Kaishable
{
    /**
     * This calculates a unique cache key for the model instance. We do this
     * based on the updated_at column. So when the model instance gets
     * updated the cache will invalidate itself automatically.
     *
     * @return string
     */
    public function getCacheKey() :string
    {
        return sprintf(
            '%s/%s-%s',
            get_class($this),
            $this->getKey(),
            $this->updated_at->timestamp
        );
    }
}
