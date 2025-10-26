<?php

namespace ForeverPHP\Core\CLI\Commands;

use ForeverPHP\Core\CLI\CommandInterface;
use ForeverPHP\Core\CLI\Color;

class HelpCommand implements CommandInterface
{
    public function getName(): string
    {
        return 'help';
    }

    public function getDescription(): string
    {
        return 'Get help for a command.';
    }

    public function run(array $args): void
    {
        echo Color::fg('green', 'Help') . " Coming soon\n";
    }
}
