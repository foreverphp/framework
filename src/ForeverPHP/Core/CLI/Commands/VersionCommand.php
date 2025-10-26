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

    public function run(array $args): void
    {
        $version = InstalledVersions::getPrettyVersion('foreverphp/framework');
        echo Color::fg('green', 'ForeverPHP') . " version $version\n";
    }
}
