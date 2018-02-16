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

namespace mhg;


/**
 * class mhg\NewsEasy
 */
class NewsEasy extends \Contao\Backend {

    /**
     * Is NewsEasy enabled
     * @var     bool
     */
    protected $blnNewsEasyEnabled = true;

    /**
     * Initialize the object, import the user class and the tl_theme lang file
     */
    public function __construct() {
        $this->import('BackendUser', 'User');
        parent::__construct();

        if (!$this->User->hasAccess('news') || $this->User->newsEasyEnable != 1) {
            $this->blnNewsEasyEnabled = false;
        }
    }

    /**
     * Add CSS and Javascript
     * 
     * @param   string
     * @return  boolean
     */
    public function loadLanguageFileHook($strName, $strLanguage) {
        if (!$this->blnNewsEasyEnabled) {
            return false;
        }

        if ($this->User->newsEasyEnable == 1) {
            $GLOBALS['TL_CSS'][] = 'system/modules/mhgNewsEasy/assets/css/backend.css?v=' . time() . '|screen';
            $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/mhgNewsEasy/assets/js/backend.js?v=' . time();

            \System::loadLanguageFile('tl_news_archive');
        }

        // make sure the hook is only executed once
        unset($GLOBALS['TL_HOOKS']['loadLanguageFile']['NewsEasyHook']);

        return true;
    }

    /**
     * Add the container
     * 
     * @param   string
     * @param   string
     * @return  string
     */
    public function parseBackendTemplate($strContent, $strTemplate) {
        if (!$this->blnNewsEasyEnabled) {
            return $strContent;
        }

        if ($strTemplate == 'be_main') {
            $strContent = str_replace('<div id="container">', '<div id="container">' . "\r\n" . $this->generateContainerContent(), $strContent);
        }

        return $strContent;
    }

    /**
     * Generate the container content
     * @return string
     */
    protected function generateContainerContent() {
        $arrArchives = $this->getArchives();

        if (empty($arrArchives) || !is_array($arrArchives)) {
            return '';
        }

        $objTemplate = new \BackendTemplate('be_newseasy');
        $objTemplate->mode = $this->User->newsEasyMode;
        $objTemplate->class = 'newseasy_level_2';
        $objTemplate->archives = $arrArchives;
        $strReturn = $objTemplate->parse();

        return $strReturn;
    }

    /**
     * Set the GET-Param for the user id so the subpalette can work
     * 
     * @param   string
     * @return  void
     */
    public function loadDataContainerHook($strTable) {
        if ($strTable == 'tl_user' && \Input::get('do') == 'login') {
            \Input::setGet('id', \BackendUser::getInstance()->id);
        }
    }

    /**
     * Prepares an array for the backend navigation
     * 
     * @param   boolean
     * @return  array
     */
    protected function getArchives() {
        $arrArchives = array();

        /* get all news archives */
        $objArchives = \Database::getInstance()
                ->prepare("SELECT id, title, newsEasyTitle FROM tl_news_archive WHERE newsEasyHide<>1 AND title <> '' ORDER BY title ASC")
                ->execute();

        while ($objArchives->next()) {
            $strKey = 'newsArchive' . $objArchives->id;
            $arrArchives[$strKey] = array(
                'title' => $objArchives->title,
                'label' => empty($objArchives->newsEasyTitle) ? $objArchives->title : $objArchives->newsEasyTitle,
                'href' => \Environment::get('script') . '?do=news&amp;table=tl_news&amp;id=' . $objArchives->id . '&amp;rt=' . REQUEST_TOKEN,
                'class' => 'navigation newsArchive'
            );
        }

        return $arrArchives;
    }

    /**
     * Modifies the user navigation
     * 
     * @param   array the modules
     * @param   boolean show all
     * @return  array
     */
    public function getUserNavigationHook($arrModules, $blnShowAll) {
        if (!$this->blnNewsEasyEnabled) {
            return $arrModules;
        }

        // if not backend_mode, get out
        if ($this->User->newsEasyMode != 'mod') {
            // add some CSS classes to the content module
            $arrModules['content']['class'].= ' newseasy_toggle' .
                    ($arrModules['content']['icon'] == 'modPlus.gif' ? ' newseasy_collapsed' : ' newseasy_expanded');

            return $arrModules;
        }

        // get the news archive. if empty, return standard
        $arrArchives = $this->getArchives();
        if (empty($arrArchives) || !is_array($arrArchives)) {
            return $arrModules;
        }

        $session = $this->Session->getData();
        $isHidden = isset($session['backend_modules']['newsArchives']) && $session['backend_modules']['newsArchives'] < 1;

        $arrNavigation = array(
            'newsArchives' => array(
                'icon' => $isHidden ? 'modPlus.gif' : 'modMinus.gif',
                'title' => $isHidden ? $GLOBALS['TL_LANG']['MSC']['expandNode'] : $GLOBALS['TL_LANG']['MSC']['collapseNode'],
                'label' => $GLOBALS['TL_LANG']['tl_news_archive']['newsArchives'],
                'href' => $this->addToUrl('mtg=newsArchives'),
                'modules' => $isHidden ? false : $arrArchives
            )
        );

        // Insert at a given position if reference is given OR prepend
        if ($this->User->newsEasyReference) {
            $intPosition = array_search(\BackendUser::getInstance()->newsEasyReference, array_keys($arrModules));
            $intPosition++;
            array_insert($arrModules, $intPosition, $arrNavigation);

            $arrReturn = $arrModules;
        } else {
            $arrReturn = array_merge($arrNavigation, $arrModules);
        }

        return $arrReturn;
    }
}
