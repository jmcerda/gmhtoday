<?php

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all envrionments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to insure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

/**
 * If there is a local settings file, then include it
 */
$local_settings = __DIR__ . "/settings.local.php";
if (file_exists($local_settings)) {
  include $local_settings;
}
$settings['install_profile'] = 'lightning';

$settings['trusted_host_patterns'] = array(
  '^www\.gmhtoday\.net$',
  '^.+\.pantheonsite\.io$',
  '^gmhtoday\.dd$'
);

$settings['twig_tweak_enable_php_filter'] = TRUE;

// <DDSETTINGS>
// Please don't edit anything between <DDSETTINGS> tags.
// This section is autogenerated by Acquia Dev Desktop.
if (isset($_SERVER['DEVDESKTOP_DRUPAL_SETTINGS_DIR']) && file_exists($_SERVER['DEVDESKTOP_DRUPAL_SETTINGS_DIR'] . '/loc_gmhtoday_dd.inc')) {
  require $_SERVER['DEVDESKTOP_DRUPAL_SETTINGS_DIR'] . '/loc_gmhtoday_dd.inc';
}
// </DDSETTINGS>
