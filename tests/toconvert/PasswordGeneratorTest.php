<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class PasswordGeneratorTest extends TestCase
{
    public function testGeneratePasswords()
    {
        $alreadyCreatedPasswords = [];
        for ($i = 0; $i < 100; $i++) {
            $password = randomPassword();
            $this->assertTrue(is_string($password));
            $this->assertTrue(strlen($password) == 10);

            $this->assertFalse(in_array($password, $alreadyCreatedPasswords));
        }
    }
}
