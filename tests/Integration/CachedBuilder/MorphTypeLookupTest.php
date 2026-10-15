<?php

declare(strict_types=1);

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Comment;
use Illuminate\Support\Facades\DB;

test('morph type lookup is not repeated for every tag generation', function () {
    seedPostAndBookComments();
    $this->cache()->flush();

    DB::enableQueryLog();

    (new Comment)->with("commentable")->get();
    (new Comment)->with("commentable")->get();
    (new Comment)->with("commentable")->get();

    expect(loggedMorphTypeLookupCount())->toBe(
        1,
        "Tags are generated for every cached query touching the relation, so the type lookup has to be answered from cache after the first."
    );
});

test('morph type lookup is repeated after the table is written to', function () {
    seedPostAndBookComments();
    $this->cache()->flush();

    (new Comment)->with("commentable")->get();

    DB::enableQueryLog();
    (new Comment)->with("commentable")->get();

    expect(loggedMorphTypeLookupCount())->toBe(
        0,
        "The memo is warm, so nothing should reach the database for it."
    );

    Comment::create([
        'commentable_id' => (new Author)->first()->id,
        'commentable_type' => Author::class,
        'description' => 'Comment on author',
        'subject' => 'Author comment',
    ]);

    DB::flushQueryLog();
    (new Comment)->with("commentable")->get();

    expect(loggedMorphTypeLookupCount())->toBe(
        1,
        "A new morph type can only arrive through a write to this table, and that write has to drop the memo."
    );
});
