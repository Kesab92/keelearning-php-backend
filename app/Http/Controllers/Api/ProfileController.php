<?php

namespace App\Http\Controllers\Api;

use App\Http\APIError;
use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\User;
use Exception;
use Illuminate\Http\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Response;
use Storage;
use Validator;

class ProfileController extends Controller
{
    public function getUserProfile()
    {
        $user = user();

        return Response::json([
            'id'           => $user->id,
            'name'         => $user->username,
            'tos_accepted' => $user->tos_accepted,
            'email'        => $user->email,
            'avatar'       => $user->avatar_url,
            'is_admin'     => $user->is_admin || $user->isSuperAdmin(),
        ]);
    }

    /**
     * The function uploads an image and creates resized version of it, which are saved in the avatars folder.
     *
     * @param Request $request
     * @return APIError|JsonResponse
     */
    public function setAvatar(Request $request)
    {
        $user = user();

        // Validate the file to be present
        $validator = Validator::make($request->all(), [
            'image' => 'required',
        ]);

        if ($validator->fails()) {
            return new APIError(__('errors.image_upload_failed'));
        }

        if ($user->avatar) {
            Storage::delete($user->avatar);
            $user->avatar = null;
            $user->avatar_url = null;
        }

        $base64String = $request->get('image');
        $tmpOutputFile = '';
        try {
            $tmpOutputFile = base64_to_file($base64String);

            $img = Image::make($tmpOutputFile);

            if($img->mime() !== 'image/svg+xml') {
                $img->orientate();
                $img->fit(User::AVATAR_SIZE);
                $img->save($tmpOutputFile, 80);
            }

            $path = Storage::putFile('uploads', new File($tmpOutputFile));

            $user->avatar = $path;
            $user->avatar_url = Storage::url($path);
            $user->save();
            unlink($tmpOutputFile);
        } catch (\Exception $e) {
            if (file_exists($tmpOutputFile)) {
                unlink($tmpOutputFile);
            }
            $user->save();

            return new APIError(__('errors.image_upload_failed'));
        }

        return Response::json([
            'success' => 1,
            'avatar' => $user->avatar_url,
        ]);
    }

    /**
     * Returns a list containing the path to all avatar icons for the current app.
     * @return JsonResponse
     * @throws Exception
     */
    public function getDefaultAvatars(): JsonResponse
    {
        $app = App::find(appId());
        $avatars = User::getDefaultAvatars($app);

        $response = [
            'success' => 1,
            'avatars' => $avatars,
        ];

        return Response::json($response);
    }
}
