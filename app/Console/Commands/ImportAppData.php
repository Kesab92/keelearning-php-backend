<?php

namespace App\Console\Commands;

use App\Models\AnalyticsEvent;
use App\Models\Category;
use App\Models\Game;
use App\Models\GamePoint;
use App\Models\GameQuestion;
use App\Models\GameQuestionAnswer;
use App\Models\GameRound;
use App\Models\QuizTeam;
use App\Models\QuizTeamMember;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionAttachment;
use App\Models\SuggestedQuestion;
use App\Models\SuggestedQuestionAnswer;
use App\Models\User;
use Illuminate\Console\Command;

class ImportAppData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:appdata {newappid : The id of the app the content should be assigned to} {oldappid : The id of the app in the old database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports compatible Quizapp data from a different database (mysql_import).';

    private array $newQuizTeamIds;
    private array $newCategoryIds;
    private array $newQuestionIds;
    private array $newAnswerIds;

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
        $this->newappid = $this->argument('newappid');
        $this->oldappid = $this->argument('oldappid');

        // IMPORT USERS
        // no dependencies
        $this->line('Importing Users');
        $data = $this->getUsers();
        $this->newUserIds = [];

        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $row) {
            $new = $this->addUser($row);
            $this->newUserIds[$row['id']] = $new->id;
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        // IMPORT QUIZ TEAMS
        // depending on USERS
        $this->line('Importing Quiz Teams');
        $data = $this->getQuizTeams();
        $this->newQuizTeamIds = [];

        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $row) {
            $new = $this->addQuizTeam($row);
            if ($new) {
                $this->newQuizTeamIds[$row['id']] = $new->id;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        // IMPORT QUIZ TEAM MEMBERS
        // depending on USERS, QUIZ TEAMS
        $this->line('Importing Quiz Team Members');
        $data = $this->getQuizTeamMembers();

        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $row) {
            $this->addQuizTeamMember($row);
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        // IMPORT CATEGORIES
        // no dependencies
        $this->line('Importing Categories');
        $data = $this->getCategories();
        $this->newCategoryIds = [];

        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $row) {
            $new = $this->addCategory($row);
            $this->newCategoryIds[$row['id']] = $new->id;
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        // IMPORT QUESTIONS
        // depending on CATEGORIES
        $this->line('Importing Questions & Answers');
        $data = $this->getQuestionsWithAnswers();
        $this->newQuestionIds = [];
        $this->newAnswerIds = [];

        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $row) {
            $new = $this->addQuestionWithAnswers($row);
            if ($new) {
                $this->newQuestionIds[$row['id']] = $new->id;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        // IMPORT SUGGESTED QUESTIONS
        // depending on CATEGORIES, USERS
        $this->line('Importing Suggested Questions & Answers');
        $data = $this->getSuggestedQuestionsWithAnswers();

        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $row) {
            $new = $this->addSuggestedQuestionWithAnswers($row);
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        // IMPORT GAME POINTS
        // depending on USERS
        $this->line('Importing Game Points');
        $data = $this->getGamePoints();

        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $row) {
            $new = $this->addGamePoint($row);
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        // IMPORT GAMES
        // depending on USERS, CATEGORIES, QUESTIONS
        $this->line('Importing Games');
        $data = $this->getGames();

        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $row) {
            $new = $this->addGame($row);
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        $this->info('All done!');
    }

    private function getUsers()
    {
        return (new User)->setConnection('mysql_import')->where('app_id', $this->oldappid)->get()
            ->makeVisible(['password', 'remember_token', 'updated_at', 'tos_accepted', 'is_admin', 'gcm_browser_auth', 'gcm_browser_p256dh', 'fcm_id', 'gcm_id_browser', 'created_at', 'apns_id', 'active', 'deleted_at'])
            ->toArray();
    }

    private function addUser($row)
    {
        $user = new User();
        foreach ($row as $key => $value) {
            if ($key != 'id') {
                $user->$key = $value;
            }
        }
        if ($user->email == 'TemporÃ¤rer Account') {
            $user->email = 'tmp'.uniqid().'@sopamo.de';
        }
        $user->app_id = $this->newappid;
        $user->save();

        AnalyticsEvent::log($user, AnalyticsEvent::TYPE_USER_CREATED);

        return $user;
    }

    private function getQuizTeams()
    {
        return (new QuizTeam)->setConnection('mysql_import')->where('app_id', $this->oldappid)->get()->toArray();
    }

    private function addQuizTeam($row)
    {
        if (! isset($this->newUserIds[$row['owner_id']])) {
            return null;
        }
        $quizTeam = new QuizTeam();
        foreach ($row as $key => $value) {
            if ($key != 'id') {
                $quizTeam->$key = $value;
            }
        }
        $quizTeam->app_id = $this->newappid;
        $quizTeam->owner_id = $this->newUserIds[$row['owner_id']];
        $quizTeam->save();

        return $quizTeam;
    }

    private function getQuizTeamMembers()
    {
        return (new QuizTeamMember)->setConnection('mysql_import')->whereIn('quiz_team_id', array_keys($this->newQuizTeamIds))->get()->toArray();
    }

    private function addQuizTeamMember($row)
    {
        if (! isset($this->newQuizTeamIds[$row['quiz_team_id']]) || ! isset($this->newUserIds[$row['user_id']])) {
            return null;
        }
        $quizTeamMember = new QuizTeamMember();
        $quizTeamMember->quiz_team_id = $this->newQuizTeamIds[$row['quiz_team_id']];
        $quizTeamMember->user_id = $this->newUserIds[$row['user_id']];
        $quizTeamMember->save();

        return $quizTeamMember;
    }

    private function getCategories()
    {
        return (new Category)->setConnection('mysql_import')->where('app_id', $this->oldappid)->get()->toArray();
    }

    private function addCategory($row)
    {
        $category = new Category();
        $category->setLanguage('de');
        foreach ($row as $key => $value) {
            if ($key != 'id') {
                $category->$key = $value;
            }
        }
        $category->app_id = $this->newappid;
        $category->save();

        return $category;
    }

    private function getQuestionsWithAnswers()
    {
        // for models with relationships we need to fetch the object instead of an array
        return (new Question)->setConnection('mysql_import')->where('app_id', $this->oldappid)->get();
    }

    private function addQuestionWithAnswers($row)
    {
        if (! isset($this->newCategoryIds[$row->category_id])) {
            return null;
        }
        $question = new Question();
        $question->setLanguage('de');
        foreach (['title', 'visible', 'created_at', 'updated_at', 'type', 'answertime', 'latex'] as $key) {
            $question->$key = $row->$key;
        }
        $question->category_id = $this->newCategoryIds[$row->category_id];
        $question->app_id = $this->newappid;
        $question->save();
        foreach ($row->questionAnswers as $oldAnswer) {
            $answer = new QuestionAnswer();
            $answer->setLanguage('de');
            foreach (['content', 'correct', 'feedback', 'created_at', 'updated_at'] as $key) {
                $answer->$key = $oldAnswer->$key;
            }
            $answer->question_id = $question->id;
            $answer->save();
            $this->newAnswerIds[$oldAnswer->id] = $answer->id;
        }
        foreach ($row->attachments as $oldAttachment) {
            $attachment = new QuestionAttachment();
            foreach (['type', 'url', 'created_at', 'updated_at'] as $key) {
                $attachment->$key = $oldAttachment->$key;
            }
            $attachment->question_id = $question->id;
            $attachment->save();
        }

        return $question;
    }

    private function getSuggestedQuestionsWithAnswers()
    {
        // for models with relationships we need to fetch the object instead of an array
        return (new SuggestedQuestion)->setConnection('mysql_import')->where('app_id', $this->oldappid)->get();
    }

    private function addSuggestedQuestionWithAnswers($row)
    {
        if (! isset($this->newCategoryIds[$row['category_id']]) || ! isset($this->newUserIds[$row['user_id']])) {
            return null;
        }
        $question = new SuggestedQuestion();
        foreach (['title', 'user_id', 'category_id', 'created_at', 'updated_at'] as $key) {
            $question->$key = $row->$key;
        }
        $question->app_id = $this->newappid;
        $question->save();
        foreach ($row->questionAnswers as $oldAnswer) {
            $answer = new SuggestedQuestionAnswer();
            foreach (['content', 'correct', 'created_at', 'updated_at'] as $key) {
                $answer->$key = $oldAnswer->$key;
            }
            $answer->suggested_question_id = $question->id;
            $answer->save();
            $this->newAnswerIds[$oldAnswer->id] = $answer->id;
        }

        return $question;
    }

    private function getGamePoints()
    {
        return (new GamePoint)->setConnection('mysql_import')->whereIn('user_id', array_keys($this->newUserIds))->get()->toArray();
    }

    private function addGamePoint($row)
    {
        if (! isset($this->newUserIds[$row['user_id']])) {
            return null;
        }
        $gamePoint = new GamePoint();
        foreach ($row as $key => $value) {
            if ($key != 'id') {
                $gamePoint->$key = $value;
            }
        }
        $gamePoint->user_id = $this->newUserIds[$row['user_id']];
        $gamePoint->save();

        return $gamePoint;
    }

    private function getGames()
    {
        // for models with relationships we need to fetch the object instead of an array
        return (new Game)->setConnection('mysql_import')->where('app_id', $this->oldappid)->get();
    }

    private function addGame($row)
    {
        if (! isset($this->newUserIds[$row->player1_id]) || ! isset($this->newUserIds[$row->player2_id])) {
            return null;
        }
        $game = new Game();
        $game->app_id = $this->newappid;
        foreach (['player1_joker_available', 'player2_joker_available', 'status', 'created_at', 'updated_at'] as $key) {
            $game->$key = $row->$key;
        }
        $game->player1_id = $this->newUserIds[$row->player1_id];
        $game->player2_id = $this->newUserIds[$row->player2_id];
        $game->save();

        foreach ($row->gameRounds as $oldRound) {
            if (! $oldRound->category_id) {
                continue;
            }
            $round = new GameRound();
            $round->game_id = $game->id;
            $round->category_id = $this->newCategoryIds[$oldRound->category_id];
            $round->created_at = $oldRound->created_at;
            $round->updated_at = $oldRound->updated_at;
            $round->save();

            foreach ($round->gameQuestions as $oldGameQuestion) {
                $gameQuestion = new GameQuestion();
                $gameQuestion->game_round_id = $round->id;
                $gameQuestion->question_id = $this->newQuestionIds[$oldGameQuestion->question_id];
                $gameQuestion->created_at = $oldGameQuestion->created_at;
                $gameQuestion->updated_at = $oldGameQuestion->updated_at;
                $gameQuestion->save();

                foreach ($oldGameQuestion->gameQuestionAnswers as $oldGameQuestionAnswer) {
                    $gameQuestionAnswer = new GameQuestionAnswer();
                    $gameQuestionAnswer->game_question_id = $gameQuestion->id;
                    $gameQuestionAnswer->user_id = $this->newUserIds[$oldGameQuestionAnswer->user_id];
                    $gameQuestionAnswer->question_answer_id = $this->newAnswerIds[$oldGameQuestionAnswer->question_answer_id];
                    $gameQuestionAnswer->created_at = $oldGameQuestionAnswer->created_at;
                    $gameQuestionAnswer->updated_at = $oldGameQuestionAnswer->updated_at;
                    $gameQuestionAnswer->result = $oldGameQuestionAnswer->result;
                    $gameQuestionAnswer->multiple = $oldGameQuestionAnswer->getRawOriginal('multiple');
                    $gameQuestionAnswer->save();
                }
            }
        }

        return $game;
    }
}
