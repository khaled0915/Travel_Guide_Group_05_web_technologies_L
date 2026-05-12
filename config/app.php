<?php

return [
    'name' => 'Travel Guide',
    'remember_cookie' => 'travel_guide_remember',
    'remember_days' => 30,
    'max_profile_upload_size' => 2 * 1024 * 1024,
    'max_post_upload_size' => 4 * 1024 * 1024,
    'profile_upload_dir' => dirname(__DIR__) . '/public/uploads',
    'post_upload_dir' => dirname(__DIR__) . '/public/uploads/posts',
];
