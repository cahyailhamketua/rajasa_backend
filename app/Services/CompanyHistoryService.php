<?php

namespace App\Services;

use App\Models\CompanyHistory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class CompanyHistoryService
{
    /**
     * Create company history.
     */
    public function createHistory(
        array $data
    ): CompanyHistory {
        return DB::transaction(function () use ($data) {
            $file = $data['gambar'] ?? null;

            unset($data['gambar']);

            $history = CompanyHistory::create($data);

            if ($file instanceof UploadedFile) {
                $path = $this->storeImage(
                    $history,
                    $file
                );

                $history->update([
                    'gambar' => $path,
                ]);
            }

            return $history->fresh();
        });
    }

    /**
     * Update company history.
     */
    public function updateHistory(
        CompanyHistory $history,
        array $data
    ): CompanyHistory {
        return DB::transaction(function () use ($history, $data) {
            $file = $data['gambar'] ?? null;

            unset($data['gambar']);

            /*
            |--------------------------------------------------------------------------
            | Update text/data fields
            |--------------------------------------------------------------------------
            */

            if (!empty($data)) {
                $history->update($data);
            }

            /*
            |--------------------------------------------------------------------------
            | Replace image
            |--------------------------------------------------------------------------
            */

            if ($file instanceof UploadedFile) {
                $oldPath = $history->gambar;

                $newPath = $this->storeImage(
                    $history,
                    $file
                );

                $history->update([
                    'gambar' => $newPath,
                ]);

                $this->deleteImageFile($oldPath);
            }

            return $history->fresh();
        });
    }

    /**
     * Delete company history and its image.
     */
    public function deleteHistory(
        CompanyHistory $history
    ): void {
        DB::transaction(function () use ($history) {
            $history->delete();

            /*
            |--------------------------------------------------------------------------
            | Delete entire history folder
            |--------------------------------------------------------------------------
            */

            Storage::disk('public')->deleteDirectory(
                "company-histories/{$history->id}"
            );
        });
    }

    /**
     * Store image as WebP.
     */
    private function storeImage(
        CompanyHistory $history,
        UploadedFile $file
    ): string {
        $manager = new ImageManager(
            new Driver()
        );

        $image = $manager->decodePath(
            $file->getPathname()
        );

        $filename = Str::uuid() . '.webp';

        $path = "company-histories/{$history->id}/{$filename}";

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
}