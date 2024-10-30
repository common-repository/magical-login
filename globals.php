<?php
// Globals are only functions that we cannot limit their access throw time (loading time of the WordPress) or user accessibility order the whole plugin to use. E.g., naming of the plugin, options, ...

// include naming
include_once(plugin_dir_path(__FILE__) . '/global/naming.php');

// include our shortcodes
include_once(plugin_dir_path(__FILE__) . '/global/our_shortcodes.php');

// include database helpers
include_once(plugin_dir_path(__FILE__) . '/global/db.php');
