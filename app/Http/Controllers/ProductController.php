<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaginationRequest;
use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    public function index(PaginationRequest $request): AnonymousResourceCollection
    {
        $data = $request->validated();

        $products = Product::query()
            ->with('category')
            ->when(
                $data['category_id'] ?? null,
                fn ($query, $categoryId) => $query->where('category_id', $categoryId)
            )
            ->latest()
            ->paginate(
                $data['per_page'] ?? 12,
                ['*'],
                'page',
                $data['page'] ?? 1
            )
            ->withQueryString();

        return ProductResource::collection($products);
    }

    public function store(CreateProductRequest $request)
    {
        $data = $request->validated();

        $product = Product::create($data);

        return ProductResource::make($product->load('category'))
            ->additional(['message' => 'Product created successfully.'])
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Product $product)
    {
        $product->load('category');

        return ProductResource::make($product)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        $product->update($data);

        return ProductResource::make($product->refresh()->load('category'))
            ->additional(['message' => 'Product updated successfully.'])
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->noContent();
    }
}
