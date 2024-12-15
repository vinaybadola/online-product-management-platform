<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProductService;
use Inertia\Inertia;

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
    public function index(Request $request)
    {
        $products = $this->productService->getAllProducts();
        if ($request->wantsJson()) {
            if (count($products)<1) {
                return response()->json(['message' => 'No products available.'], 404);
            }
            return response()->json($products); 
        }

        return Inertia::render('Products/Index', ['products' => $products]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return inertia('Products/CreateEdit', [
            'product' => null, 
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // logger('requestAll');
        // logger($request->all());
        // Validation rules with custom error messages
        $validatedData = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'description' => ['required', 'string'],
                'price' => ['required', 'numeric'],
                'images.*' => ['image', 'max:2048'],
            ],
            [
                'name.required' => 'The product name is required.',
                'description.required' => 'Please provide a description for the product.',
                'price.required' => 'Price is mandatory and must be a valid number.',
                'images.*.image' => 'Each file must be an image.',
            ]
        );
        $images = $request->file('images');

        if (!$images) {
            if($request->wantsJson()){
                return response()->json(['message' => 'Please upload at least one image.'], 422);
            }
            else{
                return redirect()->route('products.create')
                    ->withErrors(['images.*' => 'Please upload at least one image.'])
                    ->withInput();
            }
        }
    
        // Save product using service
        $product = $this->productService->createProduct($validatedData, $images);
    
        // Respond with JSON for API or redirect for web requests
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product
            ], 201);
        }
    
        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
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

        return response()->json(["success" => true, "data" => $product]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $idOrSlug)
    {
        $product = $this->productService->getProductByIdOrSlug($idOrSlug);

        if (!$product) {
            return redirect()->back()->withErrors(['message' => 'Product not found']);
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        return inertia('Products/CreateEdit', [
            'product' => $product,
        ]);    
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
            'images.*' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // Validate images
        ]);

        $images = $request->file('images'); // Get the uploaded images, if any

        $updated = $this->productService->updateProduct($id, $validatedData, $images);

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
