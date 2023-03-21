<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Categorygroup;
use App\Models\CategoryHider;
use App\Models\Question;
use App\Models\TagGroup;
use App\Services\AppSettings;
use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Image;
use Response;
use Storage;

class CategoriesController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:,categories-edit')
            ->except(['getActiveQuestionCategories', 'search']);
        $this->middleware('auth.backendaccess:,categories-edit|questions-edit')
            ->only(['getActiveQuestionCategories']);
        $this->middleware('auth.backendaccess:,tests-edit')
            ->only(['search']);
    }

    /**
     * Returns the categories & groups.
     *
     * @param AppSettings $settings
     *
     * @throws \Exception
     */
    public function getCategories(AppSettings $settings)
    {
        $categories = Category::withTranslation()
            ->ofApp(appId())
            ->with([
                'cloneRecord',
                'hiders',
                'tags',
            ])
            ->get()
            ->transform(function ($category) {
                return $this->formatCategory($category);
            });
        $categoryGroups = null;
        $useCategoryGroups = (bool) $settings->getValue('use_subcategory_system');
        if ($useCategoryGroups) {
            $categoryGroups = Categorygroup::withTranslation()
            ->ofApp(appId())
            ->with('cloneRecord')
            ->get()
            ->transform(function ($categoryGroup) {
                return [
                    'id'   => $categoryGroup->id,
                    'name' => $categoryGroup->translation()->name,
                    'is_reusable_clone' => $categoryGroup->is_reusable_clone,
                    'tags' => $categoryGroup->tags()->pluck('tags.id'),
                ];
            });
        }
        $tagGroups = TagGroup::ofApp(appId())->get();

        return Response::json([
            'categories'        => $categories,
            'categoryGroups'    => $categoryGroups,
            'tagGroups'         => $tagGroups,
            'useCategoryGroups' => $useCategoryGroups,
        ]);
    }

    /**
     * Returns the for questions enabled categories.
     *
     * @throws \Exception
     */
    public function getActiveQuestionCategories()
    {
        $categories = Category::withTranslation()
            ->ofApp(appId())
            ->with('tags')
            ->get()
            ->transform(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->translation()->name,
                    'tags' => $category->tags,
                ];
            })
            ->sortBy('name', SORT_NATURAL|SORT_FLAG_CASE)
            ->values();

        return Response::json([
            'categories' => $categories,
        ]);
    }

    /**
     * Searches categories by name.
     *
     * @param Request $request
     *
     * @return \App\Http\APIError|\Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function search(Request $request)
    {
        $searchTerm = utrim($request->input('query'));
        if (strlen($searchTerm) < 3) {
            app()->abort(422, 'Suchstring zu kurz.');
        }
        $withoutIndexcards = (bool) $request->input('withoutIndexcards', false);

        $categories = Category::where('active', 1)
            ->select(
                DB::raw('categories.id as id'),
                DB::raw('category_translations.name as name'),
                DB::raw('categories.points as points'),
                DB::raw('COUNT(questions.id) as question_count'),
            )
            ->leftJoin('category_translations', 'category_translations.category_id', '=', 'categories.id')
            ->leftJoin('questions', 'questions.category_id', '=', 'categories.id')
            ->groupBy('categories.id')
            ->where('categories.app_id', appId())
            ->where('questions.visible', 1)
            ->when($withoutIndexcards, function (Builder $q) {
                $q->where('questions.type', '!=', Question::TYPE_INDEX_CARD);
            })
            ->where('category_translations.language', language())
            ->whereRaw('category_translations.name LIKE ?', '%'.escapeLikeInput($searchTerm).'%')
            ->take(100)
            ->get()
            ->map(function ($category) {
                return [
                    'id'               => $category->id,
                    'name'             => $category->name,
                    'points'           => $category->points ?: 1,
                    'question_count'  => $category->question_count,
                ];
            });

        return Response::json([
            'categories' => $categories,
            'success' => true,
        ]);
    }

    /**
     * Create category.
     *
     * @throws \Exception
     */
    public function createCategory()
    {
        if (! request()->input('name')) {
            return Response::json([
                'error' => 'Name darf nicht leer sein!',
                'success' => false,
            ]);
        }

        $category = new Category();
        $category->setLanguage(defaultAppLanguage(appId()));
        $category->app_id = appId();
        $this->updateCategoryDataFromRequest($category);

        return Response::json([
            'category' => $this->formatCategory($category),
            'success' => true,
        ]);
    }

    /**
     * Updates category.
     *
     * @throws \Exception
     */
    public function updateCategory($id)
    {
        if (! request()->input('name')) {
            return Response::json([
                'error' => 'Name darf nicht leer sein!',
                'success' => false,
            ]);
        }
        $category = Category::ofApp(appId())->findOrFail($id);
        $this->updateCategoryDataFromRequest($category);

        return Response::json([
            'category' => $this->formatCategory($category),
            'success' => true,
        ]);
    }

    /**
     * Create category group.
     *
     * @throws \Exception
     */
    public function createCategoryGroup()
    {
        if (! request()->input('name')) {
            return Response::json([
                'error' => 'Name darf nicht leer sein!',
                'success' => false,
            ]);
        }

        $categoryGroup = new CategoryGroup();
        $categoryGroup->setLanguage(defaultAppLanguage(appId()));
        $categoryGroup->app_id = appId();
        $categoryGroup->name = request()->input('name');
        $categoryGroup->save();

        if ($tags = request()->input('tags')) {
            $categoryGroup->tags()->sync($tags);
        } else {
            $categoryGroup->tags()->detach();
        }

        return Response::json([
            'categoryGroup' => $this->formatCategoryGroup($categoryGroup),
            'success' => true,
        ]);
    }

    /**
     * Updates category group.
     *
     * @throws \Exception
     */
    public function updateCategoryGroup($id)
    {
        $categoryGroup = CategoryGroup::ofApp(appId())->findOrFail($id);
        if (request()->input('name')) {
            $categoryGroup->name = request()->input('name');
        }
        if ($tags = request()->input('tags')) {
            $categoryGroup->tags()->sync($tags);
        } else {
            $categoryGroup->tags()->detach();
        }
        $categoryGroup->save();

        return Response::json([
            'categoryGroup' => $this->formatCategoryGroup($categoryGroup),
            'success' => true,
        ]);
    }

    /**
     * Deletes category group.
     *
     * @throws \Exception
     */
    public function deleteCategoryGroup($id)
    {
        $categoryGroup = CategoryGroup::ofApp(appId())->findOrFail($id);
        $result = $categoryGroup->safeRemove();
        if ($result->success === true) {
            return Response::json([
                'success' => true,
            ]);
        } else {
            return Response::json([
                'error' => implode('<br>', $result->getMessages()),
                'success' => false,
            ]);
        }
    }

    private function formatCategory($category)
    {
        return [
            'id'                => $category->id,
            'active'            => $category->active,
            'categorygroup_id'  => $category->categorygroup_id,
            'cover_image_url'   => $category->cover_image_url,
            'hiders'            => $category->hiders()->pluck('scope'),
            'icon_url'          => $category->category_icon_url,
            'is_reusable_clone' => $category->is_reusable_clone,
            'name'              => $category->translation()->name,
            'points'            => $category->points,
            'tags'              => $category->tags->pluck('id'),
        ];
    }

    private function formatCategoryGroup($categoryGroup)
    {
        return [
            'id'   => $categoryGroup->id,
            'name' => $categoryGroup->name,
            'tags' => $categoryGroup->tags()->pluck('tags.id'),
        ];
    }

    private function updateCategoryDataFromRequest($category)
    {
        $category->name = request()->input('name');
        $category->active = (bool) request()->input('active');
        $category->points = 1;
        if (request()->has('points')) {
            $category->points = (int) request()->input('points');
        }
        $category->categorygroup_id = null;
        if (request()->input('categorygroup_id')) {
            $categoryGroup = CategoryGroup::ofApp(appId())->find(request()->input('categorygroup_id'));
            if ($categoryGroup) {
                $category->categorygroup_id = $categoryGroup->id;
            }
        }

        if (request()->has('icon')) {
            $tmpFile = '';
            try {
                $tmpFile = base64_to_file(request()->get('icon'));
                if(mime_content_type($tmpFile) !== 'image/svg+xml') {
                    $img = Image::make($tmpFile);
                    $img->fit(100)->save($tmpFile, 80);
                }
                $imagePath = Storage::putFile('uploads', new File($tmpFile));
                $category->category_icon = $imagePath;
                $category->category_icon_url = Storage::url($imagePath);
                unlink($tmpFile);
            } catch (\Exception $e) {
                if (file_exists($tmpFile)) {
                    unlink($tmpFile);
                }
                throw $e;
            }
        }

        if (request()->has('cover_image')) {
            $tmpFile = '';
            try {
                $tmpFile = base64_to_file(request()->get('cover_image'));
                if(mime_content_type($tmpFile) !== 'image/svg+xml') {
                    $img = Image::make($tmpFile);
                    $img->fit(800, 480)->save($tmpFile, 80);
                }
                $imagePath = Storage::putFile('uploads', new File($tmpFile));
                $category->cover_image = $imagePath;
                $category->cover_image_url = Storage::url($imagePath);
                unlink($tmpFile);
            } catch (\Exception $e) {
                if (file_exists($tmpFile)) {
                    unlink($tmpFile);
                }
                throw $e;
            }
        }

        $category->save();

        if ($tags = request()->input('tags')) {
            $category->tags()->sync($tags);
        } else {
            $category->tags()->detach();
        }

        // since the user checks what scopes to not hide, we have to basically inverse the array
        $toHide = [];
        $toShow = request()->input('scopes') ?: [];
        foreach (CategoryHider::scopes() as $id => $desc) {
            if (! in_array($id, $toShow)) {
                $toHide[] = $id;
            }
        }
        // remove hiders for the scopes we want to actually see
        $category->hiders()->whereIn('scope', $toShow)->delete();
        // add missing hiders for where we don't want the category to show up
        foreach ($toHide as $hideId) {
            if ($category->isVisibleForScope($hideId)) {
                $hider = new CategoryHider();
                $hider->category_id = $category->id;
                $hider->scope = $hideId;
                $hider->save();
            }
        }
    }

    public function deleteInformation($id)
    {
        $category = Category::ofApp(appId())->findOrFail($id);
        return Response::json([
            'dependencies' => $category->safeRemoveDependees(),
            'blockers' => $category->getBlockingDependees(),
        ]);
    }

    public function deleteCategory($id) {
        $category = Category::ofApp(appId())->findOrFail($id);

        $result = $category->safeRemove();

        if($result->isSuccessful()) {
            return Response::json([], 204);
        } else {
            return Response::json($result->getMessages(), 400);
        }
    }
}
