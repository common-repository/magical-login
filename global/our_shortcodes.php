<?php
// From time to time we need to use shortcodes specific to our plugins, for the same reason WordPress uses them. This file is a right place for functions handling reading and replacing those shortcodes.

// gives the list of all the shortcodes and they wp counterparts if any
function eex_up_magical_login_shortcodes_list()
{
    $list = array(
      '[user-username]' => 'user_login',
      '[user-name]' => array('first_name', 'last_name'),
      '[user-display-name]' => 'display_name',
      '[random-password]' => 'number',
    );
    return $list;
}

// replaces our shortcodes with the provide arguments or default if not set.
function eex_up_magical_login_replace_shortcodes($str, $arg = array())
{
    try {
        // set default
        $ans = array(
          'init' => $str,
          'result' => $str,
          'values' => array(
            'user' => wp_get_current_user(),
            'random-password' => wp_generate_password(16, true, false),
          ),
          'found' => array(),
        );
        // replace default with user inputs if matched
        if (is_array($arg)) {
            foreach ($arg as $key => $value) {
                if ($key == 'user') {
                    $ans['values']['user'] = $value;
                } elseif ($key == 'random-password') {
                    $ans['values']['random-password'] = $value;
                }
            }
        }

        // construct result
        $shortCodes = eex_up_magical_login_shortcodes_list();
        if (is_array($shortCodes)) {
            foreach ($shortCodes as $code => $codevalue) {
                if (!is_array($codevalue)) {
                    $codevalue = array($codevalue);
                }
                $replacement = '';
                foreach ($codevalue as $codesubvalue) {
                    if (stripos($code, 'user') !== false) {
                        $replacement .= $ans['values']['user']->$codesubvalue . ' ';
                    } elseif (stripos($code, 'random-password') !== false) {
                        $replacement .= $ans['values']['random-password'];
                    }
                }
                if (stripos($ans['result'], $code) !== false) {
                    $ans['found'][] = $code;
                    $ans['result'] = str_replace($code, $replacement, $ans['result']);
                }
            }
        }
        return $ans;
    } catch (\Exception $e) {
    }
    return false;
}
