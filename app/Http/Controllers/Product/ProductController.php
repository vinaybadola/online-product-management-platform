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
            $page = $request->query('page', 1);
            $perPage = $request->query('per_page', 10);

            $filters = $request->only(['color', 'brand', 'size', 'tags', 'min_price', 'max_price']);

            $products = $this->productService->getAllProducts($filters, $page, $perPage);

            if ($request->wantsJson()) {
                if (!$products) {
                    return response()->json(['message' => 'No products available.'], 404);
                }
                return response()->json($products);
            }
            return Inertia::render('Products/Index', ['products' => $products]);
        } catch (\Exception $e) {
            return $this->handleErrorResponse($request, ['message' => 'An error occurred while fetching products.'], $e->getCode());
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        try{
            return Inertia('Products/CreateEdit', [
                'product' => null,
            ]);
        }
        catch(\Exception $e){
            return $this->handleErrorResponse($request, ['message' => 'An error occurred while creating the product.'], $e->getCode());
        }
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
                    'color' => 'sometimes|string|nullable',
                    'brand' => 'sometimes|string|nullable',
                    'stock' => 'sometimes|integer|min:0',
                    'size' => 'sometimes|string|nullable',
                    'tags' => 'sometimes|string|nullable',
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
                    return Inertia::render('Products/CreateEdit', [
                        'errors' => ['images.*' => 'Please upload at least one image.'],
                        'input' => $request->all()
                    ]);
                }
            }
        
            if ($request->has('tags') && is_string($request->tags)) {
                $tagsArray = array_map('trim', explode(',', $request->tags));
                $validatedData['tags'] = $tagsArray;
            }
        
            $product = $this->productService->createProduct($validatedData, $images);
        
            if (!$product) {
                return Inertia::render('Products/CreateEdit', [
                    'errors' => ['message' => 'Failed to create product.'],
                    'input' => $request->all()
                ]);
            }
        
            return Inertia::render('Products/Index', [
                'message' => 'Product created successfully!',
                'product' => $product
            ]);
        } catch (ValidationException $e) {
            return Inertia::render('Products/CreateEdit', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
        } catch (\Exception $e) {
            logger('Error occurred while creating product: ' . $e->getMessage());
            return $this->handleErrorResponse($request, ['message' => 'An error occurred while creating the product.'], $e->getCode() );
        }
    }
    /**
     * Display the specified resource.
     */
    public function show( Request $request, $idOrSlug)
    {
        try {
            if(!$idOrSlug){
                
                return response()->json(['message' => 'id or slug is required'], 422);

            }
            $product = $this->productService->getProductByIdOrSlug($idOrSlug);

            if($request->wantsJson()) {
                if (!$product) {
                    return response()->json(['message' => 'Product not found'], 404);
                }
                return response()->json($product);
            }
            if(!$product){
                return redirect()->back()->withErrors(['message' => 'Product not found']);
            }
            return Inertia::render('Products/Show', [
                'params' => ['idOrSlug' => $idOrSlug],
            ]);
            return response()->json(["success" => true, "data" => $product]);
        } catch (\Exception $e) {
            logger('Error occurred while fetching product: ' . $e->getMessage());
            return $this->handleErrorResponse($request, ['message' => 'An error occurred while fetching the product.'],  $e->getCode());
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
            return inertia('Products/Edit', [
                'product' => $product,
            ]);
        } catch (\Exception $e) {
            logger('Error occurred while fetching product: ' . $e->getMessage());
            return $this->handleErrorResponse($request, ['message' => 'An error occurred while fetching the product.'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     * 
     */
    public function update(Request $request, string $id)
    {
        try {
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
            $additionalData = $request->only(['color', 'brand', 'stock', 'size', 'tags']);

            if ($request->has('tags') && is_string($request->tags)) {
                $tagsArray = array_map('trim', explode(',', $request->tags));
                $validatedData['tags'] = $tagsArray;
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
            return $this->handleErrorResponse($request, ['errors' => $e->errors()]);
        } catch (\Exception $e) {
            logger('Error occurred while updating product: ' . $e->getMessage());
            return $this->handleErrorResponse($request, ['message' => 'An error occurred while creating the product.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy( Request $request, string $id)
    {
        try {
            $deleted = $this->productService->deleteProduct($id);

            if (!$deleted) {
                return response()->json(['message' => 'Product not found or deletion failed'], 400);
            }

            return response()->json(['message' => 'Product deleted successfully']);
        } catch (\Exception $e) {
            logger('Error occurred while deleting product: ' . $e->getMessage());
            return $this->handleErrorResponse($request, ['message' => 'An error occurred while deleting the product.'], 500);
        }
    }

    private function handleErrorResponse(Request $request, array $errorResponse, int $status = 422)
    {
        if ($request->wantsJson()) {
            return response()->json($errorResponse, $status);
        }
    
        return Inertia::render('Products/CreateEdit', [
            'errors' => $errorResponse,
            'input' => $request->all(),
        ]);
    }
}
