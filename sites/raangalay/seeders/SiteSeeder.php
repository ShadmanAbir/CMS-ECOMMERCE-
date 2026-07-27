<?php

namespace Sites\Raangalay\Seeders;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SiteSeeder extends Seeder
{
    /**
     * Seed the database based on the active theme.
     */
    public function run(): void
    {
        $theme = config('themes.active', 'badlab');

        $this->command->info("Seeding for theme: {$theme}");

        $this->seedAdmin();

        match ($theme) {
            'badlab'    => $this->seedBadLab(),
            'raangalay' => $this->seedRaangalay(),
            default     => throw new \RuntimeException("Unknown theme: {$theme}"),
        };
    }

    // ─── Admin user (shared across themes) ────────────────────────────

    protected function seedAdmin(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'              => 'Admin',
                'phone'             => '01700000000',
                'password'          => Hash::make('password'),
                'role'              => 'admin',
                'status'            => 'active',
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user seeded: admin@example.com / password');
    }

    // ─── Helper: create a product with a placeholder image ─────────────

    protected function createProduct(array $data): Product
    {
        $product = Product::create([
            'name'         => $data['name'],
            'slug'         => Str::slug($data['name']),
            'sku'          => $data['sku'],
            'category_id'  => $data['category_id'],
            'price'        => $data['price'] ?? 0,
            'discount'     => $data['discount'] ?? 0,
            'stock'        => $data['stock'] ?? 50,
            'status'       => 'active',
            'description'  => $data['description'] ?? null,
            'tags'         => $data['tags'] ?? null,
            'volume'       => $data['volume'] ?? null,
            'ingredients'  => $data['ingredients'] ?? null,
            'benefits'     => $data['benefits'] ?? null,
            'how_to_use'   => $data['how_to_use'] ?? null,
            'is_trending'  => $data['is_trending'] ?? false,
            'is_new'       => $data['is_new'] ?? false,
            'is_featured'  => $data['is_featured'] ?? false,
            'colors'       => $data['colors'] ?? null,
            'sizes'        => $data['sizes'] ?? null,
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'image_path' => 'products/placeholder.png',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        return $product;
    }

    // ─── Helper: create a banner ──────────────────────────────────────

    protected function createBanner(string $title, string $image, ?string $link = null, int $sortOrder = 0): Banner
    {
        $banner = Banner::create([
            'title'  => $title,
            'image'  => $image,
            'link'   => $link,
            'status' => true,
        ]);
        // sort_order is in the schema but not in $fillable
        $banner->sort_order = $sortOrder;
        $banner->save();

        return $banner;
    }

    // ═══════════════════════════════════════════════════════════════════
    //  BAD LAB — men's grooming & cosmetics
    // ═══════════════════════════════════════════════════════════════════

    protected function seedBadLab(): void
    {
        // ── Categories ────────────────────────────────────────────────
        $hairCare = Category::create([
            'name' => 'Hair Care', 'slug' => 'hair-care',
            'description' => 'Shampoos, styling products and more for men\'s hair.',
            'status' => 'active', 'sort_order' => 1,
        ]);

        $bodyCare = Category::create([
            'name' => 'Body Care', 'slug' => 'body-care',
            'description' => 'Shower gels, deodorants and body cleansers.',
            'status' => 'active', 'sort_order' => 2,
        ]);

        $faceCare = Category::create([
            'name' => 'Face Care', 'slug' => 'face-care',
            'description' => 'Facial cleansers, scrubs and moisturisers for men.',
            'status' => 'active', 'sort_order' => 3,
        ]);

        $skinCare = Category::create([
            'name' => 'Skin Care', 'slug' => 'skin-care',
            'description' => 'Moisturisers, serums and skin treatments.',
            'status' => 'active', 'sort_order' => 4,
        ]);

        $fragrance = Category::create([
            'name' => 'Fragrance', 'slug' => 'fragrance',
            'description' => 'Bold masculine fragrances and deodorant sprays.',
            'status' => 'active', 'sort_order' => 5,
        ]);

        // ── Hair Care products ────────────────────────────────────────
        $this->createProduct([
            'name'        => 'Bad Lab No Flakes Scalp Care Shampoo – Anti Dandruff',
            'sku'         => 'BL-SHP-001',
            'category_id' => $hairCare->id,
            'price'       => 450,
            'volume'      => '350ml',
            'tags'        => ['shampoo', 'anti-dandruff', 'scalp care'],
            'description' => 'Anti-dandruff shampoo that fights flakes while keeping your scalp healthy and refreshed.',
            'is_trending' => true,
        ]);

        $this->createProduct([
            'name'        => 'Bad Lab Sculpting Hair Clay',
            'sku'         => 'BL-STY-001',
            'category_id' => $hairCare->id,
            'price'       => 350,
            'volume'      => '50g',
            'tags'        => ['hair clay', 'styling', 'matte finish'],
            'description' => 'Sculpting hair clay for textured, matte-finish styles with strong hold.',
        ]);

        $this->createProduct([
            'name'        => 'Bad Lab No Worries Scalp Care Shampoo – All Hair Types',
            'sku'         => 'BL-SHP-002',
            'category_id' => $hairCare->id,
            'price'       => 450,
            'volume'      => '350ml',
            'tags'        => ['shampoo', 'all hair types', 'scalp care'],
            'description' => 'Gentle everyday shampoo suitable for all hair types. Cleanses without stripping natural oils.',
        ]);

        // ── Body Care products ────────────────────────────────────────
        $this->createProduct([
            'name'        => 'Bad Lab 3-in-1 Hair, Face & Body Cleaner – CLASSIC',
            'sku'         => 'BL-3IN1-001',
            'category_id' => $bodyCare->id,
            'price'       => 550,
            'volume'      => '400ml',
            'tags'        => ['3-in-1', 'classic', 'all-in-one'],
            'description' => 'All-in-one cleanser for hair, face, and body. The classic formula for everyday convenience.',
            'is_featured' => true,
        ]);

        $this->createProduct([
            'name'        => 'Bad Lab Refreshing Body Wash',
            'sku'         => 'BL-BW-001',
            'category_id' => $bodyCare->id,
            'price'       => 500,
            'volume'      => '400ml',
            'tags'        => ['body wash', 'refreshing', 'shower gel'],
            'description' => 'Refreshing body wash that cleanses and invigorates. Leaves skin feeling cool and revitalized.',
        ]);

        // ── Face Care products ────────────────────────────────────────
        $this->createProduct([
            'name'        => 'Bad Lab Energising Facial Cleanser – All Skin Types',
            'sku'         => 'BL-FC-001',
            'category_id' => $faceCare->id,
            'price'       => 400,
            'volume'      => '100ml',
            'tags'        => ['facial cleanser', 'energising', 'all skin types'],
            'description' => 'Energising facial cleanser that removes dirt and impurities while refreshing skin.',
            'how_to_use'  => 'Wet face, apply a small amount, massage gently, then rinse. Use morning and night.',
            'benefits'    => ['Deep cleanses pores', 'Removes excess oil', 'Refreshes skin'],
        ]);

        $this->createProduct([
            'name'        => 'Bad Lab Anti-Acne Facial Cleanser – Oil & Acne Protection',
            'sku'         => 'BL-FC-002',
            'category_id' => $faceCare->id,
            'price'       => 420,
            'volume'      => '100ml',
            'tags'        => ['facial cleanser', 'anti-acne', 'oil control'],
            'description' => 'Anti-acne facial cleanser that targets breakouts and controls excess oil.',
            'how_to_use'  => 'Wet face, apply a small amount, massage gently, rinse thoroughly. Use morning and night.',
            'benefits'    => ['Fights acne', 'Controls oil', 'Deep cleanses pores'],
            'is_trending' => true,
        ]);

        $this->createProduct([
            'name'        => 'Bad Lab Repair & Relief Facial Moisturiser',
            'sku'         => 'BL-FC-003',
            'category_id' => $faceCare->id,
            'price'       => 480,
            'volume'      => '45ml',
            'tags'        => ['moisturiser', 'repair', 'relief'],
            'description' => 'Hydrates and soothes skin. Helps restore the skin\'s natural barrier.',
            'how_to_use'  => 'Apply a pea-sized amount to clean face and neck. Use daily.',
            'benefits'    => ['Deep hydration', 'Repairs skin barrier', 'Soothes irritation'],
        ]);

        // ── Skin Care products ────────────────────────────────────────
        $this->createProduct([
            'name'        => 'Bad Lab Daily Defence Moisturiser SPF 30',
            'sku'         => 'BL-SK-001',
            'category_id' => $skinCare->id,
            'price'       => 600,
            'volume'      => '50ml',
            'tags'        => ['moisturiser', 'SPF', 'sun protection'],
            'description' => 'Lightweight daily moisturiser with SPF 30 protection against UV damage.',
            'is_new'      => true,
        ]);

        $this->createProduct([
            'name'        => 'Bad Lab Charcoal Face Mask',
            'sku'         => 'BL-SK-002',
            'category_id' => $skinCare->id,
            'price'       => 380,
            'volume'      => '75ml',
            'tags'        => ['face mask', 'charcoal', 'detoxifying'],
            'description' => 'Activated charcoal face mask that draws out impurities and tightens pores.',
        ]);

        // ── Fragrance products ────────────────────────────────────────
        $this->createProduct([
            'name'        => 'Bad Lab Anti-Bacterial & Deodorising Fragrance',
            'sku'         => 'BL-FR-001',
            'category_id' => $fragrance->id,
            'price'       => 300,
            'volume'      => '100ml',
            'tags'        => ['deodorant', 'anti-bacterial', 'fragrance'],
            'description' => 'Anti-bacterial deodorant spray with long-lasting masculine fragrance.',
        ]);

        $this->createProduct([
            'name'        => 'Bad Lab Cooling Roll-On Deodorant',
            'sku'         => 'BL-FR-002',
            'category_id' => $fragrance->id,
            'price'       => 250,
            'volume'      => '50ml',
            'tags'        => ['roll-on', 'cooling', 'deodorant'],
            'description' => 'Cooling roll-on deodorant with an icy-fresh sensation. All-day protection.',
            'is_new'      => true,
        ]);

        // ── Banners ───────────────────────────────────────────────────
        $this->createBanner(
            'Man-Gineered Grooming',
            'images/badlab/banner-hero.jpg',
            '/shop',
            1
        );
        $this->createBanner(
            'New 3-in-1 Formula',
            'images/badlab/banner-3in1.jpg',
            '/shop/body-care',
            2
        );
        $this->createBanner(
            'Try BAD — It\'s Good for You',
            'images/badlab/banner-try-bad.jpg',
            '/shop',
            3
        );

        $this->command->info('BAD LAB seeded: 5 categories, ' . Product::count() . ' products, 3 banners.');
    }

    // ═══════════════════════════════════════════════════════════════════
    //  RAANGALAY — traditional Bangladeshi textiles
    // ═══════════════════════════════════════════════════════════════════

    protected function seedRaangalay(): void
    {
        // ── Categories ────────────────────────────────────────────────
        $sarees = Category::create([
            'name' => 'Sarees', 'slug' => 'sarees',
            'description' => 'Handwoven and designer sarees for every occasion.',
            'status' => 'active', 'sort_order' => 1,
        ]);

        $panjabis = Category::create([
            'name' => 'Panjabis', 'slug' => 'panjabis',
            'description' => 'Traditional and modern panjabis for men.',
            'status' => 'active', 'sort_order' => 2,
        ]);

        $kurtas = Category::create([
            'name' => 'Kurtas', 'slug' => 'kurtas',
            'description' => 'Comfortable and stylish kurtas for everyday and festive wear.',
            'status' => 'active', 'sort_order' => 3,
        ]);

        $homeTextiles = Category::create([
            'name' => 'Home Textiles', 'slug' => 'home-textiles',
            'description' => 'Cushion covers, table runners and traditional home décor.',
            'status' => 'active', 'sort_order' => 4,
        ]);

        $accessories = Category::create([
            'name' => 'Accessories', 'slug' => 'accessories',
            'description' => 'Jewellery, bags and traditional accessories.',
            'status' => 'active', 'sort_order' => 5,
        ]);

        // ── Sarees products ───────────────────────────────────────────
        $this->createProduct([
            'name'        => 'Jamdani Saree – Royal Blue',
            'sku'         => 'RG-SR-001',
            'category_id' => $sarees->id,
            'price'       => 4500,
            'tags'        => ['jamdani', 'saree', 'traditional', 'handwoven'],
            'description' => 'Handwoven Jamdani saree in royal blue with intricate floral motifs. Perfect for festive occasions.',
            'colors'      => ['Royal Blue', 'Navy'],
            'is_featured' => true,
        ]);

        $this->createProduct([
            'name'        => 'Silk Saree – Crimson Red',
            'sku'         => 'RG-SR-002',
            'category_id' => $sarees->id,
            'price'       => 6000,
            'tags'        => ['silk', 'saree', 'wedding', 'traditional'],
            'description' => 'Pure silk saree in rich crimson with gold border. Ideal for weddings and special events.',
            'colors'      => ['Crimson Red', 'Maroon'],
            'is_trending' => true,
        ]);

        $this->createProduct([
            'name'        => 'Cotton Saree – Off-White with Black Border',
            'sku'         => 'RG-SR-003',
            'category_id' => $sarees->id,
            'price'       => 1800,
            'tags'        => ['cotton', 'saree', 'everyday', 'comfortable'],
            'description' => 'Lightweight cotton saree for daily wear. Soft fabric with elegant black border.',
            'colors'      => ['Off-White', 'Cream'],
        ]);

        // ── Panjabis products ─────────────────────────────────────────
        $this->createProduct([
            'name'        => 'Classic White Panjabi – Eid Collection',
            'sku'         => 'RG-PJ-001',
            'category_id' => $panjabis->id,
            'price'       => 2800,
            'tags'        => ['panjabi', 'eid', 'white', 'traditional'],
            'description' => 'Classic white cotton panjabi with subtle embroidery. Perfect for Eid and festive prayers.',
            'sizes'       => ['S', 'M', 'L', 'XL'],
            'colors'      => ['White'],
            'is_featured' => true,
        ]);

        $this->createProduct([
            'name'        => 'Premium Silk Panjabi – Maroon',
            'sku'         => 'RG-PJ-002',
            'category_id' => $panjabis->id,
            'price'       => 4200,
            'tags'        => ['panjabi', 'silk', 'premium', 'wedding'],
            'description' => 'Premium silk panjabi in deep maroon with gold thread work. Ideal for weddings.',
            'sizes'       => ['M', 'L', 'XL'],
            'colors'      => ['Maroon', 'Black'],
        ]);

        // ── Kurtas products ───────────────────────────────────────────
        $this->createProduct([
            'name'        => 'Casual Cotton Kurta – Sky Blue',
            'sku'         => 'RG-KT-001',
            'category_id' => $kurtas->id,
            'price'       => 1500,
            'tags'        => ['kurta', 'cotton', 'casual', 'everyday'],
            'description' => 'Comfortable cotton kurta for everyday wear. Breathable fabric in a calming sky blue.',
            'sizes'       => ['S', 'M', 'L', 'XL'],
            'colors'      => ['Sky Blue', 'White'],
        ]);

        $this->createProduct([
            'name'        => 'Festive Kurta – Mustard Yellow',
            'sku'         => 'RG-KT-002',
            'category_id' => $kurtas->id,
            'price'       => 2200,
            'tags'        => ['kurta', 'festive', 'mustard', 'traditional'],
            'description' => 'Vibrant mustard kurta with traditional block-print details. Great for festivals.',
            'sizes'       => ['M', 'L', 'XL'],
            'colors'      => ['Mustard', 'Olive'],
            'is_new'      => true,
        ]);

        // ── Home Textiles products ────────────────────────────────────
        $this->createProduct([
            'name'        => 'Handwoven Cushion Cover Set (4 pcs)',
            'sku'         => 'RG-HT-001',
            'category_id' => $homeTextiles->id,
            'price'       => 1200,
            'tags'        => ['cushion cover', 'handwoven', 'home décor', 'set'],
            'description' => 'Set of 4 handwoven cushion covers with traditional Bengali patterns. 16×16 inches.',
            'is_featured' => true,
        ]);

        $this->createProduct([
            'name'        => 'Jute Table Runner – Natural',
            'sku'         => 'RG-HT-002',
            'category_id' => $homeTextiles->id,
            'price'       => 850,
            'tags'        => ['table runner', 'jute', 'natural', 'home décor'],
            'description' => 'Eco-friendly jute table runner with hand-stitched edges. 72×14 inches.',
        ]);

        // ── Accessories products ──────────────────────────────────────
        $this->createProduct([
            'name'        => 'Nakshi Kantha Stitch Tote Bag',
            'sku'         => 'RG-AC-001',
            'category_id' => $accessories->id,
            'price'       => 1600,
            'tags'        => ['tote bag', 'nakshi kantha', 'handmade', 'accessories'],
            'description' => 'Handmade tote bag featuring traditional Nakshi Kantha embroidery. Spacious and durable.',
            'colors'      => ['Multicolour', 'Earth Tones'],
            'is_new'      => true,
        ]);

        $this->createProduct([
            'name'        => 'Brass Bangle Set – Traditional',
            'sku'         => 'RG-AC-002',
            'category_id' => $accessories->id,
            'price'       => 950,
            'tags'        => ['bangles', 'brass', 'traditional', 'jewellery'],
            'description' => 'Set of 6 handcrafted brass bangles with traditional Bengali designs.',
            'colors'      => ['Gold', 'Antique Brass'],
        ]);

        // ── Banners ───────────────────────────────────────────────────
        $this->createBanner(
            'Celebrate Bengali Tradition',
            'images/raangalay/banner-hero.jpg',
            '/shop',
            1
        );
        $this->createBanner(
            'Eid Collection — Sarees & Panjabis',
            'images/raangalay/banner-eid.jpg',
            '/shop/sarees',
            2
        );
        $this->createBanner(
            'Handwoven Home Textiles',
            'images/raangalay/banner-home.jpg',
            '/shop/home-textiles',
            3
        );

        $this->command->info('Raangalay seeded: 5 categories, ' . Product::count() . ' products, 3 banners.');
    }
}
