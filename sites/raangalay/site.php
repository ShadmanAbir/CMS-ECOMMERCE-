<?php

/*
|--------------------------------------------------------------------------
| Raangalay — Site Configuration
|--------------------------------------------------------------------------
| Light, traditional Bangladeshi brand site.
*/

return [
    'name' => 'Raangalay',
    'slug' => 'raangalay',
    'database' => 'sites/raangalay/database/site.sqlite',
    'tagline' => 'Celebrating the beauty of Bengali tradition',
    'meta_description' => 'Raangalay - Authentic Bangladeshi beauty and personal care products rooted in tradition.',

    'colors' => [
        'primary'    => '#771A09',
        'secondary'  => '#202020',
        'accent'     => '#771A09',
        'danger'     => '#F44336',
        'dark'       => '#242424',
        'light'      => '#fcfcfc',
        'sky'        => '#647589',
        'bg_primary' => '#F9F9F9',
        'bg_secondary' => '#FFFFFF',
    ],

    'fonts' => [
        'heading'     => "'Jost', sans-serif",
        'heading_name' => 'Jost',
        'body'        => "'Jost', sans-serif",
        'body_name'   => 'Jost',
        'google_url'  => 'https://fonts.googleapis.com/css2?family=Jost:wght@200;300;400;500;600;700&display=swap',
    ],

    'logo' => [
        'path'    => 'images/raangalay/logo/logo.png',
        'alt'     => 'Raangalay',
        'favicon' => 'images/raangalay/logo/ico.png',
    ],

    'contact' => [
        'email'  => 'info@raangalay.com',
        'phone'  => null,
        'social' => [
            'facebook'  => '#',
            'instagram' => '#',
            'twitter'   => '#',
        ],
    ],

    'images_path' => 'images/raangalay',

    'css' => [
        'css/animations.css',
        'css/sites/raangalay.css',
    ],

    'js' => [
        'js/animations.js',
    ],

    'features' => [
        'blog_section'      => true,
        'service_features'  => true,
        'whatsapp_button'   => true,
        'language_switcher' => true,
        'newsletter'        => true,
        'page_loader'       => false,
        'product_reviews'   => false,
        'product_variants'  => false,
        'instagram_section' => true,
    ],

    'currency' => [
        'symbol' => '৳',
        'code'   => 'BDT',
    ],

    'homepage_sections' => [
        'hero',
        'category_grid',
        'trending',
        'new_arrivals',
        'featured',
        'blog',
        'service_features',
        'newsletter',
    ],

    'body_class' => 'bg-[#F9F9F9] text-[#202020]',

    'header' => [
        'bg_class' => 'bg-white',
        'text_class' => 'text-[#202020]',
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
        'bg_class' => 'bg-[#202020]',
        'text_class' => 'text-white',
        'border_class' => 'border-white/10',
        'muted_text_class' => 'text-white/50',
        'muted_text_class_2' => 'text-white/40',
        'icon_bg_class' => 'bg-white/10',
    ],

    'ui' => [
        'page_bg' => 'bg-[#F9F9F9]',
        'section_bg' => 'bg-white',
        'heading_text' => 'text-[#202020]',
        'card_bg' => 'bg-white',
        'card_text' => 'text-[#202020]',
        'card_border' => 'border-gray-200',
        'page_text' => 'text-[#202020]',
        'muted_text' => 'text-gray-500',
        'muted_text_2' => 'text-gray-400',
        'muted_text_3' => 'text-gray-300',
        'input_bg' => 'bg-gray-50',
        'input_border' => 'border-gray-300',
        'input_text' => 'text-[#202020]',
        'input_placeholder' => 'placeholder-gray-400',
        'divider' => 'border-gray-200',
        'hover_bg' => 'hover:bg-gray-50',
        'img_placeholder_bg' => 'bg-gray-100',
        'btn_text' => 'text-white',
        'btn_outline_border' => 'border-gray-300',
        'btn_outline_text' => 'text-[#202020]',
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
        'card_text' => 'text-[#202020]',
        'card_border' => 'border-gray-200',
        'label_text' => 'text-gray-600',
        'input_bg' => 'bg-gray-50',
        'input_border' => 'border-gray-300',
        'input_text' => 'text-[#202020]',
        'input_placeholder' => 'placeholder-gray-400',
        'muted_text' => 'text-gray-500',
        'muted_text_2' => 'text-gray-400',
        'divider_border' => 'border-gray-200',
        'divider_text' => 'text-gray-400',
        'social_bg' => 'bg-gray-50',
        'social_border' => 'border-gray-200',
        'social_hover_border' => 'hover:border-accent/50',
        'social_hover_bg' => 'hover:bg-gray-100',
        'social_text' => 'text-[#202020]',
        'checkbox_border' => 'border-gray-300',
    ],

    'pricing' => [
        'tax_rate' => 0.05,              // 5% VAT
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

    'product_schema' => [
        ['code' => 'fabric', 'label' => 'Fabric', 'type' => 'select', 'options' => ['Cotton', 'Silk', 'Jamdani', 'Muslin', 'Khadi', 'Linen'], 'is_filterable' => true],
        ['code' => 'weave_type', 'label' => 'Weave Type', 'type' => 'select', 'options' => ['Handloom', 'Power Loom', 'Jamdani', 'Katan'], 'is_filterable' => true],
        ['code' => 'occasion', 'label' => 'Occasion', 'type' => 'multiselect', 'options' => ['Casual', 'Festive', 'Wedding', 'Office', 'Daily'], 'is_filterable' => true],
        ['code' => 'care_instructions', 'label' => 'Care Instructions', 'type' => 'textarea', 'is_filterable' => false],
        ['code' => 'origin', 'label' => 'Origin', 'type' => 'text', 'is_filterable' => false],
    ],
];
