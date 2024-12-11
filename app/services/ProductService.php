<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    public function getAllProducts()
    {
        return Product::all();
    }

    public function getProductByIdOrSlug($idOrSlug)
    {
        return Product::where('id', $idOrSlug)->orWhere('slug', $idOrSlug)->first();
    }

    public function createProduct(array $data)
    {
        return Product::create($data);
    }

    public function updateProduct($id, array $data)
    {
        $product = Product::find($id);

        if (!$product) {
            return false;
        }

        $product->update($data);
        return true;
    }

    public function deleteProduct($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return false;
        }

        $product->delete();
        return true;
    }
}
