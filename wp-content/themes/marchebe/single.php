<?php

namespace AcMarche\Theme\Templates;

use AcMarche\Theme\Inc\Theme;
use AcMarche\Theme\Lib\Cache;
use AcMarche\Theme\Lib\Twig;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

get_header();

$post = get_post();

// Example 1: Basic cache usage with callback
$userData = Cache::get('user_data_123', function () {
    // This will only execute if cache miss
    return [
        'id' => 123,
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ];
});

// Example 2: Cache with tags for easy invalidation
$products = Cache::get('products_category_5', function () {
    // Fetch products from database
    return fetchProductsFromDb(5);
}, null, ['products', 'category_5']);

// Example 3: Generate cache key manually
$cacheKey = Cache::generateKey('My Complex Cache Key!@#');
// Result: 'my-complex-cache-key'

// Example 4: Delete specific cache entry
Cache::delete('user_data_123');

// Example 5: Invalidate all caches with specific tags
Cache::invalidateTags(['products']); // Clears all product-related caches

// Example 6: Direct access to cache instance if needed
$cacheInstance = Cache::instance();
$item = $cacheInstance->getItem(Cache::generateKey('some_key'));
if (!$item->isHit()) {
    $item->set('some value');
    $cacheInstance->save($item);
}

$twig = Twig::loadTwig();
$image = null;
if (has_post_thumbnail()) {
    $images = wp_get_attachment_image_src(get_post_thumbnail_id(), 'original');
    if ($images) {
        $image = $images[0];
    }
}
$catSlug = get_query_var('category_name');
$currentCategory = get_category_by_slug($catSlug);
$tags = [];
$tags[] = ['name' => 'Agenda', 'term_id' => 5, 'url' => '/agenda'];
$content = get_the_content(null, null, $post);
$content = apply_filters('the_content', $content);
$content = str_replace(']]>', ']]&gt;', $content);

try {
    echo $twig->render('@AcMarche/article/show.html.twig', [
        'post' => $post,
        'title' => $post->post_title,
        'body' => $content,
        'paths' => [],
        'site' => Theme::TOURISME,
        'tags' => $tags,
        'thumbnail' => $image,
    ]);
} catch (LoaderError|RuntimeError|SyntaxError $e) {
    echo $e->getMessage();
}

get_footer();