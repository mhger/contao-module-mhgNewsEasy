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
 * Table tl_user
 */
// modify palette
$GLOBALS['TL_DCA']['tl_user']['config']['onload_callback'][] = array('tl_user_newseasy', 'buildPalette');


// add fields
$GLOBALS['TL_DCA']['tl_user']['fields']['ne_enable'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_user']['ne_enable'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('submitOnChange' => true, 'tl_class' => 'tl_checkbox_single_container'),
    'sql' => 'char(1) NOT NULL default ""'
);
$GLOBALS['TL_DCA']['tl_user']['fields']['ne_mode'] = array
    (
    'label' => &$GLOBALS['TL_LANG']['tl_user']['ne_mode'],
    'exclude' => true,
    'inputType' => 'select',
    'options' => array('inject', 'be_mod'),
    'reference' => &$GLOBALS['TL_LANG']['tl_user'],
    'eval' => array('tl_class' => 'clr', 'submitOnChange' => true),
    'sql' => 'varchar(32) NOT NULL default "inject" '
);

$GLOBALS['TL_DCA']['tl_user']['fields']['ne_bemodRef'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_user']['ne_bemodRef'],
    'exclude' => true,
    'inputType' => 'select',
    'options' => array_keys($GLOBALS['BE_MOD']),
    'reference' => &$GLOBALS['TL_LANG']['MOD'],
    'eval' => array('tl_class' => 'clr', 'includeBlankOption' => true),
    'sql' => 'varchar(32) NOT NULL default ""'
);
$GLOBALS['TL_DCA']['tl_user']['fields']['ne_short'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_user']['ne_short'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'clr'),
    'sql' => 'char(1) NOT NULL default ""'
);

class tl_user_newseasy extends Backend {

    /**
     * Build the palette string
     * @param DataContainer
     */
    public function buildPalette(DataContainer $dc) {
        $objUser = \Database::getInstance()->prepare('SELECT * FROM tl_user WHERE id=?')->execute($dc->id);

        foreach ($GLOBALS['TL_DCA']['tl_user']['palettes'] as $palette => $v) {
            if ($palette == '__selector__') {
                continue;
            }

            if (BackendUser::getInstance()->hasAccess('themes', 'modules')) {
                $arrPalettes = explode(';', $v);
                $arrPalettes[] = '{ne_legend},ne_enable;';
                $GLOBALS['TL_DCA']['tl_user']['palettes'][$palette] = implode(';', $arrPalettes);
            }
        }

        // extend selector
        $GLOBALS['TL_DCA']['tl_user']['palettes']['__selector__'][] = 'ne_enable';

        // extend subpalettes
        $strSubpalette = 'ne_mode';

        if ($objUser->ne_mode == 'be_mod') {
            $strSubpalette .= ',ne_bemodRef,ne_short';
        }
        $GLOBALS['TL_DCA']['tl_user']['subpalettes']['ne_enable'] = $strSubpalette;
    }
}