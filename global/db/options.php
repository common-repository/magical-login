<?php
// There are four actions that we can do with options, namely, registering, unregistering, updating, and reading all of this actions will be take place here to reduce the codes and ensure complete cleanup at uninstallation time.

// this are the names of the options without the prefix
// delete this funciton for no options
function eex_up_magical_login_option_list()
{
    $list = array(
      'email_login_alowed',
      'username_login_alowed',
      'email_recovery_alowed',
      'username_recovery_alowed',
      'login_header',
      'login_username_lable',
      'login_username_placeholder',
      'login_pass_lable',
      'login_pass_placeholder',
      'login_rememberme_lable',
      'login_rememberme_defualt',
      'login_submit_text',
      'recovery_header',
      'recovery_username_lable',
      'recovery_username_placeholder',
      'recovery_submit_text',
      'recovery_email_subject',
      'recovery_email_content',
      'extra_wp_login',
    );
    return $list;
}

// registering the option table
function eex_up_magical_login__register_setting()
{
    try {
        update_option(eex_up_prefix() . 'email_login_alowed', 'email');
        update_option(eex_up_prefix() . 'username_login_alowed', 'username');
        update_option(eex_up_prefix() . 'email_recovery_alowed', 'email');
        update_option(eex_up_prefix() . 'username_recovery_alowed', 'username');
        delete_option(eex_up_prefix() . 'login_header');
        delete_option(eex_up_prefix() . 'login_username_lable');
        delete_option(eex_up_prefix() . 'login_username_placeholder');
        delete_option(eex_up_prefix() . 'login_pass_lable');
        delete_option(eex_up_prefix() . 'login_pass_placeholder');
        delete_option(eex_up_prefix() . 'login_rememberme_lable');
        delete_option(eex_up_prefix() . 'login_rememberme_defualt');
        delete_option(eex_up_prefix() . 'login_submit_text');
        delete_option(eex_up_prefix() . 'recovery_header');
        delete_option(eex_up_prefix() . 'recovery_username_lable');
        delete_option(eex_up_prefix() . 'recovery_username_placeholder');
        delete_option(eex_up_prefix() . 'recovery_submit_text');
        delete_option(eex_up_prefix() . 'recovery_email_subject');
        delete_option(eex_up_prefix() . 'extra_wp_login');
    } catch (\Exception $e) {
        return false;
    }
    return true;
}

function eex_up_magical_login__unregister_setting()
{
    try {
        $prefix = eex_up_prefix();
        $names = eex_up_magical_login_option_list();
        if (is_array($names)) {
            foreach ($names as $name) {
                delete_option($prefix .$name);
            }
        }
    } catch (\Exception $e) {
        return false;
    }
    return true;
}
