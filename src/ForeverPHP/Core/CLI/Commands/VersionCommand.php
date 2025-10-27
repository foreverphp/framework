<?php

namespace ForeverPHP\Core\CLI\Commands;

use Composer\InstalledVersions;
use ForeverPHP\Core\CLI\CommandInterface;
use ForeverPHP\Core\CLI\Color;

class VersionCommand implements CommandInterface
{
    public function getName(): string
    {
        return 'version';
    }

    public function getDescription(): string
    {
        return 'Show ForeverPHP version.';
    }

    public function showHelp(): void
    {
        echo "Usage:\n";
        echo "  " . Color::fg('green', 'forever') . " version, --version\n\n";
        echo "Print the version numbers of ForeverPHP.\n";
    }

    public function run(array $args): void
    {
        $version = InstalledVersions::getPrettyVersion('foreverphp/framework');
        echo Color::fg('green', 'ForeverPHP') . " version $version\n";
    }
}
