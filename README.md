# kaish is a russian doll caching system with blade support for Laravel 10
![Maintenance](https://img.shields.io/maintenance/yes/2023)
![Packagist](https://img.shields.io/packagist/dt/justustheis/kaish)
![Packagist Version](https://img.shields.io/packagist/v/justustheis/kaish)
![GitHub issues](https://img.shields.io/github/issues/justustheis/kaish)
![GitHub](https://img.shields.io/github/license/justustheis/kaish)

![Kaish](https://user-images.githubusercontent.com/7760415/112481392-41d47780-8d77-11eb-90a9-4f814fde28bc.png)

> This package is highly inspired by and partly forked from [laracasts/matryoshka](https://github.com/laracasts/matryoshka)

## Installation
> **Requires:**
- **[PHP ^8.0](https://php.net/releases/)**
- **[Laravel ^8.0](https://github.com/laravel/laravel)**

### Step 1: Composer

From the command line, run:

```command
composer require justustheis/kaish
```

### Step 2: Service Provider

For your Laravel app, open `config/app.php` and, within the `providers` array, append:

```php
JustusTheis\Kaish\KaishServiceProvider::class
```

This will bootstrap the package into Laravel.

### Step 3: Cache Driver

For this package to function properly, you must use a Laravel cache driver that supports tagging (like `Cache::tags('foo')`). Drivers such as Memcached and Redis support this feature.

Check your `.env` file, and ensure that your `CACHE_DRIVER` choice accomodates this requirement:

```env
CACHE_DRIVER=memcached
```

## Usage

### The Basics

With the package now installed, you may use the provided `@cache` Blade directive anywhere in your views, like so:

```html
@cache('my-cache-key')
    <div>
        <h1>Hello World</h1>
    </div>
@endcache
```

By surrounding this block of HTML with the `@cache` and `@endcache` directives, we're asking the package to cache the given HTML. Now this example is trivial, however, you can imagine a more complex view that includes various nested caches, as well as lazy-loaded relationship calls that trigger additional database queries. After the initial page load that caches the HTML fragment, each subsequent refresh will instead pull from the cache. As such, those additional database queries will never be executed.

Please keep in mind that, in production, this will cache the HTML fragment "forever." For local development, on the other hand, we'll automatically flush the relevant cache for you each time you refresh the page. That way, you may update your views and templates however you wish, without needing to worry about clearing the cache manually.

Now because your production server will cache the fragments forever, you'll want to add a step to your deployment process that clears the relevant cache.

```php
Cache::tags('views')->flush();
```

### Caching Models

While you're free to hard-code any string for the cache key, the true power of Russian-Doll caching comes into play when we use a timestamp-based approach.

Consider the following fragment:

```html
@cache('my-cache-key', $post)
    <article>
        <h2>{{ $post->title }}></h2>
        <p>Written By: {{ $post->author->username }}</p>

        <div class="body">{{ $post->body }}</div>
    </article>
@endcache
```

In this example, we're passing the `$post` object as well as a string based key to the `@cache` directive. The package will then look for a `getCacheKey()` method on the model. We've already done that work for you; just have your Eloquent model use the `JustusTheis\Kaish\Kaishable` trait, like so:

```php
use JustusTheis\Kaish\Kaishable;

class Post extends Eloquent
{
    use Kaishable;
}
```

Alternatively, you may use this trait on a parent class that each of your Eloquent models extend.

That should do it! Now, the cache key for this fragment will include the object's `id` and `updated_at` timestamp: `my-cache-key.`App\Post/1-13241235123`.

> The key is that, because we factor the `updated_at` timestamp into the cache key, whenever you update the given post, the cache key will change. This will then, in effect, bust the cache!

#### Touching

In order for this technique to work properly, it's vital that we have some mechanism to alert parent relationships (and subsequently bust parent caches) each time a model is updated. Here's a basic workflow:

1. Model is updated in the database.
2. Its `updated_at` timestamp is refreshed, triggering a new cache key for the instance.
3. The model "touches" (or pings) its parent.
4. The parent's `updated_at` timestamp, too, is updated, which busts its associated cache.
5. Only the affected fragments re-render. All other cached items remain untouched.

Luckily, Laravel offers this "touch" functionality out of the box. Consider a `Note` object that needs to alert its parent `Card` relationship each time an update occurs.

```php
<?php

namespace App;

use JustusTheis\Kaish\Kaishable;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use Kaishable;

    protected $touches = ['card'];

    public function card()
    {
        return $this->belongsTo(Card::class);
    }
}
```

Notice the `$touches = ['card']` portion. This instructs Laravel to ping the `card` relationship's timestamps each time the note is updated.

Now, everything is in place. You might render your view, like so:

**resources/views/cards/_card.blade.php**

```html
@cache('my-cache-key', $card)
    <article class="Card">
        <h2>{{ $card->title }}</h2>

        <ul>
            @foreach ($card->notes as $note)
                @include ('cards/_note')
            @endforeach
        </ul>
    </article>
@endcache
```

**resources/views/cards/_note.blade.php**

```html
@cache('my-cache-key', $note)
    <li>{{ $note->body }}</li>
@endcache
```

Notice the Russian-Doll style cascading for our caches; that's the key. If any note is updated, its individual cache will clear - along with its parent - but any  siblings will remain untouched.

### Caching Collections

You won't always want to cache model instances; you may wish to cache a Laravel collection as well! No problem.

```html
@cache('my-cache-key', $posts)
    @foreach ($posts as $post)
        @include ('post')
    @endforeach
@endcache
```

Now, as long as the `$posts` collection contents does not change, that `@foreach` section will never run. Instead, as always, we'll pull from the cache.

Behind the scenes, this package will detect that you've passed a Laravel collection to the `cache` directive, and will subsequently generate a unique cache key for the collection.
