<?php

namespace App\Console\Commands;

use App\Models\Comments\Comment;
use App\Models\Comments\CommentReport;
use App\Models\User;
use Faker\Factory;
use Illuminate\Console\Command;

class GenerateCommentReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commentreports:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It generates comment reports.';

    private $users;

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
        $this->line('It is generating comment reports.');

        $this->users = User::get()->groupBy('app_id');

        $comments = Comment::get();
        $bar = $this->output->createProgressBar(floor($comments->count() / 100));

        $i = 0;
        foreach ($comments as $comment) {
            if($i % 100 === 0) {
                $randomUser = $this->users->get($comment->app_id)->random();
                $faker = Factory::create();

                $reason = collect([
                    CommentReport::REASON_MISC,
                    CommentReport::REASON_OFFENSIVE,
                    CommentReport::REASON_ADVERTISEMENT,
                    CommentReport::REASON_PERSONAL_RIGHTS,
                ])->random();

                $commentReport = new CommentReport();
                $commentReport->comment_id = $comment->id;
                $commentReport->reporter_id = $randomUser->id;
                $commentReport->status = CommentReport::STATUS_REPORTED;
                $commentReport->reason = $reason;
                $commentReport->reason_explanation = $faker->text();
                $commentReport->save();

                $bar->advance();
            }
            $i++;
        }

        $bar->finish();
        $this->line('');
        $this->line('Comment reports are generated.');

        return 0;
    }
}
