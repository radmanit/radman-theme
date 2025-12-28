<?php
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_shortlink_wp_head');

add_theme_support('title-tag');

add_theme_support('post-thumbnails');

add_action('wp_enqueue_scripts', function () {

    wp_enqueue_style(
            'theme-main',
            get_template_directory_uri() . '/assets/css/main.css',
            [],
            filemtime(get_template_directory() . '/assets/css/main.css')
    );

});

add_action('wp_head', function () {

    if (is_404()) return;

    $description = theme_get_meta_description();
    if (!$description) return;

    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
});


add_action('init', function () {
    register_post_meta('post', 'external_image_url', [
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'auth_callback' => function () {
                return current_user_can('edit_posts');
            }
    ]);
});

function get_post_image_url($post_id = null)
{

    $post_id = $post_id ?: get_the_ID();

    if (has_post_thumbnail($post_id)) {
        return get_the_post_thumbnail_url($post_id, 'medium');
    }

    $external = get_post_meta($post_id, 'external_image_url', true);
    if ($external) {
        return esc_url($external);
    }

    return get_template_directory_uri() . '/assets/img/placeholder.jpg';
}


function gregorian_to_jalali($gy, $gm, $gd)
{
    $g_d_m = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334];
    if ($gy > 1600) {
        $jy = 979;
        $gy -= 1600;
    } else {
        $jy = 0;
        $gy -= 621;
    }
    $gy2 = ($gm > 2) ? ($gy + 1) : $gy;
    $days = (365 * $gy) + floor(($gy2 + 3) / 4) - floor(($gy2 + 99) / 100)
            + floor(($gy2 + 399) / 400) - 80 + $gd + $g_d_m[$gm - 1];
    $jy += 33 * floor($days / 12053);
    $days %= 12053;
    $jy += 4 * floor($days / 1461);
    $days %= 1461;
    if ($days > 365) {
        $jy += floor(($days - 1) / 365);
        $days = ($days - 1) % 365;
    }
    $jm = ($days < 186) ? 1 + floor($days / 31) : 7 + floor(($days - 186) / 30);
    $jd = 1 + (($days < 186) ? ($days % 31) : (($days - 186) % 30));
    return [$jy, $jm, $jd];
}

function get_jalali_date_from_timestamp($timestamp, $format = 'Y/m/d')
{
    $gy = date('Y', $timestamp);
    $gm = date('m', $timestamp);
    $gd = date('d', $timestamp);

    $hour   = date('H', $timestamp);
    $minute = date('i', $timestamp);
    $second = date('s', $timestamp);

    [$jy, $jm, $jd] = gregorian_to_jalali($gy, $gm, $gd);

    $months = [
            1  => 'فروردین',
            2  => 'اردیبهشت',
            3  => 'خرداد',
            4  => 'تیر',
            5  => 'مرداد',
            6  => 'شهریور',
            7  => 'مهر',
            8  => 'آبان',
            9  => 'آذر',
            10 => 'دی',
            11 => 'بهمن',
            12 => 'اسفند',
    ];

    $replace = [
            'Y' => $jy,
            'm' => str_pad($jm, 2, '0', STR_PAD_LEFT),
            'd' => str_pad($jd, 2, '0', STR_PAD_LEFT),
            'M' => $months[$jm] ?? '',

        // Time
            'H' => $hour,
            'i' => $minute,
            's' => $second,
    ];

    return strtr($format, $replace);
}



function theme_breadcrumb_items(): array
{
    $items = [];

    // Home
    $items[] = [
            'label' => 'خانه',
            'url' => home_url('/'),
    ];

    if (is_home() || is_front_page()) {
        return $items;
    }

    if (is_category()) {
        $cat = get_queried_object();
        if ($cat && !is_wp_error($cat)) {
            $parents = array_reverse(get_ancestors($cat->term_id, 'category'));
            foreach ($parents as $parent_id) {
                $parent = get_category($parent_id);
                if ($parent && !is_wp_error($parent)) {
                    $items[] = [
                            'label' => $parent->name,
                            'url' => get_category_link($parent),
                    ];
                }
            }

            $items[] = [
                    'label' => single_cat_title('', false),
                    'url' => '',
            ];
        }
        return $items;
    }

    if (is_single()) {
        $cats = get_the_category();
        if (!empty($cats)) {
            $cat = $cats[0];
            $parents = array_reverse(get_ancestors($cat->term_id, 'category'));

            foreach ($parents as $parent_id) {
                $parent = get_category($parent_id);
                if ($parent && !is_wp_error($parent)) {
                    $items[] = [
                            'label' => $parent->name,
                            'url' => get_category_link($parent),
                    ];
                }
            }

            $items[] = [
                    'label' => $cat->name,
                    'url' => get_category_link($cat),
            ];
        }

        $items[] = [
                'label' => get_the_title(),
                'url' => '',
        ];

        return $items;
    }

    if (is_page()) {
        $post = get_post();
        if ($post) {
            $parents = array_reverse(get_post_ancestors($post));
            foreach ($parents as $parent_id) {
                $items[] = [
                        'label' => get_the_title($parent_id),
                        'url' => get_permalink($parent_id),
                ];
            }
            $items[] = [
                    'label' => get_the_title(),
                    'url' => '',
            ];
        }
        return $items;
    }

    if (is_search()) {
        $items[] = [
                'label' => 'جستجو: ' . get_search_query(),
                'url' => '',
        ];
        return $items;
    }

    if (is_tag()) {
        $items[] = [
                'label' => single_tag_title('', false),
                'url' => '',
        ];
        return $items;
    }

    if (is_author()) {
        $items[] = [
                'label' => get_the_author(),
                'url' => '',
        ];
        return $items;
    }

    if (is_archive()) {
        $items[] = [
                'label' => get_the_archive_title(),
                'url' => '',
        ];
        return $items;
    }

    if (is_404()) {
        $items[] = [
                'label' => '404',
                'url' => '',
        ];
        return $items;
    }

    $items[] = [
            'label' => wp_get_document_title(),
            'url' => '',
    ];

    return $items;
}

function theme_render_breadcrumbs(bool $with_schema = true): void
{
    $items = theme_breadcrumb_items();
    if (count($items) <= 1) return;

    echo '<nav class="breadcrumb" aria-label="breadcrumb">';
    echo '<ol class="breadcrumbs__list">';

    $last_index = count($items) - 1;

    foreach ($items as $i => $item) {
        $label = esc_html($item['label']);
        $url = $item['url'] ? esc_url($item['url']) : '';

        echo '<li class="breadcrumbs__item">';

        if ($url && $i !== $last_index) {
            echo '<a class="breadcrumbs__link" href="' . $url . '">' . $label . '</a>';
        } else {
            echo '<span class="breadcrumbs__current" aria-current="page">' . $label . '</span>';
        }

        echo '</li>';

        if ($i !== $last_index) {
            echo '<li class="breadcrumbs__sep" aria-hidden="true">/</li>';
        }
    }

    echo '</ol>';
    echo '</nav>';

    if ($with_schema) {
        $list = [];
        $pos = 1;
        foreach ($items as $it) {
            $list[] = [
                    '@type' => 'ListItem',
                    'position' => $pos++,
                    'name' => $it['label'],
                    'item' => $it['url'] ?: home_url(add_query_arg([], $GLOBALS['wp']->request ? '/' . $GLOBALS['wp']->request . '/' : '/')),
            ];
        }

        echo '<script type="application/ld+json">' . wp_json_encode([
                        '@context' => 'https://schema.org',
                        '@type' => 'BreadcrumbList',
                        'itemListElement' => $list,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>';
    }
}


function theme_get_meta_description(): string
{
    if (is_single()) {
        if (has_excerpt()) {
            return wp_strip_all_tags(get_the_excerpt());
        }

        $content = wp_strip_all_tags(get_the_content());
        return mb_substr(trim($content), 0, 160);
    }

    if (is_category()) {
        $desc = strip_tags(category_description());
        if ($desc) {
            return $desc;
        }
        return single_cat_title('', false) . ' - آخرین اخبار و مطالب مرتبط';
    }

    if (is_search()) {
        return 'نتایج جستجو برای: ' . get_search_query();
    }

    if (is_page()) {
        if (has_excerpt()) {
            return wp_strip_all_tags(get_the_excerpt());
        }

        $content = wp_strip_all_tags(get_the_content());
        return mb_substr(trim($content), 0, 160);
    }

    if (is_home() || is_front_page()) {
        return get_bloginfo('description');
    }

    return get_bloginfo('description');
}

add_action('init', function () {

    register_post_type('backlink', [
            'labels' => [
                    'name' => 'بک‌لینک‌ها',
                    'singular_name' => 'بک‌لینک',
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_rest' => true,
            'rest_base' => 'backlinks',
            'supports' => ['title', 'custom-fields'],
            'menu_icon' => 'dashicons-admin-links',
    ]);

    register_post_meta('backlink', 'backlink_url', [
            'single' => true,
            'type'   => 'string',
            'show_in_rest' => [
                    'schema' => [
                            'type' => 'string',
                            'format' => 'uri',
                            'context' => ['view','edit'],
                    ],
            ],
            'sanitize_callback' => 'esc_url_raw',
            'auth_callback' => fn() => current_user_can('edit_posts'),
    ]);

    register_post_meta('backlink', 'backlink_text', [
            'single' => true,
            'type'   => 'string',
            'show_in_rest' => [
                    'schema' => [
                            'type' => 'string',
                            'context' => ['view','edit'],
                    ],
            ],
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback' => fn() => current_user_can('edit_posts'),
    ]);


});


add_action('add_meta_boxes', function () {

    add_meta_box(
            'backlink_meta',
            'اطلاعات بک‌لینک',
            function ($post) {

                $url = get_post_meta($post->ID, 'backlink_url', true);
                $text = get_post_meta($post->ID, 'backlink_text', true);
                ?>
                <p>
                    <label>آدرس لینک</label><br>
                    <input type="url" name="backlink_url"
                           value="<?php echo esc_attr($url); ?>"
                           style="width:100%">
                </p>

                <p>
                    <label>متن لینک (اختیاری)</label><br>
                    <input type="text" name="backlink_text"
                           value="<?php echo esc_attr($text); ?>"
                           style="width:100%">
                </p>
                <?php
            },
            'backlink'
    );

});

add_action('save_post_backlink', function ($post_id) {

    if (isset($_POST['backlink_url'])) {
        update_post_meta(
                $post_id,
                'backlink_url',
                esc_url_raw($_POST['backlink_url'])
        );
    }

    if (isset($_POST['backlink_text'])) {
        update_post_meta(
                $post_id,
                'backlink_text',
                sanitize_text_field($_POST['backlink_text'])
        );
    }

});

function get_backlinks(): array
{
    return get_posts([
            'post_type' => 'backlink',
            'numberposts' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
    ]);
}

function render_backlinks(): string
{
    $links = get_backlinks();
    if (!$links) return '';

    ob_start();

    echo '<div class="backlinks"><ul>';

    foreach ($links as $link) {

        $url = get_post_meta($link->ID, 'backlink_url', true);
        if (!$url) continue;

        $text = get_post_meta($link->ID, 'backlink_text', true)
                ?: $link->post_title;

        echo '<li><a href="' . esc_url($url) . '" target="_blank" rel="noopener" class="block text-gray-700 hover:text-blue-600 transition">';
        echo esc_html($text);
        echo '</a></li>';
    }

    echo '</ul></div>';

    return ob_get_clean();
}

add_shortcode('backlinks', function () {
    return render_backlinks();
});


require_once __DIR__ . '/vendor/plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$themeUpdateChecker = PucFactory::buildUpdateChecker(
        'https://github.com/radmanit/radman-theme',
        __FILE__,
        'radman-theme'
);

$themeUpdateChecker->setBranch('master');
