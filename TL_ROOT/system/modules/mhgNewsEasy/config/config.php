<?php
/**
 * Contao 3 Extension [mhgNewsEasy]
 *
 * Copyright (c) 2018 Medienhaus Gersöne UG (haftungsbeschränkt) | Pierre Gersöne
 *
 * @package     mhgNewsEasy
 * @author      Pierre Gersöne <mail@medienhaus-gersoene.de>
 * @link        https://www.medienhaus-gersoene.de Medienhaus Gersöne - Agentur für Neue Medien: Web, Design & Marketing
 * @license     LGPL-3.0+
 */
/**
 * Register backend hooks
 */
if (TL_MODE == 'BE') {
    if (!(($_GET['do'] == 'repository_manager' && $_GET['uninstall'] == 'newseasy') ||
            (strpos($_SERVER['PHP_SELF'], 'contao/install.php') !== false))) {

        $GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = array('mhg\NewsEasy', 'parseBackendTemplate');
        $GLOBALS['TL_HOOKS']['loadLanguageFile']['NewsEasyHook'] = array('mhg\NewsEasy', 'loadLanguageFileHook');
        $GLOBALS['TL_HOOKS']['getUserNavigation'][] = array('mhg\NewsEasy', 'getUserNavigationHook');
        $GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('mhg\NewsEasy', 'loadDataContainerHook');
    }
}