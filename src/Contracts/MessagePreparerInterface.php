<?php

namespace App\Contracts;

interface MessagePreparerInterface
{
    public function prepare(string $userMessage): array;
}
