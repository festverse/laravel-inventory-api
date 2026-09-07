<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Requests\ProductUpdateStockRequest;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;

class ProductController extends Controller
{
    public function __construct(private ProductService $productService)
    {
    }

    public function index()
    {
        $products = $this->productService->getAllProducts();
        return ProductResource::collection($products);
    }

    public function store(ProductStoreRequest $request)
    {
        $product = $this->productService->createProduct($request->validated());
        return new ProductResource($product);
    }

    public function show(int $id)
    {
        $product = $this->productService->getProductById($id);
        return new ProductResource($product);
    }

    public function update(ProductUpdateRequest $request, int $id)
    {
        $product = $this->productService->getProductById($id);
        $product = $this->productService->updateProduct($product, $request->validated());
        return new ProductResource($product);
    }

    public function updateStock(ProductUpdateStockRequest $request, int $id)
    {
        $product = $this->productService->getProductById($id);
        $product = $this->productService->updateStock($product, $request->validated('quantity'));
        return new ProductResource($product);
    }
}
