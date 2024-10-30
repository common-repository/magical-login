<?php
// Any communication with the Wordpress database has to be handled here.
// There are two type of data namely posts and options that we can handle when connecting the admin area to the front end fo the Wordpress.


// including option helpers
include_once(plugin_dir_path(__FILE__) . 'db/options.php');
