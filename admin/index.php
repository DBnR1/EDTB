<?php
function adminer_object()
{
    // required to run any plugin
    include_once "plugin.php";

    // autoloader
    foreach (glob("adminer_plugins/*.php") as $filename) {
        include_once "./$filename";
    }

    $plugins = array(
        // specify enabled plugins here
        new AdminerDumpBz2,
        new AdminerEnumOption
    );

    /* It is possible to combine customization and plugins:
    class AdminerCustomization extends AdminerPlugin {
    }
    return new AdminerCustomization($plugins);
    */

    return new AdminerPlugin($plugins);
}

// include original Adminer or Adminer Editor
include "adminer.php";
