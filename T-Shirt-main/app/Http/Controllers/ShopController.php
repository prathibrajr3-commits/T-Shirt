<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\PromotionBanner;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        // Featured products (e.g. discounted ones or first 4)
        $featuredProducts = Product::latest()->take(4)->get();
        // Discounted products
        $discountedProducts = Product::whereNotNull('discount_price')->take(4)->get();

        $banners = \App\Models\Banner::where('is_active', true)
                                     ->orderBy('order_position', 'asc')
                                     ->get();

        // Active promotional offer (lowest display_order wins)
        $activePromotion = PromotionBanner::visible()
            ->orderBy('display_order', 'asc')
            ->first();

        return view('shop.index', compact('categories', 'featuredProducts', 'discountedProducts', 'banners', 'activePromotion'));
    }

    public function shop(Request $request)
    {
        $categories = Category::all();
        $query = Product::query();

        // Search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Category filter
        if ($request->filled('category')) {
            $category = Category::where('slug', $request->category)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Sort filter
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'newest':
                default:
                    $query->latest();
                    break;
            }
        } else {
            $query->latest();
        }

        $products = $query->paginate(9)->withQueryString();

        return view('shop.list', compact('categories', 'products'));
    }

    public function show($slug)
    {
        $product = Product::where('slug', $slug)->with(['category', 'reviews.user'])->firstOrFail();
        $relatedProducts = Product::where('category_id', $product->category_id)
                                  ->where('id', '!=', $product->id)
                                  ->take(4)
                                  ->get();

        // Calculate average rating
        $avgRating = $product->reviews->avg('rating') ?? 5.0;

        return view('shop.show', compact('product', 'relatedProducts', 'avgRating'));
    }
}
