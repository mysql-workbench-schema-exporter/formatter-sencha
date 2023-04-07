<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012 Allan Sun <sunajia@gmail.com>
 * Copyright (c) 2012-2023 Toha <tohenk@yahoo.com>
 * Copyright (c) 2013 WitteStier <development@wittestier.nl>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace MwbExporter\Formatter\Sencha\ExtJS4;

use MwbExporter\Configuration\Filename as FilenameConfiguration;
use MwbExporter\Formatter\Sencha\Configuration\ClassParent as ClassParentConfiguration;
use MwbExporter\Formatter\Sencha\Configuration\ClassPrefix as ClassPrefixConfiguration;
use MwbExporter\Formatter\Sencha\ExtJS4\Configuration\IdProperty as IdPropertyConfiguration;
use MwbExporter\Formatter\Sencha\ExtJS4\Configuration\Proxy as ProxyConfiguration;
use MwbExporter\Formatter\Sencha\ExtJS4\Configuration\Validation as ValidationConfiguration;
use MwbExporter\Formatter\Sencha\Formatter as BaseFormatter;
use MwbExporter\Model\Base;

class Formatter extends BaseFormatter
{
    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\Formatter::init()
     */
    protected function init()
    {
        parent::init();
        $this->getConfigurations()
            ->add(new ValidationConfiguration())
            ->add(new ProxyConfiguration())
            ->add(new IdPropertyConfiguration())
            ->merge([
                FilenameConfiguration::class => 'model/%entity%.%extension%',
                ClassPrefixConfiguration::class => 'App.model',
                ClassParentConfiguration::class => 'Ext.data.Model',
            ], true)
        ;
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\Formatter::createTable()
     */
    public function createTable(Base $parent, $node)
    {
        return new Model\Table($parent, $node);
    }

    /**
     * (non-PHPdoc)
     * @see \MwbExporter\Formatter\Formatter::getTitle()
     */
    public function getTitle()
    {
        return 'Sencha ExtJS4 Model';
    }

    /**
     * Get configuration scope.
     *
     * @return string
     */
    public static function getScope()
    {
        return 'Sencha ExtJS4';
    }
}
