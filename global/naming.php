<?php

// Since we need to avoid name similarity with any other function in WordPress, including other plugins, we use the same naming function throw our plugin to reduce the typing mistakes and also a higher modification speed in case of conflict with other plugins in future.
// Note: we need this function for different namings including HTML attributes, CCS classes,  plugin functions, plugin name as a plain text, etc.

if (!function_exists('eex_up_prefix')) {
    function eex_up_prefix()
    {
        return 'eex_up_';
    }
}
function eex_up_ml_name()
{
    return 'Magical Login';
}
function eex_up_ml_($pos = '')
{
    // if there is an input! do not user prefix and name directly recal this fuction insted
    if (is_string($pos)) {
        if (in_array($pos, array('css','style'))) {
            // for styles we user "-" separator
            return strtolower(str_replace("_", "-", eex_up_ml_()));
        } elseif (in_array($pos, array('func', 'fun', 'js', 'jsfun', 'jsfunc'))) {
            // for js functions we user camel standard
            return lcfirst(str_replace(" ", "", eex_up_ml_('txt')));
        } elseif (in_array($pos, array('attr', 'html-attr'))) {
            // For HTML attributes like names we user "_" separator
            return eex_up_ml_();
        } elseif (in_array($pos, array('text', 'txt'))) {
            // this is were we need the nameing on a text format but not the name itself.
            return ucwords(str_replace("_", " ", eex_up_ml_()));
        } elseif (in_array($pos, array('url'))) {
            // this is were we need the nameing on a text format but not the name itself.
            return str_replace(eex_up_prefix(), "", eex_up_ml_());
        } elseif (in_array($pos, array('db', 'database', 'option', 'opt' , 'setting' ))) {
            // the fields on the data base as options need prefixing
            $abbreviation = '';
            $string = ucwords(eex_up_ml_name());
            $words = explode(" ", "$string");
            foreach ($words as $word) {
                $abbreviation .= $word[0];
            }
            return eex_up_prefix() . strtolower($abbreviation) . '_';
        }
    }
    // if none above
    return eex_up_prefix() . str_replace(" ", "_", strtolower(eex_up_ml_name())).'_';
}
