<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ArticleController extends Controller
{
    public function __construct(
        private ArticleService $articleService
    ) {
    }

    /**
     * Display public article listing.
     */
    public function index(
        Request $request
    ): JsonResponse {

        $query = Article::with([
            'user:id,username,nama_lengkap,nickname,foto_profil',
            'category:id,nama,slug',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Filter by category
        |--------------------------------------------------------------------------
        */

        if ($request->filled('category_id')) {
            $query->where(
                'category_id',
                $request->integer('category_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where(function ($query) use ($search) {
                $query
                    ->where('judul', 'like', "%{$search}%")
                    ->orWhere(
                        'isi_artikel',
                        'like',
                        "%{$search}%"
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by year
        |--------------------------------------------------------------------------
        */

        if ($request->filled('tahun')) {
            $query->whereYear(
                'created_at',
                $request->integer('tahun')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $perPage = min(
            $request->integer('per_page', 12),
            100
        );

        $articles = $query
            ->latest('created_at')
            ->latest('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data article berhasil diambil.',
            'data' => $articles,
        ]);
    }

    /**
     * Display authenticated user's articles.
     */
    public function mine(
        Request $request
    ): JsonResponse {

        $query = Article::with([
            'user:id,username,nama_lengkap,nickname,foto_profil',
            'category:id,nama,slug',
        ])->where(
            'user_id',
            $request->user()->id
        );

        if ($request->filled('category_id')) {
            $query->where(
                'category_id',
                $request->integer('category_id')
            );
        }

        if ($request->filled('tahun')) {
            $query->whereYear(
                'created_at',
                $request->integer('tahun')
            );
        }

        $perPage = min(
            $request->integer('per_page', 12),
            100
        );

        $articles = $query
            ->latest('created_at')
            ->latest('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data article milik user berhasil diambil.',
            'data' => $articles,
        ]);
    }

    /**
     * Display article detail.
     */
    public function show(
        Article $article
    ): JsonResponse {

        $article->load([
            'user:id,username,nama_lengkap,nickname,foto_profil',
            'category:id,nama,slug',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail article berhasil diambil.',
            'data' => [
                'article' => $article,
            ],
        ]);
    }

    /**
     * Store article.
     */
    public function store(
        StoreArticleRequest $request
    ): JsonResponse {

        Gate::authorize(
            'create',
            Article::class
        );

        $article = $this->articleService->createArticle(
            $request->user()->id,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Article berhasil dibuat.',
            'data' => [
                'article' => $article,
            ],
        ], 201);
    }

    /**
     * Update article.
     */
    public function update(
        UpdateArticleRequest $request,
        Article $article
    ): JsonResponse {

        Gate::authorize(
            'update',
            $article
        );

        $article = $this->articleService->updateArticle(
            $article,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Article berhasil diperbarui.',
            'data' => [
                'article' => $article,
            ],
        ]);
    }

    /**
     * Delete article.
     */
    public function destroy(
        Article $article
    ): JsonResponse {

        Gate::authorize(
            'delete',
            $article
        );

        $this->articleService->deleteArticle(
            $article
        );

        return response()->json([
            'success' => true,
            'message' => 'Article berhasil dihapus.',
        ]);
    }
}