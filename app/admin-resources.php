<?php
declare(strict_types=1);

// The approved data dictionary is the allowlist for identifiers used by the admin editor.
return [
    'products' => ['Products', 'product_id', 'product_name', [
        'product_name' => ['Product name', 'text', 150], 'category_id' => ['Category', 'category'],
        'description' => ['Description', 'long', 10000, null], 'price' => ['Price', 'decimal'],
        'currency' => ['Currency code', 'currency', 3, 'USD'], 'stock' => ['Stock', 'uint', 0, '0'],
        'weight_gram' => ['Weight in grams', 'decimal'], 'image_url' => ['Image path or URL', 'asset', 500, null],
        'is_active' => ['Visible in Store', 'flag', 0, '1'],
    ]],
    'product_categories' => ['Product Categories', 'category_id', 'category_name', [
        'category_name' => ['Category name', 'text', 100], 'description' => ['Description', 'long', 10000, null],
    ]],
    'band_members' => ['Band Members', 'member_id', 'member_name', [
        'member_name' => ['Name', 'text', 100], 'birth_date' => ['Date of birth', 'date', 0, null],
        'role' => ['Role', 'text', 100], 'biography' => ['Biography', 'long', 10000, null],
        'photo_url' => ['Photo path or URL', 'asset', 500, null], 'sort_order' => ['Display order', 'uint', 0, '0'],
    ]],
    'albums' => ['Albums', 'album_id', 'album_title', [
        'album_title' => ['Title', 'text', 150], 'release_year' => ['Release year', 'year', 0, null],
        'genre' => ['Genre', 'text', 100, null], 'cover_url' => ['Cover path or URL', 'asset', 500, null],
        'description' => ['Description', 'long', 10000, null], 'spotify_url' => ['Spotify album URL', 'spotify-album', 500, null],
    ]],
    'songs' => ['Songs', 'song_id', 'song_title', [
        'song_title' => ['Title', 'text', 150], 'album_id' => ['Album', 'album', 0, null],
        'release_year' => ['Release year', 'year', 0, null], 'genre' => ['Genre', 'text', 100, null],
        'songwriter_credit' => ['Songwriter credit', 'text', 255, null], 'summary' => ['Card summary', 'long', 500, null],
        'story' => ['Full story', 'long', 10000, null], 'spotify_url' => ['Spotify track URL', 'spotify-track', 500, null],
        'youtube_video_id' => ['YouTube video ID', 'youtube', 11, null], 'cover_url' => ['Cover path or URL', 'asset', 500, null],
        'is_featured_song' => ['Feature this song', 'flag', 0, '0'],
    ]],
    'timeline_events' => ['Timeline & Awards', 'event_id', 'event_title', [
        'event_title' => ['Title', 'text', 200], 'event_year' => ['Event year', 'year', 0, null],
        'event_type' => ['Type', 'choice', ['history', 'award'], 'history'], 'description' => ['Description', 'long', 10000],
        'image_url' => ['Image path or URL', 'asset', 500, null], 'source_url' => ['Reference URL', 'url', 500, null],
        'sort_order' => ['Display order', 'uint', 0, '0'],
    ]],
    'media_items' => ['Media', 'media_id', 'media_title', [
        'media_title' => ['Title', 'text', 200], 'media_type' => ['Type', 'choice', ['image', 'video'], 'image'],
        'category' => ['Category', 'choice', ['band', 'member', 'live', 'album', 'archive'], 'archive'],
        'media_url' => ['Image path or video URL', 'media', 500], 'thumbnail_url' => ['Thumbnail path or URL', 'asset', 500, null],
        'description' => ['Description', 'long', 10000, null], 'media_year' => ['Media year', 'year', 0, null],
        'source_url' => ['Reference URL', 'url', 500, null], 'album_id' => ['Album', 'album', 0, null],
        'sort_order' => ['Display order', 'uint', 0, '0'],
    ]],
    'admin_users' => ['Administrators', 'admin_id', 'username', [
        'username' => ['Username', 'username', 50], 'email' => ['Email', 'email', 254], 'full_name' => ['Full name', 'text', 100],
        'password' => ['Password', 'password', 72, null], 'is_active' => ['Allow sign-in', 'flag', 0, '1'],
    ]],
    'fan_zone_submissions' => ['Fan Zone', 'submission_id', 'name', [
        'name' => ['Name', 'text', 100], 'favorite_topic' => ['Favorite topic', 'text', 150],
        'message' => ['Message', 'long', 2000], 'is_active' => ['Publish on website', 'flag', 0, '0'],
    ]],
];
