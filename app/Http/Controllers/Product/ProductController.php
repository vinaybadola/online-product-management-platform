<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProductService;

class ProductController extends Controller
{
    private $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = $this->productService->getAllProducts();
        return response()->json($products);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'numeric'],
            'slug' => ['required', 'string', 'max:255', 'unique:products,slug'],
        ]);

        $this->productService->createProduct($validatedData);
        return redirect()->route('products.index')->with('success', 'Product created successfully.');

    }

    /**
     * Display the specified resource.
     */
    public function show($idOrSlug)
    {
        $product = $this->productService->getProductByIdOrSlug($idOrSlug);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return response()->json([ "success" => true, "data" => $product]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($idOrSlug)
    {
        $product = $this->productService->getProductByIdOrSlug($idOrSlug);

        if (!$product) {
            return redirect()->back()->withErrors(['message' => 'Product not found']);
        }

        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'price' => 'sometimes|required|integer|min:1',
        ]);

        $updated = $this->productService->updateProduct($id, $validatedData);

        if (!$updated) {
            return response()->json(['message' => 'Product not found or update failed'], 400);
        }

        return response()->json(['message' => 'Product updated successfully']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deleted = $this->productService->deleteProduct($id);

        if (!$deleted) {
            return response()->json(['message' => 'Product not found or deletion failed'], 400);
        }

        return response()->json(['message' => 'Product deleted successfully']);
    }
}
