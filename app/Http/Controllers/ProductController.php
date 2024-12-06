<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        return view('product.index', ['products' => Product::all()]);
    }

    public function create()
    {
        return view('product.create');
    }

    public function store(Request $request)
    {
        // return $request;
        // dd($request->all());
        $request->validate(
            [
                'name'        => 'required|string|unique:products,name|max:255',
                'description' => 'required|string',
                'price'       => 'required|numeric|min:0|max:999999.99',
                'image'       => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            ]
        );

        $imagePath = $request->file('image') ? $request->file('image')->store('product-images', 'public') : null;
        Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath,
        ]);

        return back()->with('message', 'New Product information added successfully');
    }

    public function show(Product $product)
    {
        return view('product.view', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('product.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate(
            [
                'name'        => 'required|string|max:255|unique:products,name,' . $product->id,
                'description' => 'required|string',
                'price'       => 'required|numeric|min:0|max:999999.99',
                'image'       => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            ]
        );

        $imagePath = $product->image;
        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('product-images', 'public');
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $imagePath,
        ]);

        return back()->with('message', 'Product information updated successfully');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('message', 'Product information deleted successfully');
    }
}
