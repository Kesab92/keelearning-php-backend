<?php

namespace Tests\Feature\BackendApi\Forms;

use App\Models\App;
use App\Models\ContentCategories\ContentCategory;
use App\Models\Forms\Form;
use App\Models\Tag;
use App\Services\AppSettings;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class FormListTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->activateAllAppModules();
        $this->setBackendAPIUser($this->getMainAdmin());

        Form::factory()
            ->count(5)
            ->create([
                'app_id' => $this->quizAppId,
            ]);
    }
    public function test_access_denied_when_form_module_is_disabled()
    {
        $appSettings = new AppSettings($this->quizAppId);
        $appSettings->setValue('module_forms', 0);

        $this->json('GET', $this->backendAPIUrl('/forms'))
            ->assertStatus(403);
    }

    public function test_access_denied_for_admin_without_right()
    {
        $this->setBackendAPIUser($this->getPermissionslessAdmin());

        $this->json('GET', $this->backendAPIUrl('/forms'))
            ->assertStatus(403);
    }

    public function test_basic_form_list_works()
    {
        $secondQuizApp = App::factory()->create([
            'id' => 2,
            'app_hosted_at' => 'http://127.0.0.1',
        ]);

        Form::factory()
            ->count(5)
            ->create([
                'app_id' => $secondQuizApp->id,
            ]);

        $this->json('GET', $this->backendAPIUrl('/forms'))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->where('count', 5)
                    ->has('forms', 5)
                    ->has('forms.0', function (AssertableJson $json) {
                        $json
                            ->whereAllType([
                                'categories' => 'array',
                                'id' => 'integer',
                                'is_archived' => 'integer',
                                'is_draft' => 'integer',
                                'tags' => 'array',
                                'title' => 'string',
                            ]);
                    });
            });
    }

    public function test_active_form_list_works()
    {
        Form::factory()
            ->count(5)
            ->create([
                'app_id' => $this->quizAppId,
                'is_archived' => 1,
            ]);

        $this->json('GET', $this->backendAPIUrl('/forms?filter=active'))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->where('count', 5)
                    ->has('forms', 5)
                    ->has('forms.0', function (AssertableJson $json) {
                        $json
                            ->whereAll([
                                'is_archived' => 0,
                            ])
                            ->whereAllType([
                                'categories' => 'array',
                                'id' => 'integer',
                                'is_archived' => 'integer',
                                'is_draft' => 'integer',
                                'tags' => 'array',
                                'title' => 'string',
                            ]);
                    });
            });
    }

    public function test_archived_form_list_works()
    {
        Form::factory()
            ->count(5)
            ->create([
                'app_id' => $this->quizAppId,
                'is_archived' => 1,
            ]);

        $this->json('GET', $this->backendAPIUrl('/forms?filter=archived'))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->where('count', 5)
                    ->has('forms', 5)
                    ->has('forms.0', function (AssertableJson $json) {
                        $json
                            ->whereAll([
                                'is_archived' => 1,
                            ])
                            ->whereAllType([
                                'categories' => 'array',
                                'id' => 'integer',
                                'is_archived' => 'integer',
                                'is_draft' => 'integer',
                                'tags' => 'array',
                                'title' => 'string',
                            ]);
                    });
            });
    }

    public function test_form_list_search_works()
    {
        $forms = Form::factory()
            ->count(5)
            ->create([
                'app_id' => $this->quizAppId,
                'title' => 'my_form_title_' . rand(),
            ]);

        $this->json('GET', $this->backendAPIUrl('/forms?search=title'))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($forms) {
                $json
                    ->where('count', 5)
                    ->has('forms', 5)
                    ->has('forms.0', function (AssertableJson $json) use ($forms) {
                        $json
                            ->whereAll([
                                'title' => $forms[0]->title,
                            ])
                            ->whereAllType([
                                'categories' => 'array',
                                'id' => 'integer',
                                'is_archived' => 'integer',
                                'is_draft' => 'integer',
                                'tags' => 'array',
                                'title' => 'string',
                            ]);
                    });
            });
    }

    public function test_search_by_tags()
    {
        $tag1 = Tag::factory()
            ->create(['app_id' => $this->quizAppId]);
        $tag2 = Tag::factory()
            ->create(['app_id' => $this->quizAppId]);
        $form1 = Form::factory()
            ->create(['app_id' => $this->quizAppId]);
        $form1->tags()->sync([$tag1->id]);
        $form2 = Form::factory()
            ->create(['app_id' => $this->quizAppId]);
        $form2->tags()->sync([$tag2->id]);

        $this->json('GET', $this->backendAPIUrl('/forms?tags[]=' . $tag1->id . '&tags[]=' . $tag2->id))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->where('count', 2)
                    ->has('forms', 2)
                    ->has('forms.0', function (AssertableJson $json) {
                        $json
                            ->whereAllType([
                                'categories' => 'array',
                                'id' => 'integer',
                                'is_archived' => 'integer',
                                'is_draft' => 'integer',
                                'tags' => 'array',
                                'title' => 'string',
                            ]);
                    });
            });
    }

    public function test_search_by_categories()
    {
        $category1 = ContentCategory::factory()
            ->create([
                'app_id' => $this->quizAppId,
                'type' => ContentCategory::TYPE_FORMS,
            ]);
        $category2 = ContentCategory::factory()
            ->create([
                'app_id' => $this->quizAppId,
                'type' => ContentCategory::TYPE_FORMS,
            ]);
        $form1 = Form::factory()
            ->create(['app_id' => $this->quizAppId]);
        $form1->categories()->syncWithPivotValues([$category1->id], [
            'type' => ContentCategory::TYPE_FORMS,
        ]);
        $form2 = Form::factory()
            ->create(['app_id' => $this->quizAppId]);
        $form2->categories()->syncWithPivotValues([$category2->id], [
            'type' => ContentCategory::TYPE_FORMS,
        ]);

        $this->json('GET', $this->backendAPIUrl('/forms?categories[]=' . $category1->id . '&categories[]=' . $category2->id))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json
                    ->where('count', 2)
                    ->has('forms', 2)
                    ->has('forms.0', function (AssertableJson $json) {
                        $json
                            ->whereAllType([
                                'categories' => 'array',
                                'id' => 'integer',
                                'is_archived' => 'integer',
                                'is_draft' => 'integer',
                                'tags' => 'array',
                                'title' => 'string',
                            ]);
                    });
            });
    }

    public function test_search_by_everything()
    {
        $tag = Tag::factory()
            ->create(['app_id' => $this->quizAppId]);
        $category = ContentCategory::factory()
            ->create([
                'app_id' => $this->quizAppId,
                'type' => ContentCategory::TYPE_FORMS,
            ]);

        $form = Form::factory()
            ->create(['app_id' => $this->quizAppId]);
        $form->categories()->syncWithPivotValues([$category->id], [
            'type' => ContentCategory::TYPE_FORMS,
        ]);
        $form->tags()->sync([$tag->id]);

        $this->json('GET', $this->backendAPIUrl('/forms?categories[]=' . $category->id . '&tags[]=' . $tag->id . '&search=' . $form->title))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) use ($form) {
                $json
                    ->where('count', 1)
                    ->has('forms', 1)
                    ->has('forms.0', function (AssertableJson $json) use ($form) {
                        $json
                            ->whereAll([
                                'title' => $form->title,
                            ])
                            ->whereAllType([
                                'categories' => 'array',
                                'id' => 'integer',
                                'is_archived' => 'integer',
                                'is_draft' => 'integer',
                                'tags' => 'array',
                                'title' => 'string',
                            ]);
                    });
            });
    }
}
