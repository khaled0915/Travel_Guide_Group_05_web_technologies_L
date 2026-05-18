<?php

declare(strict_types=1);

class CommentController
{
    public function add(): void
    {
        Auth::requireRole(['user']);
        Auth::requireVerified();
        require_csrf();

        $postId = (int) ($_POST['post_id'] ?? 0);
        $displayName = input_trim('display_name');
        $content = input_trim('content');

        $errors = [];
        if ($postId < 1) {
            json_response(['ok' => false, 'message' => 'Invalid post selection.'], 422);
        }

        if ($displayName === '') {
            $errors['display_name'] = 'Name is required.';
        }

        if ($content === '') {
            $errors['content'] = 'Comment cannot be empty.';
        } elseif (mb_strlen($content) > 500) {
            $errors['content'] = 'Comment must be at most 500 characters.';
        }

        $post = (new Post())->find($postId);
        if (!$post || $post['status'] !== 'approved') {
            json_response(['ok' => false, 'message' => 'Selected post is unavailable.'], 404);
        }

        if ($errors !== []) {
            json_response(['ok' => false, 'errors' => $errors], 422);
        }

        $commentModel = new Comment();
        $commentId = $commentModel->add($postId, (int) Auth::id(), $content);

        if ($commentId < 1) {
            json_response(['ok' => false, 'message' => 'Could not save comment.'], 500);
        }

        $comment = $commentModel->find($commentId);
        if (!$comment) {
            json_response(['ok' => false, 'message' => 'Could not load saved comment.'], 500);
        }

        $comment['commenter_name'] = $displayName;

        json_response([
            'ok' => true,
            'message' => 'Comment added successfully.',
            'comment' => $comment,
        ]);
    }

    public function delete(): void
    {
        Auth::requireRole(['user']);
        Auth::requireVerified();
        require_csrf();

        $commentId = (int) ($_POST['comment_id'] ?? 0);
        if ($commentId < 1) {
            json_response(['ok' => false, 'message' => 'Invalid comment selection.'], 422);
        }

        $currentUserId = (int) Auth::id();
        $comment = (new Comment())->find($commentId);
        if (!$comment || (int) $comment['user_id'] !== $currentUserId) {
            json_response(['ok' => false, 'message' => 'Comment not found.'], 404);
        }

        (new Comment())->deleteOwn($commentId, $currentUserId);
        json_response([
            'ok' => true,
            'message' => 'Comment deleted successfully.',
            'comment_id' => $commentId,
        ]);
    }
}