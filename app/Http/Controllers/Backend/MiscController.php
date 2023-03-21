<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as Input;
use Intervention\Image\Facades\Image;
use Response;
use View;

class MiscController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        View::share('activeNav', 'helpdesk');
    }

    /**
     * Displays a list of pages.
     *
     * @return mixed
     */
    public function faq()
    {
        $pages = Page::where('app_id', 0)->orderBy('id', 'ASC')->get();

        return view('misc.faq', [
            'pages' => $pages,
        ]);
    }

    /**
     * Removes a page.
     *
     * @return int
     */
    public function removeFaq($id)
    {
        if (! isSuperAdmin()) {
            app()->abort(403);
        }

        $page = Page::find($id);
        if (! $page || $page->app_id != 0) {
            app()->abort(403);
        }

        $page->delete();

        return redirect()->back();
    }

    /**
     * Uploads an image.
     *
     * @return int
     */
    public function faqImageUpload(Request $request)
    {
        if (! isSuperAdmin()) {
            app()->abort(403);
        }

        $files = $request->file('files');
        $paths = [];
        foreach ($files as $file) {
            $destinationPath = 'page_attachments/';
            $filename = md5_file($file->getPathname()).'.'.$file->getClientOriginalExtension();
            $full_path = $destinationPath.$filename;

            rename($file->getPathname(), public_path('storage/'.$full_path));
            chmod(public_path('storage/'.$full_path), 0777);
            $paths[] = [
                'url' => '/storage/'.$full_path,
            ];
        }

        return Response::json([
            'files' => $paths,
        ]);
    }

    /**
     * Updates a page.
     *
     * @return int
     */
    public function updateFaq($id)
    {
        if (! isSuperAdmin()) {
            app()->abort(403);
        }

        $page = Page::find($id);
        if (! $page || $page->app_id != 0) {
            app()->abort(403);
        }

        // Update the page
        if ($content = Input::get('content')) {
            $page->content = $content;
        }
        if ($title = Input::get('title')) {
            $page->title = $title;
        }
        $page->save();

        return 1;
    }

    /**
     * Adds a page.
     *
     * @return int
     */
    public function addFaq()
    {
        if (! isSuperAdmin()) {
            app()->abort(403);
        }

        $page = new Page();
        $page->setLanguage(defaultAppLanguage(appId()));
        $page->title = 'Neue Hilfeseite';
        $page->content = '';
        $page->save();

        return redirect()->back();
    }
}
