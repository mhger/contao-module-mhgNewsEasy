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
 * Table tl_news_archive
 */

// modify palette
$GLOBALS['TL_DCA']['tl_news_archive']['palettes']['default'] = '{title_legend},title,jumpTo;{newseasy_legend},ne_shorttitle,ne_stealth;{protected_legend:hide},protected;{comments_legend:hide},allowComments';

// add fields
$GLOBALS['TL_DCA']['tl_news_archive']['fields']['ne_shorttitle'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['ne_shorttitle'],
    'exclude' => true,
    'search' => true,
    'inputType' => 'text',
    'eval' => array( 'mandatory' => false, 'decodeEntities' => true, 'maxlength' => 20, 'tl_class' => 'w50' ),
    'sql' => "varchar(20) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_news_archive']['fields']['ne_stealth'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_news_archive']['ne_stealth'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'tl_checkbox_single_container'),
    'sql' => "char(1) NOT NULL default '0'"
);
