<?php

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Author;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\UncachedAuthor;

test('from sub query is cached', function () {
    $results = (new Author)
        ->fromSub(
            fn ($query) => $query->from("authors")->where("id", ">", 1),
            "authors",
        )
        ->get();
    $liveResults = (new UncachedAuthor)
        ->fromSub(
            fn ($query) => $query->from("authors")->where("id", ">", 1),
            "authors",
        )
        ->get();

    expect($results)->not->toBeEmpty();
    expect($results->pluck("id"))->toEqual($liveResults->pluck("id"));
});

test('two different from sub queries do not share a key', function () {
    $first = (new Author)
        ->fromSub(
            fn ($query) => $query->from("authors")->where("id", ">", 1),
            "authors",
        );
    $second = (new Author)
        ->fromSub(
            fn ($query) => $query->from("authors")->where("id", ">", 2),
            "authors",
        );

    expect(cacheKey($first))->not->toBe(cacheKey($second));

    $liveResults = (new UncachedAuthor)
        ->fromSub(
            fn ($query) => $query->from("authors")->where("id", ">", 2),
            "authors",
        )
        ->get();

    $first->get();

    expect($second->get()->pluck("id"))->toEqual($liveResults->pluck("id"));
});
