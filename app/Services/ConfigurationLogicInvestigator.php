<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ConfigurationLogicInvestigator
{
    /**
     * Select active users which cant play in a category.
     *
     * @param int $appId
     *
     * @return Collection collection of user IDs
     */
    public function usersCantPlayCategories(int $appId): Collection
    {
        // the stats server is better at this than we are
        $statsOptions = [
            'urgentnotifications' => [
                'hasAccessUsers' => true,
                'only' => ['usersWithoutPlayableCategories'],
            ],
        ];
        $statsResponse = (new StatsServerEngine)->getStats($statsOptions, $appId);
        return collect($statsResponse['urgentnotifications']['usersWithoutPlayableCategories'])
            ->map(function($user) {
                return $user['id'];
            });
    }
    /**
     * Select active appointments that don't have any users.
     *
     * @param int $appId
     *
     * @return Collection collection of appointments ids
     */
    public function activeAppointmentsWithoutParticipants(int $appId): Collection
    {
        $statsOptions = [
            'urgentnotifications' => [
                'only' => ['appointmentsWithoutParticipant'],
            ],
        ];
        $statsResponse = (new StatsServerEngine)->getStats($statsOptions, $appId);
        return collect($statsResponse['urgentnotifications']['appointmentsWithoutParticipant']['appointments_without_participants'])
            ->map(function($appointments) {
                return $appointments['id'];
            });;

    }
}
