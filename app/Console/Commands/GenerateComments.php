<?php

namespace App\Console\Commands;

use App\Models\Comments\Comment;
use App\Models\Competition;
use App\Models\Courses\Course;
use App\Models\LearningMaterial;
use App\Models\News;
use App\Models\User;
use App\Services\MorphTypes;
use Faker\Factory;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateComments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comments:generate {appId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'It generates fake comments for entries';

    protected $appId;

    protected $users;

    protected $limitForItem = 500;

    protected $answersLimit = 10;

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
        $this->appId = $this->argument('appId');
        $this->users = User::where('app_id', $this->appId)->get();

        $this->generateForNews();
        $this->generateForCourses();
        $this->generateForLearningMaterials();
        $this->generateForCompetitions();

        return 0;
    }

    /**
     * Generates comments for news
     */
    private function generateForNews() {
        $this->line('It is generating comments for news.');

        $news = News::where('app_id', $this->appId)->get();
        $bar = $this->output->createProgressBar($news->count());

        foreach($news as $newsEntry) {
            $this->generateComments($newsEntry->id, MorphTypes::TYPE_NEWS, $newsEntry->app_id);
            $bar->advance();
        }

        $bar->finish();
        $this->line('');
        $this->line('Comments for news are generated.');
    }

    /**
     * Generates comments for news
     */
    private function generateForCourses() {
        $this->line('It is generating comments for courses.');

        $courses = Course::where('app_id', $this->appId)->get();
        $bar = $this->output->createProgressBar($courses->count());

        foreach($courses as $course) {
            $this->generateComments($course->id, MorphTypes::TYPE_COURSE, $course->app_id);
            $bar->advance();
        }

        $bar->finish();
        $this->line('');
        $this->line('Comments for courses are generated.');
    }

    /**
     * Generates comments for learning materials
     */
    private function generateForLearningMaterials() {
        $this->line('It is generating comments for learning material.');

        $learningMaterials = LearningMaterial::whereHas('learningMaterialFolder', function($q){
            $q->where('app_id', $this->appId);
        })->get();
        $bar = $this->output->createProgressBar($learningMaterials->count());

        foreach($learningMaterials as $learningMaterial) {
            $this->generateComments($learningMaterial->id, MorphTypes::TYPE_LEARNINGMATERIAL, $learningMaterial->app_id);
            $bar->advance();
        }

        $bar->finish();
        $this->line('');
        $this->line('Comments for learning materials are generated.');
    }

    /**
     * Generates comments for competitions
     */
    private function generateForCompetitions() {
        $this->line('It is generating comments for competitions.');

        $competitions = Competition::where('app_id', $this->appId)->get();
        $bar = $this->output->createProgressBar($competitions->count());

        foreach($competitions as $competition) {
            $this->generateComments($competition->id, MorphTypes::TYPE_COMPETITION, $competition->app_id);
            $bar->advance();
        }

        $bar->finish();
        $this->line('');
        $this->line('Comments for competitions are generated.');
    }

    /**
     * Generates comments
     *
     * @param $foreignId
     * @param $foreignType
     * @param $appId
     */
    private function generateComments($foreignId, $foreignType, $appId) {
        $limit = rand(0, $this->limitForItem);

        for($i = 0; $i < $limit; $i++) {
            $randomUser = $this->users->random();
            $commentId = $this->store($foreignId, $foreignType, $randomUser->id, $appId);
            if(rand(0,5) === 3) {
                $this->generateAnswers($foreignId, $foreignType, $appId, $commentId);
            }
        }
    }

    /**
     * Generates answers for the comment
     *
     * @param $foreignId
     * @param $foreignType
     * @param $appId
     * @param $commentId
     */
    private function generateAnswers($foreignId, $foreignType, $appId, $commentId) {
        $limit = rand(0, $this->answersLimit);

        for ($i = 0; $i < $limit; $i++) {
            $randomUser = $this->users->random();
            $this->store($foreignId, $foreignType, $randomUser->id, $appId, $commentId);
        }
    }

    /**
     * Stores a comment
     *
     * @param $foreignId
     * @param $foreignType
     * @param $authorId
     * @param $appId
     * @param null $parentId
     * @return mixed
     */
    private function store($foreignId, $foreignType, $authorId, $appId, $parentId = null) {
        $faker = Factory::create();

        $comment = new Comment();
        $comment->body = $faker->text(400);
        $comment->app_id = $appId;
        $comment->author_id = $authorId;
        $comment->foreign_id = $foreignId;
        $comment->foreign_type = $foreignType;
        $comment->parent_id = $parentId;

        if(rand(0,10) === 3) {
            $comment->deleted_at = Carbon::now();
            if(rand(0,2) === 1) {
                $comment->deleted_by_id = $authorId;
            } else {
                $comment->deleted_by_id = $this->users->random();
            }
        }

        $comment->save();

        return $comment->id;
    }
}
