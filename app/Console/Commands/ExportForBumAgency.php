<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\StatsEngine;
use IDCT\Networking\Ssh\Credentials;
use IDCT\Networking\Ssh\SftpClient;
use Illuminate\Console\Command;
use League\Csv\Writer;

class ExportForBumAgency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:bum {appid : app to export}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Exports the CSV used by Buben und Mädchen agency';

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
        $this->appid = $this->argument('appid');

        $export = [];
        $export[] = ['rank', 'voNr', 'name', 'email', 'duelsWon'];

        $usersRaw = User::ofSameApp($this->appid)->get();
        $usersRanked = (new StatsEngine($this->appid))->getAPIPlayerList();

        $idx = 0;
        foreach ($usersRanked as $key => $user) {
            $uraw = $usersRaw->find($user['id']);
            if (! $uraw || ! isset($uraw['meta']) || ! isset($uraw['meta']['voNr'])) {
                continue;
            }
            $idx++;
            $export[] = [
                $idx,
                $uraw['meta']['voNr'],
                $user['name'],
                $user['email'],
                $user['gameWins'],
            ];
        }

        $filename = date('ymd').'_BSH_ranking_quiz.csv';
        $filepath = public_path().'/export/fuchsquiz/'.$filename;

        $writer = Writer::createFromPath($filepath, 'w+');
        $writer->setDelimiter("\t");
        $writer->insertAll($export);

        /*
        $sftpClient = new SftpClient();
        $sftpClient->setCredentials(Credentials::withPassword(env('BUM_SFTP_USER'), env('BUM_SFTP_PASS')));
        $sftpClient->connect(env('BUM_SFTP_HOST'));
        $sftpClient->upload($filepath, '/home/sftp/keeunit/' . $filename);
        $sftpClient->close();
        */
    }
}
