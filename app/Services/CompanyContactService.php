<?php

namespace App\Services;

use App\Models\CompanyContact;
use Illuminate\Support\Collection;

class CompanyContactService
{
    /**
     * Get all company contacts.
     */
    public function getAllContacts(): Collection
    {
        return CompanyContact::query()
            ->orderBy('urutan')
            ->orderBy('id')
            ->get();
    }

    /**
     * Create a company contact.
     */
    public function createContact(array $data): CompanyContact
    {
        return CompanyContact::create($data);
    }

    /**
     * Update a company contact.
     */
    public function updateContact(
        CompanyContact $companyContact,
        array $data
    ): CompanyContact {
        $companyContact->update($data);

        return $companyContact->fresh();
    }

    /**
     * Delete a company contact.
     */
    public function deleteContact(
        CompanyContact $companyContact
    ): void {
        $companyContact->delete();
    }
}