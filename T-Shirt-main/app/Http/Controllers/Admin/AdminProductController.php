<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'sizes' => 'required|string', // Comma separated, e.g. "S,M,L,XL"
            'colors' => 'required|string', // Comma separated, e.g. "Black,White,Blue"
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Parse comma separated values to array
        $sizesArray = array_map('trim', explode(',', $request->sizes));
        $colorsArray = array_map('trim', explode(',', $request->colors));

        $imagePath = 'images/tshirts/cyberpunk.png'; // default fallback

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . '_' . $image->getClientOriginalName();
            $destinationPath = public_path('/images/tshirts');
            $image->move($destinationPath, $name);
            $imagePath = 'images/tshirts/' . $name;
        }

        Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . time(),
            'category_id' => $request->category_id,
            'description' => $request->description,
            'price' => $request->price,
            'discount_price' => $request->discount_price,
            'stock' => $request->stock,
            'sizes' => $sizesArray,
            'colors' => $colorsArray,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully!');
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $categories = Category::all();
        
        // Convert array back to comma separated string
        $sizesString = implode(', ', $product->sizes ?? []);
        $colorsString = implode(', ', $product->colors ?? []);

        return view('admin.products.edit', compact('product', 'categories', 'sizesString', 'colorsString'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'sizes' => 'required|string',
            'colors' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $sizesArray = array_map('trim', explode(',', $request->sizes));
        $colorsArray = array_map('trim', explode(',', $request->colors));

        $product->name = $request->name;
        $product->category_id = $request->category_id;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->discount_price = $request->discount_price;
        $product->stock = $request->stock;
        $product->sizes = $sizesArray;
        $product->colors = $colorsArray;

        if ($request->hasFile('image')) {
            // Delete old file if it exists and isn't the seeded default ones
            if ($product->image_path && file_exists(public_path($product->image_path)) && !str_contains($product->image_path, 'cyberpunk.png') && !str_contains($product->image_path, 'minimalist.png') && !str_contains($product->image_path, 'retro.png') && !str_contains($product->image_path, 'anime.png')) {
                @unlink(public_path($product->image_path));
            }

            $image = $request->file('image');
            $name = time() . '_' . $image->getClientOriginalName();
            $destinationPath = public_path('/images/tshirts');
            $image->move($destinationPath, $name);
            $product->image_path = 'images/tshirts/' . $name;
        }

        $product->save();

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        // Delete image file if it exists and isn't one of the seeded default ones
        if ($product->image_path && file_exists(public_path($product->image_path)) && !str_contains($product->image_path, 'cyberpunk.png') && !str_contains($product->image_path, 'minimalist.png') && !str_contains($product->image_path, 'retro.png') && !str_contains($product->image_path, 'anime.png')) {
            @unlink(public_path($product->image_path));
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully!');
    }
}
