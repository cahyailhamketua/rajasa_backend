<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyProfileRequest;
use App\Http\Requests\UpdateCompanyProfileRequest;
use App\Models\CompanyProfile;
use App\Services\CompanyProfileService;
use Illuminate\Http\JsonResponse;

class CompanyProfileController extends Controller
{
    public function __construct(
        private CompanyProfileService $companyProfileService
    ) {
    }

    /**
     * Display a listing of company profiles.
     *
     * Public endpoint.
     */
    public function index(): JsonResponse
    {
        $profiles = $this->companyProfileService
            ->getAllProfiles();

        return response()->json([
            'success' => true,
            'message' => 'Data company profile berhasil diambil.',
            'data' => [
                'profiles' => $profiles,
            ],
        ]);
    }

    /**
     * Display the specified company profile.
     *
     * Public endpoint.
     */
    public function show(
        CompanyProfile $companyProfile
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => 'Detail company profile berhasil diambil.',
            'data' => [
                'profile' => $companyProfile,
            ],
        ]);
    }

    /**
     * Store a newly created company profile.
     */
    public function store(
        StoreCompanyProfileRequest $request
    ): JsonResponse {
        $profile = $this->companyProfileService
            ->createProfile(
                $request->validated()
            );

        return response()->json([
            'success' => true,
            'message' => 'Company profile berhasil dibuat.',
            'data' => [
                'profile' => $profile,
            ],
        ], 201);
    }

    /**
     * Update the specified company profile.
     */
    public function update(
        UpdateCompanyProfileRequest $request,
        CompanyProfile $companyProfile
    ): JsonResponse {
        $profile = $this->companyProfileService
            ->updateProfile(
                $companyProfile,
                $request->validated()
            );

        return response()->json([
            'success' => true,
            'message' => 'Company profile berhasil diperbarui.',
            'data' => [
                'profile' => $profile,
            ],
        ]);
    }

    /**
     * Remove the specified company profile.
     */
    public function destroy(
        CompanyProfile $companyProfile
    ): JsonResponse {
        $this->companyProfileService
            ->deleteProfile(
                $companyProfile
            );

        return response()->json([
            'success' => true,
            'message' => 'Company profile berhasil dihapus.',
        ]);
    }
}