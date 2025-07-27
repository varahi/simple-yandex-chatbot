<?php

namespace src\Contracts;

interface MessagePreparerInterface
{
    public function prepare(string $userMessage): array;
}
