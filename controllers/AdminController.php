<?php

declare(strict_types=1);

class AdminController
{
    public function dashboard(): void
    {
        Auth::requireRole(['admin']);

        $userModel = new User();
        $postRequestModel = new PostRequest();
        $postModel = new Post();
        $commentModel = new Comment();

        render('admin/dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'counts' => [
                'roles' => $userModel->countsByRole(),
                'pending_requests' => $postRequestModel->countPending(),
                'posts' => $postModel->countAll(),
                'comments' => $commentModel->countAll(),
            ],
            'users' => $userModel->all(),
            'pendingRequests' => $postRequestModel->pendingAll(),
            'posts' => $postModel->allForAdmin(),
            'comments' => $commentModel->allWithContext(),
            'pageScripts' => ['assets/js/admin.js'],
        ]);
    }

    public function createUser(): void
    {
        Auth::requireRole(['admin']);
        require_csrf();

        $data = [
            'name' => input_trim('name'),
            'email' => input_trim('email'),
            'password' => (string) ($_POST['password'] ?? ''),
            'role' => (string) ($_POST['role'] ?? 'user'),
            'is_verified' => isset($_POST['is_verified']) ? 1 : 0,
        ];

        $errors = [];
        if ($data['name'] === '') {
            $errors['name'] = 'Name is required.';
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required.';
        } elseif ((new User())->emailExists($data['email'])) {
            $errors['email'] = 'Email already exists.';
        }

        if (strlen($data['password']) < 8) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if (!in_array($data['role'], ['admin', 'scout', 'user'], true)) {
            $errors['role'] = 'Invalid role selected.';
        }

        if ($errors !== []) {
            json_response(['ok' => false, 'errors' => $errors], 422);
        }

        (new User())->create($data);
        json_response(['ok' => true, 'message' => 'User created successfully.']);
    }

    public function toggleVerification(): void
    {
        Auth::requireRole(['admin']);
        require_csrf();

        $userId = (int) ($_POST['user_id'] ?? 0);
        $isVerified = (int) ($_POST['is_verified'] ?? 0);
        $currentUserId = (int) Auth::id();

        if ($userId === $currentUserId && $isVerified === 0) {
            json_response(['ok' => false, 'message' => 'You cannot unverify your own admin account.'], 422);
        }

        (new User())->setVerified($userId, $isVerified);
        json_response(['ok' => true, 'message' => 'Verification status updated.']);
    }

    public function deleteUser(): void
    {
        Auth::requireRole(['admin']);
        require_csrf();

        $userId = (int) ($_POST['user_id'] ?? 0);
        $currentUserId = (int) Auth::id();

        if ($userId === $currentUserId) {
            json_response(['ok' => false, 'message' => 'You cannot delete your own admin account.'], 422);
        }

        (new User())->delete($userId);
        json_response(['ok' => true, 'message' => 'User deleted successfully.']);
    }

    public function approveRequest(): void
    {
        Auth::requireRole(['admin']);
        require_csrf();

        $requestId = (int) ($_POST['request_id'] ?? 0);
        $postRequest = (new PostRequest())->findPending($requestId);
        if (!$postRequest) {
            json_response(['ok' => false, 'message' => 'Pending request not found.'], 404);
        }

        $postModel = new Post();
        $postData = [
            'scout_id' => (int) $postRequest['scout_id'],
            'title' => $postRequest['title'],
            'short_history' => $postRequest['short_history'],
            'country' => $postRequest['country'],
            'genre' => $postRequest['genre'],
            'cost_level' => $postRequest['cost_level'],
            'travel_medium_info' => $postRequest['travel_medium_info'],
            'country_representation' => $postRequest['country_representation'] ?? null,
            'image_path' => $postRequest['image_path'] ?? null,
            'status' => 'approved',
        ];

        $originalPostId = (int) ($postRequest['original_post_id'] ?? 0);
        if ($originalPostId > 0 && $postModel->find($originalPostId)) {
            $postId = $originalPostId;
            $postModel->update($postId, $postData);
        } else {
            $postId = $postModel->create($postData);
        }

        $costModel = new CostEstimate();
        $costModel->upsert(
            $postId,
            $costModel->defaultCostForLevel((string) $postRequest['cost_level']),
            'USD'
        );

        (new PostRequest())->delete($requestId);
        json_response(['ok' => true, 'message' => $originalPostId > 0 ? 'Change request approved and post updated.' : 'Post request approved and published.']);
    }

    public function rejectRequest(): void
    {
        Auth::requireRole(['admin']);
        require_csrf();

        $requestId = (int) ($_POST['request_id'] ?? 0);
        (new PostRequest())->reject($requestId);
        json_response(['ok' => true, 'message' => 'Post request rejected.']);
    }

    public function updatePost(): void
    {
        Auth::requireRole(['admin']);
        require_csrf();

        $postId = (int) ($_POST['post_id'] ?? 0);
        $postModel = new Post();
        $existingPost = $postModel->find($postId);

        if (!$existingPost) {
            json_response(['ok' => false, 'message' => 'Post not found.'], 404);
        }

        $data = [
            'title' => input_trim('title'),
            'short_history' => input_trim('short_history'),
            'country' => input_trim('country'),
            'genre' => input_trim('genre'),
            'cost_level' => input_trim('cost_level'),
            'travel_medium_info' => input_trim('travel_medium_info'),
            'country_representation' => input_trim('country_representation'),
            'status' => input_trim('status', 'approved'),
            'image_path' => $existingPost['image_path'],
        ];

        $errors = $this->validatePostData($data);

        if (uploaded_file_is_present($_FILES['image'] ?? [])) {
            $upload = store_uploaded_file(
                $_FILES['image'],
                (string) app_config('post_upload_dir'),
                ['image/jpeg', 'image/png', 'image/webp'],
                (int) app_config('max_post_upload_size')
            );

            if (!$upload['ok']) {
                $errors['image'] = $upload['message'];
            } else {
                $data['image_path'] = $upload['path'];
            }
        }

        if ($errors !== []) {
            json_response(['ok' => false, 'errors' => $errors], 422);
        }

        $postModel->update($postId, $data);
        $baseCost = (float) ($_POST['base_cost'] ?? 0);
        $currency = input_trim('currency', 'USD');
        if ($baseCost <= 0) {
            $baseCost = (new CostEstimate())->defaultCostForLevel($data['cost_level']);
        }

        (new CostEstimate())->upsert($postId, $baseCost, $currency !== '' ? $currency : 'USD');
        json_response(['ok' => true, 'message' => 'Post updated successfully.']);
    }

    public function deletePost(): void
    {
        Auth::requireRole(['admin']);
        require_csrf();

        $postId = (int) ($_POST['post_id'] ?? 0);
        (new Post())->delete($postId);
        json_response(['ok' => true, 'message' => 'Post deleted successfully.']);
    }

    public function deleteComment(): void
    {
        Auth::requireRole(['admin']);
        require_csrf();

        $commentId = (int) ($_POST['comment_id'] ?? 0);
        (new Comment())->deleteAny($commentId);
        json_response(['ok' => true, 'message' => 'Comment deleted successfully.']);
    }

    private function validatePostData(array $data): array
    {
        $errors = [];

        foreach (['title', 'short_history', 'country', 'travel_medium_info', 'country_representation'] as $field) {
            if ($data[$field] === '') {
                $errors[$field] = 'This field is required.';
            }
        }

        if (!in_array($data['genre'], ['beach', 'mountain', 'city', 'historical', 'nature', 'adventure'], true)) {
            $errors['genre'] = 'Please choose a valid genre.';
        }

        if (!in_array($data['cost_level'], ['low', 'medium', 'high'], true)) {
            $errors['cost_level'] = 'Please choose a valid cost level.';
        }

        if (!in_array($data['status'], ['pending', 'approved', 'rejected'], true)) {
            $errors['status'] = 'Please choose a valid status.';
        }

        return $errors;
    }
}
