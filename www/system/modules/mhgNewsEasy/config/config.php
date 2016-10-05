<?php
/**
 * Contao 3 Extension [mhgNewsEasy]
 *
 * Copyright (c) 2016 Medienhaus Gersöne UG | Pierre Gersöne
 *
 * @package     mhgNewsEasy
 * @link        http://www.medienhaus-gersoene.de
 * @license     propitary licence
 */

/**
 * Hooks
 */
if (!(($_GET['do'] == 'repository_manager' && $_GET['uninstall'] == 'newseasy') || (strpos($_SERVER['PHP_SELF'], 'contao/install.php') !== false))) {
    if (TL_MODE == 'BE') {
        $GLOBALS['TL_HOOKS']['parseBackendTemplate'][] = array('mhg\NewsEasy', 'addContainer');
        $GLOBALS['TL_HOOKS']['loadLanguageFile']['NewsEasyHook'] = array('mhg\NewsEasy', 'addHeadings');
        $GLOBALS['TL_HOOKS']['getUserNavigation'][] = array('mhg\NewsEasy', 'modifyUserNavigation');
        $GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('mhg\NewsEasy', 'setUser');
    }
}


/**
 * Backend form fields
 */
//$GLOBALS['BE_FFL']['checkbox_minOne'] = 'CheckBoxChooseAtLeastOne';