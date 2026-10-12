<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyHistoryRequest;
use App\Http\Requests\UpdateCompanyHistoryRequest;
use App\Models\CompanyHistory;
use App\Services\CompanyHistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyHistoryController extends Controller
{
    public function __construct(
        private CompanyHistoryService $companyHistoryService
    ) {
    }

    /**
     * Display a listing of company histories.
     *
     * Public endpoint.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(
            $request->integer('per_page', 12),
            100
        );

        $histories = CompanyHistory::query()
            ->orderBy('urutan')
            ->orderByDesc('tahun')
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data company history berhasil diambil.',
            'data' => $histories,
        ]);
    }

    /**
     * Display the specified company history.
     *
     * Public endpoint.
     */
    public function show(
        CompanyHistory $companyHistory
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => 'Detail company history berhasil diambil.',
            'data' => [
                'history' => $companyHistory,
            ],
        ]);
    }

    /**
     * Store a newly created company history.
     *
     * Super admin only.
     */
    public function store(
        StoreCompanyHistoryRequest $request
    ): JsonResponse {
        $history = $this->companyHistoryService->createHistory(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Company history berhasil dibuat.',
            'data' => [
                'history' => $history,
            ],
        ], 201);
    }

    /**
     * Update the specified company history.
     *
     * Super admin only.
     */
    public function update(
        UpdateCompanyHistoryRequest $request,
        CompanyHistory $companyHistory
    ): JsonResponse {
        $history = $this->companyHistoryService->updateHistory(
            $companyHistory,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Company history berhasil diperbarui.',
            'data' => [
                'history' => $history,
            ],
        ]);
    }

    /**
     * Remove the specified company history.
     *
     * Super admin only.
     */
    public function destroy(
        CompanyHistory $companyHistory
    ): JsonResponse {
        $this->companyHistoryService->deleteHistory(
            $companyHistory
        );

        return response()->json([
            'success' => true,
            'message' => 'Company history berhasil dihapus.',
        ]);
    }
}