<?php

declare(strict_types=1);

class ScoutController
{
    public function index(): void
    {
        Auth::requireRole(['scout']);
        Auth::requireVerified();

        $scoutId = (int) Auth::id();
        $postRequestModel = new PostRequest();
        $requests = $postRequestModel->byScout($scoutId);
        $approvedPosts = (new Post())->approvedByScout($scoutId);

        render('scout/index', [
            'pageTitle' => 'Scout Requests',
            'requests' => $requests,
            'approvedPosts' => $approvedPosts,
            'pageScripts' => ['assets/js/scout.js'],
        ]);
    }

    public function save(): void
    {
        Auth::requireRole(['scout']);
        Auth::requireVerified();
        require_csrf();

        $requestId = (int) ($_POST['request_id'] ?? 0);
        $payload = $this->sanitizePayload();
        $errors = $this->validatePayload($payload);

        $postRequestModel = new PostRequest();
        $existingRequest = $requestId > 0
            ? $postRequestModel->findOwned($requestId, (int) Auth::id())
            : null;

        if ($requestId > 0 && !$existingRequest) {
            json_response(['ok' => false, 'message' => 'The selected request could not be found.'], 404);
        }

        $imagePath = $existingRequest['image_path'] ?? null;
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
                $imagePath = $upload['path'];
            }
        }

        if ($errors !== []) {
            json_response(['ok' => false, 'errors' => $errors], 422);
        }

        $payload['image_path'] = $imagePath;
        if ($requestId > 0) {
            $postRequestModel->update($requestId, (int) Auth::id(), $payload);
            $savedRequest = $postRequestModel->findOwned($requestId, (int) Auth::id());
            json_response([
                'ok' => true,
                'message' => 'Post request updated successfully.',
                'request' => $savedRequest,
            ]);
        }

        $newId = $postRequestModel->create((int) Auth::id(), $payload);
        $savedRequest = $postRequestModel->findOwned($newId, (int) Auth::id());
        json_response([
            'ok' => true,
            'message' => 'Post request submitted successfully.',
            'request' => $savedRequest,
        ]);
    }

    public function delete(): void
    {
        Auth::requireRole(['scout']);
        Auth::requireVerified();
        require_csrf();

        $requestId = (int) ($_POST['request_id'] ?? 0);
        if ($requestId < 1) {
            json_response(['ok' => false, 'message' => 'Invalid request selected.'], 422);
        }

        (new PostRequest())->deletePending($requestId, (int) Auth::id());
        json_response([
            'ok' => true,
            'message' => 'Pending post request deleted.',
            'request_id' => $requestId,
        ]);
    }

    private function sanitizePayload(): array
    {
        return [
            'title' => input_trim('title'),
            'short_history' => input_trim('short_history'),
            'country' => input_trim('country'),
            'country_representation' => input_trim('country_representation'),
            'genre' => input_trim('genre'),
            'cost_level' => input_trim('cost_level'),
            'travel_medium_info' => input_trim('travel_medium_info'),
            'original_post_id' => (int) ($_POST['original_post_id'] ?? 0),
        ];
    }

    private function validatePayload(array $payload): array
    {
        $errors = [];
        $allowedGenres = ['beach', 'mountain', 'city', 'historical', 'nature', 'adventure'];
        $allowedCostLevels = ['low', 'medium', 'high'];

        foreach (['title', 'short_history', 'country', 'country_representation', 'travel_medium_info'] as $field) {
            if ($payload[$field] === '') {
                $errors[$field] = 'This field is required.';
            }
        }

        if (!in_array($payload['genre'], $allowedGenres, true)) {
            $errors['genre'] = 'Please choose a valid genre.';
        }

        if (!in_array($payload['cost_level'], $allowedCostLevels, true)) {
            $errors['cost_level'] = 'Please choose a valid cost level.';
        }

        return $errors;
    }
}
