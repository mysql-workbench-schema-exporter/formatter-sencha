<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012 Allan Sun <sunajia@gmail.com>
 * Copyright (c) 2012-2024 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Formatter\Sencha\ExtJS3\Model;

use MwbExporter\Configuration\Comment as CommentConfiguration;
use MwbExporter\Configuration\Header as HeaderConfiguration;
use MwbExporter\Formatter\Sencha\Model\Table as BaseTable;
use MwbExporter\Helper\ZendURLFormatter;
use MwbExporter\Writer\WriterInterface;

class Table extends BaseTable
{
    public function writeTable(WriterInterface $writer)
    {
        if (!$this->isExternal()) {
            $writer
                ->open($this->getTableFileName())
                ->writeCallback(function(WriterInterface $writer, ?Table $_this = null) {
                    /** @var \MwbExporter\Configuration\Header $header */
                    $header = $this->getConfig(HeaderConfiguration::class);
                    if ($content = $header->getHeader()) {
                        $writer
                            ->writeComment($content)
                            ->write('')
                        ;
                    }
                    if ($_this->getConfig(CommentConfiguration::class)->getValue()) {
                        if ($content = $_this->getFormatter()->getComment(null)) {
                            $writer
                                ->writeComment($content)
                                ->write('')
                            ;
                        }
                    }
                })
                ->write('%s.%s = Ext.extend(%s, %s);', $this->getClassPrefix(), $this->getModelName(), $this->getParentClass(), $this->asModel())
                ->write('')
                ->write('%1$s.%2$s = Ext.extend(%1$s.%2$s, %3$s);', $this->getClassPrefix(), $this->getModelName(), $this->asUI())
                ->write('')
                ->close()
            ;

            return self::WRITE_OK;
        }

        return self::WRITE_EXTERNAL;
    }

    public function asModel()
    {
        $fields = [];
        foreach ($this->getColumns() as $column) {
            $fields[] = $column->asField();
        }

        return $this->getJSObject([
            'id' => $this->getModelName(),
            'url' => ZendURLFormatter::fromCamelCaseToDashConnection($this->getModelName()),
            'title' => str_replace('-', ' ', ZendURLFormatter::fromCamelCaseToDashConnection($this->getModelName())),
            'fields' => $fields,
        ]);
    }

    public function asUI()
    {
        $columns = [];
        $forms = [];
        foreach ($this->getColumns() as $column) {
            $columns[] = $column->asColumn();
            $forms[] = $column->asFormItem();
        }

        return $this->getJSObject([
            'columns' => $columns,
            'formItems' => [
                'title' => 'Basic Details',
                'layout' => 'form',
                'items' => $forms,
            ],
        ]);
    }
}
