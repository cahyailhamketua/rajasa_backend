<?php

namespace App\Services;

use App\Models\Gallery;
use App\Models\GalleryImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class GalleryService
{
    /**
     * Create gallery with multiple images.
     */
    public function createGallery(
        User $user,
        array $data
    ): Gallery {
        return DB::transaction(function () use ($user, $data) {
            $images = $data['images'] ?? [];

            unset($data['images']);

            $gallery = Gallery::create([
                ...$data,
                'user_id' => $user->id,
            ]);

            foreach ($images as $index => $file) {
                $this->createGalleryImage(
                    $gallery,
                    $file,
                    $index
                );
            }

            return $gallery->load('images');
        });
    }

    /**
     * Update gallery and synchronize images.
     */
    public function updateGallery(
        Gallery $gallery,
        array $data
    ): Gallery {
        return DB::transaction(function () use ($gallery, $data) {
            $images = $data['images'] ?? null;

            unset($data['images']);

            if (!empty($data)) {
                $gallery->update($data);
            }

            /*
            |--------------------------------------------------------------------------
            | Images are only synchronized when the field is sent.
            |--------------------------------------------------------------------------
            */

            if ($images !== null) {
                $this->syncGalleryImages(
                    $gallery,
                    $images
                );
            }

            return $gallery->fresh([
                'images',
            ]);
        });
    }

    /**
     * Delete gallery and all physical image files.
     */
    public function deleteGallery(
        Gallery $gallery
    ): void {
        DB::transaction(function () use ($gallery) {
            $gallery->load('images');

            foreach ($gallery->images as $image) {
                $this->deleteImageFile($image->image);
            }

            // Hapus folder gallery jika sudah kosong
            Storage::disk('public')->deleteDirectory(
                "galleries/{$gallery->id}"
            );

            $gallery->delete();
        });
    }

    /**
     * Create a gallery image.
     */
    private function createGalleryImage(
        Gallery $gallery,
        UploadedFile $file,
        int $urutan
    ): GalleryImage {
        $path = $this->storeGalleryImage(
            $gallery,
            $file
        );

        return $gallery->images()->create([
            'image' => $path,
            'urutan' => $urutan,
        ]);
    }

    /**
     * Synchronize gallery images.
     */
    private function syncGalleryImages(
        Gallery $gallery,
        array $images
    ): void {
        $gallery->load('images');

        $existingImages = $gallery->images
            ->keyBy('id');

        $submittedIds = [];

        foreach ($images as $index => $imageData) {
            $imageId = $imageData['id'] ?? null;
            $file = $imageData['file'] ?? null;

            /*
            |--------------------------------------------------------------------------
            | Existing image
            |--------------------------------------------------------------------------
            */

            if ($imageId !== null) {
                $image = $existingImages->get($imageId);

                /*
                |--------------------------------------------------------------------------
                | Image ID exists but does not belong to this gallery.
                |--------------------------------------------------------------------------
                */

                if (!$image) {
                    abort(422, 'Gallery image tidak ditemukan pada gallery ini.');
                }

                $submittedIds[] = $image->id;

                /*
                |--------------------------------------------------------------------------
                | Replace existing image file.
                |--------------------------------------------------------------------------
                */

                if ($file instanceof UploadedFile) {
                    $oldPath = $image->image;

                    $newPath = $this->storeGalleryImage(
                        $gallery,
                        $file
                    );

                    $image->update([
                        'image' => $newPath,
                    ]);

                    $this->deleteImageFile($oldPath);
                }

                /*
                |--------------------------------------------------------------------------
                | Update order.
                |--------------------------------------------------------------------------
                */

                $image->update([
                    'urutan' => $imageData['urutan'] ?? $index,
                ]);

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | New image
            |--------------------------------------------------------------------------
            */

            if ($file instanceof UploadedFile) {
                $this->createGalleryImage(
                    $gallery,
                    $file,
                    $imageData['urutan'] ?? $index
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Delete old images that are not submitted anymore.
        |--------------------------------------------------------------------------
        */

        foreach ($existingImages as $existingImage) {
            if (!in_array($existingImage->id, $submittedIds)) {
                $this->deleteImageFile(
                    $existingImage->image
                );

                $existingImage->delete();
            }
        }
    }

    /**
     * Store gallery image as WebP.
     */
    private function storeGalleryImage(
        Gallery $gallery,
        UploadedFile $file
    ): string {
        $manager = new ImageManager(
            new Driver()
        );

        $image = $manager->decodePath(
            $file->getPathname()
        );

        $filename = Str::uuid() . '.webp';

        $path = "galleries/{$gallery->id}/{$filename}";

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
     * Delete physical image file.
     */
    private function deleteImageFile(
        ?string $path
    ): void {
        if (!$path) {
            return;
        }

        Storage::disk('public')->delete($path);
    }
}