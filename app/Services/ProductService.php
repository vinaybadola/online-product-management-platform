<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Laravel\Facades\Image;
class ProductService
{
    public function createProduct(array $data, $images = null)
    {
        // check if product is already created throw validation error
        if (Product::where('name', $data['name'])->exists()) {
            throw ValidationException::withMessages(['name' => 'Product already exists']);
        }
        logger('createdData');
        logger($data);
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
        logger('updateProduct');
        logger($data);
        $product->update($data);

        if ($images) {
            logger('images');
            logger($images);
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
    public function getAllProducts($filters, $page, $perPage)
    {
        $query = Product::query();

        if (!empty($filters['color'])) {
            $query->whereIn('color', $filters['color']);
        }
    
        if (!empty($filters['brand'])) {
            $query->where('brand', $filters['brand']);
        }
    
        if (!empty($filters['size'])) {
            $query->where('size', $filters['size']);
        }
    
        if (!empty($filters['tags'])) {
            $query->whereJsonContains('tags', $filters['tags']);
        }
    
        if (!empty($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }
    
        if (!empty($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }
        return $query->orderBy('created_at', 'desc')
        ->with('images')
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
