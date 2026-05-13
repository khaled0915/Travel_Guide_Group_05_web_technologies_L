<?php

declare(strict_types=1);

class WishlistController
{
    public function index(): void
    {
        Auth::requireRole(['user']);
        Auth::requireVerified();

        $wishlistItems = (new Wishlist())->byUser((int) Auth::id());

        render('wishlist/index', [
            'pageTitle' => 'Wishlist',
            'wishlistItems' => $wishlistItems,
            'pageScripts' => ['assets/js/wishlist.js'],
        ]);
    }

    public function add(): void
    {
        Auth::requireRole(['user']);
        Auth::requireVerified();
        require_csrf();

        $postId = (int) ($_POST['post_id'] ?? 0);
        if ($postId < 1) {
            json_response(['ok' => false, 'message' => 'Invalid post selection.'], 422);
        }

        $post = (new Post())->find($postId);
        if (!$post || $post['status'] !== 'approved') {
            json_response(['ok' => false, 'message' => 'Selected post is unavailable.'], 404);
        }

        (new Wishlist())->add((int) Auth::id(), $postId);
        json_response(['ok' => true, 'message' => 'Post added to wishlist.']);
    }

    public function remove(): void
    {
        Auth::requireRole(['user']);
        Auth::requireVerified();
        require_csrf();

        $postId = (int) ($_POST['post_id'] ?? 0);
        if ($postId < 1) {
            json_response(['ok' => false, 'message' => 'Invalid post selection.'], 422);
        }

        (new Wishlist())->remove((int) Auth::id(), $postId);
        json_response(['ok' => true, 'message' => 'Post removed from wishlist.']);
    }
}
