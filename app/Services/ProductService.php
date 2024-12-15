<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function createProduct(array $data, $images = null)
    {
        // check if product is already created throw validation error
        if (Product::where('name', $data['name'])->exists()) {
            throw ValidationException::withMessages(['name' => 'Product already exists']);
        }
        $product = Product::create($data);

        if ($images) {
            $this->storeImages($product, $images);
        }

        return $product;
    }

    public function updateProduct($id, array $data, $images = null)
    {
        $product = Product::find($id);

        if (!$product) {
            return false;
        }

        $product->update($data);

        if ($images) {
            $this->storeImages($product, $images);
        }

        return true;
    }

    private function storeImages(Product $product, $images)
    {
        foreach ($images as $image) {
            $path = $image->store('product_images', 'public'); 

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $path,
            ]);
        }
    }

    public function getAllProducts($page, $perPage)
    {
        return Product::with('images')
        ->orderBy('created_at', 'desc')
        ->paginate($perPage, ['*'], 'page', $page);  
    }

    public function getProductByIdOrSlug($idOrSlug)
    {
        return Product::with('images')
            ->where('id', $idOrSlug)
            ->orWhere('slug', $idOrSlug)
            ->first();
    }

    public function deleteProduct($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return false;
        }

        // Delete associated images
        foreach ($product->images as $image) {
            Storage::delete('public/' . $image->image_path);
            $image->delete();
        }

        $product->delete();
        return true;
    }
}
