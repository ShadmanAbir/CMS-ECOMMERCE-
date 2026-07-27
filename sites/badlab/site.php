<?php

/*
|--------------------------------------------------------------------------
| BAD LAB — Site Configuration
|--------------------------------------------------------------------------
| Dark, masculine grooming brand site.
*/

return [
    'name' => 'BAD LAB',
    'slug' => 'badlab',
    'database' => 'sites/badlab/database/site.sqlite',
    'tagline' => 'Man-Gineered men\'s grooming and personal care',
    'meta_description' => 'BAD LAB - Man-Gineered men\'s grooming and personal care. Active ingredients chosen for men.',

    'colors' => [
        'primary'    => '#c8a45c',
        'secondary'  => '#1a1a1a',
        'accent'     => '#c8a45c',
        'danger'     => '#e74c3c',
        'dark'       => '#111111',
        'light'      => '#f5f5f5',
        'sky'        => '#647589',
        'bg_primary' => '#111111',
        'bg_secondary' => '#1a1a1a',
    ],

    'fonts' => [
        'heading'     => "'Bebas Neue', sans-serif",
        'heading_name' => 'Bebas Neue',
        'body'        => "'Inter', sans-serif",
        'body_name'   => 'Inter',
        'google_url'  => 'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;500;600;700&display=swap',
    ],

    'logo' => [
        'path'    => 'images/logo/badlab-logo.svg',
        'alt'     => 'BAD LAB',
        'favicon' => 'images/logo/ico.png',
    ],

    'contact' => [
        'email'  => 'hello@badlabco.com',
        'phone'  => null,
        'social' => [
            'facebook'  => '#',
            'instagram' => '#',
            'twitter'   => '#',
        ],
    ],

    'images_path' => 'images/badlab',

    'css' => [
        'css/animations.css',
        'css/sites/badlab.css',
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
        'page_loader'       => true,
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
        'marquee',
        'category_grid',
        'most_popular',
        'manifesto',
        'category_products',
        'why_bad_is_good',
        'daily_armour',
        'try_bad_banner',
        'newsletter',
    ],

    'body_class' => 'bg-[#111111] text-white',

    'header' => [
        'bg_class' => 'bg-[#1a1a1a]',
        'text_class' => 'text-white',
        'border_class' => 'border-white/10',
        'muted_text_class' => 'text-white/60',
        'muted_text_class_2' => 'text-white/80',
        'muted_text_class_3' => 'text-white/40',
        'input_bg_class' => 'bg-white/10',
        'input_border_class' => 'border-white/20',
        'input_placeholder_class' => 'placeholder-white/40',
        'hover_bg_class' => 'hover:bg-white/5',
    ],

    'footer' => [
        'bg_class' => 'bg-black',
        'text_class' => 'text-white',
        'border_class' => 'border-white/10',
        'muted_text_class' => 'text-white/50',
        'muted_text_class_2' => 'text-white/40',
        'icon_bg_class' => 'bg-white/10',
    ],

    'ui' => [
        'page_bg' => 'bg-[#111111]',
        'section_bg' => 'bg-[#1a1a1a]',
        'heading_text' => 'text-white',
        'card_bg' => 'bg-secondary',
        'card_text' => 'text-white',
        'card_border' => 'border-white/10',
        'page_text' => 'text-white',
        'muted_text' => 'text-white/50',
        'muted_text_2' => 'text-white/40',
        'muted_text_3' => 'text-white/30',
        'input_bg' => 'bg-black',
        'input_border' => 'border-white/20',
        'input_text' => 'text-white',
        'input_placeholder' => 'placeholder-white/30',
        'divider' => 'border-white/10',
        'hover_bg' => 'hover:bg-white/5',
        'img_placeholder_bg' => 'bg-black',
        'btn_text' => 'text-[#111111]',
        'btn_outline_border' => 'border-white/20',
        'btn_outline_text' => 'text-white',
        'search_overlay_bg' => 'bg-black/80',
        'search_overlay_text' => 'text-white',
        'search_overlay_muted' => 'text-white/60',
        'search_overlay_border' => 'border-white/20',
        'search_overlay_input_bg' => 'bg-white/10',
        'search_overlay_placeholder' => 'placeholder-white/40',
        'banner_overlay' => 'from-black/90 via-black/60',
        'banner_text' => 'text-white',
        'banner_muted' => 'text-white/60',
        'banner_muted_2' => 'text-white/40',
    ],

    'admin' => [
        'card_bg' => 'bg-[#1a1a1a]',
        'card_border' => 'border border-white/10',
        'card_text' => 'text-white',
        'muted_text' => 'text-white/40',
        'muted_text_2' => 'text-white/30',
        'muted_text_3' => 'text-white/50',
        'input_bg' => 'bg-black',
        'input_border' => 'border-white/20',
        'input_text' => 'text-white',
        'table_head_bg' => 'bg-black/50',
        'table_divider' => 'divide-white/10',
        'table_row_divider' => 'divide-white/5',
        'table_row_hover' => 'hover:bg-white/5',
        'btn_cancel_bg' => 'bg-white/10',
        'btn_cancel_text' => 'text-white',
        'btn_cancel_hover' => 'hover:bg-white/20',
        'heading_text' => 'text-white',
        'border_divider' => 'border-white/10',
        'checkbox_border' => 'border-white/30',
        'checkbox_bg' => 'bg-black',
        'flag_border' => 'border-white/10',
        'flag_hover' => 'hover:bg-white/5',
        'img_placeholder_bg' => 'bg-white/5',
        'img_placeholder_border' => 'border-white/10',
        'image_preview_bg' => 'bg-black/50',
    ],

    'auth' => [
        'overlay_class' => 'bg-black/85',
        'card_bg' => 'bg-secondary',
        'card_text' => 'text-white',
        'card_border' => 'border-white/5',
        'label_text' => 'text-white/60',
        'input_bg' => 'bg-bg-primary',
        'input_border' => 'border-white/20',
        'input_text' => 'text-white',
        'input_placeholder' => 'placeholder-white/30',
        'muted_text' => 'text-white/50',
        'muted_text_2' => 'text-white/40',
        'divider_border' => 'border-white/10',
        'divider_text' => 'text-white/40',
        'social_bg' => 'bg-white/5',
        'social_border' => 'border-white/10',
        'social_hover_border' => 'hover:border-accent/50',
        'social_hover_bg' => 'hover:bg-white/10',
        'social_text' => 'text-white',
        'checkbox_border' => 'border-white/30',
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
        ['code' => 'ingredients', 'label' => 'Key Ingredients', 'type' => 'textarea', 'is_filterable' => false],
        ['code' => 'volume', 'label' => 'Volume', 'type' => 'text', 'is_filterable' => true],
        ['code' => 'how_to_use', 'label' => 'How To Use', 'type' => 'textarea', 'is_filterable' => false],
        ['code' => 'benefits', 'label' => 'Benefits', 'type' => 'textarea', 'is_filterable' => false],
        ['code' => 'scent', 'label' => 'Scent Profile', 'type' => 'select', 'options' => ['Woody', 'Fresh', 'Citrus', 'Musk', 'Unscented'], 'is_filterable' => true],
        ['code' => 'skin_type', 'label' => 'Skin Type', 'type' => 'multiselect', 'options' => ['Oily', 'Dry', 'Normal', 'Combination', 'Sensitive'], 'is_filterable' => true],
    ],
];
