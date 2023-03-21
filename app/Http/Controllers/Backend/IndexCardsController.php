<?php

namespace App\Http\Controllers\Backend;

use App\Exports\DefaultExport;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\IndexCard;
use App\Services\AppSettings;
use App\Services\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Request as Input;
use Image;
use Maatwebsite\Excel\Facades\Excel;
use Redirect;
use Session;
use Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use View;

class IndexCardsController extends Controller
{
    public function __construct(AppSettings $settings)
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:index_cards,indexcards-edit');
        View::share('activeNav', 'indexcards');
    }

    /**
     * Shows the list of questions.
     *
     * @return View
     */
    public function index()
    {
        View::share('activeNav', 'indexcards');

        $query = Input::get('query');
        $indexcards = IndexCard::with('category.translationRelation')->where('app_id', appId());

        if ($query) {
            $indexcards->where(function ($q) use ($query) {
                $q->whereRaw('front LIKE ?', '%'.escapeLikeInput($query).'%')
                  ->orWhereRaw('back LIKE ?', '%'.escapeLikeInput($query).'%')
                  ->orWhere('id', extractHashtagNumber($query));
            });
        }

        $indexcards = $indexcards->paginate(20);
        $categories = Category
            ::where('app_id', appId())
            ->where('active', 1)
            ->with('translationRelation')
            ->get()
            ->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE);

        return view('indexcards.main', [
                'indexcards' => $indexcards,
                'categories' => $categories,
                'query' => $query,
                'types' => IndexCard::TYPES,
        ]);
    }

    public function create()
    {
        $indexcard = new IndexCard();
        $indexcard->app_id = appId();
        $indexcard->front = nl2br(Input::get('front'));
        $indexcard->back = nl2br(Input::get('back'));
        $indexcard->category_id = Input::get('category');
        $indexcard->type = Input::get('type');
        $indexcard->save();

        return Redirect::to('/indexcards?edit='.$indexcard->id);
    }

    /**
     * Shows the edit view.
     *
     * @param $id
     *
     * @return View
     */
    public function edit($id, AppSettings $settings)
    {
        /** @var IndexCard $indexcard */
        $indexcard = IndexCard::find($id);
        $categories = Category
            ::where('app_id', appId())
            ->where('active', 1)
            ->with(['translationRelation', 'categorygroup.translationRelation'])
            ->get()
            ->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE);

        return view('indexcards.edit', [
            'categories' => $categories,
            'indexcard'  => $indexcard,
            'settings'   => $settings,
        ]);
    }

    /**
     * Updates a user.
     *
     * @param Request $request
     * @param $id
     * @return int
     */
    public function update(Request $request, $id)
    {
        /** @var IndexCard $indexcard */
        $indexcard = IndexCard::find($id);

        // Check the access rights
        if ($indexcard->app_id != appId()) {
            app()->abort(403);
        }

        // Update the card
        $indexcard->front = Input::get('front');
        $indexcard->back = Input::get('back');
        $indexcard->json = Input::get('json');
        $indexcard->category_id = Input::get('category');
        $indexcard->save();

        return 1;
    }

    public function image($id, ImageUploader $imageUploader)
    {
        $indexcard = IndexCard::findOrFail($id);
        $file = Input::file('file');

        if (! $file) {
            app()->abort(403);
        }

        if (! $imageUploader->validate($file)) {
            Session::flash('error-message', 'Dieses Dateiformat wird leider nicht unterstützt.');
            app()->abort(403);
        }

        $oldImage = $indexcard->image_url;

        $img = Image::make($file->getPathname());
        $img->resize(900, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $img->save($file->getPathname(), 80);

        if (! $imagePath = $imageUploader->upload($file)) {
            app()->abort(400);
        }

        $indexcard->cover_image = $imagePath;
        $indexcard->cover_image_url = Storage::url($imagePath);
        $indexcard->save();
        $this->deletePhysicalImage($oldImage);
    }

    private function deletePhysicalImage($path)
    {
        Storage::delete($path);
    }

    public function deleteImage($id)
    {
        $indexcard = IndexCard::findOrFail($id);
        $imagePath = $indexcard->image_url;
        $indexcard->cover_image = null;
        $indexcard->cover_image_url = null;
        $indexcard->save();
        $this->deletePhysicalImage($imagePath);

        return Redirect::to('/indexcards?edit='.$id);
    }

    /**
     * @param $id
     *
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function delete($id)
    {
        /** @var IndexCard $indexcard */
        $indexcard = IndexCard::find($id);

        // Check the access rights
        if ($indexcard->app_id != appId()) {
            app()->abort(403);
        }

        $indexcard->delete();

        Session::flash('success-message', $indexcard->front.' wurde erfolgreich gelöscht');

        return Redirect::back();
    }

    /**
     * Exports standard index cards.
     *
     * @return BinaryFileResponse
     */
    public function export() {
        $indexCards = IndexCard::with('category.translationRelation', 'category.categorygroup.translationRelation')
            ->where('app_id', appId())
            ->where('type', IndexCard::TYPE_STANDARD)
            ->get();

        $filename = 'index-card-export-'.Carbon::now()->format('d.m.Y-H:i').'.xlsx';

        return Excel::download(new DefaultExport([
            'indexCards' => $indexCards,
        ], 'indexcards.csv.export'), $filename);
    }
}
