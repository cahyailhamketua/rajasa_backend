<?php

namespace App\Services;

use App\Models\ArticleCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ArticleCategoryService
{
    /**
     * Create category.
     */
    public function createCategory(
        array $data
    ): ArticleCategory {
        return DB::transaction(function () use ($data) {

            $slug = $this->generateUniqueSlug(
                $data['nama']
            );

            return ArticleCategory::create([
                'nama' => $data['nama'],
                'slug' => $slug,
            ]);
        });
    }

    /**
     * Update category.
     */
    public function updateCategory(
        ArticleCategory $category,
        array $data
    ): ArticleCategory {
        return DB::transaction(function () use (
            $category,
            $data
        ) {

            if (
                isset($data['nama'])
                && $data['nama'] !== $category->nama
            ) {
                $data['slug'] = $this->generateUniqueSlug(
                    $data['nama'],
                    $category->id
                );
            }

            $category->update($data);

            return $category->fresh();
        });
    }

    /**
     * Delete category.
     */
    public function deleteCategory(
        ArticleCategory $category
    ): void {
        if ($category->articles()->exists()) {
            abort(
                422,
                'Category tidak dapat dihapus karena masih digunakan oleh article.'
            );
        }

        $category->delete();
    }

    /**
     * Generate unique category slug.
     */
    private function generateUniqueSlug(
        string $nama,
        ?int $ignoreId = null
    ): string {
        $baseSlug = Str::slug($nama);

        if ($baseSlug === '') {
            $baseSlug = 'category';
        }

        $slug = $baseSlug;
        $counter = 2;

        while (
            ArticleCategory::where('slug', $slug)
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