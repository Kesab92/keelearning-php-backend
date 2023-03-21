<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Competition;
use App\Models\IndexCard;
use App\Models\LearningMaterial;
use App\Models\LearningMaterialFolder;
use App\Models\LearningMaterialTranslation;
use App\Models\News;
use App\Models\QuestionAttachment;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Http\File;
use Intervention\Image\Facades\Image;
use Storage;
use Str;

class MigrateAssetsToStorage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:assets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate file assets to new storage api';

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
        $this->comment('Migrating categories');
        $categories = Category::where('cover_image', '!=', '')
            ->whereNotNull('cover_image')
            ->whereNull('cover_image_url')
            ->get();
        $bar = $this->output->createProgressBar($categories->count());
        foreach ($categories as $category) {
            $this->migrateAsset($category);
            $bar->advance();
        }
        $bar->finish();
        $this->line('');
        $this->comment('Migrated '.$categories->count().' categories');
        $this->info('You can now delete '.public_path().'/storage/categories_attachments');

        $this->comment('Migrating competitions');
        $competitions = Competition::where('cover_image', '!=', '')
            ->whereNotNull('cover_image')
            ->whereNull('cover_image_url')
            ->get();
        $bar = $this->output->createProgressBar($competitions->count());
        foreach ($competitions as $competition) {
            $this->migrateAsset($competition);
            $bar->advance();
        }
        $bar->finish();
        $this->line('');
        $this->comment('Migrated '.$competitions->count().' competitions');
        $this->info('You can now delete '.public_path().'/storage/competition_attachments');

        $this->comment('Migrating indexcards');
        $indexcards = IndexCard::where('cover_image', '!=', '')
            ->whereNotNull('cover_image')
            ->whereNull('cover_image_url')
            ->get();
        $bar = $this->output->createProgressBar($indexcards->count());
        foreach ($indexcards as $indexcard) {
            $this->migrateAsset($indexcard);
            $bar->advance();
        }
        $bar->finish();
        $this->line('');
        $this->comment('Migrated '.$indexcards->count().' indexcards');
        $this->info('You can now delete '.public_path().'/storage/indexcard_attachments');

        $this->comment('Migrating learning materials');
        $learningMaterials = LearningMaterial::where('cover_image', '!=', '')
            ->whereNotNull('cover_image')
            ->whereNull('cover_image_url')
            ->get();
        $bar = $this->output->createProgressBar($learningMaterials->count());
        foreach ($learningMaterials as $learningMaterial) {
            $this->migrateAsset($learningMaterial);
            $bar->advance();
        }
        $bar->finish();
        $this->line('');
        $this->comment('Migrated '.$learningMaterials->count().' learning materials');

        $this->comment('Migrating learning material folders');
        $learningMaterialFolders = LearningMaterialFolder::where('folder_icon', '!=', '')
            ->whereNotNull('folder_icon')
            ->whereNull('folder_icon_url')
            ->get();
        $bar = $this->output->createProgressBar($learningMaterialFolders->count());
        foreach ($learningMaterialFolders as $learningMaterialFolder) {
            $this->migrateAsset($learningMaterialFolder, 'folder_icon');
            $bar->advance();
        }
        $bar->finish();
        $this->line('');
        $this->comment('Migrated '.$learningMaterialFolders->count().' learning material folders');

        $this->comment('Migrating learning material translations');
        $learningMaterialTranslations = LearningMaterialTranslation::where('file', '!=', '')
            ->whereNotNull('file')
            ->whereNull('file_url')
            ->get();
        $bar = $this->output->createProgressBar($learningMaterialTranslations->count());
        foreach ($learningMaterialTranslations as $learningMaterialTranslation) {
            $this->migrateAsset($learningMaterialTranslation, 'file');
            $bar->advance();
        }
        $bar->finish();
        $this->line('');
        $this->comment('Migrated '.$learningMaterialTranslations->count().' learning material translations');
        $this->info('You can now delete '.public_path().'/storage/learning_material_attachments');

        $this->comment('Migrating news');
        $news = News::where('cover_image', '!=', '')
            ->whereNotNull('cover_image')
            ->whereNull('cover_image_url')
            ->get();
        $bar = $this->output->createProgressBar($news->count());
        foreach ($news as $newsEntry) {
            $this->migrateAsset($newsEntry);
            $bar->advance();
        }
        $bar->finish();
        $this->line('');
        $this->comment('Migrated '.$news->count().' news');
        $this->info('You can now delete '.public_path().'/storage/news_attachments');

        $this->comment('Migrating question attachments');
        $questionAttachments = QuestionAttachment::where('type', '!=', QuestionAttachment::ATTACHMENT_TYPE_YOUTUBE)
            ->where('attachment', '!=', '')
            ->whereNotNull('attachment')
            ->whereNull('attachment_url')
            ->get();
        $bar = $this->output->createProgressBar($questionAttachments->count());
        foreach ($questionAttachments as $questionAttachment) {
            $this->migrateAsset($questionAttachment, 'attachment');
            $bar->advance();
        }
        $bar->finish();
        $this->line('');
        $this->comment('Migrated '.$questionAttachments->count().' question attachments');
        $this->info('You can now delete '.public_path().'/storage/question_attachments');

        $this->line('Migrating user avatars');
        $users = User::all();
        $migratedAvatars = 0;
        $bar = $this->output->createProgressBar($users->count());
        foreach ($users as $user) {
            if ($this->migrateUserAvatar($user)) {
                $migratedAvatars += 1;
            }
            $bar->advance();
        }
        $bar->finish();
        $this->line('');
        $this->comment('Migrated '.$migratedAvatars.' old avatars');
        $this->info('You can now delete '.public_path().'/storage/avatars');
    }

    private function convertFilename(File $file)
    {
        $filename = pathinfo($file->path(), PATHINFO_FILENAME);
        // remove appended md5
        $filename = preg_replace('/-[a-f0-9]{32}$/', '', $filename);

        $fileExtension = '-'.$file->hashName();
        // truncate slug so filename has a max total length of 255 chars
        return substr(Str::slug($filename), 0, (255 - strlen($fileExtension))).$fileExtension;
    }

    private function migrateAsset($model, $asset = 'cover_image')
    {
        $assetUrl = $asset.'_url';

        if (substr($model->{$asset}, 0, 1) === '/') {
            $oldPath = public_path().$model->{$asset};
        } else {
            $oldPath = public_path().'/'.$model->{$asset};
        }
        if (! file_exists($oldPath)) {
            $model->{$asset} = null;
            $model->save();

            return;
        }

        $oldFile = new File($oldPath);
        $newPath = Storage::putFileAs('uploads', $oldFile, $this->convertFilename($oldFile));
        $model->{$asset} = $newPath;
        $model->{$assetUrl} = Storage::url($newPath);
        $model->save();
    }

    private function migrateUserAvatar(User $user)
    {
        $filename = public_path().'/storage/avatars/'.$user->id.'_500.jpg';
        if (! file_exists($filename)) {
            return false;
        }
        $tmpOutputFile = storage_path('/tmp/'.uniqid().'.jpg');
        $img = Image::make($filename);
        $img->fit(User::AVATAR_SIZE)->save($tmpOutputFile, 80);

        $path = Storage::putFile('uploads', new File($tmpOutputFile));
        $user->avatar = $path;
        $user->avatar_url = Storage::url($path);
        $user->save();

        unlink($tmpOutputFile);

        return true;
    }
}
