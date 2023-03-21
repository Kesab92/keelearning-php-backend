<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\MailTemplate;
use Illuminate\Support\Facades\Request;
use View;

class MailsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:,mails-edit');
        View::share('activeNav', 'mails');
    }

    /**
     * Displays a list of all mail templates.
     *
     * @return mixed
     * @throws \Exception
     */
    public function index()
    {
        $mails = MailTemplate::getAllTemplates(appId());

        return view('mails.main', [
            'mails' => $mails,
        ]);
    }

    /**
     * Shows the edit view.
     *
     * @param $type
     *
     * @return View
     * @throws \Exception
     */
    public function edit($type)
    {
        $mail = MailTemplate::getTemplate($type, appId());
        $class = 'App\\Mail\\'.$type;

        return view('mails.edit', [
                'mail' => $mail,
                'tags' => $class::getTags(),
        ]);
    }

    /**
     * Creates/updates a mail template.
     *
     * @param $type
     *
     * @return int
     * @throws \Exception
     */
    public function update($type)
    {
        $mail = MailTemplate::getTemplate($type, appId());

        if ($mail->app_id != appId()) {
            $mail = new MailTemplate();
            $mail->app_id = appId();
            $mail->type = $type;
        }

        $mail->title = Request::get('title');
        $mail->body = Request::get('body');
        $mail->save();

        return 1;
    }
}
