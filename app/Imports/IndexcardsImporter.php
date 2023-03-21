<?php

namespace App\Imports;

use App\Models\App;
use App\Models\Import;
use App\Models\IndexCard;
use DB;

class IndexcardsImporter extends Importer
{
    protected $necessaryHeaders = [
        'front',
        'back',
    ];

    /**
     * @param $additionalData
     * @param $indexcards
     * @param $headers
     */
    protected function importData($additionalData, $headers, $indexcards)
    {
        $category = $additionalData['category'];
        $this->import = Import::findOrFail($additionalData['importId']);

        $idx = 0;
        $indexcardsCount = count($indexcards);

        DB::transaction(function () use ($indexcards, $headers, $category, $indexcardsCount, &$idx) {
            foreach ($indexcards as $cardData) {
                $indexcard = new IndexCard();
                $indexcard->app_id = $category->app_id;
                $indexcard->back = $this->getDataPoint($cardData, $headers, 'back');
                $indexcard->category_id = $category->id;
                $indexcard->front = $this->getDataPoint($cardData, $headers, 'front');
                $indexcard->type = IndexCard::TYPE_STANDARD;
                $indexcard->save();

                $this->setStepProgress($idx++ / $indexcardsCount);
            }

            $this->stepDone();
        });

        $this->importDone();
    }
}
