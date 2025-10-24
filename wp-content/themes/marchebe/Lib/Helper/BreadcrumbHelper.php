<?php

namespace AcMarche\Theme\Lib\Helper;

use AcMarche\Theme\Inc\Theme;
use AcMarche\Theme\Repository\WpRepository;

class BreadcrumbHelper
{
    public static function post(int $postId): array
    {
        $paths = [];
        $blogId = get_current_blog_id();

        if ($blogId > Theme::CITOYEN) {
            $path = Theme::getPathBlog($blogId);
            $blogName = Theme::getTitleBlog($blogId);
            $paths[] = ['name' => $blogName, 'term_id' => $blogId, 'url' => $path];
        }

        $catSlug = get_query_var('category_name');

        if (preg_match("#/#", $catSlug)) {
            $vars = explode("/", $catSlug);
            $catSlug = end($vars);
        }
        $currentCategory = get_category_by_slug($catSlug);
        if ($currentCategory) {
            $paths[] = [
                'name' => $currentCategory->name,
                'term_id' => $currentCategory->cat_ID,
                'url' => get_category_link($currentCategory),
            ];
        }

        return $paths;
    }

    public static function category(int $categoryId): array
    {
        $blogId = get_current_blog_id();
        $wpRepository = new WpRepository();
        $paths = [];
        if ($blogId > Theme::CITOYEN) {
            $path = Theme::getPathBlog($blogId);
            $blogName = Theme::getTitleBlog($blogId);
            $paths[] = ['name' => $blogName, 'term_id' => $blogId, 'url' => $path];
        }

        $parent = $wpRepository->getParentCategory($categoryId);
        if ($parent) {
            $paths = ['name' => $parent->name, 'term_id' => $parent->cat_ID, 'url' => ''];
        }

        return $paths;
    }
}