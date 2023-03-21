<?php

namespace App\Console\Commands;

use App\Models\AppProfile;
use App\Models\AppProfileSetting;
use App\Models\AppSetting;
use App\Models\IndexCard;
use Illuminate\Console\Command;

class IndexCardUsingStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stats:usingindexcards';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Shows stats of index card using';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $appSettings = AppSetting::where('key', 'module_index_cards')
            ->with('app')
            ->get();

        $output = [];

        foreach ($appSettings as $appSetting) {
            $appProfileIds = AppProfile::where('app_id', $appSetting->app_id)
                ->pluck('id');

            $profilesWithActiveIndexCards = AppProfileSetting::whereIn('app_profile_id', $appProfileIds)
                ->where('key', 'module_indexcards')
                ->where('value', 1)
                ->count();

            $indexCardCount = IndexCard::where('app_id', $appSetting->app_id)
                ->count();

            $standardIndexCardCount = IndexCard::where('app_id', $appSetting->app_id)
                ->where('type', IndexCard::TYPE_STANDARD)
                ->count();

            $imageMapIndexCardCount = IndexCard::where('app_id', $appSetting->app_id)
                ->where('type', IndexCard::TYPE_IMAGEMAP)
                ->count();

            $output[] = [
                'app_id' => $appSetting->app_id,
                'app_name' => $appSetting->app->name,
                'module_index_cards' => $appSetting->value,
                'profiles_contain_active_index_cards' => $profilesWithActiveIndexCards > 0 ? '1' : '0',
                'index_card_count' => $indexCardCount,
                'standard_index_card_count' => $standardIndexCardCount,
                'image_map_index_card_count' => $imageMapIndexCardCount,
            ];
        }

        $this->table([
            'App id',
            'App name',
            'Active index cards',
            'At least one profile with active index cards',
            'Index card count',
            'Standard index card count',
            'Image map index card count',
        ],
            $output
        );
        return 0;
    }
}
