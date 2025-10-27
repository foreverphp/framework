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

    public function showHelp(): void
    {
        echo "Get help for a command.\n";
    }

    public function run(array $args): void
    {
        if ($args['commandClass'] !== null) {
            $args['commandClass']->showHelp();
            exit(0);
        }

        echo Color::fg('red', 'Error:') . " Command not found: {$args['commandName']}\n";
    }
}
