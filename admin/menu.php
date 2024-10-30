<?php

include_once(plugin_dir_path(__FILE__) .'tabs.php');

function eex_up_magical_login_admin_menu_html()
{
    // title of the plugin
    echo '<h1>'.eex_up_ml_name().'</h1>';
    // get the massages of all tabs
    eex_up_magical_login_massages_of(eex_up_magical_login_find_tabs());
    // print the tabs and their content
    echo eex_up_magical_login_get_submenu_html(eex_up_magical_login_find_tabs());
}

// Search for any functions named  eex_up_ml_() . 'admin_menu_' . [tab-name] . '_html' to construct the tab data from them.
// In other words, if you need a new tab in the admin menu, add a function and modify the posible tabs in this function as well.
function eex_up_magical_login_find_tabs()
{
    $postibleTabs = array('setting' , 'style', 'security');
    // initiate the tabs
    $tabs = array();
    try {
        if (is_array($postibleTabs)) {
            foreach ($postibleTabs as $tabName) {
                if (function_exists(eex_up_ml_(). 'admin_menu_' . $tabName . '_html')) {
                    $tabs[] =  array(
                      'name' => eex_up_ml_() . $tabName,
                      'title' => ucwords(str_replace("_", " ", $tabName)),
                      'content' => call_user_func(eex_up_ml_(). 'admin_menu_' . $tabName . '_html')
                    );
                }
            }
        }
        if ($tabs[0]) {
            $tabs[0]['default'] = true;
        }
    } catch (\Exception $e) {
    }
    return $tabs;
}
function eex_up_magical_login_the_massages($messages)
{
    try {
        $head = false;
        if (is_array($messages)) {
            foreach ($messages as $type => $array) {
                if (is_array($array)) {
                    foreach ($array as $message) {
                        if (!$head) {
                            echo '<div style="max-width: 95%;">' ;
                            $head = true;
                        }
                        echo '<div class="notice notice-'.$type.' inline"> <p><strong>'.$message.'</strong></p></div>';
                    }
                }
            }
        }
        if ($head) {
            echo '</div>';
        }
    } catch (\Exception $e) {
    }
}
function eex_up_magical_login_massages_of($tabs=array())
{
    try {
        $messages = array(
          'success' => array(),
          'error' => array(),
          'warning' => array(),
          'info' => array(),
        );
        // search for the massages
        if (is_array($tabs)) {
            foreach ($tabs as $tab) {
                if (function_exists($tab['name'] . '_massages')) {
                    $tempMessages = call_user_func($tab['name'] . '_massages');
                    if (is_array($tempMessages)) {
                        foreach ($tempMessages as $type => $array) {
                            if (is_array($array) and is_array($messages[$type])) {
                                $temp = array_merge($messages[$type], $tempMessages[$type]);
                                $messages[$type] = $temp;
                            } elseif (is_string($array) and is_array($messages[$type])) {
                                $messages[$type][] = $array;
                            }
                        }
                    }
                }
            }
        }
        // print out the massages
        eex_up_magical_login_the_massages($messages);
    } catch (\Exception $e) {
    }
}
function eex_up_magical_login_get_submenu_html($tabs=array())
{
    $ans = '<div class="wrap">';
    // add tabs links
    if (is_array($tabs) and sizeof($tabs) > 1) {
        $ans .= '<div class="'.eex_up_ml_('css').'tab">';
        foreach ($tabs as $tab) {
            $ans .= '<button class="'.eex_up_ml_('css').'tab-links' . (($tab['default']) ? ' active' : '') . '" onclick="open'.ucfirst(eex_up_ml_('func')).'(event,\''. $tab['name'] . '\')">' . $tab['title']. '</button>';
        }
        $ans .= '</div>';
    }
    // add tabs content
    if (is_array($tabs)) {
        foreach ($tabs as $tab) {
            $ans .= '<div id="'. $tab['name'].'" class="'.eex_up_ml_('css').'tab-content"' .(($tab['default']) ? ' style="display: block;"' : '') .'>';
            $ans .=  $tab['content'];
            $ans .= '</div>';
        }
    }
    return $ans . '</div>';
}
