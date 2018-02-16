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
 * Register the classes
 */
ClassLoader::addClasses(array(
    // Classes
    'mhg\NewsEasy' => 'system/modules/mhgNewsEasy/classes/NewsEasy.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array(
    // Backend
    'be_newseasy' => 'system/modules/mhgNewsEasy/templates/backend',
));
