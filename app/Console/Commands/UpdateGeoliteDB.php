<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PharData;

class UpdateGeoliteDB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:geolite';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloads the GeoLite2 Country IP Database';

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
        $dbfile = storage_path('app/GeoLite2-Country.mmdb');
        $this->line('Downloading GeoLite2-Country.tar.gz');
        $dlfile = storage_path('app/GeoLite2-Country.tar.gz');
        $downloadURL = 'https://download.maxmind.com/app/geoip_download?edition_id=GeoLite2-Country&license_key=3HW39zUUAgoILSzD&suffix=tar.gz';
        file_put_contents($dlfile, fopen($downloadURL, 'r'));

        $this->line('Extracting GeoLite2-Country.tar.gz');
        (new PharData($dlfile))->extractTo(storage_path('app'));
        unlink($dlfile);

        $this->line('Replacing old DB file with new one');
        if (file_exists($dbfile)) {
            unlink($dbfile);
        }
        $folder = glob(storage_path('app/GeoLite2-Country*'))[0];
        rename($folder.'/GeoLite2-Country.mmdb', $dbfile);
        array_map('unlink', glob($folder.'/*.*'));
        rmdir($folder);

        $this->info('All done!');
    }
}
