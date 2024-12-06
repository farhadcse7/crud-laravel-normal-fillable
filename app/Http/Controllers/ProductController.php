<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

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

        $data = $request->all();

        if ($image = $request->file('image')) {
            $imageName = time() . '-' . Str::uuid() . '.' . $image->getClientOriginalExtension();
            $directory = 'uploads/product-images/';
            $image->move($directory, $imageName);
            $data['image'] = $directory . $imageName;
        }

        Product::create($data);

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

        $data = $request->all();

        $data['image'] = $product->image;
        if ($image = $request->file('image')) {
            if ($product->image) {
                unlink($product->image);
            }
            $imageName = time() . '-' . Str::uuid() . '.' . $image->getClientOriginalExtension();
            $directory = 'uploads/product-images/';
            $image->move($directory, $imageName);
            $data['image'] = $directory . $imageName;
        }

        $product->update($data);

        return back()->with('message', 'Product information updated successfully');
    }

    public function destroy(Product $product)
    {
        if ($product->image && file_exists(public_path($product->image))) {
            unlink($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')->with('message', 'Product information deleted successfully');
    }
}
