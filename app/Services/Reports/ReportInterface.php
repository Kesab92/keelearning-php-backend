<?php

namespace App\Services\Reports;

interface ReportInterface
{
    public function prepareReport():void;
    public function getHeaders():array;
    public function getData():array;
}
