<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Imports\Exceptions\InvalidDataException;
use App\Imports\Exceptions\InvalidHeadersException;
use App\Imports\UsersDeleter;
use App\Imports\UsersImporter;
use App\Jobs\DeleteUsers;
use App\Models\Import;
use Auth;
use Illuminate\Http\Request;
use Response;

class UsersDeletionController extends Controller
{
    private $appSettings;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:,users-edit');
        $this->middleware('auth.appsetting:import_users_delete');
    }

    /**
     * Imports users.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function collectChanges(Request $request)
    {
        $data = json_decode($request->get('data'), true);

        $compareHeader = $data['compare_header'];
        $headers = $data['headers'];
        $users = $data['users'];

        /** @var UsersImporter $importer */
        $importer = app(UsersDeleter::class);
        $error = null;
        $additionalData = [
            'appId' => appId(),
            'compareHeader' => $compareHeader,
            'creatorId' => Auth::user()->id,
        ];
        $changes = [];
        try {
            $changes = $importer->collectChanges($additionalData, $headers, $users);
        } catch (InvalidDataException $e) {
            report($e);
            $error = 'Es wurden ungültige Daten übergeben.';
        } catch (InvalidHeadersException $e) {
            report($e);
            $error = 'Es wurden eine ungültige Spaltenzuordnung übergeben.';
        } catch (\Exception $e) {
            report($e);
            $error = $e->getMessage();
        }
        if ($error) {
            return Response::json(['errors' => [$error]], 400);
        }

        return Response::json($changes);
    }

    /**
     * Imports users.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function import(Request $request)
    {
        $data = json_decode($request->get('data'), true);

        $compareHeader = $data['compare_header'];
        $headers = $data['headers'];
        $users = $data['users'];

        $import = new Import();
        $import->app_id = appId();
        $import->creator_id = Auth::user()->id;
        $import->type = Import::TYPE_USERS_IMPORT;
        $import->status = Import::STATUS_INPROGRESS;
        $import->steps = 2;
        $import->save();

        $additionalData = [
            'appId' => appId(),
            'creatorId' => Auth::user()->id,
            'compareHeader' => $compareHeader,
            'importId' => $import->id,
        ];

        DeleteUsers::dispatch($additionalData, $headers, $users);

        return Response::json([
            'importId' => $import->id,
        ]);
    }
}
