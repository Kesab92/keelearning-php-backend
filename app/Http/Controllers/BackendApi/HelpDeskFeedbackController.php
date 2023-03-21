<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Mail\Mailer;
use App\Models\HelpdeskPage;
use App\Models\HelpdeskPageFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class HelpDeskFeedbackController extends Controller
{
    /**
     * Creates a new HelpdeskFeedback.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($id)
    {
        if (HelpdeskPageFeedback::where('user_id', Auth::user()->id)
                            ->where('page_id', $id)
                            ->count()) {
            return Response::json([
                'success' => false,
            ]);
        }

        $feedback = new HelpdeskPageFeedback();
        $feedback->user_id = Auth::user()->id;
        $feedback->page_id = $id;
        $feedback->save();

        return Response::json([
            'success' => true,
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendFeedback(Request $request, $id)
    {
        $this->validate($request, [
            'text' => 'required',
        ]);

        $page = HelpdeskPage::find($id);
        if (! $page) {
            return Response::json([
                'success' => false,
            ]);
        }

        $mailer = app()->make(Mailer::class);
        // Comments out it because of sendFeedbackMail removing
        /*        $mailer->sendFeedbackMail(
                    Auth::user(),
                    $request->input('text'),
                    $page
                );*/

        return Response::json([
            'success' => true,
        ]);
    }
}
