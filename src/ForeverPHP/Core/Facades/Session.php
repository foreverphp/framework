<?php

namespace ForeverPHP\Core\Facades;

/**
 * @method static bool exists(string $key, string $section = 'main')
 * @method static bool existsSection(string $section = 'main')
 * @method static void set(string $key, mixed $value, string $section = 'main')
 * @method static void get(string $key, string $section = 'main')
 * @method static void remove(string $key, string $section = 'main')
 * @method static void removeSection(string $section = 'main')
 * @method static void regenerate(bool $deleteOldSession = false)
 * @method static void destroy()
 * @see \ForeverPHP\Session\SessionManager
 */
class Session extends Facade
{
    /**
     * Obtiene el nombre registrado del componente o una instancia de el.
     *
     * @return mixed
     */
    protected static function getComponent()
    {
        return \ForeverPHP\Session\SessionManager::getInstance();
    }
}
