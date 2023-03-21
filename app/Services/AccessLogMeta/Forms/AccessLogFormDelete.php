<?php


namespace App\Services\AccessLogMeta\Forms;

use App\Models\Forms\Form;
use App\Services\AccessLogMeta\AccessLogMeta;

class AccessLogFormDelete implements AccessLogMeta
{
    /**
     * Deleted object
     * @var null
     */
    protected $form = null;

    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        return [
            'form_id' => $this->form->id,
            'form_title' => $this->form->title,
        ];
    }

    /**
     * @param $meta
     * @return string
     */
    public static function displayMeta($meta)
    {
        return view('access-logs.types.forms.delete', [
            'meta' => $meta
        ]);
    }
}
