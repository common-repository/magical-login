<?php
// include list creators for the admin setting
include_once(plugin_dir_path(__FILE__) . '/list-creator.php');


function eex_up_magical_login_get_warnings($options, $messages)
{
    try {
        $temp = array();
        foreach ($options as $opp) {
            if ($opp['name']) {
                if (stripos($opp['type'], 'checkbox') !== false) {
                    $temp[$opp['name']] = $opp['current'];
                } elseif (stripos($opp['type'], 'text') !== false) {
                    $temp[$opp['name']] = $opp['value'];
                }
            }
        }

        // warning if no one can login anymore!
        if (!$temp['email_login_alowed'] and !$temp['username_login_alowed']) {
            $messages['warning'][] = 'Since you closed both email and username on login, only admins can log in.';
        }
        // warning if no one can recover password anymore!
        if (!$temp['email_recovery_alowed'] and !$temp['username_recovery_alowed']) {
            $messages['warning'][] = 'Since you closed both email and username for pasword recovery, its only available for admins.';
        }
        // no subject for email is not recommanded.
        if (!empty($temp['recovery_email_subject']) and empty(preg_match('/\S/', $temp['recovery_email_subject']))) {
            $messages['warning'][] = 'Sending an email without a subject is not recommended.';
        }
    } catch (\Exception $e) {
    }
    return $messages;
}
function eex_up_magical_login_setting_massages()
{
    $messages = array(
      'success' => array(),
      'error' => array(),
      'warning' => array(),
      'info' => array(),
    );


    // check for form submitions:
    //    1. Updat seting,
    //    2. Sending test email
    if (isset($_POST[eex_up_ml_() . 'setting_nonce'])) {
        if (wp_verify_nonce($_POST[eex_up_ml_() . 'setting_nonce'], eex_up_ml_() . 'setting')) {
            // the connection is safe!
            $_POST = array_map('stripslashes_deep', $_POST);
            if ($_POST[eex_up_ml_() . 'update'] === 'Update Settings') {
                //    1. Updat seting,

                // construct future values on options
                $options = eex_up_magical_login_setting_options();

                // search for errors
                $emailContent = sanitize_text_field($_POST[eex_up_ml_() . 'recovery_email_content']);
                if (is_string($emailContent) and !empty($emailContent)) {
                    if (stripos($emailContent, '[random-password]') === false) {
                        $messages['error'][] = 'The password recovery content needs at least one [random-password] in it.';
                    }
                }
                // if thereis no way to login we cannot close wp-login
                $wpAdmin = sanitize_text_field($_POST[eex_up_ml_() . 'extra_wp_login']);
                if (is_string($wpAdmin) and strlen($wpAdmin)) {
                    $postIDs = eex_up_magical_login_get_post_ids_having('eex_up_login_form');
                    if (!is_array($postIDs) or empty($postIDs)) {
                        $messages['error'][] = 'Since you haven\'t had any page or post with our shortcode inside of it, you cannot close the WordPress default login page. <br> Otherwise, you will not be able to log in to this website anymore.';
                    }
                }

                // save if not error
                if (is_array($messages['error']) and empty($messages['error'])) {
                    if (is_array($options)) {
                        foreach ($options as $o) {
                            if (in_array($o['type'], array('checkbox', 'checkbox_with_subtitle'))) {
                                $temp = sanitize_text_field($_POST[eex_up_ml_() . $o['name']]);
                                if (is_string($temp) and !empty($temp)) {
                                    update_option(eex_up_prefix() .$o['name'], $temp);
                                } else {
                                    delete_option(eex_up_prefix() .$o['name']);
                                }
                            }
                            if (in_array($o['type'], array('text', 'textarea', 'text_with_subtitle', 'textarea_with_subtitle'))) {
                                $temp = sanitize_text_field($_POST[eex_up_ml_() . $o['name']]);
                                // if current value is the same as the default delete too
                                if (is_string($temp) and !empty($temp) and stripos($temp, '[ml_empty]') !== false) {
                                    if (stripos($o['name'], 'submit') !== false) {
                                        $messages['error'][] = 'A submit button without text is not possible. Related data was not saved.';
                                    } else {
                                        update_option(eex_up_prefix() .$o['name'], ' ');
                                    }
                                } elseif (is_string($temp) and !empty($temp) and $temp !== $o['default']) {
                                    // only headers and lables can be erased.
                                    if (stripos($temp, '[ml_erase]') !== false) {
                                        if (stripos($o['name'], 'header') === false and stripos($o['name'], 'lable') === false) {
                                            $messages['error'][] = '[ml_erase] shortcode only can be used for headers and labels. Related data was not saved.';
                                        } else {
                                            update_option(eex_up_prefix() .$o['name'], $temp);
                                        }
                                    } else {
                                        update_option(eex_up_prefix() .$o['name'], $temp);
                                    }
                                } else {
                                    delete_option(eex_up_prefix() .$o['name']);
                                }
                            }
                        }
                    }

                    $messages['success'][] = 'Your data has been successfully saved.';
                }
            } elseif ($_POST[eex_up_ml_() . 'recovery_email_test'] === 'Send Test to ' . wp_get_current_user()->user_email) {
                $user = wp_get_current_user();
                // if the user found try to send an email to user!
                if ($user) {
                    $random_password = wp_generate_password(16, true, false);
                    // construct future values on options
                    $options = eex_up_magical_login_setting_options();
                    $saveSubject = false;
                    $subject = sanitize_text_field($_POST[eex_up_ml_() . 'recovery_email_subject']);
                    if ($subject !== $option['value']) {
                        $saveSubject = true;
                    }
                    $saveContent = false;
                    $content = sanitize_text_field($_POST[eex_up_ml_() . 'recovery_email_content']);
                    if ($content !== $option['value']) {
                        $saveContent = true;
                    }


                    if (is_array($options)) {
                        foreach ($options as $option) {
                            if ($option['name'] == 'recovery_email_subject') {
                                if (!is_string($subject) or empty($subject)) {
                                    $subject = $option['default'];
                                }
                            }
                            if ($option['name'] == 'recovery_email_content') {
                                if (!is_string($content) or empty($content)) {
                                    $content = $option['default'];
                                }
                            }
                        }
                    }

                    if (stripos($content, '[random-password]') === false) {
                        $messages['error'][] = 'The password recovery content needs at least one [random-password] in it.';
                    }

                    // replace the fields shortcodes
                    $subject = eex_up_magical_login_replace_shortcodes($subject, array('user' => $user, 'random-password' => $random_password));
                    $content = eex_up_magical_login_replace_shortcodes($content, array('user' => $user, 'random-password' => $random_password));

                    if (!is_array($content['found']) or !in_array('[random-password]', $content['found'])) {
                        $messages['error'][] = 'Oops, we cannot generate a random password for you, please contact our support for resolving this problem.';
                    }

                    if (empty($messages['error'])) {
                        // if the email subject or content are changed, save the new ones
                        if ($saveSubject === true) {
                            update_option(eex_up_prefix() . 'recovery_email_subject', sanitize_text_field($_POST[eex_up_ml_() . 'recovery_email_subject']));
                        }
                        if ($saveContent === true) {
                            update_option(eex_up_prefix() . 'recovery_email_content', sanitize_text_field($_POST[eex_up_ml_() . 'recovery_email_content']));
                        }

                        // find the email first
                        $tempemail = $user->user_email;

                        // create and send the email
                        $to = $tempemail;

                        $sender = get_option('name');

                        $headers[] = 'MIME-Version: 1.0' . "\r\n";
                        $headers[] = 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                        $headers[] = "X-Mailer: PHP \r\n";
                        $headers[] = 'From: '.$sender.' < '.$tempemail.'>' . "\r\n";

                        $mail = wp_mail($to, $subject['result'], $content['result'], $headers);
                        // $mail = wp_mail($to, 'subject', 'contnet', $headers);

                        if ($mail) {
                            // if the email is successfuly sent, update password
                            // wp_set_password($random_password, $user->ID);
                            $messages['success'][] = 'Test email successfully sent to ' . wp_get_current_user()->user_email . '. Please be aware that the email might end up in your spam or junk folder.';
                        } else {
                            $messages['error'][] = 'Oops we are not able to send you email please contact our support.';
                        }
                    }
                }
            } else {
                $messages['error'][] = 'No data saved.';
            }
        } else {
            $messages['error'][] = 'Your connection is not safe; no data has been saved.';
        }
    }

    // construct future values on options
    // Recomputation is necessary since some information may be changed in the submitting process.
    $options = eex_up_magical_login_setting_options();
    // add the general massages:
    return eex_up_magical_login_get_warnings($options, $messages);
}
function eex_up_magical_login_set_options()
{
    // get addmin options
    $options = eex_up_magical_login_setting_options();
    $adminValues = array();
    if (is_array($options)) {
        foreach ($options as $option) {
            if (is_string($option['name']) and !empty($option['name'])) {
                if (stripos($option['type'], 'checkbox') !== false) {
                    $adminValues[$option['name']] = $option['current'];
                } elseif (stripos($option['type'], 'text') !== false or stripos($option['type'], 'textarea') !== false) {
                    $adminValues[$option['name']] = $option['value'];
                    if (!is_string($adminValues[$option['name']]) or empty($adminValues[$option['name']])) {
                        $adminValues[$option['name']] = $option['default'];
                    }
                }
            }
        }
    }
    return $adminValues;
}
function eex_up_magical_login_setting_options()
{
    // setup default email footer
    $default_email_text = '<p>
    Dear [user-username], <br>
    <br>
    We received a request for changing your password on our server. <br>
    If you didn\'t make the request simply ignore this email. <br>
    Otherwise, use the following password to open your account. <br>
    <br>
    Your new password is: [random-password] <br>
    <br>
    Sincerely, <br>
    The '. get_bloginfo('name') .' Team<br>
    <a href="'. get_bloginfo('url') .'">'. get_bloginfo('url') .'</a>
</p>';

    $EmailLogin = strlen(get_option(eex_up_prefix() . 'email_login_alowed')) > 0;
    $UsernameLogin = strlen(get_option(eex_up_prefix() . 'username_login_alowed')) > 0;
    $onlyAdminLogin = ($EmailLogin === false and $UsernameLogin ===false);
    $EmailPass = strlen(get_option(eex_up_prefix() . 'email_recovery_alowed')) > 0;
    $UsernamePass = strlen(get_option(eex_up_prefix() . 'username_recovery_alowed')) > 0;
    $onlyAdminPass = ($EmailPass=== false and $UsernamePass=== false);

    $ans =
    array(
      array(
        'type' => 'title',
        'text' => 'Manage login form',
      ),
      array(
        'type' => '',
        'text' => 'You can have the login from anywhere on your website, use the shortcode, <strong>[eex_up_login_form]</strong>, for this purpose.',
      ),
      array(
        'type' => 'title',
        'text' => 'Manage inputs',
      ),
      array(
        'type' => 'checkbox',
        'name' => 'email_login_alowed',
        'value' => 'email',
        'current' => get_option(eex_up_prefix() . 'email_login_alowed'),
        'lable' => 'Users allowed to use their <strong>"E-Mail"</strong> on login.',
        'break' => 1,
      ),
      array(
        'type' => 'checkbox',
        'name' => 'username_login_alowed',
        'value' => 'username',
        'current' => get_option(eex_up_prefix() . 'username_login_alowed'),
        'lable' =>  'Users allowed to use their <strong>"username"</strong> on login.',
        'break' => 1,
      ),
      array(
        'type' => 'checkbox',
        'name' => 'email_recovery_alowed',
        'value' => 'email',
        'current' => get_option(eex_up_prefix() . 'email_recovery_alowed'),
        'lable' => 'Users allowed to use their <strong>"E-Mail"</strong> on pasword recovery',
        'break' => 1,
      ),
      array(
        'type' => 'checkbox',
        'name' => 'username_recovery_alowed',
        'value' => 'username',
        'current' => get_option(eex_up_prefix() . 'username_recovery_alowed'),
        'lable' => 'Users allowed to use their <strong>"username"</strong> on pasword recovery',
        'break' => 1,
      ),
      array(
        'type' => 'title',
        'text' => 'Login Form Options',
      ),
      array(
        'type' => '',
        'text' => 'Use <strong>[ml_empty]</strong> shortcode for having an empty input and use <strong>[ml_erase]</strong> shortcode for eliminating the labels and headers.'
      ),
      array(
        'type' => 'title',
        'text' => '',
      ),
      array(
        'type' => 'text_with_subtitle',
        'name' => 'login_header',
        'text' => 'Header:',
        'value' => get_option(eex_up_prefix() . 'login_header'),
        'default' => 'Log in',
        'size' => 80,
        'break' => 1,
      ),
      array(
        'type' => 'text_with_subtitle',
        'name' => 'login_username_lable',
        'text' => 'Username lable:',
        'value' => get_option(eex_up_prefix() . 'login_username_lable'),
        'default' => (($onlyAdminLogin === true) ? 'Only Admins Can Login' : (($UsernameLogin === true) ? ('Username' . (($EmailLogin === true)? ' or email' : '') . ':') : 'Email:')),
        'size' => 80,
        'break' => 1,
      ),
      array(
        'type' => 'text_with_subtitle',
        'name' => 'login_username_placeholder',
        'text' => 'Username placeholder:',
        'value' => get_option(eex_up_prefix() . 'login_username_placeholder'),
        'default' => (($onlyAdminLogin === true) ? 'Only admin username/email' : (($UsernameLogin === true) ? ('Your username' . (($EmailLogin === true)? '/email' : '') . ' here...') : 'Email')),
        'size' => 80,
        'break' => 1,
      ),
      array(
        'type' => 'text_with_subtitle',
        'name' => 'login_pass_lable',
        'text' => 'Password lable:',
        'value' => get_option(eex_up_prefix() . 'login_pass_lable'),
        'default' => 'Password',
        'size' => 80,
        'break' => 1,
      ),
      array(
        'type' => 'text_with_subtitle',
        'name' => 'login_pass_placeholder',
        'text' => 'Password placeholder:',
        'value' => get_option(eex_up_prefix() . 'login_pass_placeholder'),
        'default' => 'Password here...',
        'size' => 80,
        'break' => 1,
      ),
      array(
        'type' => 'text_with_subtitle',
        'name' => 'login_rememberme_lable',
        'text' => '"Remember Me" lable:',
        'value' => get_option(eex_up_prefix() . 'login_rememberme_lable'),
        'default' => 'Remember Me',
        'size' => 80,
        'break' => 1,
      ),
      array(
        'type' => 'text_with_subtitle',
        'name' => 'login_submit_text',
        'text' => 'Submit button text:',
        'value' => get_option(eex_up_prefix() . 'login_submit_text'),
        'default' => 'Log In',
        'size' => 80,
        'break' => 2,
      ),
      array(
        'type' => 'checkbox_with_subtitle',
        'name' => 'login_rememberme_defualt',
        'text' => '"Remember Me" default:',
        'value' => 'rememberme',
        'current' => get_option(eex_up_prefix() . 'login_rememberme_defualt'),
        'break' => 2,
      ),
      array(
        'type' => 'title',
        'text' => 'Password Recovery Form Options',
      ),
      array(
        'type' => 'text_with_subtitle',
        'name' => 'recovery_header',
        'text' => 'Header:',
        'value' =>  get_option(eex_up_prefix() . 'recovery_header'),
        'default' => 'Recover your password',
        'size' => 80,
        'break' => 1,
      ),
      array(
        'type' => 'text_with_subtitle',
        'name' => 'recovery_username_lable',
        'text' => 'Username lable:',
        'value' =>  get_option(eex_up_prefix() . 'recovery_username_lable'),
        'default' => (($onlyAdminPass === true) ? 'Only Admins Can Recover Password' : (($UsernamePass === true) ? ('Username' . (($EmailPass === true)? ' or email' : '') . ':') : 'Email:')),
        'size' => 80,
        'break' => 1,
      ),
      array(
        'type' => 'text_with_subtitle',
        'name' => 'recovery_username_placeholder',
        'text' => 'Username placeholder:',
        'value' => get_option(eex_up_prefix() . 'recovery_username_placeholder'),
        'default' => (($onlyAdminPass === true) ? 'Only admin username/email' : (($UsernamePass === true) ? ('Your username' . (($EmailPass === true)? '/email' : '') . ' here...') : 'Email')),
        'size' => 80,
        'break' => 1,
      ),
      array(
        'type' => 'text_with_subtitle',
        'name' => 'recovery_submit_text',
        'text' => 'Submit button text:',
        'value' =>  get_option(eex_up_prefix() . 'recovery_submit_text'),
        'default' => 'Submit',
        'size' => 80,
        'break' => 2,
      ),
      array(
        'type' => 'title',
        'text' => 'Recovery E-Mail Options',
      ),
      array(
        'type' => 'text_with_subtitle',
        'name' => 'recovery_email_subject',
        'text' => 'Subject:',
        'value' =>  get_option(eex_up_prefix() . 'recovery_email_subject'),
        'default' => 'Your New Pasword',
        'size' => 80,
        'break' => 1,
      ),
      array(
        'type' => 'textarea_with_subtitle',
        'name' => 'recovery_email_content',
        'text' => 'Content:',
        'value' => get_option(eex_up_prefix() . 'recovery_email_content'),
        'default' => $default_email_text,
        'size' => array('cols' => 79, 'rows' => 12),
        'break' => 2,
      ),
      array(
        'type' => 'text_with_submit',
        'name' => 'recovery_email_test',
        'text' => 'Test:',
        'value' => 'Send test to ' . wp_get_current_user()->user_email,
        'break' => 1,
      ),
      array(
        'type' => 'title',
        'text' => 'Extra Options',
      ),
      array(
        'type' => 'checkbox',
        'name' => 'extra_wp_login',
        'value' => 'email',
        'current' => get_option(eex_up_prefix() . 'extra_wp_login'),
        'lable' => 'Deactive the defult login page of wordpress.',
        'break' => 2,
      ),
    );
    return $ans;
}
function eex_up_magical_login_admin_menu_setting_html()
{
    $postIDs = eex_up_magical_login_get_post_ids_having('eex_up_login_form');
    $options = eex_up_magical_login_setting_options();

    $ans = '';
    $ans .= '<form action="" method="post"><div>';
    $ans .= wp_nonce_field(eex_up_ml_() . 'setting', eex_up_ml_(). 'setting_nonce', true, false);
    $open = false;
    if (is_array($options)) {
        foreach ($options as $o) {
            switch ($o['type']) {
              case 'title':
                if ($open) {
                    $ans .= '<div></div>';
                } else {
                    $open = true;
                    $ans .= '<div>';
                }
                $ans .= call_user_func(eex_up_ml_(). 'option_' . $o['type'], $o['text']);
              break;
              case 'subtitle':
                $ans .= call_user_func(eex_up_ml_(). 'option_' . $o['type'], $o['text'], $o['name']);
              break;
              case 'checkbox':
                $checked = false;
                if (is_string($o['current']) and !empty($o['current'])) {
                    $checked = true;
                }
                $ans .= call_user_func(eex_up_ml_(). 'option_' . $o['type'], $o['name'], $o['value'], $checked, $o['lable'], $o['break']);
              break;
              case 'submit':
                $ans .= call_user_func(eex_up_ml_(). 'option_' . $o['type'], $o['name'], $o['value'], $o['break']);
              break;
              case 'text':
                $value = $o['value'];
                $Stored = get_option(eex_up_prefix() . $o['name']);
                if (!empty($Stored) and empty(preg_match('/\S/', $Stored))) {
                    $value = '[ml_empty]';
                }
                $ans .= call_user_func(eex_up_ml_(). 'option_' . $o['type'], $o['name'], $value, $o['default'], $o['size'], $o['break']);
              break;
              case 'textarea':
                $ans .= call_user_func(eex_up_ml_(). 'option_' . $o['type'], $o['name'], $o['value'], $o['default'], $o['size']['cols'], $o['size']['rows'], $o['break']);
              break;
              case 'text_with_subtitle':
                $value = $o['value'];
                $Stored = get_option(eex_up_prefix() . $o['name']);
                if (!empty($Stored) and empty(preg_match('/\S/', $Stored))) {
                    $value = '[ml_empty]';
                }
                $ans .= call_user_func(eex_up_ml_(). 'option_subtitle', $o['text'], $o['name']) . call_user_func(eex_up_ml_(). 'option_text', $o['name'], $value, $o['default'], $o['size'], $o['break']);
              break;
              case 'textarea_with_subtitle':
                $ans .= call_user_func(eex_up_ml_(). 'option_subtitle', $o['text'], $o['name']) . call_user_func(eex_up_ml_(). 'option_textarea', $o['name'], $o['value'], $o['default'], $o['size']['cols'], $o['size']['rows'], $o['break']);
              break;
              case 'checkbox_with_subtitle':
                $checked = false;
                if (is_string($o['current']) and !empty($o['current'])) {
                    $checked = true;
                }
                $ans .= call_user_func(eex_up_ml_(). 'option_subtitle', $o['text'], $o['name']) . call_user_func(eex_up_ml_(). 'option_checkbox', $o['name'], $o['value'], $checked, $o['lable'], $o['break']);
              break;
              case 'text_with_submit':
                $ans .= call_user_func(eex_up_ml_(). 'option_subtitle', $o['text'], $o['name']) . call_user_func(eex_up_ml_(). 'option_submit', $o['name'], $o['value'], $o['break']);
              break;
              default:
                $ans .= $o['text'] . '<br>';
                break;
            }
        }
    }
    $ans .= '</div><div>' . eex_up_magical_login_option_title('Status');
    if (is_array($postIDs) and !empty($postIDs)) {
        $ans .= '<div>Currently you have added '.sizeof($postIDs).' of our shortcode'. ((sizeof($postIDs) > 1) ? 's' : '') .' in your website! </div>';

        $ans .='<ul style="list-style-type:disc">';
        foreach ($postIDs as $id) {
            $ans .= '<li> In ' . str_replace('_', ' ', get_post_type($id)). ' named: "<a href="' . get_edit_post_link($id). '" target="_blank">' . get_the_title($id) . '</a>"</li>';
        }
        $ans .='</ul>';
    } else {
        $ans .= 'Currently you haven\'t add any shortcode in your website! <br> If you dont know how to do it, see our starting video at: <a href="https://www.youtube.com/playlist?list=PLgq3YulEpVveIzk3E13rLkj5hEH7zSgJ_">YouTube</a> or <a href="https://vimeo.com/album/5527824">vimeo</a> ';
    }
    $ans .= '<br><br>' .eex_up_magical_login_option_submit('update', 'Update Settings', 1);
    $ans .= '</div></div></form>';
    return $ans; ?>  <?php
}
//get all the posts that appares in them
function eex_up_magical_login_get_post_ids_having($str)
{
    $postIDs = array(); // defult we dont have any!
    // get all the posts of any type and status
    try {
        $allPosts = get_posts(array(
          'numberposts' => -1,
          'post_status' => 'any',
          'post_type' => 'any',
        ));
        if (is_array($allPosts)) {
            foreach ($allPosts as $post) {
                // check the content.
                if (!empty($post->post_content)) {
                    if (stripos($post->post_content, $str)) {
                        $postIDs[] = $post->ID;
                    }
                }
            }
        }
    } catch (\Exception $e) {
    }
    return $postIDs;
}
