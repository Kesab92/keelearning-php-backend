<?php

namespace App\Push\Deepstream;

define('SUCCESS_RESPONSE', 'SUCCESS');

/**
 * The deepstream PHP client running against the dsh/dsx
 * HTTP API.
 *
 * @author deepstreamHub GmbH <info@deepstreamhub.com>
 * @copyright (c) 2017, deepstreamHub GmbH
 */
class DeepstreamClient
{
    private $url;
    private $batchApiRequest = null;

    /**
     * Constructs the client.
     *
     * @param string $url HTTP(S) URL for a deepstream endpoint
     * @param mixed $authData any authentication information
     *
     * @public
     * @return void
     */
    public function __construct($url, $authData)
    {
        $this->url = $url;
        $this->authData = $authData;
    }

    /**
     * Initiates a set of batch operations. No actual request
     * will be sent until executeBatch is called.
     *
     * @public
     * @return void
     */
    public function startBatch()
    {
        $this->batchApiRequest = new ApiRequest($this->url, $this->authData);
    }

    /**
     * Executes a set of batch operations.
     *
     * @public
     * @return object result
     */
    public function executeBatch()
    {
        if ($this->hasBatch() === false) {
            trigger_error('no batch found, call startBatch first');
        }

        return $this->batchApiRequest->execute();
    }

    /**
     * Retrieves data for a single record.
     *
     * @param string $recordName
     *
     * @public
     * @return mixed response data
     */
    public function getRecord($recordName)
    {
        $apiRequest = $this->getApiRequest();
        $apiRequest->add([
            'topic' => 'record',
            'action' => 'read',
            'recordName' => $recordName,
        ]);

        if ($this->hasBatch()) {
            return true;
        }
        $result = $apiRequest->execute();
        if ($result->result === SUCCESS_RESPONSE) {
            return $result->body[0]->data;
        } else {
            return false;
        }
    }

    /**
     * Updates a records data. Can be called with a path
     * for partial updates.
     *
     * @param string recordName
     * @param string path optional path
     * @param mixed data
     *
     * @return bool
     */
    public function setRecord()
    {
        $apiRequest = $this->getApiRequest();
        $requestData = [
            'topic' => 'record',
            'action' => 'write',
            'recordName' => func_get_arg(0),
        ];

        if (func_num_args() === 2) {
            $requestData['data'] = func_get_arg(1);
        } elseif (func_num_args() === 3) {
            $requestData['path'] = func_get_arg(1);
            $requestData['data'] = func_get_arg(2);
        }

        $apiRequest->add($requestData);
        if ($this->hasBatch()) {
            return true;
        }

        return $apiRequest->execute()->result === SUCCESS_RESPONSE;
    }

    /**
     * Executes a Remote Procedure Call.
     *
     * @param string rpcName
     * @param mixed data optional
     *
     * @public
     * @return mixed response data
     */
    public function makeRpc()
    {
        $apiRequest = $this->getApiRequest();
        $requestData = [
            'topic' => 'rpc',
            'action' => 'make',
            'rpcName' => func_get_arg(0),
        ];

        if (func_num_args() === 2) {
            $requestData['data'] = func_get_arg(1);
        } else {
            $requestData['data'] = null;
        }

        $apiRequest->add($requestData);

        if ($this->hasBatch()) {
            return true;
        }

        $response = $apiRequest->execute();

        if ($response->result === SUCCESS_RESPONSE) {
            return $response->body[0]->data;
        } else {
            return false;
        }
    }

    /**
     * Emits a deepstream event.
     *
     * @param string eventName
     * @param mixed data optional
     *
     * @public
     * @return bool success
     */
    public function emitEvent()
    {
        $apiRequest = $this->getApiRequest();
        $requestData = [
            'topic' => 'event',
            'action' => 'emit',
            'eventName' => func_get_arg(0),
        ];

        if (func_num_args() === 2) {
            $requestData['data'] = func_get_arg(1);
        } else {
            $requestData['data'] = null;
        }

        $apiRequest->add($requestData);
        if ($this->hasBatch()) {
            return true;
        }
        $response = $apiRequest->execute();

        return  $response && $response->result === SUCCESS_RESPONSE;
    }

    /**
     * Returns the current version for a record.
     *
     * @param type $recordName
     *
     * @public
     * @return mixed the version of the record
     */
    public function getRecordVersion($recordName)
    {
        $apiRequest = $this->getApiRequest();
        $apiRequest->add([
            'topic' => 'record',
            'action' => 'read',
            'recordName' => $recordName,
        ]);
        if ($this->hasBatch()) {
            return true;
        }

        return $apiRequest->execute()->body[0]->version;
    }

    /**
     * Deletes a record.
     *
     * @param type $recordName
     *
     * @public
     * @return bool
     */
    public function deleteRecord($recordName)
    {
        $apiRequest = $this->getApiRequest();
        $apiRequest->add([
            'topic' => 'record',
            'action' => 'delete',
            'recordName' => $recordName,
        ]);
        if ($this->hasBatch()) {
            return true;
        }

        return $apiRequest->execute()->result === SUCCESS_RESPONSE;
    }

    public function getPresence()
    {
        $apiRequest = $this->getApiRequest();
        $apiRequest->add([
                'topic' => 'presence',
                'action' => 'query',
        ]);
        $response = $apiRequest->execute();
        if ($response->result === SUCCESS_RESPONSE) {
            return $response->body[0]->users;
        } else {
            return false;
        }
    }

    /**
     * Check if a batch operation is in progress.
     *
     * @return bool
     */
    private function hasBatch()
    {
        return $this->batchApiRequest !== null;
    }

    /**
     * Returns a new API request or an existing one if a batch is in progress.
     *
     * @return \ApiRequest
     */
    private function getApiRequest()
    {
        if ($this->hasBatch()) {
            return $this->batchApiRequest;
        } else {
            return new ApiRequest($this->url, $this->authData);
        }
    }
}
