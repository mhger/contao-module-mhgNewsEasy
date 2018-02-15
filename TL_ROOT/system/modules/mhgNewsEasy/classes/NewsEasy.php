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

namespace mhg;

class NewsEasy extends \Contao\Backend {

    /**
     * Load NewsEasy
     * @var bool
     */
    protected $blnLoadNE = true;
    protected $userMode = NULL;
    protected $objUser = NULL;

    /**
     * Initialize the object, import the user class and the tl_theme lang file
     */
    public function __construct() {
        parent::__construct();
        $this->objUser = \BackendUser::getInstance();

        // we never need to do anything at all if the user has 
        // - no admin access, - module is disabled
        if (!$this->objUser->admin || $this->objUser->ne_enable != 1) {
            $this->blnLoadNE = false;
        }
    }

    /**
     * Add CSS and Javascript
     * @param string
     * @return boolean
     */
    public function addHeadings($strName, $strLanguage) {
        if (!$this->blnLoadNE) {
            return false;
        }

        if (\BackendUser::getInstance()->ne_enable == 1) {
            $GLOBALS['TL_CSS'][] = 'system/modules/mhgNewsEasy/assets/newseasy.css|screen';
            $GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/mhgNewsEasy/assets/newseasy.js';
        }

        // make sure the hook is only executed once
        unset($GLOBALS['TL_HOOKS']['loadLanguageFile']['NewsEasyHook']);

        return false;
    }

    /**
     * Add the container
     * @param string
     * @param string
     * @return string
     */
    public function addContainer($strContent, $strTemplate) {
        if (!$this->blnLoadNE) {
            return $strContent;
        }

        if ($strTemplate == 'be_main') {
            $strContent = str_replace('<div id="container">', '<div id="container">' . "\n" . $this->generateContainerContent(), $strContent);
        }

        return $strContent;
    }

    /**
     * Generate the container content
     * @return string
     */
    protected function generateContainerContent() {
        /* generate template content */
        $arrNavArray = $this->prepareBackendNavigationArray();

        if (!$arrNavArray) {
            return '';
        }

        $objTemplate = new \BackendTemplate('be_newseasy');
        $objTemplate->mode = $this->objUser->ne_mode;
        $objTemplate->class = 'newseasy_' . $this->objUser->ne_mode;
        $objTemplate->newsarchives = $arrNavArray;

        return $objTemplate->parse();
    }

    /**
     * Set the GET-Param for the user id so the subpalette can work
     * @param string
     */
    public function setUser($strTable) {
        if ($strTable == 'tl_user' && \Input::get('do') == 'login') {
            \Input::setGet('id', \BackendUser::getInstance()->id);
        }
    }

    /**
     * Prepares an array for the backend navigation
     * @param boolean
     * @return array|false
     */
    protected function prepareBackendNavigationArray() {
        $arrNewsArchives = array();

        /* get all news archives */
        $objNewsArchives = \Database::getInstance()->prepare('SELECT id, title, ne_shorttitle FROM tl_news_archive WHERE ne_stealth <> 1 AND title <> "" ORDER BY ne_shorttitle ASC')->execute();
        while ($objNewsArchives->next()) {
            $arrNewsArchives[$objNewsArchives->id]['archiveTitle'] = !empty($objNewsArchives->ne_shorttitle) ? $objNewsArchives->ne_shorttitle : $objNewsArchives->title;
            $arrNewsArchives[$objNewsArchives->id]['archiveHref'] = \Environment::get('script') . '?do=news&amp;table=tl_news&amp;id=' . $objNewsArchives->id . '&amp;rt=' . REQUEST_TOKEN;
        }

        if (empty($arrNewsArchives)) {
            return false;
        }

        /* get sorting of the backend newsarchive */
        $session = $this->Session->getData();
        $sorting = $session['sorting']['tl_news'] ? $session['sorting']['tl_news'] : 'tstamp';


        if ($sorting == 'author') {
            $sorting = 'author, date DESC';
        }

        /* get content of every archive */
        foreach ($arrNewsArchives as $newsArchiveId => $newsArchiveTitle) {
            $arrNews = array();
            $intNewsArchiveId = (int) $newsArchiveId;
            $objNews = \Database::getInstance()->prepare('SELECT id, headline FROM tl_news WHERE pid=? ORDER BY ' . $sorting)->execute($intNewsArchiveId);
            while ($objNews->next()) {
                $arrNews[$objNews->id]['newsHeadline'] = $objNews->headline;
                $arrNews[$objNews->id]['newsHref'] = \Environment::get('script') . '?do=news&amp;table=tl_content&amp;id=' . $objNews->id . '&amp;rt=' . REQUEST_TOKEN;
            }

            if (empty($arrNews) && $this->objUser->ne_mode == 'be_mod' && !$this->objUser->ne_short) {
                unset($arrNewsArchives[$newsArchiveId]);
            } else {
                $arrNewsArchives[$newsArchiveId]['news'] = $arrNews;
            }
        }

        return $arrNewsArchives;
    }

    /**
     * Modifies the user navigation
     * @param array the modules
     * @param boolean show all
     * @return array
     */
    public function modifyUserNavigation($arrModules, $blnShowAll) {
        if (!$this->blnLoadNE) {
            return $arrModules;
        }

        // add some CSS classes to the design module
        $strClass = 'newseasy_toggle ';
        $strClass .= ($arrModules['content']['modules']['news']['icon'] == 'modPlus.gif') ? 'newseasy_collapsed' : 'newseasy_expanded';
        $arrModules['content']['modules']['news']['class'] = ' ' . trim($arrModules['content']['modules']['news']['class']) . ((trim($arrModules['content']['modules']['news']['class'])) ? ' ' : '') . $strClass;

        // if not backend_mode, git out
        if ($this->objUser->ne_mode != 'be_mod') {
            return $arrModules;
        }

        //get the news archive. if empty, return standard
        $arrNewsArchive = $this->prepareBackendNavigationArray(true);
        if (!is_array($arrNewsArchive) || empty($arrNewsArchive)) {
            return $arrModules;
        }

        $session = $this->Session->getData();
        $arrNewsNavigation = array();

        /* short list */
        if ($this->objUser->ne_short == 1) {
            $arrNewsNavigation['headliner']['icon'] = 'modMinus.gif';
            $arrNewsNavigation['headliner']['title'] = 'Nachrichtenarchive';
            $arrNewsNavigation['headliner']['label'] = 'Nachrichtenarchive';


            foreach ($arrNewsArchive as $intNewsArchiveId => $newsArchiveContent) {
                $strArchiveKey = 'newsArchive_' . $intNewsArchiveId;
                $href = $newsArchiveContent['archiveHref'];

                if (!$blnShowAll && isset($session['backend_modules'][$strKey]) && $session['backend_modules'][$strKey] < 1) {
                    $arrNewsNavigation['headliner']['modules'] = false;
                    $arrNewsNavigation[$strKey]['icon'] = 'modPlus.gif';
                    $arrNewsNavigation[$strKey]['title'] = specialchars($GLOBALS['TL_LANG']['MSC']['expandNode']);
                    continue;
                }

                $arrNewsNavigation['headliner']['modules'][$strArchiveKey] = [
                    'title' => specialchars($newsArchiveContent['archiveTitle']),
                    'href' => $href,
					'class' => 'navigation newseasy',					
                    'icon' => $img,
                    'label' => specialchars($newsArchiveContent['archiveTitle'])
                ];
            }

            /* extra long collapsible */
        } else {

            foreach ($arrNewsArchive as $intNewsArchiveId => $newsArchiveContent) {
                $strKey = 'newsArchive_' . $intNewsArchiveId;

                $arrNewsNavigation[$strKey]['icon'] = is_array($newsArchiveContent['news']) ? 'modMinus.gif' : '';
                $arrNewsNavigation[$strKey]['title'] = specialchars($newsArchiveContent['archiveTitle']);
                $arrNewsNavigation[$strKey]['label'] = specialchars($newsArchiveContent['archiveTitle']);
                $arrNewsNavigation[$strKey]['href'] = $newsArchiveContent['archiveHref'];

                // Do not show the news if the group is closed
                if (!$blnShowAll && isset($session['backend_modules'][$strKey]) && $session['backend_modules'][$strKey] < 1) {
                    $arrNewsNavigation[$strKey]['news'] = false;
                    $arrNewsNavigation[$strKey]['icon'] = 'modPlus.gif';
                    $arrNewsNavigation[$strKey]['title'] = specialchars($GLOBALS['TL_LANG']['MSC']['expandNode']);
                    continue;
                }

                // now the news modules
                if (is_array($newsArchiveContent['news']) && count($newsArchiveContent['news'])) {
                    foreach ($newsArchiveContent['news'] as $strNewsId => $arrNews) {
                        $title = $arrNews['newsHeadline'];
                        $label = strlen($arrNews['newsHeadline']) > 23 ? substr($arrNews['newsHeadline'], 0, 20) . '...' : $arrNews['newsHeadline'];
                        $href = $arrNews['newsHref'];
                        $img = 'system/themes/default/images/edit.gif';

                        $arrNewsNavigation[$strKey]['modules'][$strNewsId] = [
                            'title' => $title,
                            'href' => $href,
							'class' => 'navigation newseasy',
                            'icon' => $img,
                            'label' => $label
                        ];
                    }
                } else {
                    //make an empty array (otherwise the menu will show an error on open)
                    $arrNewsNavigation[$strKey]['modules'] = [];
                }
            }
        }

        /* return navigation */
        if ($this->objUser->ne_bemodRef) {
            /* if a reference is given */
            $intPosition = array_search(\BackendUser::getInstance()->ne_bemodRef, array_keys($arrModules));
            $intPosition++;
            array_insert($arrModules, $intPosition, $arrNewsNavigation);
            return $arrModules;
        }
        /* if no refernce is given to the top */
        return array_merge($arrNewsNavigation, $arrModules);
    }

}