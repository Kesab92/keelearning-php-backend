<?php

namespace App\Console\Commands;

use App\Models\Competition;
use App\Stats\PlayerCorrectAnswersByCategoryBetweenDates;
use Illuminate\Console\Command;
use League\Csv\Writer;

class ExportCompetitionStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:challenge {competitionid : competition to export}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports competition stats';

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
     * @return mixed
     */
    public function handle()
    {
        $competitionId = $this->argument('competitionid');
        $competition = Competition::find($competitionId);
        $this->line('Exporting competition with title "'.$competition->title.'" for App '.$competition->app->name);
        $export = [];
        $header = ['Benutzername', 'Email', 'Richtige Antworten'];
        foreach ($competition->tags as $tag) {
            $header[] = $tag->label;
        }

        $export[] = $header;

        $this->line('Fetching member data');
        $members = $competition->members();
        $bar = $this->output->createProgressBar($members->count());
        $members->map(function ($user) use ($competition, $bar) {
            $user->stats = array_merge(['answersCorrect' => 0]);

            if ($competition->hasStartDate()) {
                if ($competition->category_id === null) {
                    $user->stats = [
                        'answersCorrect' => (new PlayerCorrectAnswersByCategoryBetweenDates($user->id, $competition->category_id, $competition->start_at, $competition->getEndDate()))->fetch(),
                    ];
                } else {
                    $user->stats = [
                        'answersCorrect' => (new PlayerCorrectAnswersByCategoryBetweenDates($user->id, $competition->category_id, $competition->start_at, $competition->getEndDate()))->fetch(),
                    ];
                }
            }
            $bar->advance();
        });
        $bar->finish();

        $this->line('Creating csv data');
        $members = $members->sortByDesc(function ($a) {
            return $a->stats['answersCorrect'];
        });

        foreach ($members as $member) {
            $entry = [$member->username, $member->email, $member->stats['answersCorrect']];
            $usersTags = $member->tags()->pluck('tags.id');
            foreach ($competition->tags as $tag) {
                if ($usersTags->contains($tag->id)) {
                    $entry[] = 1;
                } else {
                    $entry[] = 0;
                }
            }
            $export[] = $entry;
        }
        $this->line('');

        $filename = 'competition_export_'.$competition->id.'_'.time().'.csv';
        $filepath = storage_path('export/'.$filename);

        $writer = Writer::createFromPath($filepath, 'w+');
        $writer->insertAll($export);

        $this->info('Export saved to '.$filepath);
    }
}
