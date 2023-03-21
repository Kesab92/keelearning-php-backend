<?php

namespace App\Services\TAGs;

class TAGChange
{
    private $add = [];
    private $remove = [];

    /**
     * @return array
     */
    public function getRemove(): array
    {
        return $this->remove;
    }

    /**
     * @param array $remove
     */
    public function setRemove(array $remove): void
    {
        $this->remove = $remove;
    }

    /**
     * @return array
     */
    public function getAdd(): array
    {
        return $this->add;
    }

    /**
     * @param array $add
     */
    public function setAdd(array $add): void
    {
        $this->add = $add;
    }
}
