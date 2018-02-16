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
 * Modify DCA palette
 */
$GLOBALS['TL_DCA']['tl_user']['config']['onload_callback'][] = array('tl_user_newseasy', 'buildPalette');


/**
 * add DCA fields
 */
mhg\Dca::addField('tl_user', 'newsEasyEnable', array(
    'label' => &$GLOBALS['TL_LANG']['tl_user']['newsEasyEnable'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('submitOnChange' => true, 'tl_class' => 'tl_checkbox_single_container'),
    'sql' => "char(1) NOT NULL default '0'"
));

mhg\Dca::addField('tl_user', 'newsEasyMode', array(
    'label' => &$GLOBALS['TL_LANG']['tl_user']['newsEasyMode'],
    'exclude' => true,
    'inputType' => 'select',
    'options' => array('inject', 'mod'),
    'reference' => &$GLOBALS['TL_LANG']['tl_user']['newsEasyModes'],
    'eval' => array('tl_class' => 'clr', 'submitOnChange' => true),
    'sql' => "varchar(32) NOT NULL default 'inject'"
));

mhg\Dca::addField('tl_user', 'newsEasyReference', array(
    'label' => &$GLOBALS['TL_LANG']['tl_user']['newsEasyReference'],
    'exclude' => true,
    'inputType' => 'select',
    'options' => array_keys($GLOBALS['BE_MOD']),
    'reference' => &$GLOBALS['TL_LANG']['MOD'],
    'eval' => array('tl_class' => 'clr', 'includeBlankOption' => true),
    'sql' => "varchar(32) NOT NULL default ''"
));


/**
 * Class tl_user_newseasy
 */
class tl_user_newseasy extends Backend {

    /**
     * Build the palette string
     * @param DataContainer
     */
    public function buildPalette(DataContainer $dc) {
        $objUser = \Database::getInstance()->prepare('SELECT * FROM tl_user WHERE id=?')
                ->execute($dc->id);

        foreach ($GLOBALS['TL_DCA']['tl_user']['palettes'] as $palette => $v) {
            if ($palette == '__selector__') {
                continue;
            }

            if (BackendUser::getInstance()->hasAccess('create', 'newp')) {
                $arrPalettes = explode(';', $v);
                $arrPalettes[] = '{newsEasy_legend},newsEasyEnable;';
                $GLOBALS['TL_DCA']['tl_user']['palettes'][$palette] = implode(';', $arrPalettes);
            }
        }

        // extend selector
        $GLOBALS['TL_DCA']['tl_user']['palettes']['__selector__'][] = 'newsEasyEnable';

        // extend subpalettes
        $strSubpalette = 'newsEasyMode';

        if ($objUser->newsEasyMode == 'mod') {
            $strSubpalette .= ',newsEasyReference';
        }
        $GLOBALS['TL_DCA']['tl_user']['subpalettes']['newsEasyEnable'] = $strSubpalette;
    }
}
