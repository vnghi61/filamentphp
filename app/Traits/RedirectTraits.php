<?php

namespace App\Traits;

trait RedirectTraits
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}