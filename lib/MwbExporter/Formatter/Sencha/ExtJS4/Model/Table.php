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

use MwbExporter\Formatter\Sencha\Model\Table as BaseTable;
use MwbExporter\Formatter\Sencha\ExtJS4\Formatter;
use MwbExporter\Writer\WriterInterface;

class Table extends BaseTable
{
    /**
     * Get the generate validations flag.
     * 
     * @return bool
     */
    public function generateValidation()
    {
        return $this->getDocument()->getConfig()->get(Formatter::CFG_GENERATE_VALIDATION);
    }

    public function generateProxy()
    {
        return $this->getDocument()->getConfig()->get(Formatter::CFG_GENERATE_PROXY);
    }

    public function writeTable(WriterInterface $writer)
    {
        switch (true) {
            case $this->isExternal():
                return self::WRITE_EXTERNAL;

            case $this->isManyToMany():
                return self::WRITE_M2M;

            default:
                $writer->open($this->getTableFileName());
                $this->writeBody($writer);
                $writer->close();
                return self::WRITE_OK;
        }
    }

    /**
     * Write model body code.
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS4\Model\Table
     */
    public function writeBody(WriterInterface $writer)
    {
        $writer
            ->write("Ext.define('%s', {", $this->getClassPrefix() . '.' . $this->getModelName())
            ->indent()
            ->write("extend: '%s',", $this->getParentClass())
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) {
                    $_this->writeUses($writer);

                    $_this->writeBelongsTo($writer);

                    $_this->writeHasOne($writer);

                    $_this->writeHasMany($writer);

                    $_this->getColumns()->write($writer);

                    if ($_this->generateValidation()) {
                        $_this->getColumns()->writeValidations($writer);
                    }

                    if ($_this->generateProxy()) {
                        $_this->writeAjaxProxy($writer);
                    }
                })
            ->outdent()
            ->write('});')
        ;

        return $this;
    }

    /**
     * Write uses.
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.Class-cfg-uses
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS4\Model\Table
     */
    public function writeUses(WriterInterface $writer)
    {
        $uses = array();
        $current = sprintf('%s.%s', $this->getClassPrefix(), $this->getModelName());

        // Collect belongsTo uses.
        foreach ($this->relations as $relation) {
            $refTableName = sprintf('%s.%s', $this->getClassPrefix(), $relation->getReferencedTable()->getModelName());
            if ($relation->isManyToOne() && !in_array($refTableName, $uses) && ($refTableName !== $current)) {
                $uses[] = $refTableName;
            }
        }

        // Collect hasOne uses.
        foreach ($this->relations as $relation) {
            $refTableName = sprintf('%s.%s', $this->getClassPrefix(), $relation->getReferencedTable()->getModelName());
            if (!$relation->isManyToOne() && !in_array($refTableName, $uses) && ($refTableName !== $current)) {
                $uses[] = $refTableName;
            }
        }

        // Collect hasMany uses.
        foreach ($this->getManyToManyRelations() as $relation) {
            $referencedTable = $relation['refTable'];
            $refTableName = sprintf('%s.%s', $this->getClassPrefix(), $referencedTable->getModelName());
            if (!in_array($refTableName, $uses) && ($refTableName !== $current)) {
                $uses[] = $refTableName;
            }
        }

        $usesCount = count($uses);

        if (0 === $usesCount) {
            // End, No uses found.
            return $this;
        }

        $writer
            ->write('uses: [')
            ->indent()
            ->writeCallback(function(WriterInterface $writer) use($uses, $usesCount) {
                    foreach ($uses as $use) {
                        $use = sprintf("'%s'", $use);
                        if (--$usesCount) {
                            $use .= ',';
                        }

                        $writer->write($use);
                    }
                })
            ->outdent()
            ->write('],')
        ;

        // End.
        return $this;
    }

    /**
     * Write BelongsTo relations.
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.data.association.BelongsTo
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS4\Model\Table
     */
    public function writeBelongsTo(WriterInterface $writer)
    {
        $belongToCount = $this->getBelongToCount();

        if (0 === $belongToCount) {
            // End, No belongTo relations found.
            return false;
        }

        $writer
            ->write('belongsTo: [')
            ->indent()
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) use($belongToCount) {
                    foreach ($_this->getRelations() as $relation) {
                        if (!$relation->isManyToOne()) {
                            // Do not list OneToOne relations.
                            continue;
                        }
                        $hasMore = (bool) --$belongToCount;
                        $referencedTable = $relation->getReferencedTable();
                        $relation = (string) $_this->getJSObject(array(
                                'model' => sprintf('%s.%s', $_this->getClassPrefix(), $referencedTable->getModelName()),
                                'associationKey' => lcfirst($referencedTable->getModelName()),
                                'getterName' => sprintf('get%s', $referencedTable->getModelName()),
                                'setterName' => sprintf('set%s', $referencedTable->getModelName()),
                        ));

                        if ($hasMore) {
                            $relation .= ',';
                        }

                        $writer->write($relation);
                    }
                })
            ->outdent()
            ->write('],')
        ;

        // End.
        return $this;
    }

    /**
     * Write HasOne relations.
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.data.association.HasOne
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS4\Model\Table
     */
    public function writeHasOne(WriterInterface $writer)
    {
        $hasOneCount = $this->getHasOneCount();

        if (0 === $hasOneCount) {
            // End, No HasOne relations found.
            return false;
        }

        $writer
            ->write('hasOne: [')
            ->indent()
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) use($hasOneCount) {
                    foreach ($_this->getRelations() as $relation) {
                        if ($relation->isManyToOne()) {
                            // Do not list manyToOne relations.
                            continue;
                        }
                        $hasMore = (bool) --$hasOneCount;
                        $referencedTable = $relation->getReferencedTable();
                        $relation = (string) $_this->getJSObject(array(
                                'model' => sprintf('%s.%s', $_this->getClassPrefix(), $referencedTable->getModelName()),
                                'associationKey' => lcfirst($referencedTable->getModelName()),
                                'getterName' => sprintf('get%s', $referencedTable->getModelName()),
                                'setterName' => sprintf('set%s', $referencedTable->getModelName()),
                        ));

                        if ($hasMore) {
                            $relation .= ',';
                        }

                        $writer->write($relation);
                    }
                })
            ->outdent()
            ->write('],')
        ;
        // End.
        return $this;
    }

    /**
     * Write HasMany relations.
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.data.association.HasMany
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS4\Model\Table
     */
    public function writeHasMany(WriterInterface $writer)
    {
        $hasManyCount = $this->getHasManyCount();

        if (0 === $hasManyCount) {
            // End, No HasMany relations found.
            return false;
        }

        $writer
            ->write('hasMany: [')
            ->indent()
            ->writeCallback(function(WriterInterface $writer, Table $_this = null) use($hasManyCount) {
                    foreach ($_this->getManyToManyRelations() as $relation) {
                        $referencedTable = $relation['refTable'];
                        $hasMore = (bool) --$hasManyCount;
                        $relation = (string) $_this->getJSObject(array(
                                'model' => sprintf('%s.%s', $_this->getClassPrefix(), $referencedTable->getModelName()),
                                'associationKey' => lcfirst($referencedTable->getModelName()),
                                'name' => sprintf('get%sStore', $referencedTable->getModelName()),
                        ));

                        if ($hasMore) {
                            $relation .= ',';
                        }

                        $writer->write($relation);
                    }
                })
            ->outdent()
            ->write('],')
        ;

        // End.
        return $this;
    }

    /**
     * Write model ajax proxy object.
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.data.proxy.Ajax
     * 
     * @param \MwbExporter\Writer\WriterInterface $writer
     * @return \MwbExporter\Formatter\Sencha\ExtJS4\Model\Table
     */
    public function writeAjaxProxy(WriterInterface $writer)
    {
        $writer
            ->write('proxy: ' . $this->getJSObject(array(
                    'type' => 'ajax',
                    'url' => sprintf('/data/%s', strtolower($this->getModelName())),
                    'api' => $this->getApi(),
                    'reader' => $this->getJsonReader(),
                    'writer' => $this->getJsonWriter()
            )))
        ;

        // End.
        return $this;
    }

    /**
     * Get the model API object.
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.data.proxy.Ajax-cfg-api
     * 
     * @return \MwbExporter\Helper\JSObject
     */
    private function getApi()
    {
        $modelName = strtolower($this->getModelName());

        // End.
        return $this->getJSObject(array(
                'read' => sprintf('/data/%s', $modelName),
                'update' => sprintf('/data/%s/update', $modelName),
                'create' => sprintf('/data/%s/add', $modelName),
                'destroy' => sprintf('/data/%s/destroy', $modelName)
        ));
    }

    /**
     * Get the model json reader.
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.data.reader.Json
     * 
     * @return \MwbExporter\Helper\JSObject
     */
    private function getJsonReader()
    {
        // End.
        return $this->getJSObject(array(
                'type' => 'json',
                'root' => strtolower($this->getModelName()),
                'messageProperty' => 'message'
        ));
    }

    /**
     * Get the model json writer
     * http://docs.sencha.com/ext-js/4-2/#!/api/Ext.data.writer.Json
     * 
     * @return \MwbExporter\Helper\JSObject
     */
    private function getJsonWriter()
    {
        // End.
        return $this->getJSObject(array(
                'type' => 'json',
                'root' => strtolower($this->getModelName()),
                'encode' => true,
                'expandData' => true
        ));
    }

    /**
     * Get the number of belong to relations.
     * 
     * @return int
     */
    private function getBelongToCount()
    {
        $count = 0;
        foreach ($this->relations as $relation) {
            if ($relation->isManyToOne()) {
                $count++;
            }
        }

        // End.
        return $count;
    }

    /**
     * Get the number of hasOne relations.
     * 
     * @return int
     */
    private function getHasOneCount()
    {
        $count = 0;
        foreach ($this->relations as $relation) {
            if (!$relation->isManyToOne()) {
                $count++;
            }
        }

        // End.
        return $count;
    }

    /**
     * Get the number of hasMany relations.
     * 
     * @return int
     */
    private function getHasManyCount()
    {
        // End.
        return count($this->manyToManyRelations);
    }
}