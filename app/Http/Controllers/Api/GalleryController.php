<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGalleryRequest;
use App\Http\Requests\UpdateGalleryRequest;
use App\Models\Gallery;
use App\Services\GalleryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GalleryController extends Controller
{
    public function __construct(
        private GalleryService $galleryService
    ) {
    }

    /**
     * Display a listing of galleries.
     *
     * Public endpoint.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Gallery::with([
            'user:id,username,nama_lengkap,nickname,foto_profil',
            'images',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Filter by user_id
        |--------------------------------------------------------------------------
        */

        if ($request->filled('user_id')) {
            $query->where(
                'user_id',
                $request->integer('user_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by year
        |--------------------------------------------------------------------------
        */

        if ($request->filled('tahun')) {
            $query->whereYear(
                'tanggal_kegiatan',
                $request->integer('tahun')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter user's own galleries
        |--------------------------------------------------------------------------
        */

        if ($request->boolean('mine')) {
            if (!$request->user()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $query->where(
                'user_id',
                $request->user()->id
            );
        }

        $perPage = min(
            $request->integer('per_page', 12),
            100
        );

        $galleries = $query
            ->latest('tanggal_kegiatan')
            ->latest('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data gallery berhasil diambil.',
            'data' => $galleries,
        ]);
    }

    /**
     * Display the specified gallery.
     */
    public function show(
        Gallery $gallery
    ): JsonResponse {
        $gallery->load([
            'user:id,username,nama_lengkap,nickname,foto_profil',
            'images',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Detail gallery berhasil diambil.',
            'data' => [
                'gallery' => $gallery,
            ],
        ]);
    }

    /**
     * Store a newly created gallery.
     */
    public function store(
        StoreGalleryRequest $request
    ): JsonResponse {
        Gate::authorize('create', Gallery::class);

        $gallery = $this->galleryService->createGallery(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Gallery berhasil dibuat.',
            'data' => [
                'gallery' => $gallery,
            ],
        ], 201);
    }

    /**
     * Update the specified gallery.
     */
    public function update(
        UpdateGalleryRequest $request,
        Gallery $gallery
    ): JsonResponse {
        Gate::authorize('update', $gallery);

        $gallery = $this->galleryService->updateGallery(
            $gallery,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Gallery berhasil diperbarui.',
            'data' => [
                'gallery' => $gallery,
            ],
        ]);
    }

    /**
     * Remove the specified gallery.
     */
    public function destroy(
        Gallery $gallery
    ): JsonResponse {
        Gate::authorize('delete', $gallery);

        $this->galleryService->deleteGallery(
            $gallery
        );

        return response()->json([
            'success' => true,
            'message' => 'Gallery berhasil dihapus.',
        ]);
    }
}