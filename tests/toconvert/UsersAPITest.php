<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class UsersAPITest extends TestCase
{
    public function testCanSearchUsers()
    {
        // Create a unique user
        $user = \App\Models\User::factory()->create([
            'app_id' => 1,
        ]);
        $this->setAPIUser($user->id);

        $user2 = \App\Models\User::factory()->create([
                'app_id' => 1,
        ]);

        // Check we can find a user
        $this->get('/api/v1/users/search?q='.urlencode($user2->username))->seeJson(['id'=>$user2->id]);

        // Check we can't find a user with an exclusive tag
        $tag = \App\Models\Tag::factory()->create([
                'app_id' => 1,
                'exclusive' => 1,
        ]);

        $user2->tags()->sync([$tag->id]);

        $this->get('/api/v1/users/search?q='.urlencode($user2->username))
             ->dontSeeJson(['id' => $user2->id]);

        // Check we can't find users with a different tag, and not overlapping categories
        $tag2 = \App\Models\Tag::factory()->create([
                'app_id'    => 1,
                'exclusive' => 0,
        ]);
        $tag3 = \App\Models\Tag::factory()->create([
                'app_id'    => 1,
                'exclusive' => 0,
        ]);

        $user2->tags()->sync([$tag2->id]);
        $user->tags()->sync([$tag3->id]);

        $category1 = \App\Models\Category::factory()->create([
                'app_id'    => 1,
        ]);
        $category2 = \App\Models\Category::factory()->create([
                'app_id' => 1,
        ]);
        $category1->tags()->sync([$tag2->id]);
        $category2->tags()->sync([$tag3->id]);
        $this->get('/api/v1/users/search?q='.urlencode($user2->username))
             ->dontSeeJson(['id' => $user2->id]);

        // Check we can find users with a different tag, and overlapping categories
        $category1->tags()
                  ->sync([$tag2->id, $tag3->id]);
        $category2->tags()
                  ->sync([]);
        $this->get('/api/v1/users/search?q='.urlencode($user2->username))
             ->seeJson(['id' => $user2->id]);
    }
}
