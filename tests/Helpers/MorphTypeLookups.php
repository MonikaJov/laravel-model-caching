<?php

declare(strict_types=1);

use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Book;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Comment;
use GeneaLabs\LaravelModelCaching\Tests\Fixtures\Post;
use Illuminate\Support\Facades\DB;

function seedPostAndBookComments(): void
{
    Comment::create([
        'commentable_id' => (new Post)->first()->id,
        'commentable_type' => Post::class,
        'description' => 'Comment on post',
        'subject' => 'Post comment',
    ]);
    Comment::create([
        'commentable_id' => (new Book)->first()->id,
        'commentable_type' => Book::class,
        'description' => 'Comment on book',
        'subject' => 'Book comment',
    ]);
}

function loggedMorphTypeLookupCount(): int
{
    return collect(DB::getQueryLog())
        ->filter(function (array $query) {
            return str_contains($query["query"], 'distinct "commentable_type"');
        })
        ->count();
}
