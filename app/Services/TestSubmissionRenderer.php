<?php

namespace App\Services;

use App\Models\App;
use App\Models\TestSubmission;
use App\Models\User;

class TestSubmissionRenderer extends CertificateRenderer
{
    /**
     * @var TestSubmission
     */
    private TestSubmission $testSubmission;
    private $_forceLang = null;


    public function __construct(TestSubmission $testSubmission, $forceLang = null)
    {
        $this->testSubmission = $testSubmission;
        $this->_forceLang = $forceLang;
    }

    public function getCertificate()
    {
        $certificate = $this->testSubmission->test->certificateTemplates()->first();
        if ($this->_forceLang) {
            $certificate->setAppId($this->testSubmission->test->app_id)->setLanguage($this->_forceLang);
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
        return [
            'username'              => $this->testSubmission->user->username,
            'firstname'             => $this->testSubmission->user->firstname,
            'lastname'              => $this->testSubmission->user->lastname,
            'realname_or_username'  => $this->testSubmission->user->getFullName(),
            'passed_date'           => $this->testSubmission->updated_at->format('d.m.Y'),
            'passed_percentage'     => $this->testSubmission->percentage(),
            'test_name'             => $this->testSubmission->test->name,
            'submission_id'         => '#'.$this->testSubmission->id,
        ];
    }

    public function getTitle() : string
    {
        return 'Zertifikat #'.$this->testSubmission->id;
    }

    public function getApp() : App
    {
        return $this->testSubmission->test->app;
    }

    public function getUser() : User
    {
        return $this->testSubmission->user;
    }
}
