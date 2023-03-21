<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\CertificateTemplate;
use App\Models\Courses\CourseContent;
use App\Models\Test;
use Illuminate\Http\Request;
use Image;
use Response;
use Route;
use Storage;

class CertificatesController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        switch (Route::current()->parameter('type')) {
            case 'test':
                $this->middleware('auth.backendaccess:tests,tests-edit|tests-view')->only(['getCertificate']);
                $this->middleware('auth.backendaccess:tests,tests-edit')->except(['getCertificate']);
                break;
            case 'course':
                $this->middleware('auth.backendaccess:courses,courses-edit|courses-view')->only(['getCertificate']);
                $this->middleware('auth.backendaccess:courses,courses-edit')->except(['getCertificate']);
                break;
            default:
                app()->abort(403);
        }
    }

    /**
     * Uploads a background image for certificate.
     * @param Request $request
     * @param $test_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, $type)
    {
        $this->validate($request, [
            'background_image' => 'required|file',
            'elements' => 'required',
        ]);

        if (! $request->hasFile('background_image')) {
            return Response::json([
               'success' => false,
               'error' => 'No image uploaded',
            ]);
        }

        $foreignId = $request->input('foreign_id');

        $certificate = new CertificateTemplate();
        $certificate->setLanguage(defaultAppLanguage(appId()));

        if ($type === 'test') {
            $test = Test::findOrFail($foreignId);
            if ($test->app_id !== appId()) {
                app()->abort(404);
            }
            $certificate->test_id = $foreignId;
        } elseif ($type === 'course') {
            $courseContent = CourseContent::findOrFail($foreignId);
            if ($courseContent->chapter->course->app_id !== appId()) {
                app()->abort(404);
            }
            $certificate->test_id = 0;
        } else {
            $certificate->test_id = 0;
        }

        $uploadInfo = $this->uploadImage($request);

        $certificate->background_image = $uploadInfo['filename'];
        $certificate->background_image_url = Storage::url($uploadInfo['filename']);
        $certificate->background_image_size = $uploadInfo['backgroundImageSize'];
        $certificate->elements = $request->input('elements');
        $certificate->save();

        if ($type === 'course') {
            $courseContent->foreign_id = $certificate->id;
            $courseContent->save();
        }

        return Response::json([
            'success' => true,
            'certificate' => $certificate,
        ]);
    }

    /**
     * Returns the certificate.
     * @param $test_id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getCertificate($type, $certificateId = null)
    {
        // This is a special case, because we also need to get data about the app when we don't have a certificate id yet
        $certificate = null;
        if ($certificateId) {
            $certificate = CertificateTemplate::findOrFail($certificateId)->disableTranslationFallback();

            // Access check
            $this->getForeignObject($type, $certificate);
        }

        /** @var App $app */
        $app = App::find(appId());

        return Response::json([
            'certificate' => $certificate,
            'metaFields'  => $app->getUserMetaDataFields(true),
        ]);
    }

    /**
     * Returns the object this certificate is used in
     * Might be a test or a course content.
     *
     * @param $type
     * @param CertificateTemplate $certificate
     * @return CourseContent|Test|mixed|null
     * @throws \Exception
     */
    private function getForeignObject($type, CertificateTemplate $certificate)
    {
        if ($type === 'test') {
            if (!$certificate->test || $certificate->test->app_id !== appId()) {
                app()->abort(404);
            }
            if (!$certificate->test->isAccessibleByAdmin()) {
                app()->abort(403);
            }

            return $certificate->test;
        }

        if ($type === 'course') {
            /** @var CourseContent $courseContent */
            $courseContent = CourseContent::where('type', CourseContent::TYPE_CERTIFICATE)
                ->where('foreign_id', $certificate->id)
                ->first();
            if (!$courseContent || $courseContent->chapter->course->app_id !== appId()) {
                app()->abort(404);
            }

            return $courseContent;
        }

        return null;
    }

    /**
     * Updates an existing certificate.
     * @param Request $request
     * @param $test_id
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function update(Request $request, $type, $id)
    {
        $certificate = CertificateTemplate::findOrFail($id);

        // Access check
        $foreignObject = $this->getForeignObject($type, $certificate);

        if ($request->has('reset')) {
            $certificate->elements = '';
            $certificate->background_image_size = '';
            $certificate->background_image = '';
            $certificate->background_image_url = '';
        } else {
            if ($request->has('elements')) {
                $certificate->elements = $request->input('elements');
            }

            $uploadInfo = $this->uploadImage($request);
            if ($uploadInfo) {
                $certificate->background_image = $uploadInfo['filename'];
                $certificate->background_image_url = Storage::url($uploadInfo['filename']);
                if ($uploadInfo['backgroundImageSize']) {
                    $certificate->background_image_size = $uploadInfo['backgroundImageSize'];
                }
            }
        }

        $certificate->save();

        return Response::json([
            'success' => true,
            'certificate' => $certificate,
        ]);
    }

    /**
     * @param $request
     * @return mixed
     */
    private function uploadImage($request)
    {
        if ($request->hasFile('background_image')) {
            $backgroundImageFile = $request->file('background_image');
            $image = Image::make($backgroundImageFile);
            $backgroundImageSize = null;
            if ($request->has('background_image_size')) {
                $backgroundImageSize = json_decode($request->get('background_image_size'), true);
                $backgroundImageSize['actualHeight'] = $image->getHeight();
                $backgroundImageSize['actualWidth'] = $image->getWidth();
                $backgroundImageSize = json_encode($backgroundImageSize);
            }
            $filename = Storage::putFileAs('uploads', $backgroundImageFile, createFilename($backgroundImageFile));

            return [
                'filename' => $filename,
                'backgroundImageSize' => $backgroundImageSize,
            ];
        }
    }
}
