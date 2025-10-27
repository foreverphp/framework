<?php

namespace ForeverPHP\Core\CLI\Commands;

use ForeverPHP\Core\CLI\CommandInterface;
use ForeverPHP\Core\CLI\Color;

class GenerateSecretsCommand implements CommandInterface
{
    public function getName(): string
    {
        return 'generate-secrets';
    }

    public function getDescription(): string
    {
        return 'Generates the application secrets.';
    }

    public function showHelp(): void
    {
        echo "Usage:\n";
        echo "  " . Color::fg('green', 'forever') . " generate-secrets [options]\n\n";
        echo "Generates the secrets file with the given passwords.\n\n";
        echo "Options:\n";
        echo "  " . str_pad("--no-regenerate", 20, " ") .
            "Indicates that if the secrets file exists, passwords are not prompted again\n";
    }

    public function run(array $args): void
    {
        $keyFile = safe_const('ROOT_PATH') . safe_const('DS') . '.secrets' . safe_const('DS') . 'master-password.key';
        $secretsFile = safe_const('ROOT_PATH') . safe_const('DS') . '.secrets' . safe_const('DS') . 'secrets.json';

        if (!file_exists($keyFile)) {
            echo Color::fg('red', '✗') . " First generate the master key with:\n";
            echo "  ./forever generate-password-secrets\n";
            return;
        }

        // Check if the secrets file already exists, for overwrite
        if (file_exists($secretsFile)) {
            echo "The secrets file already exists. Do you want to update the secrets? [y/N]:";
            $response = trim(fgets(STDIN));

            if (strtolower($response) !== 'y') {
                return;
            }

            echo "Overwriting the secrets file...\n";
        }

        $key = base64_decode(file_get_contents($keyFile));
        $secrets = [];

        // Check if there are already secrets in the settings
        if (!\ForeverPHP\Core\Settings::getInstance()->exists('secrets')) {
            echo Color::fg('red', '✗') . " There are no secrets in settings.\n";
            return;
        }

        $secretsList = \ForeverPHP\Core\Settings::getInstance()->get('secrets');

        foreach ($secretsList as $secretName) {
            echo "Password for \"$secretName\" secret: ";

            // Disable eco
            system('stty -echo');
            $passwordSecret = trim(fgets(STDIN));
            // Enable eco
            system('stty echo');

            if ($passwordSecret === '') {
                break;
            }

            $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
            $cipher = sodium_crypto_secretbox($passwordSecret, $nonce, $key);
            $secrets[$secretName] = base64_encode("$nonce$cipher");

            echo "\n";
        }

        // Check if all secrets were entered
        if (count($secrets) !== count($secretsList)) {
            echo "\n";
            echo Color::fg('red', '✗') . " Not all secrets were entered.\n";
            return;
        }

        file_put_contents($secretsFile, json_encode($secrets, JSON_PRETTY_PRINT));
        echo Color::fg('green', '✔') . " Secrets save in $secretsFile\n";
    }
}
