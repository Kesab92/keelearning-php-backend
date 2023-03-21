<?php

namespace App\Services\Reports;

use App\Models\User;
use Illuminate\Support\Collection;

abstract class Report
{
    const TYPE_USER_REPORT = 'users';

    /**
     * @var array headers
     */
    protected array $headers = [];

    /**
     * @var array Contains data for a report
     */
    protected array $data = [];

    /**
     * @var User
     */
    protected User $user;
    /**
     * @var Collection
     */
    protected Collection $settings;

    public function __construct(User $user, Collection $settings)
    {
        $this->user = $user;
        $this->settings = $settings;
    }

    /**
     * Returns data
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Returns headers
     *
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Checks permissions
     * @return bool
     */
    protected function hasPermissions(): bool
    {
        return true;
    }

    /**
     * Checks if report contains all given settings
     * @param array $columnSettings
     * @return bool
     */
    protected function hasNecessarySettings(array $columnSettings):bool
    {
        foreach ($columnSettings as $columnSetting) {
            if(!$this->settings->contains($columnSetting)) {
                return false;
            }
        }
        return true;
    }
}
