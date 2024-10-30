<?php
add_action('admin_footer_text', eex_up_ml_() . 'our_foother');

if (!function_exists(eex_up_ml_() . 'our_foother')) {
    function eex_up_magical_login_our_foother($arg)
    {
        global $pagenow;
        $urlPageValue = sanitize_url($_GET['page']);
        if ($pagenow == 'options-general.php' and stripos($urlPageValue, eex_up_prefix()) !== false) {
            return '<p><a href="http://www.umbrella-plan.com" target="_blank">Umbrella Plan Team</a> appreciates your trust. Find more about their products <a href="https://plugin.umbrella-plan.com" target="_blank" >here</a>.</p>';
        }
        return $arg;
    }
}
