<?php
function eex_up_magical_login_option_title($title)
{
    $ans = '';
    try {
        $temp = array();
        $temp['title'] = esc_html($title);
        if (is_string($temp['title']) and !empty($temp['title'])) {
            $ans = '<div class="'.eex_up_ml_('css').'option-title"><strong>'.$temp['title'].'</strong></div>';
        } else {
            $ans = '<div class="'.eex_up_ml_('css').'option-title"><strong></strong></div>';
        }
    } catch (\Exception $e) {
    }
    return $ans;
}
function eex_up_magical_login_option_subtitle($subtitle, $for = '')
{
    $ans = '';
    try {
        $temp = array();
        $temp['for'] = preg_replace('/\s+/', '', esc_html($for));
        if (is_string($temp['for']) and !empty($temp['for'])) {
            $ans = '<label class="'.eex_up_ml_('css').'option-subtitle" for="'.eex_up_ml_().$temp['for'].'">';
        }
        $temp['subtitle'] = esc_html($subtitle);
        if (is_string($temp['subtitle']) and !empty($temp['subtitle'])) {
            $ans .=  $temp['subtitle'] . '</label>';
        } else {
            $ans .=  '</label>';
        }
    } catch (\Exception $e) {
    }
    return $ans;
}
function eex_up_magical_login_option_checkbox($name, $value = '', $checked = false, $lable = '', $break = 1)
{
    $ans = '';
    try {
        $temp = array();
        $temp['name'] = preg_replace('/\s+/', '', esc_attr($name));
        if (is_string($temp['name']) and !empty($temp['name'])) {
            $ans = '<input type="checkbox" name="' . eex_up_ml_(). $temp['name']. '"';
        } else {
            return $ans;
        }
        $temp['value'] = preg_replace('/\s+/', '', esc_attr($value));
        if (is_string($temp['value']) and !empty($temp['value'])) {
            $ans .= ' value="' . $temp['value']. '"';
        }
        if ($checked === true) {
            $ans .= ' checked';
        }
        $ans .= '>';
        $temp['lable'] = $lable;
        if (is_string($temp['lable']) and !empty($temp['lable'])) {
            $ans .= '<label for="' . eex_up_ml_(). $temp['name'] .'">'.$temp['lable'].'</label>';
        }
        $temp['br'] = (int)$break;
        if (is_numeric($temp['br']) and $temp['br'] > 0) {
            for ($itr=0; $itr < $temp['br'] ; $itr++) {
                $ans .= '<br>';
            }
        }
    } catch (\Exception $e) {
    }
    return $ans;
}
function eex_up_magical_login_option_submit($name, $value = '', $break = 1, $tabindex = -1)
{
    $ans = '';
    try {
        $temp = array();
        $temp['name'] = preg_replace('/\s+/', '', esc_attr($name));
        if (is_string($temp['name']) and !empty($temp['name'])) {
            $ans = '<input type="submit" class="button button-primary" name="' . eex_up_ml_(). $temp['name']. '"';
        } else {
            return $ans;
        }
        $temp['tabindex'] = esc_attr($tabindex);
        if (is_numeric($temp['tabindex'])) {
            $ans .= ' tabindex="' . $temp['tabindex']. '"';
        }
        $temp['value'] = esc_attr($value);
        if (is_string($temp['value']) and !empty($temp['value'])) {
            $ans .= ' value="' . $temp['value']. '"';
        }
        $ans .= '">';
        $temp['br'] = (int)$break;
        if (is_numeric($temp['br']) and $temp['br'] > 0) {
            for ($itr=0; $itr < $temp['br'] ; $itr++) {
                $ans .= '<br>';
            }
        }
    } catch (\Exception $e) {
    }
    return $ans;
}
function eex_up_magical_login_option_text($name, $value = '', $placeholder = '', $size = 80, $break = 1)
{
    $ans = '';
    try {
        $temp = array();
        $temp['name'] = preg_replace('/\s+/', '', esc_attr($name));
        if (is_string($temp['name']) and !empty($temp['name'])) {
            $ans = '<input type="text" name="' . eex_up_ml_(). $temp['name']. '"';
        } else {
            return $ans;
        }
        $temp['value'] = esc_attr($value);
        if (is_string($temp['value']) and !empty($temp['value'])) {
            $ans .= ' value="' . $temp['value']. '"';
        }
        $temp['placeholder'] = esc_attr($placeholder);
        if (is_string($temp['placeholder']) and !empty($temp['placeholder'])) {
            $ans .= ' placeholder="' . $temp['placeholder']. '"';
        }
        $temp['size'] = (int)preg_replace('/\s+/', '', esc_attr($size));
        if (is_numeric($temp['size']) and $temp['size'] > 0) {
            $ans .= ' size="' . $temp['size']. '"';
        }
        $ans .= '>';
        $temp['br'] = (int)$break;
        if (is_numeric($break) and $break > 0) {
            for ($itr=0; $itr < $break ; $itr++) {
                $ans .= '<br>';
            }
        }
    } catch (\Exception $e) {
    }
    return $ans;
}
function eex_up_magical_login_option_textarea($name, $value = '', $placeholder = '', $cols = 79, $rows = 8, $break = 1)
{
    $ans = '';
    try {
        $temp = array();
        $temp['name'] = preg_replace('/\s+/', '', esc_attr($name));
        if (is_string($temp['name']) and !empty($temp['name'])) {
            $ans = '<textarea class="wp-editor-area" autocomplete="off" name="' . eex_up_ml_(). $temp['name']. '"';
        } else {
            return $ans;
        }
        $temp['placeholder'] = esc_attr($placeholder);
        if (is_string($temp['placeholder']) and !empty($temp['placeholder'])) {
            $ans .= 'placeholder="' . $temp['placeholder']. '"';
        }
        $temp['cols'] = (int)preg_replace('/\s+/', '', esc_attr($cols));
        if (is_numeric($temp['cols']) and $temp['cols'] > 0) {
            $ans .= ' cols="' . $temp['cols']. '"';
        }
        $temp['rows'] = (int)preg_replace('/\s+/', '', esc_attr($rows));
        if (is_numeric($temp['rows']) and $temp['rows'] > 0) {
            $ans .= ' rows="' . $temp['rows']. '"';
        }
        $temp['value'] = esc_attr($value);
        if (is_string($temp['value']) and !empty($temp['value'])) {
            $ans .= '>' . $temp['value'] ;
        } else {
            $ans .= '>';
        }
        $ans .= '</textarea>';
        $temp['br'] = (int)$break;
        if (is_numeric($break) and $break > 0) {
            for ($itr=0; $itr < $break ; $itr++) {
                $ans .= '<br>';
            }
        }
    } catch (\Exception $e) {
    }
    return $ans;
}
