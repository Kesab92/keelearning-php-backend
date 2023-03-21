<?php

namespace App\Exceptions\Users;

use App\Http\APIError;

class UserCreationException extends \Exception
{
    private APIError $errorResponse;

    public function __construct(APIError $errorResponse)
    {
        parent::__construct();
        $this->errorResponse = $errorResponse;
    }

    public function getErrorResponse(): APIError
    {
        return $this->errorResponse;
    }
}
