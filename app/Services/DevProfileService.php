<?php

namespace App\Services;

use App\Models\File;
use App\Models\DevProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class DevProfileService
{
    public function __construct(
        private FileService $fileService
    ) {}

    /**
     * Update profile
     * @param DevProfile $profile
     * @param array $data
     * @return DevProfile
     * @throws \Throwable
     */
    public function updateProfile(DevProfile $profile, array $data): DevProfile
    {
        return DB::transaction(function () use ($profile, $data) {
            $profile->update($data);
            return $profile->fresh(['avatar', 'resume']);
        });
    }

    /**
     * Delete previous avatar if exists, upload the new one.
     * @param DevProfile $profile
     * @param UploadedFile $uploadedFile
     * @return array
     * @throws \Throwable
     */
    public function uploadAvatar(DevProfile $profile, UploadedFile $uploadedFile): array
    {
        return DB::transaction(function () use ($profile, $uploadedFile) {
            if ($profile->avatar_file_id) {
                $this->fileService->deleteFile($profile->avatar);
            }

            return $this->fileService->upload($profile, [$uploadedFile], [
                'type' => 'image',
                'relationship_type' => 'one_to_one',
                'foreign_key' => 'avatar_file_id',
            ]);
        });
    }

    /**
     * Delete previous resume if exists, upload the new one.
     * @param DevProfile $profile
     * @param UploadedFile $uploadedFile
     * @return array
     * @throws \Throwable
     */
    public function uploadResume(DevProfile $profile, UploadedFile $uploadedFile): array
    {
        return DB::transaction(function () use ($profile, $uploadedFile) {
            if ($profile->resume_file_id) {
                $this->fileService->deleteFile($profile->resume);
            }

            return $this->fileService->upload($profile, [$uploadedFile], [
                'type' => 'document',
                'relationship_type' => 'one_to_one',
                'foreign_key' => 'resume_file_id',
            ]);
        });
    }

    /**
     * Deletes avatar
     * @param DevProfile $profile
     * @return void
     * @throws \Throwable
     */
    public function deleteAvatar(DevProfile $profile): void
    {
        DB::transaction(function () use ($profile) {
            if ($profile->avatar) {
                $this->fileService->deleteFile($profile->avatar);
            }
        });
    }

    /**
     * Deletes resume
     * @param DevProfile $profile
     * @return void
     * @throws \Throwable
     */
    public function deleteResume(DevProfile $profile): void
    {
        DB::transaction(function () use ($profile) {
            if ($profile->resume) {
                $this->fileService->deleteFile($profile->resume);
            }
        });
    }
}
