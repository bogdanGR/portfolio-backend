<?php
namespace App\Http\Controllers;

use App\Models\DevProfile;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\DevProfileService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DevProfileController extends Controller
{
    /**
     * Inject dev profile service
     * @param DevProfileService $devProfileService
     */
    public function __construct(
        private DevProfileService $devProfileService
    ) {}

    /**
     * Show the profile edit form
     * @return \Inertia\Response
     */
    public function edit()
    {
        $profile = DevProfile::current();

        return Inertia::render('profile/Edit', [
            'profile' => $profile,
        ]);
    }

    /**
     * Update Dev profile model
     * @param UpdateProfileRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(UpdateProfileRequest $request)
    {
        $profile = DevProfile::current();

        $this->devProfileService->updateProfile($profile, $request->validated());

        return redirect()
            ->back()
            ->with('message', 'Profile updated successfully!');
    }

    /**
     * Uploads dev's profile Avatar
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $profile = DevProfile::current();

        $this->devProfileService->uploadAvatar($profile, $request->file('avatar'));

        return back()->with('message', 'Avatar uploaded successfully!');
    }

    /**
     * Uploads resume
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function uploadResume(Request $request)
    {
        $request->validate([
            'resume' => 'required|file|mimes:pdf|max:5120',
        ]);

        $profile = DevProfile::current();

        $this->devProfileService->uploadResume($profile, $request->file('resume'));

        return back()->with('message', 'Resume uploaded successfully!');
    }

    /**
     * Delete avatar
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteAvatar()
    {
        $profile = DevProfile::current();

        $this->devProfileService->deleteAvatar($profile);

        return back()->with('message', 'Avatar deleted successfully!');
    }

    /**
     * Delete resume
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteResume()
    {
        $profile = DevProfile::current();

        $this->devProfileService->deleteResume($profile);

        return back()->with('message', 'Resume deleted successfully!');
    }
}
