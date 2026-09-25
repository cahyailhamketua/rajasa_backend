<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Drivers\Gd\Driver;

class UserService
{
    public function getUsers(int $perPage = 10): LengthAwarePaginator
    {
        return User::query()
            ->where('role', 'admin')
            ->with('socialMedia')
            ->select([
                'id',
                'username',
                'nama_lengkap',
                'email',
                'nickname',
                'foto_profil',
                'hobi',
                'pendidikan',
                'pengalaman',
                'role',
                'created_at',
                'updated_at',
            ])
            ->latest()
            ->paginate($perPage);
    }

    public function getUser(User $user): User
    {
        return $user->load('socialMedia');
    }

    public function updateUser(User $user, array $data): User
    {
        $oldPhotoPath = $user->foto_profil;
        $newPhotoPath = null;

        try {
            $updatedUser = DB::transaction(function () use (
                $user,
                $data,
                &$newPhotoPath
            ) {
                $socialMedia = $data['social_media'] ?? null;
                $photo = $data['foto_profil'] ?? null;

                unset(
                    $data['social_media'],
                    $data['foto_profil']
                );

                if ($photo instanceof UploadedFile) {
                    $newPhotoPath = $this->storeProfilePhoto(
                        $user,
                        $photo
                    );

                    $data['foto_profil'] = $newPhotoPath;
                }

                $user->update($data);

                if ($socialMedia !== null) {
                    $this->syncSocialMedia(
                        $user,
                        $socialMedia
                    );
                }

                return $user->load('socialMedia');
            });

            /*
            * DB sudah berhasil commit.
            * Sekarang aman menghapus foto lama.
            */
            if (
                $newPhotoPath !== null &&
                $oldPhotoPath &&
                $oldPhotoPath !== $newPhotoPath
            ) {
                Storage::disk('public')->delete($oldPhotoPath);
            }

            return $updatedUser;

        } catch (\Throwable $e) {

            /*
            * Database gagal → hapus file baru
            * yang sudah sempat dibuat.
            */
            if ($newPhotoPath !== null) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            throw $e;
        }
    }

    private function syncSocialMedia(User $user, array $socialMedia): void
    {
        $existingIds = $user->socialMedia()
            ->pluck('id')
            ->toArray();

        $receivedIds = [];

        foreach ($socialMedia as $item) {

            // EDIT social media
            if (isset($item['id'])) {

                // Pastikan ID benar-benar milik user tersebut
                if (! in_array($item['id'], $existingIds)) {
                    abort(422, 'Social media tidak valid.');
                }

                $social = $user->socialMedia()
                    ->findOrFail($item['id']);

                $social->update([
                    'platform' => $item['platform'],
                    'username' => $item['username'] ?? null,
                    'url' => $item['url'] ?? null,
                ]);

                $receivedIds[] = $social->id;

            } else {

                // TAMBAH social media baru
                $social = $user->socialMedia()->create([
                    'platform' => $item['platform'],
                    'username' => $item['username'] ?? null,
                    'url' => $item['url'] ?? null,
                ]);

                $receivedIds[] = $social->id;
            }
        }

        // HAPUS social media yang tidak dikirim lagi
        if (count($receivedIds) > 0) {
            $user->socialMedia()
                ->whereNotIn('id', $receivedIds)
                ->delete();
        } else {
            // Kalau social_media dikirim sebagai array kosong [],
            // berarti user memang ingin menghapus semuanya.
            $user->socialMedia()->delete();
        }
    }

    private function storeProfilePhoto(User $user, UploadedFile $file): string 
    {
        $manager = new ImageManager(
            new Driver()
        );

        $image = $manager->decodePath(
            $file->getPathname()
        );

        $filename = Str::uuid() . '.webp';

        $path = "users/profile/{$user->id}/{$filename}";

        $encoded = $image->encode(
            new WebpEncoder(quality: 80)
        );

        Storage::disk('public')->put(
            $path,
            $encoded
        );

        return $path;
    }

    public function deleteUser(User $user): void
    {
        $user->delete();
    }
}