<?php

declare(strict_types=1);

class BrowseController
{
    public function index(): void
    {
        Auth::requireVerified();

        if (!class_exists('Post')) {
            http_response_code(404);
            render('partials/not-found', ['pageTitle' => 'Browse Unavailable']);
            return;
        }

        $postModel = new Post();
        $filters = $postModel->getFilterOptions();
        $user = Auth::user();
        $wishlistPostIds = [];

        if ($user && $user['role'] === 'user') {
            $wishlistPostIds = (new Wishlist())->postIdsByUser((int) $user['id']);
        }

        render('browse/index', [
            'pageTitle' => 'Browse Posts',
            'posts' => $postModel->approvedAll(),
            'filters' => $filters,
            'wishlistPostIds' => $wishlistPostIds,
            'pageScripts' => ['assets/js/browse.js', 'assets/js/wishlist.js'],
        ]);
    }

    public function detail(): void
    {
        Auth::requireVerified();

        if (!class_exists('Post')) {
            http_response_code(404);
            render('partials/not-found', ['pageTitle' => 'Post Not Found']);
            return;
        }

        $postId = (int) ($_GET['id'] ?? 0);
        $post = (new Post())->find($postId);

        if (!$post || $post['status'] !== 'approved') {
            http_response_code(404);
            render('partials/not-found', ['pageTitle' => 'Post Not Found']);
            return;
        }

        $user = Auth::user();
        $isWishlisted = false;
        if ($user && $user['role'] === 'user') {
            $isWishlisted = (new Wishlist())->exists((int) $user['id'], $postId);
        }

        render('browse/detail', [
            'pageTitle' => $post['title'],
            'post' => $post,
            'comments' => (new Comment())->byPost($postId),
            'costEstimate' => (new CostEstimate())->resolveForPost($postId, $post['cost_level']),
            'isWishlisted' => $isWishlisted,
            'pageScripts' => ['assets/js/comments.js', 'assets/js/cost.js', 'assets/js/wishlist.js'],
        ]);
    }

    public function search(): void
    {
        Auth::requireVerified();

        if (!class_exists('Post')) {
            json_response(['ok' => false, 'message' => 'Browse data is unavailable.'], 404);
        }

        $query = trim((string) ($_GET['q'] ?? ''));
        $country = trim((string) ($_GET['country'] ?? ''));
        $costLevel = trim((string) ($_GET['cost'] ?? ''));
        $genres = $_GET['genre'] ?? [];

        if (is_string($genres)) {
            $genres = array_filter(array_map('trim', explode(',', $genres)));
        }

        $posts = (new Post())->searchFilter($query, $country, $genres, $costLevel);
        json_response(['ok' => true, 'posts' => $posts]);
    }
}
