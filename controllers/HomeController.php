<?php

declare(strict_types=1);

class HomeController
{
    public function index(): void
    {
        $user = Auth::user();
        $latestPosts = $user && (int) $user['is_verified'] === 1
            ? (new Post())->approvedLatest()
            : [];

        render('home', [
            'pageTitle' => 'Home',
            'latestPosts' => $latestPosts,
            'user' => $user,
        ]);
    }
}
