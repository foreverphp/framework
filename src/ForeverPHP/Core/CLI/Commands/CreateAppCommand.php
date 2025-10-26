<?php

namespace ForeverPHP\Core\CLI\Commands;

use ForeverPHP\Core\CLI\CommandInterface;
use ForeverPHP\Core\CLI\Color;

class CreateAppCommand implements CommandInterface
{
    public function getName(): string
    {
        return 'create-app';
    }

    public function getDescription(): string
    {
        return 'Create new App.';
    }

    public function run(array $args): void
    {
        echo Color::fg('Yellow', 'Coming soon') . "\n";
    }
}
