<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleCategoryRequest;
use App\Http\Requests\UpdateArticleCategoryRequest;
use App\Models\ArticleCategory;
use App\Services\ArticleCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ArticleCategoryController extends Controller
{
    public function __construct(
        private ArticleCategoryService $categoryService
    ) {
    }

    /**
     * Display category listing.
     *
     * Public endpoint.
     */
    public function index(): JsonResponse
    {
        $categories = ArticleCategory::query()
            ->orderBy('nama')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data article category berhasil diambil.',
            'data' => [
                'categories' => $categories,
            ],
        ]);
    }

    /**
     * Display category detail.
     *
     * Public endpoint.
     */
    public function show(
        ArticleCategory $articleCategory
    ): JsonResponse {

        return response()->json([
            'success' => true,
            'message' => 'Detail article category berhasil diambil.',
            'data' => [
                'category' => $articleCategory,
            ],
        ]);
    }

    /**
     * Store category.
     */
    public function store(
        StoreArticleCategoryRequest $request
    ): JsonResponse {

        Gate::authorize(
            'create',
            ArticleCategory::class
        );

        $category = $this->categoryService->createCategory(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Article category berhasil dibuat.',
            'data' => [
                'category' => $category,
            ],
        ], 201);
    }

    /**
     * Update category.
     */
    public function update(
        UpdateArticleCategoryRequest $request,
        ArticleCategory $articleCategory
    ): JsonResponse {

        Gate::authorize(
            'update',
            $articleCategory
        );

        $category = $this->categoryService->updateCategory(
            $articleCategory,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Article category berhasil diperbarui.',
            'data' => [
                'category' => $category,
            ],
        ]);
    }

    /**
     * Delete category.
     */
    public function destroy(
        ArticleCategory $articleCategory
    ): JsonResponse {

        Gate::authorize(
            'delete',
            $articleCategory
        );

        $this->categoryService->deleteCategory(
            $articleCategory
        );

        return response()->json([
            'success' => true,
            'message' => 'Article category berhasil dihapus.',
        ]);
    }
}