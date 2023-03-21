<?php

namespace App\Services;

use App\Models\App;
use App\Models\CertificateTemplate;
use App\Models\Courses\CourseContent;
use App\Models\Courses\CourseContentAttempt;
use App\Models\Courses\CourseParticipation;
use App\Models\User;
use Carbon\Carbon;

class CourseCertificateRenderer extends CertificateRenderer
{
    /**
     * @var CourseParticipation
     */
    private CourseParticipation $courseParticipation;

    /**
     * @var CourseContent
     */
    private CourseContent $content;
    /**
     * @var CourseContentAttempt|null
     */
    private $attempt;
    /**
     * @var null
     */
    private $_forceLang;

    public function __construct(CourseContent $content, CourseParticipation $courseParticipation, $attempt = null, $forceLang = null)
    {
        $this->courseParticipation = $courseParticipation;
        $this->content = $content;
        $this->attempt = $attempt;
        $this->_forceLang = $forceLang;
    }

    public function getCertificate()
    {
        if ($this->content->type !== CourseContent::TYPE_CERTIFICATE || ! $this->content->foreign_id) {
            return null;
        }
        $certificate = CertificateTemplate::find($this->content->foreign_id);

        if ($this->_forceLang) {
            $certificate->setAppId($this->content->chapter->course->app_id)->setLanguage($this->_forceLang);
        }

        return $certificate;
    }

    /**
     * Returns some of the replacement values
     * Others are defined in the CertificateRenderer.
     *
     * @return array
     */
    public function getReplacementValues() : array
    {
        $passed_date = Carbon::now();
        if ($this->attempt) {
            $passed_date = $this->attempt->finished_at;
        }

        return [

            'username'                      => $this->courseParticipation->user->username,
            'firstname'                     => $this->courseParticipation->user->firstname,
            'lastname'                      => $this->courseParticipation->user->lastname,
            'realname_or_username'          => $this->courseParticipation->user->getFullName(),
            'course_name'                   => $this->courseParticipation->course->title,
            'course_start_date'             => $this->courseParticipation->course->available_from ? $this->courseParticipation->course->available_from->format('d.m.Y') : '',
            'course_end_date'               => $this->courseParticipation->course->available_until ? $this->courseParticipation->course->available_until->format('d.m.Y') : '',
            'certificate_awarded_year'      => $passed_date->format('Y'),
            'passed_date'                   => $passed_date->format('d.m.Y'),
            'submission_id'                 => $this->getSubmissionId(),
            'test_name'                     => $this->courseParticipation->course->title,
        ];
    }

    private function getSubmissionId()
    {
        return '#c'.$this->courseParticipation->id.'-'.$this->content->id;
    }

    public function getTitle() : string
    {
        return $this->content->setAppId($this->getApp()->id)->setLanguage($this->_forceLang ?: $this->getUser()->language)->title;
    }

    public function getApp() : App
    {
        return $this->courseParticipation->course->app;
    }

    public function getUser() : User
    {
        return $this->courseParticipation->user;
    }
}
