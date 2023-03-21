<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class GroupAPITest extends TestCase
{
    use DatabaseTransactions;

    public function testGroupCreation()
    {

        // Create a unique user
        $user = \App\Models\User::factory()->create();

        $this->setAPIUser($user->id);

        // We are in no group initially
        $this->get('/api/v1/groups')
             ->seeJson([]);

        $groupName = 'Group '.time();

        // Check if we are a member of the newly created group
        $group = $this->call('POST', '/api/v1/groups', [
            'name' => $groupName,
        ])->getData();

        $this->assertEquals($group->name, $groupName);

        $user2 = \App\Models\User::factory()->create();

        // Check if we can add members
        $this->post('/api/v1/groups/'.$group->id.'/members', [
            'user_id' => $user2->id,
        ])->assertResponseOk();

        // Check if the new user is in the group
        $this->setAPIUser($user2->id);
        $this->get('/api/v1/groups')
             ->seeJson([
                 [
                     'id' => $group->id,
                     'name' => $group->name,
                 ],
             ]);
    }
}
