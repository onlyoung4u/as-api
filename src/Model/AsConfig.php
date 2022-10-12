<?php

namespace Onlyoung4u\AsApi\Model;

class AsConfig extends BaseModel
{
    /**
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date) {
        return $date->format($this->dateFormat ?: 'Y-m-d H:i:s');
    }
}