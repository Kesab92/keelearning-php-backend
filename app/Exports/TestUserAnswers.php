<?php

namespace App\Exports;

use App\Models\Test;
use App\Models\TestSubmission;
use App\Models\User;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Events\AfterSheet;

class TestUserAnswers implements FromView, ShouldAutoSize, WithEvents, WithColumnWidths
{
    private TestSubmission $submission;
    private Test $test;
    private User $user;
    private $showEmails;
    private $showPersonalData;

    public function __construct(Test $test, User $user, TestSubmission $submission, $showPersonalData = false, $showEmails = false)
    {
        $this->submission = $submission;
        $this->showEmails = $showEmails;
        $this->showPersonalData = $showPersonalData;
        $this->test = $test;
        $this->user = $user;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 60,
            'B' => 60,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $worksheet = $event->getDelegate()->getDelegate();
                $worksheet->getStyle('A1:B' . $worksheet->getHighestRow())
                    ->getAlignment()
                    ->setWrapText(true);
            },
        ];
    }

    public function view(): View
    {
        return view('tests.user-answers-csv', $this->getData());
    }

    private function getData(): Array
    {
        return [
            'showEmails' => $this->showEmails,
            'showPersonalData' => $this->showPersonalData,
            'submission' => $this->submission,
            'test' => $this->test,
            'user' => $this->user,
        ];
    }
}
