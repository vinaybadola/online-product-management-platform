<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ProductService;
use Inertia\Inertia;
use Illuminate\Validation\ValidationException;

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
        try {

            // Handle the pagination 
            $page = $request->query('page', 1);  
            $perPage = $request->query('per_page', 10);


            $products = $this->productService->getAllProducts($page, $perPage);
            if ($request->wantsJson()) {
                if (count($products) < 1) {
                    return response()->json(['message' => 'No products available.'], 404);
                }
                return response()->json($products);
            }

            return Inertia::render('Products/Index', ['products' => $products]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while fetching products.'], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia('Products/CreateEdit', [
            'product' => null,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate(
                [
                    'name' => ['required', 'string', 'max:255', 'unique:products,name'],
                    'description' => ['required', 'string'],
                    'price' => ['required', 'numeric'],
                    'images.*' => ['image', 'max:2048'],
                ],
                [
                    'name.required' => 'The product name is required.',
                    'name.unique' => 'A product with the same name already exists.',
                    'description.required' => 'Please provide a description for the product.',
                    'price.required' => 'Price is mandatory and must be a valid number.',
                    'images.*.image' => 'Each file must be an image.',
                ]
            );

            $images = $request->file('images');

            if (!$images) {
                if ($request->wantsJson()) {
                    return response()->json(['message' => 'Please upload at least one image.'], 422);
                } else {
                    return redirect()->route('products.create')
                        ->withErrors(['images.*' => 'Please upload at least one image.'])
                        ->withInput();
                }
            }

            $product = $this->productService->createProduct($validatedData, $images);
            if (!$product) {
                if ($request->wantsJson()) {
                    return response()->json(['message' => 'Failed to create product.'], 500);
                } else {
                    return redirect()->route('products.create')
                        ->withErrors(['message' => 'Failed to create product.'])
                        ->withInput();
                }
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Product created successfully',
                    'product' => $product
                ], 201);
            }

            return redirect()->route('products.index')
                ->with('success', 'Product created successfully.');
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            logger(' Error occurred while creating product: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while creating the product.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($idOrSlug)
    {
        try {
            $product = $this->productService->getProductByIdOrSlug($idOrSlug);

            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            return response()->json(["success" => true, "data" => $product]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while fetching the product.'], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $idOrSlug)
    {
        try {
            $product = $this->productService->getProductByIdOrSlug($idOrSlug);

            if (!$product) {
                return redirect()->back()->withErrors(['message' => 'Product not found']);
            }

            if ($request->wantsJson()) {
                if (!$product) {
                    return response()->json(['message' => 'Product not found'], 404);
                }
                return response()->json($product);
            }
            return inertia('Products/CreateEdit', [
                'product' => $product,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while fetching the product.'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     * TODO: fix the image updation problem
     */
    public function update(Request $request, string $id)
    {
        try {
            // Validate the request data
            $validatedData = $request->validate([
                'name' => 'sometimes|required|string|max:255|unique:products,name,' . $id,
                'description' => 'sometimes|required|string',
                'price' => 'sometimes|required|integer|min:1',
                'color' => 'sometimes|string|nullable',
                'brand' => 'sometimes|string|nullable',
                'stock' => 'sometimes|integer|min:0',
                'size' => 'sometimes|string|nullable',
                'tags' => 'sometimes|string|nullable',
                'images.*' => ['image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            ]);


            // Merge additional fields from the request
            $additionalData = $request->only(['color', 'brand', 'stock', 'size', 'tags']);
            
            // Ensure tags are properly formatted as JSON
            if (isset($additionalData['tags'])) {
                $additionalData['tags'] = json_encode(explode(',', $additionalData['tags']));
            }

            $data = array_merge($validatedData, $additionalData);

            $images = $request->file('images');

            $updated = $this->productService->updateProduct($id, $data, $images);

            if (!$updated) {
                if ($request->wantsJson()) {
                    return response()->json(['message' => 'Product not found or update failed'], 400);
                }
                return redirect()->back()->withErrors(['message' => 'Product not found or update failed']);
            }

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Product updated successfully']);
            }

            return redirect()->route('products.index')->with('success', 'Product updated successfully');
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            logger('Error occurred while updating product: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while updating the product.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $deleted = $this->productService->deleteProduct($id);

            if (!$deleted) {
                return response()->json(['message' => 'Product not found or deletion failed'], 400);
            }

            return response()->json(['message' => 'Product deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while deleting the product.'], 500);
        }
    }
}