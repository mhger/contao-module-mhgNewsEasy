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
 * Register the namespaces
 */
ClassLoader::addNamespace('mhg');

/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    'mhg\NewsEasy' => 'system/modules/mhgNewsEasy/classes/NewsEasy.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'be_newseasy' => 'system/modules/mhgNewsEasy/templates',
));
