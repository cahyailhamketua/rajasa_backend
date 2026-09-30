<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyContactRequest;
use App\Http\Requests\UpdateCompanyContactRequest;
use App\Models\CompanyContact;
use App\Services\CompanyContactService;
use Illuminate\Http\JsonResponse;

class CompanyContactController extends Controller
{
    public function __construct(
        private CompanyContactService $companyContactService
    ) {
    }

    /**
     * Display a listing of company contacts.
     *
     * Public endpoint.
     */
    public function index(): JsonResponse
    {
        $contacts = $this->companyContactService
            ->getAllContacts();

        return response()->json([
            'success' => true,
            'message' => 'Data company contact berhasil diambil.',
            'data' => [
                'contacts' => $contacts,
            ],
        ]);
    }

    /**
     * Display the specified company contact.
     *
     * Public endpoint.
     */
    public function show(
        CompanyContact $companyContact
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => 'Detail company contact berhasil diambil.',
            'data' => [
                'contact' => $companyContact,
            ],
        ]);
    }

    /**
     * Store a newly created company contact.
     */
    public function store(
        StoreCompanyContactRequest $request
    ): JsonResponse {
        $contact = $this->companyContactService
            ->createContact(
                $request->validated()
            );

        return response()->json([
            'success' => true,
            'message' => 'Company contact berhasil dibuat.',
            'data' => [
                'contact' => $contact,
            ],
        ], 201);
    }

    /**
     * Update the specified company contact.
     */
    public function update(
        UpdateCompanyContactRequest $request,
        CompanyContact $companyContact
    ): JsonResponse {
        $contact = $this->companyContactService
            ->updateContact(
                $companyContact,
                $request->validated()
            );

        return response()->json([
            'success' => true,
            'message' => 'Company contact berhasil diperbarui.',
            'data' => [
                'contact' => $contact,
            ],
        ]);
    }

    /**
     * Remove the specified company contact.
     */
    public function destroy(
        CompanyContact $companyContact
    ): JsonResponse {
        $this->companyContactService
            ->deleteContact(
                $companyContact
            );

        return response()->json([
            'success' => true,
            'message' => 'Company contact berhasil dihapus.',
        ]);
    }
}