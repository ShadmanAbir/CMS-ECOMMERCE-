<?php

/*
|--------------------------------------------------------------------------
| {SITE_NAME} — Site Configuration
|--------------------------------------------------------------------------
| Replace {SITE_NAME} with your brand name.
| This file is loaded automatically by config/themes.php.
*/

return [
    'name' => '{SITE_NAME}',
    'slug' => '{slug}',
    'database' => 'sites/{slug}/database/site.sqlite',
    'tagline' => 'Your tagline here',
    'meta_description' => '{SITE_NAME} - Your meta description',

    'colors' => [
        'primary'      => '#000000',
        'secondary'    => '#1a1a1a',
        'accent'       => '#000000',
        'danger'       => '#e74c3c',
        'dark'         => '#111111',
        'light'        => '#f5f5f5',
        'sky'          => '#647589',
        'bg_primary'   => '#ffffff',
        'bg_secondary' => '#f9f9f9',
    ],

    'fonts' => [
        'heading'      => "'Inter', sans-serif",
        'heading_name' => 'Inter',
        'body'         => "'Inter', sans-serif",
        'body_name'    => 'Inter',
        'google_url'   => 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap',
    ],

    'logo' => [
        'path'    => 'images/{slug}/logo.svg',
        'alt'     => '{SITE_NAME}',
        'favicon' => 'images/{slug}/favicon.ico',
    ],

    'contact' => [
        'email'  => 'info@example.com',
        'phone'  => null,
        'social' => [
            'facebook'  => '#',
            'instagram' => '#',
            'twitter'   => '#',
        ],
    ],

    'images_path' => 'images/{slug}',

    'css' => [
        'css/animations.css',
        'css/sites/{slug}.css',
    ],

    'js' => [
        'js/animations.js',
    ],

    'features' => [
        'blog_section'      => false,
        'service_features'  => false,
        'whatsapp_button'   => false,
        'language_switcher' => false,
        'newsletter'        => true,
        'page_loader'       => false,
        'product_reviews'   => true,
        'product_variants'  => true,
        'instagram_section' => false,
    ],

    'currency' => [
        'symbol' => '৳',
        'code'   => 'BDT',
    ],

    'homepage_sections' => [
        'hero',
        'category_grid',
        'newsletter',
    ],

    'body_class' => 'bg-white text-gray-900',

    'header' => [
        'bg_class' => 'bg-white',
        'text_class' => 'text-gray-900',
        'border_class' => 'border-gray-200',
        'muted_text_class' => 'text-gray-500',
        'muted_text_class_2' => 'text-gray-600',
        'muted_text_class_3' => 'text-gray-400',
        'input_bg_class' => 'bg-gray-100',
        'input_border_class' => 'border-gray-300',
        'input_placeholder_class' => 'placeholder-gray-400',
        'hover_bg_class' => 'hover:bg-gray-100',
    ],

    'footer' => [
        'bg_class' => 'bg-gray-900',
        'text_class' => 'text-white',
        'border_class' => 'border-white/10',
        'muted_text_class' => 'text-white/50',
        'muted_text_class_2' => 'text-white/40',
        'icon_bg_class' => 'bg-white/10',
    ],

    'ui' => [
        'page_bg' => 'bg-white',
        'section_bg' => 'bg-gray-50',
        'heading_text' => 'text-gray-900',
        'card_bg' => 'bg-white',
        'card_text' => 'text-gray-900',
        'card_border' => 'border-gray-200',
        'page_text' => 'text-gray-900',
        'muted_text' => 'text-gray-500',
        'muted_text_2' => 'text-gray-400',
        'muted_text_3' => 'text-gray-300',
        'input_bg' => 'bg-gray-50',
        'input_border' => 'border-gray-300',
        'input_text' => 'text-gray-900',
        'input_placeholder' => 'placeholder-gray-400',
        'divider' => 'border-gray-200',
        'hover_bg' => 'hover:bg-gray-50',
        'img_placeholder_bg' => 'bg-gray-100',
        'btn_text' => 'text-white',
        'btn_outline_border' => 'border-gray-300',
        'btn_outline_text' => 'text-gray-900',
        'search_overlay_bg' => 'bg-gray-800/90',
        'search_overlay_text' => 'text-white',
        'search_overlay_muted' => 'text-gray-300',
        'search_overlay_border' => 'border-gray-500',
        'search_overlay_input_bg' => 'bg-white/20',
        'search_overlay_placeholder' => 'placeholder-gray-400',
        'banner_overlay' => 'from-gray-800/80 via-gray-600/50',
        'banner_text' => 'text-white',
        'banner_muted' => 'text-gray-200',
        'banner_muted_2' => 'text-gray-300',
    ],

    'admin' => [
        'card_bg' => 'bg-white',
        'card_border' => 'border border-gray-200',
        'card_text' => 'text-gray-900',
        'muted_text' => 'text-gray-500',
        'muted_text_2' => 'text-gray-400',
        'muted_text_3' => 'text-gray-600',
        'input_bg' => 'bg-gray-50',
        'input_border' => 'border-gray-300',
        'input_text' => 'text-gray-900',
        'table_head_bg' => 'bg-gray-50',
        'table_divider' => 'divide-gray-200',
        'table_row_divider' => 'divide-gray-100',
        'table_row_hover' => 'hover:bg-gray-50',
        'btn_cancel_bg' => 'bg-gray-100',
        'btn_cancel_text' => 'text-gray-700',
        'btn_cancel_hover' => 'hover:bg-gray-200',
        'heading_text' => 'text-gray-900',
        'border_divider' => 'border-gray-200',
        'checkbox_border' => 'border-gray-300',
        'checkbox_bg' => 'bg-white',
        'flag_border' => 'border-gray-200',
        'flag_hover' => 'hover:bg-gray-50',
        'img_placeholder_bg' => 'bg-gray-100',
        'img_placeholder_border' => 'border-gray-200',
        'image_preview_bg' => 'bg-gray-100',
    ],

    'auth' => [
        'overlay_class' => 'bg-black/60',
        'card_bg' => 'bg-white',
        'card_text' => 'text-gray-900',
        'card_border' => 'border-gray-200',
        'label_text' => 'text-gray-600',
        'input_bg' => 'bg-gray-50',
        'input_border' => 'border-gray-300',
        'input_text' => 'text-gray-900',
        'input_placeholder' => 'placeholder-gray-400',
        'muted_text' => 'text-gray-500',
        'muted_text_2' => 'text-gray-400',
        'divider_border' => 'border-gray-200',
        'divider_text' => 'text-gray-400',
        'social_bg' => 'bg-gray-50',
        'social_border' => 'border-gray-200',
        'social_hover_border' => 'hover:border-accent/50',
        'social_hover_bg' => 'hover:bg-gray-100',
        'social_text' => 'text-gray-900',
        'checkbox_border' => 'border-gray-300',
    ],

    'pricing' => [
        'tax_rate' => 0,                 // No tax by default
        'shipping_cost' => 0,            // Flat shipping (0 = free)
        'free_shipping_threshold' => null, // null = always free
        'currency' => 'BDT',
        'currency_symbol' => '৳',
    ],

    'inventory' => [
        'low_stock_threshold' => 5,      // Alert when stock drops to this level
        'digest_frequency' => 'daily',   // daily | weekly
        'digest_day' => 'monday',        // Day of week for weekly digest
    ],

    /*
    |--------------------------------------------------------------------------
    | Dynamic Product Attributes (per-site schema)
    |--------------------------------------------------------------------------
    | Define custom product fields unique to this brand. These appear in the
    | admin product form, on the product detail page, and (if filterable)
    | in the shop sidebar. Run `php artisan schema:sync {slug}` after editing,
    | or simply activate the site — sync runs automatically on activation.
    |
    | Supported types: text, textarea, number, boolean, select, multiselect
    */
    'product_schema' => [
        // ['code' => 'material', 'label' => 'Material', 'type' => 'select', 'options' => ['Cotton', 'Silk', 'Leather'], 'is_filterable' => true],
        // ['code' => 'warranty', 'label' => 'Warranty', 'type' => 'text', 'is_filterable' => false],
        // ['code' => 'care_instructions', 'label' => 'Care Instructions', 'type' => 'textarea', 'is_filterable' => false],
    ],
];
