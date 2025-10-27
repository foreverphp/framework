<?php

namespace ForeverPHP\Core\CLI;

class CLI
{
    private $commands = [];

    public function register(CommandInterface $command): void
    {
        $this->commands[$command->getName()] = $command;
    }

    public function run(array $argv): void
    {
        if (!defined('FOREVERPHP_ADMIN')) {
            die('Critical failure in ForeverPHP');
        }

        array_shift($argv); // quitar nombre de script
        $commandName = $argv[0] ?? '';

        if ($commandName === '--version') {
            echo Color::fg('green', 'ForeverPHP') . " version " . safe_const('FOREVERPHP_VERSION') . "\n";
            return;
        }

        if ($commandName === '') {
            $this->showHelp();
            exit(1);
        }

        if (!isset($this->commands[$commandName])) {
            echo Color::fg('red', "Comando desconocido: $commandName") . "\n\n";
            $this->showHelp();
            exit(1);
        }

        // Si es help en sus args va la clase del commando a llamar su ayuda
        if ($commandName === 'help') {
            $this->commands[$commandName]->run([
                'commandName' => $argv[1],
                'commandClass' => $this->commands[$argv[1]] ?? null,
            ]);
            exit(0);
        }

        array_shift($argv);
        $this->commands[$commandName]->run($argv);
    }

    public function showHelp(): void
    {
        echo "Usage:\n";
        echo "  " . Color::fg('green', 'forever') . " <command> [options]\n\n";
        echo "Commands:\n";
        foreach ($this->commands as $cmd) {
            echo "  " . str_pad(Color::fg('yellow', $cmd->getName()), 40, " ") . $cmd->getDescription() . "\n";
        }
        echo "\n";
        echo "See \"forever help <command>\" for more information on a specific command.\n";
    }
}
