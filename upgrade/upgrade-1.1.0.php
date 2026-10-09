<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_1_0($module)
{
    // Keep existing exports unchanged until the new optional filter is enabled.
    if (Configuration::get('WISOEXPORT_EXCLUDE_ZERO') === false) {
        return Configuration::updateValue('WISOEXPORT_EXCLUDE_ZERO', 0);
    }
    return true;
}
