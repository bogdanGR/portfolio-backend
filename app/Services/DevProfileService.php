<?php

namespace App\Services;

use App\Models\File;
use App\Models\DevProfile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DevProfileService
{
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
     * @return File
     * @throws \Throwable
     */
    public function uploadAvatar(DevProfile $profile, UploadedFile $uploadedFile): File
    {
        return DB::transaction(function () use ($profile, $uploadedFile) {
            if ($profile->avatar_file_id) {
                $this->deleteFile($profile->avatar);
            }

            $file = $this->storeFile($uploadedFile, 'profile/avatar');

            $profile->update(['avatar_file_id' => $file->id]);

            return $file;
        });
    }

    /**
     * Delete previous resume if exists, upload the new one.
     * @param DevProfile $profile
     * @param UploadedFile $uploadedFile
     * @return File
     * @throws \Throwable
     */
    public function uploadResume(DevProfile $profile, UploadedFile $uploadedFile): File
    {
        return DB::transaction(function () use ($profile, $uploadedFile) {
            if ($profile->resume_file_id) {
                $this->deleteFile($profile->resume);
            }

            $file = $this->storeFile($uploadedFile, 'profile/resume');

            $profile->update(['resume_file_id' => $file->id]);

            return $file;
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
                $this->deleteFile($profile->avatar);
                $profile->update(['avatar_file_id' => null]);
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
                $this->deleteFile($profile->resume);
                $profile->update(['resume_file_id' => null]);
            }
        });
    }

    /**
     * Store file in files table
     * @param UploadedFile $uploadedFile
     * @param string $directory
     * @return File
     */
    private function storeFile(UploadedFile $uploadedFile, string $directory): File
    {
        $filename = Str::uuid() . '.' . $uploadedFile->getClientOriginalExtension();
        $path = $uploadedFile->storeAs($directory, $filename, 'public');

        return File::create([
            'original_name' => $uploadedFile->getClientOriginalName(),
            'filename' => $filename,
            'path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'type' => $this->getFileType($uploadedFile->getMimeType()),
        ]);
    }

    /**
     * Delete file from storage and database
     * @param File $file
     * @return void
     */
    private function deleteFile(File $file): void
    {
        Storage::disk('public')->delete($file->path);
        $file->delete();
    }

    /**
     * Get file type from mime type
     * @param string $mimeType
     * @return string
     */
    private function getFileType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if ($mimeType === 'application/pdf') {
            return 'document';
        }

        return 'file';
    }
}
