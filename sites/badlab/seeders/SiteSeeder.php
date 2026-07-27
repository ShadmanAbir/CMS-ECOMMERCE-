<?php

namespace Sites\Badlab\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        // ── Categories ──────────────────────────────────────────────
        $hairCare   = Category::create(['name' => 'Hair Care',       'slug' => 'hair-care',      'status' => 'active', 'sort_order' => 1]);
        $shampoo    = Category::create(['name' => 'Shampoo',         'slug' => 'shampoo',         'parent_id' => $hairCare->id, 'status' => 'active', 'sort_order' => 1]);
        $styling    = Category::create(['name' => 'Hair Styling',    'slug' => 'hair-styling',    'parent_id' => $hairCare->id, 'status' => 'active', 'sort_order' => 2]);

        $bodyCare   = Category::create(['name' => 'Body Care',       'slug' => 'body-care',       'status' => 'active', 'sort_order' => 2]);
        $showerGel  = Category::create(['name' => 'Shower Gel',      'slug' => 'shower-gel',      'parent_id' => $bodyCare->id, 'status' => 'active', 'sort_order' => 1]);
        $threeInOne = Category::create(['name' => '3 in 1',          'slug' => '3-in-1',          'parent_id' => $bodyCare->id, 'status' => 'active', 'sort_order' => 2]);
        $deodorant  = Category::create(['name' => 'Deodorant',       'slug' => 'deodorant',       'parent_id' => $bodyCare->id, 'status' => 'active', 'sort_order' => 3]);
        $rollOn     = Category::create(['name' => 'Roll On Deodorant','slug' => 'roll-on-deodorant','parent_id' => $bodyCare->id, 'status' => 'active', 'sort_order' => 4]);

        $facialCare = Category::create(['name' => 'Facial Care',     'slug' => 'facial-care',     'status' => 'active', 'sort_order' => 3]);
        $cleansers  = Category::create(['name' => 'Cleansers',       'slug' => 'cleansers',       'parent_id' => $facialCare->id, 'status' => 'active', 'sort_order' => 1]);

        // ── Placeholder image path ──────────────────────────────────
        $placeholderImage = 'products/placeholder.png';

        // ── Price map by category ID (BDT) ─────────────────────────
        $priceMap = [
            $shampoo->id    => 450,
            $styling->id    => 350,
            $showerGel->id  => 500,
            $threeInOne->id => 550,
            $deodorant->id  => 300,
            $rollOn->id     => 250,
            $cleansers->id  => 400,
        ];

        // ── Helper to create product + image ────────────────────────
        $slugCounters = [];
        $createProduct = function (array $data) use ($placeholderImage, &$slugCounters, $priceMap) {
            // Auto-assign price from category if not explicitly set
            if (!isset($data['price'])) {
                $data['price'] = $priceMap[$data['category_id']] ?? 350;
            }
            // Generate unique slug by appending volume if name is duplicated
            $baseSlug = Str::slug($data['name']);
            $slug = $baseSlug;
            if (isset($slugCounters[$baseSlug])) {
                $slugCounters[$baseSlug]++;
                $volumeSuffix = isset($data['volume']) ? '-' . Str::slug($data['volume']) : '';
                $slug = $baseSlug . $volumeSuffix . '-' . $slugCounters[$baseSlug];
            } else {
                $slugCounters[$baseSlug] = 1;
                // If volume exists, include it in the slug from the start
                if (isset($data['volume'])) {
                    $slug = $baseSlug . '-' . Str::slug($data['volume']);
                }
            }

            $product = Product::create([
                'name'         => $data['name'],
                'slug'         => $slug,
                'sku'          => $data['sku'],
                'category_id'  => $data['category_id'],
                'volume'       => $data['volume'] ?? null,
                'price'        => $data['price'] ?? 0,
                'discount'     => 0,
                'stock'        => 100,
                'status'       => 'active',
                'description'  => $data['description'] ?? null,
                'tags'         => $data['tags'] ?? null,
                'ingredients'  => $data['ingredients'] ?? null,
                'benefits'     => $data['benefits'] ?? null,
                'how_to_use'   => $data['how_to_use'] ?? null,
                'is_trending'  => $data['is_trending'] ?? false,
                'is_new'       => $data['is_new'] ?? false,
                'is_featured'  => $data['is_featured'] ?? false,
            ]);

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $placeholderImage,
                'is_primary' => true,
                'sort_order' => 0,
            ]);

            return $product;
        };

        // ════════════════════════════════════════════════════════════
        // HAIR CARE – SHAMPOO
        // ════════════════════════════════════════════════════════════
        $createProduct([
            'name'        => 'Bad Lab No Flakes Scalp Care Shampoo – Anti Dandruff',
            'category_id' => $shampoo->id,
            'sku'         => '955-5723-80032-7',
            'volume'      => '350ml',
            'tags'        => ['shampoo', 'anti-dandruff', 'scalp care', 'hair care'],
            'description' => 'Anti-dandruff scalp care shampoo that effectively fights flakes while keeping your scalp healthy and refreshed.',
            'is_trending' => true,
        ]);

        $createProduct([
            'name'        => 'Bad Lab No Worries Scalp Care Shampoo – All Hair Types',
            'category_id' => $shampoo->id,
            'sku'         => '955-57238-0034-1',
            'volume'      => '350ml',
            'tags'        => ['shampoo', 'all hair types', 'scalp care', 'hair care'],
            'description' => 'Gentle everyday shampoo suitable for all hair types. Cleanses and nourishes without stripping natural oils.',
        ]);

        $createProduct([
            'name'        => 'Bad Lab No Drama Scalp Care Shampoo – Hair Fall Control',
            'category_id' => $shampoo->id,
            'sku'         => '955-5723-80415-8',
            'volume'      => '350ml',
            'tags'        => ['shampoo', 'hair fall control', 'scalp care', 'hair care'],
            'description' => 'Strengthening shampoo formulated to reduce hair fall by nourishing the scalp and fortifying hair from root to tip.',
            'is_trending' => true,
        ]);

        $createProduct([
            'name'        => 'Bad Lab No Fuss Scalp Care Shampoo – Oily Hair',
            'category_id' => $shampoo->id,
            'sku'         => '955-5723-80416-5',
            'volume'      => '350ml',
            'tags'        => ['shampoo', 'oily hair', 'scalp care', 'hair care'],
            'description' => 'Deep-cleansing shampoo for oily hair that controls excess sebum and keeps your scalp matte and fresh all day.',
        ]);

        // ════════════════════════════════════════════════════════════
        // HAIR CARE – HAIR STYLING
        // ════════════════════════════════════════════════════════════
        $createProduct([
            'name'        => 'Bad Lab Sculpting Hair Clay',
            'category_id' => $styling->id,
            'sku'         => '955-5723-80035-8',
            'volume'      => '50g',
            'tags'        => ['hair clay', 'styling', 'matte finish', 'hair care'],
            'description' => 'Sculpting hair clay for textured, matte-finish styles. Provides strong hold with natural-looking results.',
        ]);

        $createProduct([
            'name'        => 'Bad Lab Hair Cream',
            'category_id' => $styling->id,
            'sku'         => '955-5723-80112-6',
            'volume'      => '80g',
            'tags'        => ['hair cream', 'styling', 'moisturizing', 'hair care'],
            'description' => 'Lightweight hair cream that provides flexible hold and natural shine. Tames frizz without crunch.',
        ]);

        $createProduct([
            'name'        => 'Bad Lab Hair Gel',
            'category_id' => $styling->id,
            'sku'         => '955-5723-80083-9',
            'volume'      => '325g',
            'tags'        => ['hair gel', 'styling', 'strong hold', 'hair care'],
            'description' => 'High-hold hair gel for maximum control and glossy finish. Keeps your style locked in all day.',
        ]);

        $createProduct([
            'name'        => 'Bad Lab Hair Gel',
            'category_id' => $styling->id,
            'sku'         => '955-5723-80036-5',
            'volume'      => '150ml',
            'tags'        => ['hair gel', 'styling', 'strong hold', 'hair care'],
            'description' => 'Compact size hair gel for on-the-go styling. Strong hold with a brilliant glossy finish.',
        ]);

        // Water-Based Pomade – Medium & Glossy
        $createProduct([
            'name'        => 'Bad Lab Water-Based Pomade – Medium Hold & Glossy',
            'category_id' => $styling->id,
            'sku'         => '955-5723-80082-2',
            'volume'      => '40g',
            'tags'        => ['pomade', 'medium hold', 'glossy', 'water-based', 'hair care'],
            'description' => 'Water-based pomade with medium hold and glossy finish. Washes out easily with water.',
        ]);
        $createProduct([
            'name'        => 'Bad Lab Water-Based Pomade – Medium Hold & Glossy',
            'category_id' => $styling->id,
            'sku'         => '955-5723-80113-3',
            'volume'      => '80g',
            'tags'        => ['pomade', 'medium hold', 'glossy', 'water-based', 'hair care'],
            'description' => 'Water-based pomade with medium hold and glossy finish. Washes out easily with water.',
        ]);

        // Water-Based Pomade – Strong & Glossy
        $createProduct([
            'name'        => 'Bad Lab Water-Based Pomade – Strong Hold & Glossy',
            'category_id' => $styling->id,
            'sku'         => '955-5723-80176-8',
            'volume'      => '40g',
            'tags'        => ['pomade', 'strong hold', 'glossy', 'water-based', 'hair care'],
            'description' => 'Strong hold water-based pomade with high shine. Perfect for classic slick-back styles.',
        ]);
        $createProduct([
            'name'        => 'Bad Lab Water-Based Pomade – Strong Hold & Glossy',
            'category_id' => $styling->id,
            'sku'         => '955-5723-80177-5',
            'volume'      => '80g',
            'tags'        => ['pomade', 'strong hold', 'glossy', 'water-based', 'hair care'],
            'description' => 'Strong hold water-based pomade with high shine. Perfect for classic slick-back styles.',
        ]);

        // Water-Based Pomade – Strong & Matte
        $createProduct([
            'name'        => 'Bad Lab Water-Based Pomade – Strong Hold & Matte',
            'category_id' => $styling->id,
            'sku'         => '955-5723-80178-2',
            'volume'      => '40g',
            'tags'        => ['pomade', 'strong hold', 'matte', 'water-based', 'hair care'],
            'description' => 'Strong hold water-based pomade with natural matte finish. Ideal for modern textured looks.',
        ]);
        $createProduct([
            'name'        => 'Bad Lab Water-Based Pomade – Strong Hold & Matte',
            'category_id' => $styling->id,
            'sku'         => '955-5723-80179-9',
            'volume'      => '80g',
            'tags'        => ['pomade', 'strong hold', 'matte', 'water-based', 'hair care'],
            'description' => 'Strong hold water-based pomade with natural matte finish. Ideal for modern textured looks.',
        ]);

        // Water-Based Pomade Gel – Strong & Glossy
        $createProduct([
            'name'        => 'Bad Lab Water-Based Pomade Gel – Strong Hold & Glossy',
            'category_id' => $styling->id,
            'sku'         => '9-555-7238-0446-2',
            'volume'      => '40g',
            'tags'        => ['pomade gel', 'strong hold', 'glossy', 'water-based', 'hair care'],
            'description' => 'Hybrid pomade gel combining the hold of a gel with the reworkability of a pomade. High shine finish.',
        ]);
        $createProduct([
            'name'        => 'Bad Lab Water-Based Pomade Gel – Strong Hold & Glossy',
            'category_id' => $styling->id,
            'sku'         => '9-555-7238-0447-9',
            'volume'      => '80g',
            'tags'        => ['pomade gel', 'strong hold', 'glossy', 'water-based', 'hair care'],
            'description' => 'Hybrid pomade gel combining the hold of a gel with the reworkability of a pomade. High shine finish.',
        ]);

        // Water-Based Pomade Cream – Medium & Glossy
        $createProduct([
            'name'        => 'Bad Lab Water-Based Pomade Cream – Medium Hold & Glossy',
            'category_id' => $styling->id,
            'sku'         => '9-555-7238-0448-6',
            'volume'      => '40g',
            'tags'        => ['pomade cream', 'medium hold', 'glossy', 'water-based', 'hair care'],
            'description' => 'Creamy pomade with medium hold and glossy finish. Easy to apply and restyle throughout the day.',
        ]);
        $createProduct([
            'name'        => 'Bad Lab Water-Based Pomade Cream – Medium Hold & Glossy',
            'category_id' => $styling->id,
            'sku'         => '9-555-7238-0449-3',
            'volume'      => '80g',
            'tags'        => ['pomade cream', 'medium hold', 'glossy', 'water-based', 'hair care'],
            'description' => 'Creamy pomade with medium hold and glossy finish. Easy to apply and restyle throughout the day.',
        ]);

        // Hair Wax
        $createProduct([
            'name'        => 'Bad Lab Soft Texture Hair Wax',
            'category_id' => $styling->id,
            'sku'         => '955-5723-80180-5',
            'volume'      => '70g',
            'tags'        => ['hair wax', 'soft texture', 'styling', 'hair care'],
            'description' => 'Soft texture hair wax for pliable, natural-looking styles with medium hold.',
        ]);
        $createProduct([
            'name'        => 'Bad Lab Soft Texture Hair Wax',
            'category_id' => $styling->id,
            'sku'         => '955-5723-80181-2',
            'volume'      => '120g',
            'tags'        => ['hair wax', 'soft texture', 'styling', 'hair care'],
            'description' => 'Soft texture hair wax for pliable, natural-looking styles with medium hold.',
        ]);
        $createProduct([
            'name'        => 'Bad Lab Solid Texture Hair Wax',
            'category_id' => $styling->id,
            'sku'         => '955-5723-80182-9',
            'volume'      => '70g',
            'tags'        => ['hair wax', 'solid texture', 'styling', 'strong hold', 'hair care'],
            'description' => 'Firm hold hair wax for defined, structured styles with lasting control.',
        ]);
        $createProduct([
            'name'        => 'Bad Lab Solid Texture Hair Wax',
            'category_id' => $styling->id,
            'sku'         => '955-5723-80183-6',
            'volume'      => '120g',
            'tags'        => ['hair wax', 'solid texture', 'styling', 'strong hold', 'hair care'],
            'description' => 'Firm hold hair wax for defined, structured styles with lasting control.',
        ]);

        // ════════════════════════════════════════════════════════════
        // BODY CARE – SHOWER GEL
        // ════════════════════════════════════════════════════════════
        $createProduct([
            'name'        => 'Bad Lab Refreshing Body Wash',
            'category_id' => $showerGel->id,
            'sku'         => '955-5723-80038-9',
            'volume'      => '400ml',
            'tags'        => ['body wash', 'refreshing', 'shower gel', 'body care'],
            'description' => 'Refreshing body wash that cleanses and invigorates. Leaves skin feeling cool and revitalized.',
        ]);

        $createProduct([
            'name'        => 'Bad Lab Body Sculpting Shower Gel',
            'category_id' => $showerGel->id,
            'sku'         => '955-5723-80040-2',
            'volume'      => '400ml',
            'tags'        => ['shower gel', 'body sculpting', 'energizing', 'body care'],
            'description' => 'Energizing shower gel with body-sculpting formula. Invigorates senses and refreshes skin.',
        ]);

        // ════════════════════════════════════════════════════════════
        // BODY CARE – 3 IN 1
        // ════════════════════════════════════════════════════════════
        $createProduct([
            'name'        => 'Bad Lab 3-in-1 Hair, Face & Body Cleaner – CLASSIC',
            'category_id' => $threeInOne->id,
            'sku'         => '955-5723-80029-7',
            'volume'      => '200ml',
            'tags'        => ['3-in-1', 'classic', 'all-in-one', 'body care'],
            'description' => 'All-in-one cleanser for hair, face, and body. The classic formula for everyday convenience.',
        ]);

        $createProduct([
            'name'        => 'Bad Lab 3-in-1 Hair, Face & Body Cleaner – CLASSIC',
            'category_id' => $threeInOne->id,
            'sku'         => '955-5723-80030-3',
            'volume'      => '400ml',
            'tags'        => ['3-in-1', 'classic', 'all-in-one', 'body care'],
            'description' => 'All-in-one cleanser for hair, face, and body. The classic formula in a larger size.',
        ]);

        $createProduct([
            'name'        => 'Bad Lab 3-in-1 Hair, Face & Body Cleaner – CLASSIC',
            'category_id' => $threeInOne->id,
            'sku'         => '955-5723-80175-1',
            'volume'      => '800ml',
            'tags'        => ['3-in-1', 'classic', 'all-in-one', 'body care', 'value size'],
            'description' => 'All-in-one cleanser for hair, face, and body. The classic formula in a family size.',
        ]);

        $createProduct([
            'name'        => 'Bad Lab 3-in-1 Hair, Face & Body Cleaner – ANTI-AGING',
            'category_id' => $threeInOne->id,
            'sku'         => '955-5723-80266-6',
            'volume'      => '400ml',
            'tags'        => ['3-in-1', 'anti-aging', 'all-in-one', 'body care'],
            'description' => 'Anti-aging formula 3-in-1 cleanser that helps reduce signs of aging while cleaning hair, face, and body.',
            'is_new'      => true,
        ]);

        $createProduct([
            'name'        => 'Bad Lab 3-in-1 Hair, Face & Body Cleaner – DEEP CLEANSING',
            'category_id' => $threeInOne->id,
            'sku'         => '955-5723-80267-3',
            'volume'      => '400ml',
            'tags'        => ['3-in-1', 'deep cleansing', 'all-in-one', 'body care'],
            'description' => 'Deep cleansing formula 3-in-1 cleanser that provides a thorough clean for hair, face, and body.',
            'is_new'      => true,
        ]);

        // ════════════════════════════════════════════════════════════
        // BODY CARE – DEODORANT
        // ════════════════════════════════════════════════════════════
        $createProduct([
            'name'        => 'Bad Lab Anti-Bacterial & Deodorising Fragrance',
            'category_id' => $deodorant->id,
            'sku'         => '955-5723-80042-6',
            'volume'      => '100ml',
            'tags'        => ['deodorant', 'anti-bacterial', 'fragrance', 'body care'],
            'description' => 'Anti-bacterial deodorant spray with long-lasting fragrance. Keeps you fresh and odor-free.',
        ]);

        $createProduct([
            'name'        => 'Bad Lab Dual Anti-Bacterial & Deodorising Fragrance',
            'category_id' => $deodorant->id,
            'sku'         => '955-5723-80140-9',
            'volume'      => '100ml',
            'tags'        => ['deodorant', 'dual action', 'anti-bacterial', 'fragrance', 'body care'],
            'description' => 'Dual-action anti-bacterial deodorant with enhanced odor protection and a bold masculine scent.',
        ]);

        $createProduct([
            'name'        => 'Bad Lab Cooling Anti-Bacterial & Deodorising Fragrance',
            'category_id' => $deodorant->id,
            'sku'         => '955-5723-80139-3',
            'volume'      => '100ml',
            'tags'        => ['deodorant', 'cooling', 'anti-bacterial', 'fragrance', 'body care'],
            'description' => 'Cooling anti-bacterial deodorant spray that provides an instant burst of freshness with lasting protection.',
        ]);

        // ════════════════════════════════════════════════════════════
        // BODY CARE – ROLL ON DEODORANT
        // ════════════════════════════════════════════════════════════
        $createProduct([
            'name'        => 'Bad Lab Roll-On Deodorant',
            'category_id' => $rollOn->id,
            'sku'         => '955-5723-80300-7',
            'volume'      => '50ml',
            'tags'        => ['roll-on', 'deodorant', 'antiperspirant', 'body care'],
            'description' => 'Smooth roll-on deodorant that provides reliable all-day protection against odor and wetness.',
        ]);

        $createProduct([
            'name'        => 'Bad Lab Roll-On Deodorant',
            'category_id' => $rollOn->id,
            'sku'         => '955-5723-80302-1',
            'volume'      => '50ml',
            'tags'        => ['roll-on', 'deodorant', 'antiperspirant', 'body care'],
            'description' => 'Smooth roll-on deodorant in a convenient size. Reliable all-day odor protection.',
        ]);

        $createProduct([
            'name'        => 'Bad Lab Cooling Roll-On Deodorant',
            'category_id' => $rollOn->id,
            'sku'         => '955-5723-80301-4',
            'volume'      => '50ml',
            'tags'        => ['roll-on', 'deodorant', 'cooling', 'antiperspirant', 'body care'],
            'description' => 'Cooling roll-on deodorant with an icy fresh sensation. Keeps you cool and protected all day.',
        ]);

        $createProduct([
            'name'        => 'Bad Lab Cooling Roll-On Deodorant',
            'category_id' => $rollOn->id,
            'sku'         => '955-5723-80303-8',
            'volume'      => '50ml',
            'tags'        => ['roll-on', 'deodorant', 'cooling', 'antiperspirant', 'body care'],
            'description' => 'Cooling roll-on deodorant with an icy fresh sensation. Keeps you cool and protected all day.',
        ]);

        // ════════════════════════════════════════════════════════════
        // FACIAL CARE – CLEANSERS (All Skin Types)
        // ════════════════════════════════════════════════════════════
        $createProduct([
            'name'        => 'Bad Lab Energising Facial Cleanser – All Skin Types',
            'category_id' => $cleansers->id,
            'sku'         => '955-5723-80026-6',
            'volume'      => '100ml',
            'tags'        => ['facial cleanser', 'energising', 'all skin types', 'facial care'],
            'description' => 'Energising facial cleanser suitable for all skin types. Removes dirt and impurities while refreshing skin.',
            'how_to_use'  => 'Wet face, apply a small amount, massage gently in circular motions, then rinse thoroughly. Use morning and night.',
            'benefits'    => ['Deep cleanses pores', 'Removes excess oil', 'Refreshes and energizes skin'],
        ]);

        $createProduct([
            'name'        => 'Bad Lab Invigorating Facial Scrub – All Skin Types',
            'category_id' => $cleansers->id,
            'sku'         => '955-5723-80027-3',
            'volume'      => '100ml',
            'tags'        => ['facial scrub', 'invigorating', 'exfoliating', 'all skin types', 'facial care'],
            'description' => 'Invigorating facial scrub that exfoliates dead skin cells, unclogs pores, and reveals smoother, healthier skin.',
            'how_to_use'  => 'Apply to damp face, gently massage in circular motions avoiding eye area, rinse thoroughly. Use 2-3 times per week.',
            'benefits'    => ['Exfoliates dead skin', 'Unclogs pores', 'Reveals smoother skin'],
        ]);

        $createProduct([
            'name'        => 'Bad Lab Repair & Relief Facial Moisturiser',
            'category_id' => $cleansers->id,
            'sku'         => '955-5723-80028-0',
            'volume'      => '45ml',
            'tags'        => ['moisturiser', 'repair', 'relief', 'facial care'],
            'description' => 'Repair and relief facial moisturiser that hydrates and soothes skin. Helps restore the skin\'s natural barrier.',
            'how_to_use'  => 'Apply a pea-sized amount to clean face and neck. Gently massage until absorbed. Use daily.',
            'benefits'    => ['Deep hydration', 'Repairs skin barrier', 'Soothes irritation'],
        ]);

        // FACIAL CARE – CLEANSERS (Oil & Acne Protection)
        $createProduct([
            'name'        => 'Bad Lab Anti-Acne Facial Cleanser – Oil & Acne Protection',
            'category_id' => $cleansers->id,
            'sku'         => '955-5723-80111-9',
            'volume'      => '100ml',
            'tags'        => ['facial cleanser', 'anti-acne', 'oil control', 'facial care'],
            'description' => 'Anti-acne facial cleanser that targets breakouts and controls excess oil. Keeps skin clear and balanced.',
            'how_to_use'  => 'Wet face, apply a small amount, massage gently, rinse thoroughly. Use morning and night.',
            'benefits'    => ['Fights acne and breakouts', 'Controls excess oil', 'Deep cleanses pores'],
            'is_trending' => true,
        ]);

        $createProduct([
            'name'        => 'Bad Lab Deep Cleanse Charcoal Cleanser – Oil & Acne Protection',
            'category_id' => $cleansers->id,
            'sku'         => '955-5723-80109-6',
            'volume'      => '100ml',
            'tags'        => ['charcoal cleanser', 'deep cleanse', 'anti-acne', 'oil control', 'facial care'],
            'description' => 'Activated charcoal deep cleanse facial cleanser that draws out impurities and excess oil for clearer skin.',
            'how_to_use'  => 'Apply to wet face, massage in circular motions, rinse thoroughly. Use daily for best results.',
            'benefits'    => ['Draws out impurities', 'Absorbs excess oil', 'Purifies skin'],
        ]);

        $createProduct([
            'name'        => 'Bad Lab Pore Refine & Matte-Finish Mud Cleanser – Oil & Acne Protection',
            'category_id' => $cleansers->id,
            'sku'         => '955-5723-80254-3',
            'volume'      => '100ml',
            'tags'        => ['mud cleanser', 'pore refine', 'matte finish', 'anti-acne', 'facial care'],
            'description' => 'Mud-based cleanser that refines pores and leaves a matte finish. Controls oil and prevents acne.',
            'how_to_use'  => 'Apply to damp face, massage gently focusing on T-zone, rinse thoroughly. Use daily.',
            'benefits'    => ['Minimizes pore appearance', 'Mattifies skin', 'Controls oil production'],
        ]);

        $createProduct([
            'name'        => 'Bad Lab Clear Blackhead Foaming Gel – Oil & Acne Protection',
            'category_id' => $cleansers->id,
            'sku'         => '9555723804660',
            'volume'      => '100ml',
            'tags'        => ['blackhead removal', 'foaming gel', 'anti-acne', 'facial care'],
            'description' => 'Foaming gel cleanser that targets blackheads and prevents acne. Gently dissolves impurities for clearer skin.',
            'how_to_use'  => 'Dispense onto palms, apply to wet face, massage gently, rinse thoroughly. Use morning and night.',
            'benefits'    => ['Clears blackheads', 'Prevents breakouts', 'Gentle foaming action'],
            'is_new'      => true,
        ]);

        // FACIAL CARE – CLEANSERS (Extra Cooling)
        $createProduct([
            'name'        => 'Bad Lab Brightening Facial Cleanser – Extra Cooling',
            'category_id' => $cleansers->id,
            'sku'         => '955-5723-80253-6',
            'volume'      => '100ml',
            'tags'        => ['facial cleanser', 'brightening', 'cooling', 'facial care'],
            'description' => 'Brightening facial cleanser with an extra cooling sensation. Reveals brighter, more radiant skin with every wash.',
            'how_to_use'  => 'Wet face, apply a small amount, massage gently, rinse thoroughly. Use morning and night.',
            'benefits'    => ['Brightens skin', 'Cooling sensation', 'Even skin tone'],
        ]);

        $createProduct([
            'name'        => 'Bad Lab Soothing Facial Cleanser For Sensitive Skin – Extra Cooling',
            'category_id' => $cleansers->id,
            'sku'         => '9555723804677',
            'volume'      => '100ml',
            'tags'        => ['facial cleanser', 'sensitive skin', 'soothing', 'cooling', 'facial care'],
            'description' => 'Gentle soothing facial cleanser specially formulated for sensitive skin. Extra cooling without irritation.',
            'how_to_use'  => 'Wet face, apply a small amount, massage gently, rinse thoroughly. Use morning and night.',
            'benefits'    => ['Soothes sensitive skin', 'Cooling comfort', 'Gentle cleansing'],
            'is_new'      => true,
        ]);

        $this->command->info('BAD LAB: Seeded 3 parent categories, 7 subcategories, and ' . Product::count() . ' products with placeholder images.');
    }
}
