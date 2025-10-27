<?php

namespace ForeverPHP\Core\CLI\Commands;

use ForeverPHP\Core\CLI\CommandInterface;
use ForeverPHP\Core\CLI\Color;

class RunServerCommand implements CommandInterface
{
    private string $host = '127.0.0.1';
    private string $port = '8080';

    public function getName(): string
    {
        return 'run-server';
    }

    public function getDescription(): string
    {
        return 'Running development server.';
    }

    public function showHelp(): void
    {
        echo "Usage:\n";
        echo "  " . Color::fg('green', 'forever') . " run-server [options]\n\n";
        echo "Runs a ForeverPHP instance in development mode.\n\n";
        echo "Options:\n";
        echo "  " . str_pad("--host", 20, " ") . "Sets the host, default value is 127.0.0.1\n";
        echo "  " . str_pad("--port", 20, " ") . "Sets the port, default value is 8080\n";
    }

    public function run(array $args): void
    {
        $keyHost = array_search('--host', $args);
        $keyPort = array_search('--port', $args);

        if ($keyHost !== false && isset($args[$keyHost + 1])) {
            $this->host = $args[$keyHost + 1];
        }

        if ($keyPort !== false && isset($args[$keyPort + 1])) {
            $this->port = $args[$keyPort + 1];
        }

        echo Color::fg('green', 'ForeverPHP') . " versión " . safe_const('FOREVERPHP_VERSION') . "\n";
        echo "Servidor en " . Color::fg('blue', "http://{$this->host}:{$this->port}/") . "\n";
        echo "Presiona Ctrl-C para salir.\n";

        shell_exec("cd public && php -S {$this->host}:{$this->port}");
    }
}
