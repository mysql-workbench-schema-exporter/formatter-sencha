<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012 Allan Sun <sunajia@gmail.com>
 * Copyright (c) 2012-2013 Toha <tohenk@yahoo.com>
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

namespace MwbExporter\Formatter\Sencha\ExtJS4\Model;

use MwbExporter\Model\Columns as BaseColumns;
use MwbExporter\Writer\WriterInterface;

class Columns extends BaseColumns
{
    /**
     * Write model fields.
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS4\Model\Columns
     */
    public function write(WriterInterface $writer)
    {
        $table = $this->getParent();

        $writer
            ->write('fields: [')
            ->indent()
            ->writeCallback(function(WriterInterface $writer, Columns $_this = null) {
                    $columns = $_this->getColumns();
                    $columnsCount = count($columns);

                    foreach ($columns as $column) {
                        $hasMore = (bool) --$columnsCount;
                        $column->write($writer, $hasMore);
                    }
                })
            ->outdent();

        if ($table->generateValidation() || $table->generateProxy()) {
            $writer->write('],');
        } else {
            $writer->write(']');
        }

        return $this;
    }

    /**
     * Write model validations.
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     */
    public function writeValidations(WriterInterface $writer)
    {
        $writer
            ->write('validations: [')
            ->indent()
            ->writeCallback(function(WriterInterface $writer, Columns $_this = null) {
                    $columns = $_this->getColumns();
                    $columnsCount = count($columns);

                    foreach ($columns as $column) {
                        $hasMore = (bool) --$columnsCount;
                        $column->writeValidation($writer, $hasMore);
                    }
                })
            ->outdent()
            ->write('],')
        ;
    }
}