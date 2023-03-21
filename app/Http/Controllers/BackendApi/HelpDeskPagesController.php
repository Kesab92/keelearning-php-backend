<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\HelpdeskPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Response;

class HelpDeskPagesController extends Controller
{
    protected $pageValidationRules = [
        'category' => 'nullable|max:255',
        'title' => 'required|max:255',
        'content' => 'required',
        'type' => 'required',
    ];

    /**
     * Retrieves the page.
     *
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function findPage($id)
    {
        $page = HelpdeskPage::find($id);

        return Response::json([
            'success' => true,
            'data' => [
                'page' => $page,
                'superadmin' => Auth::user()->isSuperAdmin(),
            ],
        ]);
    }

    /**
     * Stores a new knowledge page.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function storePage(Request $request)
    {
        $this->validate($request, $this->pageValidationRules);
        $page = new HelpdeskPage();
        $page->type = $request->input('type');
        $page->title = $request->input('title');
        $page->content = $request->input('content');
        $page->category = $request->input('category');
        $successful = $page->save();

        return Response::json([
            'success' => $successful,
        ]);
    }

    /**
     * Removes an helpdesk page.
     * @param $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removePage($id)
    {
        $page = HelpdeskPage::find($id);

        if (! $page) {
            return Response::json([
                'success' => false,
                'error' => 'Diese Seite konnte nicht gefunden werden.',
            ]);
        }

        $page->delete();

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * Updates an existing page.
     *
     * @param Request $request
     *
     * @param         $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePage(Request $request, $id)
    {
        $this->validate($request, $this->pageValidationRules);
        $page = HelpdeskPage::find($id);
        if (! $page) {
            return Response::json([
               'success' => false,
               'error' => 'Die Seite konnte nicht gefunden werden',
            ]);
        }

        $page->category = $request->input('category');
        $page->content = $request->input('content');
        $page->title = $request->input('title');
        $page->save();

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * Queries for a given keyword all pages.
     * @param $keyword
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchPages($keyword)
    {
        $pages = HelpdeskPage::whereRaw('title LIKE ?', '%'.escapeLikeInput($keyword).'%')
                    ->orWhereRaw('content LIKE ?', '%'.escapeLikeInput($keyword).'%')
                    ->get();

        return Response::json([
            'success' => true,
            'data' => $pages,
        ]);
    }

    /**
     *  Retrieves the faq pages.
     */
    public function findFAQPages()
    {
        $pages = HelpdeskPage::where('type', HelpdeskPage::CATEGORY_FAQ)->get();

        return Response::json([
            'success' => true,
            'data' => [
                'pages' => $pages,
                'superadmin' => Auth::user()->isSuperAdmin(),
            ],
        ]);
    }

    /**
     * App specific support info text, which includes
     * the email address and phone number.
     */
    public function supportInfo()
    {
        $default_phone = '06131 - 930 600 33';
        $default_email = 'support@keeunit.de';
        $app_id = appId();

        $phone = collect([
            App::ID_DMI => '*+49 (0)1803 / 306 171(* 0,09 /Min. aus dem dt. Festnetz)',
        ])
        ->get($app_id, $default_phone);

        $email = collect([
            App::ID_DMI => 'servicedesk@nexus-ag.de',
        ])
        ->get($app_id, $default_email);

        return Response::json([
            'success' => true,
            'data' => [
                'phone' => $phone,
                'email' => $email,
            ],
        ]);
    }
}
