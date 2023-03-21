<?php

namespace App\Http\Controllers\Backend;

use App\Exports\ImportExample;
use App\Http\Controllers\Controller;
use App\Models\App;
use App\Models\TagGroup;
use Auth;
use Maatwebsite\Excel\Facades\Excel;
use Session;
use Str;
use View;

class ImportController extends Controller
{
    private $appSettings;

    public function __construct()
    {
        parent::__construct();
        $this->appSettings = app(\App\Services\AppSettings::class);
        View::share('activeNav', 'import');
    }

    /**
     * Shows the different available import options.
     *
     * @return View
     */
    public function index()
    {
        $user = Auth::user();
        $indexcardsImport = ($user->hasRight('indexcards-edit') && $this->appSettings->getValue('import_index_cards'));
        $questionsImport = ($user->hasRight('questions-edit') && $this->appSettings->getValue('import_questions'));
        $usersImport = ($user->hasRight('users-edit') && $this->appSettings->getValue('import_users'));
        $usersImportDelete = ($user->hasRight('users-edit') && $this->appSettings->getValue('import_users_delete'));
        if (!($indexcardsImport || $questionsImport || $usersImport || $usersImportDelete)) {
            Session::flash('error-message', 'Sie haben dazu leider keine Berechtigung!');
            return redirect()->to('/');
        }

        $currentLanguage = language();
        $defaultLanguage = defaultAppLanguage(appId());

        return view('vue-component', [
            'component' => 'import',
            'hasFluidContent' => false,
            'props' => [
                'indexcards'  => $indexcardsImport,
                'questions'   => $questionsImport,
                'removeusers' => $usersImportDelete,
                'users'       => $usersImport,
                'current-language' => $currentLanguage,
                'default-language' => $defaultLanguage,
            ],
        ]);
    }

    /**
     * The function returns view to import questions.
     *
     * @return View
     */
    public function questionsImport()
    {
        if (!(Auth::user()->hasRight('questions-edit') && $this->appSettings->getValue('import_questions'))) {
            Session::flash('error-message', 'Sie haben dazu leider keine Berechtigung!');
            return redirect()->to('/import');
        }

        return view('vue-component', [
            'component' => 'import-questions',
            'hasFluidContent' => false,
        ]);
    }

    /**
     * The function returns the view to import users.
     *
     * @return View
     */
    public function usersImport()
    {
        if (!(Auth::user()->hasRight('users-edit') && $this->appSettings->getValue('import_users'))) {
            Session::flash('error-message', 'Sie haben dazu leider keine Berechtigung!');
            return redirect()->to('/import');
        }

        return view('vue-component', [
            'component' => 'import-users',
            'hasFluidContent' => false,
        ]);
    }

    /**
     * The function returns the view to delete users.
     *
     * @return View
     */
    public function usersDeletion()
    {
        if (!(Auth::user()->hasRight('users-edit') && $this->appSettings->getValue('import_users_delete'))) {
            Session::flash('error-message', 'Sie haben dazu leider keine Berechtigung!');
            return redirect()->to('/import');
        }

        return view('vue-component', [
            'component' => 'delete-users',
            'hasFluidContent' => false,
        ]);
    }

    /**
     * The function returns the view to import cards.
     *
     * @return View
     */
    public function cardsImport()
    {
        if (!(Auth::user()->hasRight('indexcards-edit') && $this->appSettings->getValue('import_index_cards'))) {
            Session::flash('error-message', 'Sie haben dazu leider keine Berechtigung!');
            return redirect()->to('/import');
        }

        return view('vue-component', [
            'component' => 'import-cards',
            'hasFluidContent' => false,
        ]);
    }

    /**
     *  Creates a dynamic example file with header information.
     * @param $type
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function createExampleFile($type)
    {
        $app = App::find(appId());
        if (! $app) {
            return app()->abort(404);
        }

        if (! $type) {
            return app()->abort(404);
        }

        $fields = [];
        $filename = 'demo-import-';
        switch ($type) {
            case 'user-import':
                $languages = $app->getLanguages();
                $fields = [
                    '*Vorname',
                    'Nachname',
                    'E-Mail',
                ];
                if (count($languages) > 1) {
                    $fields[] = '*Sprache ('.implode(',', $languages).')';
                }

                $meta = $app->getUserMetaDataFields(true);
                $meta = collect($meta)
                    ->filter(function ($meta) {
                        return (bool) $meta['import'];
                    })
                    ->map(function ($meta) {
                        switch ($meta['type']) {
                            case 'date':
                                return 'Meta: '.$meta['label'].' (YYYY-MM-DD)';
                            default:
                                return 'Meta: '.$meta['label'];
                        }
                    })->toArray();
                $fields = array_merge($fields, $meta);

                $tagGroups = TagGroup::where('app_id', $app->id)->get()->transform(function ($tagGroup) {
                    return 'TAG Gruppe: '.$tagGroup->name;
                })->toArray();
                $fields = array_merge($fields, $tagGroups);
                $filename .= 'users-'.Str::slug($app->name);
                break;
        }

        return Excel::download(new ImportExample($fields), $filename.'.xlsx');
    }
}
