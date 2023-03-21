<?php

namespace App\Http\Controllers\BackendApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackendApi\Form\TodolistItemStoreRequest;
use App\Models\Todolist;
use App\Models\TodolistItem;
use App\Transformers\BackendApi\Todolists\TodolistItemEditTransformer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class TodolistItemsController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth.backendaccess:courses,courses-edit')->except('list');
        $this->middleware('auth.backendaccess:courses,courses-edit|courses-view')->only('list');
    }

    /**
     * Lists all items for a given todolist
     *
     * @param int $todolistId
     * @return JsonResponse
     * @throws Exception
     */
    public function list(int $todolistId): JsonResponse
    {
        /** @var Todolist $todolist */
        $todolist = Todolist::where('app_id', appId())
            ->where('id', $todolistId)
            ->firstOrFail();

        return response()->json([
            'todolistItems' => $this->getTodolistItemsResponse($todolist),
        ]);
    }

    /**
     * Adds a todolist item
     *
     * @param int $todolistId
     * @param TodolistItemEditTransformer $todolistItemEditTransformer
     * @return JsonResponse
     * @throws Exception
     */
    public function store(int $todolistId, TodolistItemEditTransformer $todolistItemEditTransformer): JsonResponse
    {
        /** @var Todolist $todolist */
        $todolist = Todolist::where('app_id', appId())->where('id', $todolistId)->firstOrFail();
        $todolistItem = new TodolistItem();
        $todolistItem->todolist_id = $todolist->id;
        $todolistItem->setLanguage(defaultAppLanguage(appId()));
        $todolistItem->position = $todolist->todolistItems()->count();
        $todolistItem->title = '';
        $todolistItem->description = '';
        $todolistItem->save();

        return response()->json([
            'todolistItem' => $todolistItemEditTransformer->transform($todolistItem),
        ]);
    }

    /**
     * Updates the data of todolist items
     *
     * @param int $todolistId
     * @return JsonResponse
     * @throws Exception
     */
    public function update(int $todolistId): JsonResponse
    {
        /** @var Todolist $todolist */
        $todolist = Todolist::where('app_id', appId())->where('id', $todolistId)->firstOrFail();
        foreach (request()->input('todolistItems') as $todolistItemUpdate) {
            $todolistItem = $todolist->todolistItems->where('id', $todolistItemUpdate['id'])->firstOrFail();
            $todolistItem->position = $todolistItemUpdate['position'];
            $todolistItem->title = $todolistItemUpdate['title'];
            $todolistItem->description = $todolistItemUpdate['description'];
            $todolistItem->save();
        }
        return response()->json([]);
    }

    /**
     * Returns the response for a list of todolist items
     *
     * @param Todolist $todolist
     * @return Collection
     * @throws Exception
     */
    private function getTodolistItemsResponse(Todolist $todolist)
    {
        $todolist->load('todolistItems.translationRelation');
        if (language() !== defaultAppLanguage()) {
            $todolist->load('todolistItems.defaultTranslationRelation');
        }

        /** @var TodolistItemEditTransformer $transformer */
        $transformer = app(TodolistItemEditTransformer::class);
        return $transformer->transformAll($todolist->todolistItems);
    }

    /**
     * @param int $todolistId
     * @param int $todolistItemId
     * @return JsonResponse
     * @throws Exception
     */
    public function deleteInformation(int $todolistId, int $todolistItemId): JsonResponse
    {
        /** @var Todolist $todolist */
        $todolist = Todolist::where('app_id', appId())->where('id', $todolistId)->firstOrFail();
        /** @var TodolistItem $todolistItem */
        $todolistItem = $todolist->todolistItems()->where('id', $todolistItemId)->firstOrFail();
        return response()->json([
            'dependencies' => $todolistItem->safeRemoveDependees(),
            'blockers' => $todolistItem->getBlockingDependees(),
        ]);
    }

    /**
     * @param int $todolistId
     * @param int $todolistItemId
     * @return JsonResponse
     * @throws Exception
     */
    public function delete(int $todolistId, int $todolistItemId): JsonResponse
    {
        /** @var Todolist $todolist */
        $todolist = Todolist::where('app_id', appId())->where('id', $todolistId)->firstOrFail();
        /** @var TodolistItem $todolistItem */
        $todolistItem = $todolist->todolistItems()->where('id', $todolistItemId)->firstOrFail();

        $deletionResult = $todolistItem->safeRemove();

        if ($deletionResult->isSuccessful()) {
            return response()->json([], 204);
        } else {
            return response()->json($deletionResult->getMessages(), 400);
        }
    }
}
