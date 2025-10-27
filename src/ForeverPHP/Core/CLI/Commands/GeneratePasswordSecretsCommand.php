<?php

namespace ForeverPHP\Core\CLI\Commands;

use ForeverPHP\Core\CLI\CommandInterface;
use ForeverPHP\Core\CLI\Color;

class GeneratePasswordSecretsCommand implements CommandInterface
{
    public function getName(): string
    {
        return 'generate-password-secrets';
    }

    public function getDescription(): string
    {
        return 'Generates the master password for the secrets.';
    }

    public function showHelp(): void
    {
        echo "Usage:\n";
        echo "  " . Color::fg('green', 'forever') . " generate-password-secrets [options]\n\n";
        echo "Generates the master password that is later used to create the secrets.\n";
    }

    public function run(array $args): void
    {
        $keyFile = safe_const('ROOT_PATH') . safe_const('DS') . '.secrets' . safe_const('DS') . 'master-password.key';

        if (file_exists($keyFile)) {
            echo Color::fg('red', '✗') . " Master key already exists in $keyFile\n";
            return;
        }

        $key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);
        if (!is_dir(dirname($keyFile))) {
            mkdir(dirname($keyFile), 0700, true);
        }

        file_put_contents($keyFile, base64_encode($key));
        echo Color::fg('green', '✔') . " Master key generated in: $keyFile\n";
    }
}
