<?php

namespace Onlyoung4u\AsApi;

class Install
{
    const WEBMAN_PLUGIN = true;

    /**
     * @var array
     */
    protected static $pathRelation = [
        '.env.example' => '.env.example',
        'config/plugin/onlyoung4u/as-api' => 'config/plugin/onlyoung4u/as-api',
        'database/migrations.php' => 'database/migrations',
        'database/seeds' => 'database/seeds',
    ];

    /**
     * Install
     * @return void
     */
    public static function install()
    {
        copy(__DIR__ . '/phinx.php', base_path() . '/phinx.php');
        chmod(base_path() . '/phinx.php', 0755);

        static::installByRelation();
    }

    /**
     * Uninstall
     * @return void
     */
    public static function uninstall()
    {
        self::uninstallByRelation();
    }

    /**
     * installByRelation
     * @return void
     */
    public static function installByRelation()
    {
        foreach (static::$pathRelation as $source => $dest) {
            if ($pos = strrpos($dest, '/')) {
                $parent_dir = base_path() . '/' . substr($dest, 0, $pos);
                if (!is_dir($parent_dir)) {
                    mkdir($parent_dir, 0777, true);
                }
            }

            copy_dir(__DIR__ . "/$source", base_path() . "/$dest");

            echo "Create $dest";
        }
    }

    /**
     * uninstallByRelation
     * @return void
     */
    public static function uninstallByRelation()
    {
        foreach (static::$pathRelation as $source => $dest) {
            $path = base_path() . "/$dest";

            if (!is_dir($path) && !is_file($path)) {
                continue;
            }

            echo "Remove $dest";

            if (is_file($path) || is_link($path)) {
                unlink($path);
                continue;
            }

            remove_dir($path);
        }
    }

}