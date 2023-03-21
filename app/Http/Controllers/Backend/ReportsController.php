<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackendApi\Report\ReportExportRequest;
use App\Services\AppSettings;
use App\Services\Reports\Report;
use App\Services\Reports\ReportExport;
use App\Services\Reports\ReportInterface;
use App\Services\Reports\UserReport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ReportsController extends Controller
{

    private ReportInterface $report;

    public function export(string $reportType, ReportExportRequest $request)
    {
        $settings = $request->input('settings', []);
        $tags = $request->input('tags', []);
        $year = $request->input('year');
        if($year) {
            Carbon::setTestNow(Carbon::createFromDate($year, 12, 31));
        }
        switch($reportType) {
            case Report::TYPE_USER_REPORT:
                $appSettings = app(AppSettings::class);
                $this->report = new UserReport(Auth::user(), collect($settings), $appSettings, collect($tags));
                break;
            default:
                abort(404);
        }
        $this->report->prepareReport();

        return Excel::download(new ReportExport($this->report->getHeaders(), $this->report->getData()), 'user-statistics-' . date('Y-m-d') . '.xlsx');
    }
}
