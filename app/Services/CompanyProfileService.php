<?php

namespace App\Services;

use App\Models\CompanyProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class CompanyProfileService
{
    /**
     * Get all company profiles.
     */
    public function getAllProfiles()
    {
        return CompanyProfile::query()
            ->latest('id')
            ->get();
    }

    /**
     * Create company profile.
     */
    public function createProfile(array $data): CompanyProfile
    {
        return DB::transaction(function () use ($data) {

            $logo = $data['logo'] ?? null;
            $fotoCover = $data['foto_cover'] ?? null;

            unset(
                $data['logo'],
                $data['foto_cover']
            );

            $profile = CompanyProfile::create($data);

            if ($logo instanceof UploadedFile) {
                $profile->update([
                    'logo' => $this->storeImage(
                        $profile,
                        $logo,
                        'logo'
                    ),
                ]);
            }

            if ($fotoCover instanceof UploadedFile) {
                $profile->update([
                    'foto_cover' => $this->storeImage(
                        $profile,
                        $fotoCover,
                        'cover'
                    ),
                ]);
            }

            return $profile->fresh();
        });
    }

    /**
     * Update company profile.
     */
    public function updateProfile(
        CompanyProfile $profile,
        array $data
    ): CompanyProfile {
        return DB::transaction(function () use (
            $profile,
            $data
        ) {
            $logo = $data['logo'] ?? null;
            $fotoCover = $data['foto_cover'] ?? null;

            unset(
                $data['logo'],
                $data['foto_cover']
            );

            if (!empty($data)) {
                $profile->update($data);
            }

            /*
            |------------------------------------------------------------------
            | Replace logo
            |------------------------------------------------------------------
            */

            if ($logo instanceof UploadedFile) {
                $oldLogo = $profile->logo;

                $newLogo = $this->storeImage(
                    $profile,
                    $logo,
                    'logo'
                );

                $profile->update([
                    'logo' => $newLogo,
                ]);

                $this->deleteImageFile($oldLogo);
            }

            /*
            |------------------------------------------------------------------
            | Replace cover photo
            |------------------------------------------------------------------
            */

            if ($fotoCover instanceof UploadedFile) {
                $oldCover = $profile->foto_cover;

                $newCover = $this->storeImage(
                    $profile,
                    $fotoCover,
                    'cover'
                );

                $profile->update([
                    'foto_cover' => $newCover,
                ]);

                $this->deleteImageFile($oldCover);
            }

            return $profile->fresh();
        });
    }

    /**
     * Delete company profile and all physical files.
     */
    public function deleteProfile(
        CompanyProfile $profile
    ): void {
        DB::transaction(function () use ($profile) {

            $profile->delete();

            /*
            |--------------------------------------------------------------
            | Delete entire profile folder.
            |--------------------------------------------------------------
            */

            Storage::disk('public')->deleteDirectory(
                "company-profile/{$profile->id}"
            );
        });
    }

    /**
     * Store image as WebP.
     */
    private function storeImage(
        CompanyProfile $profile,
        UploadedFile $file,
        string $type
    ): string {
        $manager = new ImageManager(
            new Driver()
        );

        $image = $manager->decodePath(
            $file->getPathname()
        );

        $filename = Str::uuid() . '.webp';

        $path = "company-profile/{$profile->id}/{$type}/{$filename}";

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