<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\SiteTheme;
use Illuminate\Contracts\View\View;

class ShopController extends Controller
{
    public function index(): View
    {
        $products = Product::query()
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        $featuredProducts = Product::query()
            ->where('status', 'published')
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view(SiteTheme::view('pages.shop-index', 'themes.default.pages.shop-index'), [
            'products' => $products,
            'featuredProducts' => $featuredProducts,
            'shopMetrics' => [
                'published_products' => Product::query()->where('status', 'published')->count(),
                'featured_products' => $featuredProducts->count(),
                'download_products' => Product::query()->where('status', 'published')->where('delivery_type', 'download')->count(),
            ],
        ]);
    }

    public function show(string $slug): View
    {
        $product = Product::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $relatedProducts = Product::query()
            ->where('status', 'published')
            ->whereKeyNot($product->id)
            ->where('delivery_type', $product->delivery_type)
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();

        return view(SiteTheme::view('pages.shop-show', 'themes.default.pages.shop-show'), [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
