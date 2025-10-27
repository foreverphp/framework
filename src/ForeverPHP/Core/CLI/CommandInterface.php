<?php

namespace ForeverPHP\Core\CLI;

interface CommandInterface
{
    public function getName(): string;
    public function getDescription(): string;
    public function showHelp(): void;
    public function run(array $args): void;
}
