<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;

class CategoryComposer
{
    /**
     * Provides theme-specific category image maps and taglines.
     * These were previously hardcoded match() blocks in view files.
     */
    public function compose(View $view): void
    {
        $themeSlug = theme('slug', 'badlab');

        // Category image map - theme-specific
        $view->with('categoryImageMap', $this->getImageMap($themeSlug));
        $view->with('categoryTaglineMap', $this->getTaglineMap($themeSlug));
    }

    protected function getImageMap(string $theme): array
    {
        $maps = [
            'badlab' => [
                'hair care' => asset('images/badlab/cat-hair.png'),
                'hair styling' => asset('images/badlab/cat-hair.png'),
                'body care' => asset('images/badlab/cat-body.png'),
                'facial care' => asset('images/badlab/cat-skin.png'),
                'face care' => asset('images/badlab/cat-skin.png'),
                'skin care' => asset('images/badlab/cat-skin.png'),
            ],
            'raangalay' => [
                'sarees'         => asset('images/raangalay/product/product1.png'),
                'panjabis'       => asset('images/raangalay/product/product2.png'),
                'kurtas'         => asset('images/raangalay/product/product4.png'),
                'home textiles'  => asset('images/raangalay/product/product7.png'),
                'accessories'    => asset('images/raangalay/product/product8.png'),
            ],
        ];

        return $maps[$theme] ?? [];
    }

    protected function getTaglineMap(string $theme): array
    {
        $maps = [
            'badlab' => [
                'hair care' => 'BAD hair days never looked so good.',
                'hair styling' => 'BAD hair days never looked so good.',
                'body care' => 'Play dirty, wash clean, roam free.',
                'facial care' => 'Keep your game face on when the gloves come off.',
                'face care' => 'Keep your game face on when the gloves come off.',
                'skin care' => 'Keep your game face on when the gloves come off.',
            ],
            'raangalay' => [
                'sarees'         => 'Handwoven elegance for every occasion.',
                'panjabis'       => 'Tradition meets modern style.',
                'kurtas'         => 'Comfort rooted in heritage.',
                'home textiles'  => 'Bring Bengali artistry into your home.',
                'accessories'    => 'Complete your traditional look.',
            ],
        ];

        return $maps[$theme] ?? [];
    }
}
