<?php

namespace Tests;

use Prophecy\PhpUnit\ProphecyTrait;
use PHPUnit\Framework\TestCase as PHPUnit;
use Illuminate\Database\Capsule\Manager as DB;

abstract class TestCase extends PHPUnit
{
    use ProphecyTrait;

    public function setUp() : void
    {
        $this->setUpDatabase();
        $this->migrateTables();
    }

    protected function setUpDatabase() :void
    {
        $database = new DB;

        $database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $database->bootEloquent();
        $database->setAsGlobal();
    }

    protected function migrateTables() :void
    {
        DB::schema()->create('posts', function ($table) {
            $table->increments('id');
            $table->string('title');
            $table->timestamps();
        });
    }

    protected function makePost()
    {
        $post = new \Tests\Stubs\Post;
        $post->title = 'Some title';
        $post->save();

        return $post;
    }
}
