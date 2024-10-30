<?php
// editing the page POST content before header
ob_start();

// registers all our custom shortcodes on init
add_shortcode('eex_up_login_form', eex_up_ml_() . 'shortcode');

function eex_up_magical_login_shortcode($args, $content = "")
{
    // since we are in the public area user is not loged in, thus show the login form
    // Get addmin options
    if (!is_user_logged_in()) {
        $action = sanitize_text_field($_GET['action']);
        if (is_string($action) and $action === 'lostpassword') {
            return eex_up_magical_login_lostpassword_form($args, $content);
        }
        return eex_up_magical_login_login_form($args, $content);
    }
    return '';
}

function eex_up_magical_login_login_form($arg='', $content = "")
{
    // get addmin options
    $options = eex_up_magical_login_set_options();

    // check if the form is submited!
    $errors = array();
    try {
        if (isset($_POST[eex_up_ml_() . 'username'])) {
            $_POST = array_map('stripslashes_deep', $_POST);

            // prepare sign in data
            $sign_in_data = array(
                'user_name'=> esc_attr(sanitize_text_field($_POST[eex_up_ml_() . 'username'])),
                'password'=> esc_attr(sanitize_text_field($_POST[eex_up_ml_() . 'password'])),
                'remember_me'=> esc_attr(sanitize_text_field($_POST[eex_up_ml_() . 'remember_me'])),
              );

            // Check for errors
            // gives errors in case you dont get the right info!
            if (!strlen($sign_in_data['password'])) {
                $errors[] = 'Password it too short!</a>';
            }

            // is login posible? if so with what?
            $emailAlowed = false;
            $usernameAlowed = false;
            if (is_string($options['email_login_alowed']) and !empty($options['email_login_alowed'])) {
                $usernameAlowed = true;
            }
            if (is_string($options['username_login_alowed']) and !empty($options['username_login_alowed'])) {
                $emailAlowed = true;
            }

            $signinAlowed = true;
            if ($usernameAlowed === false and $emailAlowed === false) {
                $signinAlowed = false;
            }

            // check if its addmin, $signinAlowed = true;
            $isaddmin = false;
            $getuserby = '';
            if (filter_var($sign_in_data['user_name'], FILTER_VALIDATE_EMAIL)) {
                if (is_email($sign_in_data['user_name']) and email_exists($sign_in_data['user_name'])) {
                    $getuserby = 'email';
                }
            } elseif (username_exists($sign_in_data['user_name'])) {
                $getuserby = 'login';
            }
            $user = get_user_by($getuserby, $sign_in_data['user_name']);
            if ($user) {
                if (is_array($user->roles) and in_array('administrator', $user->roles)) {
                    $isaddmin = true;
                }
            }

            if ($signinAlowed or $isaddmin) {
                if (!strlen($sign_in_data['user_name'])) {
                    $errors[] = 'Enter a valid' . (($usernameAlowed ===  true) ? ' username' : '') . (($usernameAlowed ===  true and $emailAlowed === true) ? ' or' : '') . (($emailAlowed === true) ? ' email address.' : '.');
                } else {
                    if (filter_var($sign_in_data['user_name'], FILTER_VALIDATE_EMAIL)) {
                        if ($emailAlowed === true) {
                            // check if it is valid email
                            if (!is_email($sign_in_data['user_name'])) {
                                $errors[] = 'Invalid email address.';
                            } elseif (!email_exists($sign_in_data['user_name'])) {
                                $errors[] = 'There is no user registered with this email address.';
                            }
                        } elseif ($isaddmin === false) {
                            $errors[] = 'No email is alowed use your username.';
                        }
                    } elseif (!username_exists($sign_in_data['user_name'])) {
                        $errors[] = 'Invalid username.';
                    } elseif ($isaddmin === false) {
                        if ($usernameAlowed ===  false) {
                            $errors[] = 'No username is alowed use your email.';
                        }
                    }
                }
            } else {
                $errors[] = 'Only admins are alowed to log in.';
            }


            // attempt to sign in user if no errors
            if (empty($errors)) {
                $user =  wp_signon(array(
                  'user_login' => $sign_in_data['user_name'],
                  'user_password' => $sign_in_data['password'],
                  'remember' => $sign_in_data['remember_me'],
                ), false);

                if (is_wp_error($user)) {
                    $errors[] = 'Username/email and password do not match<br><a href="'.esc_url(get_permalink()).'?action=lostpassword#'. eex_up_ml_('id') . 'form">Lost your password?</a>';
                } else {
                    wp_set_current_user($user->ID);
                    wp_redirect(get_admin_url(), $status = 302);
                    return '';
                }
            }
        }
    } catch (Exception $e) {
        $errors[] = 'Something went wrong during the processing your information. Please restart your browser, make sure your internet connection is secure and try again.';
    }

    // set up output variables - the form html;
    $output = '<div class="'. eex_up_ml_('css') . 'login-holder">
                <form id="'. eex_up_ml_('id') . 'form" name="'. eex_up_ml_() . 'form" class="'. eex_up_ml_('css') . 'form" action="'.esc_url(get_permalink()).'#'. eex_up_ml_('id') . 'form" method="post">';
    if (stripos($options['login_header'], '[ml_erase]') === false) {
        $output .= '<label>' . $options['login_header'] . '</label><br />';
    }
    $output .= '<p class="'. eex_up_ml_('css') . 'input-continer">';
    if (stripos($options['login_username_lable'], '[ml_erase]') === false) {
        $output .= '<label>' . $options['login_username_lable'] . '</label><br />';
    }
    $output .= '<input required type="text" name="'. eex_up_ml_() . 'username" placeholder="' . $options['login_username_placeholder'] . '"' .(($sign_in_data['user_name']) ? 'value="' . $sign_in_data['user_name']. '"' : '');
    $output .= '</p><p class="'. eex_up_ml_('css') . 'input-continer">';
    if (stripos($options['login_pass_lable'], '[ml_erase]') === false) {
        $output .= '<label>' . $options['login_pass_lable'] . '</label><br />';
    }
    $output .= '<input required type="password" name="'. eex_up_ml_() . 'password" placeholder="' . $options['login_pass_placeholder'] . '"></p><p class="'. eex_up_ml_('css') . 'input-continer">';
    if (stripos($options['login_rememberme_lable'], '[ml_erase]') === false) {
        $output .= '<input '. ((is_string($options['login_rememberme_lable'])) ? 'checked' : '') .' name="'. eex_up_ml_() . 'remember_me" id="'. eex_up_ml_() . 'rememberme" type="checkbox" value="forever"><label for="' . eex_up_ml_() . 'remember_me">' . $options['login_rememberme_lable'] . '</label>';
    }
    $output .= '</p>';
    if (!empty($errors)) {
        $output .= '<p class="'. eex_up_ml_('css') . 'input-continer" id="' . eex_up_ml_('id') . 'errors">';
        foreach ($errors as $error) {
            $output .= '<label class"'. eex_up_ml_('css') . 'error-item">' . $error . '</label>';
        }
        $output .= '</p>';
    }

    // including content in out form HTML if content is passed into the function
    if (strlen($content)) {
        $output .= '<div class="'. eex_up_ml_('css') . 'content">'. wpautop($content).'</div>';
    }

    // completing our form HTML
    $output .= '
            <p class="'. eex_up_ml_('css') . 'input-continer">
              <input type="submit" name="' . eex_up_ml_() . 'submit" value="' . $options['login_submit_text'] . '">
            </p>
          </form>
        </div>';
    return $output;
}
function eex_up_magical_login_lostpassword_form($arg='', $content = "")
{
    // get addmin options
    $options = eex_up_magical_login_set_options();
    $errors = array();
    $success = ''; // is successfuly sent the email? if yes this is the massage!
    $getuserby = ''; // get user by email or by username?
    $useID = false;
    try {
        if (isset($_POST[eex_up_ml_() . 'username'])) {
            $_POST = array_map('stripslashes_deep', $_POST);
            $inputIdf = sanitize_text_field($_POST[eex_up_ml_() . 'username']);

            if (!strlen($inputIdf)) {
                $errors[] = 'Username/email it too short!</a>';
            }

            // is password recovery posible? if so with what?
            $emailAlowed = false;
            $usernameAlowed = false;
            if (is_string($options['email_recovery_alowed']) and !empty($options['email_recovery_alowed'])) {
                $usernameAlowed = true;
            }
            if (is_string($options['username_recovery_alowed']) and !empty($options['username_recovery_alowed'])) {
                $emailAlowed = true;
            }

            $signinAlowed = true;
            if ($usernameAlowed === false and $emailAlowed === false) {
                $signinAlowed = false;
            }

            // check if its addmin, $signinAlowed = true;
            $isaddmin = false;
            $getuserby = '';
            if (filter_var($sign_in_data['user_name'], FILTER_VALIDATE_EMAIL)) {
                if (is_email($sign_in_data['user_name']) and email_exists($sign_in_data['user_name'])) {
                    $getuserby = 'email';
                }
            } elseif (username_exists($sign_in_data['user_name'])) {
                $getuserby = 'login';
            }
            $user = get_user_by($getuserby, $sign_in_data['user_name']);
            if ($user) {
                if (is_array($user->roles) and in_array('administrator', $user->roles)) {
                    $isaddmin = true;
                }
            }

            $getuserby = '';
            if ($signinAlowed or $isaddmin) {
                if (!strlen($sign_in_data['user_name'])) {
                    $errors[] = 'Enter a valid' . (($usernameAlowed ===  true) ? ' username' : '') . (($usernameAlowed ===  true and $emailAlowed === true) ? ' or' : '') . (($emailAlowed === true) ? ' email address.' : '.');
                } else {
                    // if user send a text check wether it is email or eex_up_ml_username
                    if (filter_var($inputIdf, FILTER_VALIDATE_EMAIL)) {
                        if ($emailAlowed === true or $isaddmin === true) {
                            // check if it is valid email
                            if (!is_email($inputIdf)) {
                                $errors[] = 'Invalid email address.';
                            } elseif (!email_exists($inputIdf)) {
                                $errors[] = 'There is no user registered with this email address.';
                            } else {
                                $getuserby = 'email';
                            }
                        } elseif ($isaddmin === false) {
                            $errors[] = 'No email is alowed use your username.';
                        }
                    } elseif (!username_exists($inputIdf)) {
                        $errors[] = 'Invalid username.';
                    } else {
                        $getuserby = 'login';
                        if ($usernameAlowed ===  false) {
                            $errors[] = 'No username is alowed use your email.';
                        }
                    }
                }
            } else {
                $errors[] = 'Only admins are alowed to log in.';
            }

            // try to log in
            if (empty($errors)) {
                // Get user data by field and data, other field are ID, slug, slug and login
                $user = get_user_by($getuserby, $inputIdf);

                $random_password = wp_generate_password(16, true, false);

                // replace the fields shortcodes in email subject
                $subject =  eex_up_magical_login_replace_shortcodes($options['recovery_email_subject'], array('user' => $user, 'random-password' => $random_password));

                // replace the fields shortcodes in email content
                $content = eex_up_magical_login_replace_shortcodes($options['recovery_email_content'], array('user' => $user, 'random-password' => $random_password));

                if (!is_array($content['found']) or !in_array('[random-password]', $content['found'])) {
                    $errors = 'Oops, we cannot generate a random password for you, please contact our support for resolving this problem.';
                }

                // if the user found try to send an email to user!
                if ($user and empty($errors)) {
                    // find the user email
                    $tempemail = $user->user_email;
                    // create and send the email
                    $to = $tempemail;
                    $sender = get_option('name');

                    $headers[] = 'MIME-Version: 1.0' . "\r\n";
                    $headers[] = 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                    $headers[] = "X-Mailer: PHP \r\n";
                    $headers[] = 'From: '.$sender.' < '.$tempemail.'>' . "\r\n";

                    $mail = wp_mail($to, $subject['result'], $content['result'], $headers);
                    if ($mail) {
                        // if the email is successfuly sent, update a new password
                        wp_set_password($random_password, $user->ID);
                        $success = 'We send you an email containing your new password; please be aware that the email might end up in your spam or junk folder.';
                    } else {
                        $errors[] = 'Oops we are not able to send you email! <br> Please contact our support.';
                    }
                }
            }
        }
    } catch (Exception $e) {
        $errors[] = 'Something went wrong during the processing your information. Please restart your browser, make sure your internet connection is secure and try again.';
    }
    // set up output variables - the form html;
    $output = '<div class="'. eex_up_ml_('css') . 'login-holder">
              <form id="'. eex_up_ml_('id') . 'form" name="'. eex_up_ml_() . 'form" class="'. eex_up_ml_('css') . 'form" action="'.esc_url(get_permalink()).'?action=lostpassword#'. eex_up_ml_('id') . 'form" method="post">';
    if (stripos($options['recovery_header'], '[ml_erase]') === false) {
        $output .= '<label>' . $options['recovery_header'] . '</label><br />';
    }
    $output .= '<p class="'. eex_up_ml_('css') . 'input-continer">';
    if (stripos($options['recovery_username_lable'], '[ml_erase]') === false) {
        $output .= '<label>' . $options['recovery_username_lable'] . '</label><br />';
    }
    $output .= '<input required type="text" name="'. eex_up_ml_() . 'username" placeholder="' . $options['recovery_username_placeholder'] . '"';
    if ($inputIdf) {
        $output .= 'value="' . $inputIdf. '"';
    }
    $output .= '><p class="'. eex_up_ml_('css') . 'input-continer"><a href="' . esc_url(get_permalink()).'#'. eex_up_ml_('id') . 'form">Back to login</a></p>';
    $output .= '</p>';
    if (!empty($errors)) {
        $output .= '<p class="'. eex_up_ml_('css') . 'input-continer" id="errors">';
        foreach ($errors as $error) {
            $output .= '<label class"eex_up_ml-error-item">' . $error . '</label>';
        }
        $output .= '</p>';
    }
    if (strlen($success)) {
        $output .= '<div class="'. eex_up_ml_('css') . 'content">'. wpautop($success).'</div>';
    }



    // including content in out form HTML if content is passed into the function
    if (strlen($content)) {
        $output .= '<div class="'. eex_up_ml_('css') . 'content">'. wpautop($content).'</div>';
    }

    // completing our form HTML
    $output .= '
        <p class="'. eex_up_ml_('css') . 'input-continer">
          <input type="submit" name="eex_up_ml_submit" value="' . $options['recovery_submit_text'] . '">
        </p>
      </form>
    </div>';
    return $output;
}
