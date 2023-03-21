<?php

namespace App\Console\Commands;

use App\Models\News;
use Illuminate\Console\Command;

class MigrateNewsPublishedAt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:newspublishedat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sets the published_at of all news missing it to their created_at day, effectively publishing them immediately';

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
        $newsEntries = News::whereNull('published_at')->orWhere('published_at', '=', '0000-00-00 00:00:00')->get();
        $this->line('Migrating news published at');
        $bar = $this->output->createProgressBar($newsEntries->count());
        foreach ($newsEntries as $newsEntry) {
            $newsEntry->published_at = $newsEntry->created_at->setTime(0, 0, 0);
            $newsEntry->save();
            $bar->advance();
        }
        $bar->finish();
        $this->line('');
        $this->line('News published at migrated');
    }
}
