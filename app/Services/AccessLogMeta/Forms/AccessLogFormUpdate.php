<?php


namespace App\Services\AccessLogMeta\Forms;

use App\Models\ContentCategories\ContentCategory;
use App\Services\AccessLogMeta\AccessLogDifferences;
use App\Services\AccessLogMeta\AccessLogMeta;
use App\Traits\GetsAccessLogChanges;

class AccessLogFormUpdate implements AccessLogMeta, AccessLogDifferences
{
    use GetsAccessLogChanges;

    /**
     * @var array
     */
    protected array $differences = [];
    protected string $selectedLanguage = '';
    protected int $formId;

    public function __construct(array $oldForm, array $newForm, string $selectedLanguage)
    {
        $this->differences = $this->getDifferences($oldForm, $newForm) ?? [];
        $this->selectedLanguage = $selectedLanguage;
        $this->formId = $newForm['id'];

        foreach ($this->differences as $key => $difference) {
            if($key === 'categories') {
                $this->differences[$key] = ContentCategory
                    ::whereIn('id', $difference)
                    ->get()
                    ->pluck('name');
            }
        }
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'formId' => $this->formId,
            'selectedLanguage' => $this->selectedLanguage,
            'differences' => $this->differences,
        ];
    }

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta)
    {
        return view('access-logs.types.forms.update', [
            'meta' => $meta
        ]);
    }
}
