<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Models\User;
use Illuminate\Console\Command;

class FixImportAppData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:appdatafix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes: Imports compatible Quizapp data from a different database (mysql_import).';

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
        // IMPORT GAMES
        $this->line('Fixing Games');
        $data = $this->getGames();

        $bar = $this->output->createProgressBar(count($data));
        foreach ($data as $row) {
            $new = $this->fixGame($row);
            $bar->advance();
        }
        $bar->finish();
        $this->line('');

        $this->info('All done!');
    }

    private function getGames()
    {
        // for models with relationships we need to fetch the object instead of an array
        return (new Game)->where('app_id', 13)->get();
    }

    private function fixGame($row)
    {
        $row->player1_id = $this->getNewPlayerId($row->player1_id);
        $row->player2_id = $this->getNewPlayerId($row->player2_id);
        $row->save();

        return $row;
    }

    private function getNewPlayerId($oldPlayerId)
    {
        $oldPlayer = (new User)->setConnection('mysql_import')->find($oldPlayerId);
        $newPlayer = User::where('email', $oldPlayer->email)->where('password', $oldPlayer->password)->where('app_id', 13)->first();
        if (! $newPlayer) {
            $this->error('Cant find user '.$oldPlayer->email);
            throw new \Exception('cant find user');
        }

        return $newPlayer->id;
    }
}
