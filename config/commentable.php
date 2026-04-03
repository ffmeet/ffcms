<?php

return [
    'comment' => [
        'model' => App\Models\Comment::class,
        'policy' => Tilto\Commentable\Policies\CommentPolicy::class,
    ],

    'reply' => [
        'allow_self_reply' => false,
    ],

    'reaction' => [
        'model' => Tilto\Commentable\Models\CommentReaction::class,
        'allowed' => ['👍', '❤️', '😂', '😮', '😢', '🤔'],
    ],
];
