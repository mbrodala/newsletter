<?php

namespace Ecodev\Newsletter\ViewHelpers\Be;

use Ecodev\Newsletter\ViewHelpers\AbstractViewHelper;

/* * *************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Dennis Ahrens <dennis.ahrens@googlemail.com>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 * ************************************************************* */

/**
 * View helper which allows you to create ExtBase-based modules in the style of
 * TYPO3 default modules.
 * Note: This feature is experimental!
 *
 * = Examples =
 *
 * <code title="Simple">
 * {namespace newsletter=Ecodev\Newsletter\ViewHelpers}
 * <newsletter:be.container>your additional viewhelpers inside</ext:be.container>
 * </code>
 *
 * Output:
 * "your module content" wrapped with propper head & body tags.
 * Default backend CSS styles and JavaScript will be included
 *
 * <code title="All options">
 * {namespace newsletter=Ecodev\Newsletter\ViewHelpers}
 * <newsletter:be.moduleContainer pageTitle="foo">your module content</f:be.container>
 * </code>
 *
 * @author      Bastian Waidelich <bastian@typo3.org>
 * @author      Dennis Ahrens <dennis.ahrens@googlemail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class ModuleContainerViewHelper extends AbstractViewHelper
{
    /**
     * Renders start page with template.php and pageTitle.
     *
     * @param string  $pageTitle title tag of the module. Not required by default, as BE modules are shown in a frame
     * @return string
     * @see template
     * @see TYPO3\CMS\Core\Page\PageRenderer
     */
    public function render($pageTitle = '')
    {
        $doc = $this->getDocInstance();
        $this->pageRenderer->backPath = '';
        $this->pageRenderer->loadExtJS();
        $this->pageRenderer->addCssFile('sysext/t3skin/extjs/xtheme-t3skin.css');

        $this->renderChildren();

        $this->pageRenderer->enableCompressJavaScript();
        $this->pageRenderer->enableCompressCss();
        $this->pageRenderer->enableConcatenateFiles();

        $output = $doc->startPage($pageTitle);
        $output .= $this->pageRenderer->getBodyContent();
        $output .= $doc->endPage();

        return $output;
    }
}
