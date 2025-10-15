<?php

namespace App\Services;

use App\Models\Certification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CertificationService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly FileService $fileService,
        private readonly TechnologyService $technologyService
    ) {}

    /**
     * @throws \Throwable
     */
    public function create(array $data, ?array $certificationImage): Certification
    {
        try {
            return DB::transaction(function () use ($data, $certificationImage) {
                $certification = Certification::create([
                    'name' => $data['name'],
                    'issuing_organization' => $data['issuing_organization'],
                    'issue_date' => $data['issue_date'],
                    'expiration_date' => $data['expiration_date'],
                    'credential_id' => $data['credential_id'],
                    'credential_url' => $data['credential_url'],

                ]);

                if (!empty($certificationImage)) {
                    $this->fileService->upload($certification, $certificationImage, [
                        'type' => 'image',
                        'relationship_type' => 'one_to_one',
                        'foreign_key' => 'certification_image_id',
                    ]);
                }

                $this->technologyService->syncTechnologies($certification, $data['technology_ids'] ?? []);

                return $certification->fresh(['certificationImage', 'technologies']);
            });
        } catch (\Exception $e) {
            Log::error('Certification creation failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }
}
