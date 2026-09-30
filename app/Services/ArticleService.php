<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class ArticleService
{
    /**
     * Create article.
     */
    public function createArticle(
        int $userId,
        array $data
    ): Article {
        return DB::transaction(function () use ($userId, $data) {

            $file = $data['gambar_cover'] ?? null;

            unset($data['gambar_cover']);

            $slug = $this->generateUniqueSlug(
                $data['judul']
            );

            $article = Article::create([
                ...$data,
                'user_id' => $userId,
                'slug' => $slug,
            ]);

            if ($file instanceof UploadedFile) {
                $path = $this->storeCoverImage(
                    $article,
                    $file
                );

                $article->update([
                    'gambar_cover' => $path,
                ]);
            }

            return $article->fresh([
                'user',
                'category',
            ]);
        });
    }

    /**
     * Update article.
     */
    public function updateArticle(
        Article $article,
        array $data
    ): Article {
        return DB::transaction(function () use (
            $article,
            $data
        ) {

            $file = $data['gambar_cover'] ?? null;

            unset($data['gambar_cover']);

            /*
            |--------------------------------------------------------------------------
            | Regenerate slug when title changes
            |--------------------------------------------------------------------------
            */

            if (
                isset($data['judul'])
                && $data['judul'] !== $article->judul
            ) {
                $data['slug'] = $this->generateUniqueSlug(
                    $data['judul'],
                    $article->id
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Update article data
            |--------------------------------------------------------------------------
            */

            if (!empty($data)) {
                $article->update($data);
            }

            /*
            |--------------------------------------------------------------------------
            | Replace cover image
            |--------------------------------------------------------------------------
            */

            if ($file instanceof UploadedFile) {

                $oldPath = $article->gambar_cover;

                $newPath = $this->storeCoverImage(
                    $article,
                    $file
                );

                $article->update([
                    'gambar_cover' => $newPath,
                ]);

                $this->deleteImageFile($oldPath);
            }

            return $article->fresh([
                'user',
                'category',
            ]);
        });
    }

    /**
     * Delete article.
     */
    public function deleteArticle(
        Article $article
    ): void {
        DB::transaction(function () use ($article) {

            $articleId = $article->id;

            $article->delete();

            /*
            |--------------------------------------------------------------------------
            | Delete article storage directory
            |--------------------------------------------------------------------------
            */

            Storage::disk('public')->deleteDirectory(
                "articles/{$articleId}"
            );
        });
    }

    /**
     * Store article cover as WebP.
     */
    private function storeCoverImage(
        Article $article,
        UploadedFile $file
    ): string {
        $manager = new ImageManager(
            new Driver()
        );

        $image = $manager->decodePath(
            $file->getPathname()
        );

        $filename = Str::uuid() . '.webp';

        $path = "articles/{$article->id}/{$filename}";

        $encoded = $image->encode(
            new WebpEncoder(
                quality: 80
            )
        );

        Storage::disk('public')->put(
            $path,
            $encoded
        );

        return $path;
    }

    /**
     * Delete image file.
     */
    private function deleteImageFile(
        ?string $path
    ): void {
        if (!$path) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    /**
     * Generate unique article slug.
     */
    private function generateUniqueSlug(
        string $judul,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($judul);

        /*
        |--------------------------------------------------------------------------
        | Handle empty slug
        |--------------------------------------------------------------------------
        */

        if ($baseSlug === '') {
            $baseSlug = 'article';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            Article::where('slug', $slug)
                ->when(
                    $ignoreId !== null,
                    fn ($query) =>
                        $query->where('id', '!=', $ignoreId)
                )
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}