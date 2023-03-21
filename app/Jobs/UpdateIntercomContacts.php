<?php

namespace App\Jobs;

use App\Exceptions\Intercom\NotFetchedException;
use App\Models\User;
use Http\Client\Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Intercom\IntercomClient;

class UpdateIntercomContacts implements ShouldQueue
{
    use Dispatchable, Queueable;

    /**
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 2;

    private IntercomClient $client;

    private array $intercomContacts = [];
    private array $intercomCompanies = [];

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->client = new IntercomClient(
            env('INTERCOM_ACCESS_TOKEN'),
            null,
            ['Intercom-Version' => '2.5']
        );
        $this->fetchIntercomContacts();
        $this->fetchIntercomCompanies();

        $admins = User::admin()->with(['app', 'role'])->get();
        $adminsByEmail = $admins->groupBy('email');
        $adminIds = $admins->pluck('id');

        foreach ($adminsByEmail as $admins) {
            $newestAdminAccount = $admins->sortByDesc('created_at')->first();
            $isMainAdmin = $admins->contains(function ($admin) {
                return $admin->isMainAdmin();
            });

            $foundIntercomContact = $this->findIntercomContact($newestAdminAccount);

            if (!is_null($foundIntercomContact)) {
                try {
                    $contactUpdate = [
                        'email' => $newestAdminAccount->email,
                        'custom_attributes' => [
                            'keelearning user ID' => $newestAdminAccount->id,
                            'Hauptadmin' => $isMainAdmin,
                        ],
                    ];
                    $this->client->contacts->update($foundIntercomContact->id, $contactUpdate);
                } catch (\Exception $e) {
                    \Sentry::captureException($e);
                    \Sentry::captureMessage('Contact can\'t be added. User ID: ' . $newestAdminAccount->id);
                }
            } else {
                $intercomCompany = $this->findIntercomCompany($newestAdminAccount->app_id);
                if (is_null($intercomCompany)) {
                    $intercomCompany = $this->client->companies->create([
                        'company_id' => $newestAdminAccount->app_id,
                        'name' => $newestAdminAccount->app->getDefaultAppProfile()->getValue('app_name')
                    ]);
                }

                try {
                    $createdIntercomContact = $this->client->contacts->create([
                        'role' => 'user',
                        'email' => $newestAdminAccount->email,
                        'name' => $newestAdminAccount->getFullName(),
                        'custom_attributes' => [
                            'keelearning user ID' => $newestAdminAccount->id,
                            'Hauptadmin' => $isMainAdmin,
                        ],
                    ]);
                    $this->client->companies->attachContact($createdIntercomContact->id, $intercomCompany->id);
                } catch (\Exception $e) {
                    \Sentry::captureException($e);
                    \Sentry::captureMessage('Contact can\'t be added. User ID: ' . $newestAdminAccount->id);
                }
            }
        }

        foreach ($this->intercomContacts as $contact) {
            if (empty($contact->custom_attributes->{'keelearning user ID'})) {
                continue;
            }

            $contactExistsInDB = $adminIds->contains($contact->custom_attributes->{'keelearning user ID'});

            // if the Intercom contact doesn't exist in the DB anymore, set as a not main admin
            if (!$contactExistsInDB) {
                try {
                    $this->client->contacts->update($contact->id, [
                        'custom_attributes' => [
                            'Hauptadmin' => false,
                        ]
                    ]);
                } catch (\Exception $e) {
                    \Sentry::captureException($e);
                    \Sentry::captureMessage('Contact can\'t be added. User ID: ' . $contact->custom_attributes->{'keelearning user ID'});
                }
            }
        }
    }

    /**
     * @throws Exception
     * @throws NotFetchedException
     */
    private function fetchIntercomContacts()
    {
        $contacts = [];
        $response = $this->client->contacts->getContacts(['per_page' => 150]);

        $contacts = array_merge($contacts, $response->data);

        while (isset($response->pages->next)) {
            $response = $this->client->nextCursorPage('contacts', $response->pages->next->starting_after);
            $contacts = array_merge($contacts, $response->data);
        }

        if (empty($contacts)) {
            throw new NotFetchedException('Contacts don\'t be fetched');
        }

        $this->intercomContacts = $contacts;
    }


    /**
     * @throws NotFetchedException
     * @throws Exception
     */
    private function fetchIntercomCompanies()
    {
        $companies = [];
        $response = $this->client->companies->getCompanies([]);

        $companies = array_merge($companies, $response->data);

        while (isset($response->pages->next)) {
            $response = $this->client->nextPage($response->pages);
            $companies = array_merge($companies, $response->data);
        }

        if (empty($companies)) {
            throw new NotFetchedException('Contacts don\'t be fetched');
        }

        $this->intercomCompanies = $companies;
    }

    private function findIntercomContact(User $admin): ?\stdClass
    {
        // Firstly, it looks for a contact by user ID
        // because Intercom might have multiple accounts with the same email
        foreach ($this->intercomContacts as $contact) {
            if (empty($contact->custom_attributes->{'keelearning user ID'})) {
                continue;
            }
            if ($contact->custom_attributes->{'keelearning user ID'} == $admin->id) {
                return $contact;
            }
        }
        foreach ($this->intercomContacts as $contact) {
            if ($contact->email === $admin->email) {
                return $contact;
            }
        }
        return null;
    }

    private function findIntercomCompany(int $companyId): ?\stdClass
    {
        foreach ($this->intercomCompanies as $company) {
            if ($company->company_id == $companyId) {
                return $company;
            }
        }
        return null;
    }
}
