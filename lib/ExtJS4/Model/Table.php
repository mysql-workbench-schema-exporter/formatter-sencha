<?php

/*
 * The MIT License
 *
 * Copyright (c) 2012 Allan Sun <sunajia@gmail.com>
 * Copyright (c) 2012-2024 Toha <tohenk@yahoo.com>
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

use MwbExporter\Configuration\Comment as CommentConfiguration;
use MwbExporter\Configuration\Header as HeaderConfiguration;
use MwbExporter\Configuration\M2MSkip as M2MSkipConfiguration;
use MwbExporter\Formatter\Sencha\ExtJS4\Configuration\IdProperty as IdPropertyConfiguration;
use MwbExporter\Formatter\Sencha\ExtJS4\Configuration\Proxy as ProxyConfiguration;
use MwbExporter\Formatter\Sencha\ExtJS4\Configuration\Validation as ValidationConfiguration;
use MwbExporter\Formatter\Sencha\Model\Table as BaseTable;
use MwbExporter\Writer\WriterInterface;

class Table extends BaseTable
{
    public function writeTable(WriterInterface $writer)
    {
        switch (true) {
            case $this->isExternal():
                return self::WRITE_EXTERNAL;
            case $this->getConfig(M2MSkipConfiguration::class)->getValue() && $this->isManyToMany():
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
            ->write("Ext.define('%s', %s);", $this->getClassPrefix().'.'.$this->getModelName(), $this->asModel())
        ;

        return $this;
    }

    public function asModel()
    {
        $result = ['extend' => $this->getParentClass()];

        if ($this->getConfig(IdPropertyConfiguration::class)->getValue()) {
            $primaryKeyColumnName = $this->getPrimaryKey();
            if ($primaryKeyColumnName) {
                $result['idProperty'] = $primaryKeyColumnName;
            }
        }

        if (count($data = $this->getUses())) {
            $result['uses'] = $data;
        }
        if (count($data = $this->getBelongsTo())) {
            $result['belongsTo'] = $data;
        }
        if (count($data = $this->getHasOne())) {
            $result['hasOne'] = $data;
        }
        if (count($data = $this->getHasMany())) {
            $result['hasMany'] = $data;
        }
        if (count($data = $this->getFields())) {
            $result['fields'] = $data;
        }
        if ($this->getConfig(ValidationConfiguration::class)->getValue() && count($data = $this->getValidations())) {
            $result['validations'] = $data;
        }
        if ($this->getConfig(ProxyConfiguration::class)->getValue() && count($data = $this->getAjaxProxy())) {
            $result['proxy'] = $data;
        }

        return $this->getJSObject($result);
    }

    /**
     * Get uses.
     *
     * @link http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.Class-cfg-uses
     * @return array
     */
    protected function getUses()
    {
        $result = [];
        $current = sprintf('%s.%s', $this->getClassPrefix(), $this->getModelName());

        // Collect belongsTo uses.
        foreach ($this->getTableRelations() as $relation) {
            $refTableName = sprintf('%s.%s', $this->getClassPrefix(), $relation->getReferencedTable()->getModelName());
            if ($relation->isManyToOne() && !in_array($refTableName, $result) && ($refTableName !== $current)) {
                $result[] = $refTableName;
            }
        }

        // Collect hasOne uses.
        foreach ($this->getTableRelations() as $relation) {
            $refTableName = sprintf('%s.%s', $this->getClassPrefix(), $relation->getReferencedTable()->getModelName());
            if (!$relation->isManyToOne() && !in_array($refTableName, $result) && ($refTableName !== $current)) {
                $result[] = $refTableName;
            }
        }

        // Collect hasMany uses.
        foreach ($this->getTableM2MRelations() as $relation) {
            $referencedTable = $relation['refTable'];
            $refTableName = sprintf('%s.%s', $this->getClassPrefix(), $referencedTable->getModelName());
            if (!in_array($refTableName, $result) && ($refTableName !== $current)) {
                $result[] = $refTableName;
            }
        }

        return $result;
    }

    /**
     * Get BelongsTo relations.
     *
     * @link http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.data.association.BelongsTo
     * @return array
     */
    protected function getBelongsTo()
    {
        $result = [];
        foreach ($this->getTableRelations() as $relation) {
            if (!$relation->isManyToOne()) {
                // Do not list OneToOne relations.
                continue;
            }
            $referencedTable = $relation->getReferencedTable();
            $result[] = [
                'model' => sprintf('%s.%s', $this->getClassPrefix(), $referencedTable->getModelName()),
                'associationKey' => lcfirst($referencedTable->getModelName()),
                'getterName' => sprintf('get%s', $referencedTable->getModelName()),
                'setterName' => sprintf('set%s', $referencedTable->getModelName()),
            ];
        }

        return $result;
    }

    /**
     * Get HasOne relations.
     *
     * @link http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.data.association.HasOne
     * @return array
     */
    protected function getHasOne()
    {
        $result = [];
        foreach ($this->getTableRelations() as $relation) {
            // Do not list manyToOne relations.
            if ($relation->isManyToOne()) {
                continue;
            }
            $referencedTable = $relation->getReferencedTable();
            $result[] = [
                'model' => sprintf('%s.%s', $this->getClassPrefix(), $referencedTable->getModelName()),
                'associationKey' => lcfirst($referencedTable->getModelName()),
                'getterName' => sprintf('get%s', $referencedTable->getModelName()),
                'setterName' => sprintf('set%s', $referencedTable->getModelName()),
            ];
        }

        return $result;
    }

    /**
     * Get HasMany relations.
     *
     * @link http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.data.association.HasMany
     * @return array
     */
    protected function getHasMany()
    {
        $result = [];
        foreach ($this->getTableM2MRelations() as $relation) {
            $referencedTable = $relation['refTable'];
            $result[] = [
                'model' => sprintf('%s.%s', $this->getClassPrefix(), $referencedTable->getModelName()),
                'associationKey' => lcfirst($referencedTable->getModelName()),
                'name' => sprintf('get%sStore', $referencedTable->getModelName()),
            ];
        }

        return $result;
    }

    /**
     * Get model fields.
     *
     * @return array
     */
    protected function getFields()
    {
        $result = [];
        foreach ($this->getColumns() as $column) {
            $type = $this->getFormatter()->getDatatypeConverter()->getType($column);
            $result[] = [
                'name' => $column->getColumnName(),
                'type' => $type ? $type : 'auto',
                'defaultValue' => $column->getDefaultValue(),
            ];
        }

        return $result;
    }

    /**
     * Get the column name of the column that is the primary key. Returns null if no primary key is defined.
     *
     * @return string|null
     */
    protected function getPrimaryKey()
    {
        foreach ($this->getColumns() as $column) {
            if ($column->isPrimary()) {
                return $column->getColumnName();
            }
        }

        return null;
    }

    /**
     * Get model field validations.
     *
     * @return array
     */
    protected function getValidations()
    {
        $result = [];
        foreach ($this->getColumns() as $column) {
            if ($column->isNotNull() && !$column->isPrimary()) {
                $result[] = [
                    'type' => 'presence',
                    'field' => $column->getColumnName(),
                ];
            }
            if (($len = $column->getLength()) > 0) {
                $result[] = [
                    'type' => 'length',
                    'field' => $column->getColumnName(),
                    'max' => $len,
                ];
            }
        }

        return $result;
    }

    /**
     * Get model ajax proxy object.
     *
     * @link http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.data.proxy.Ajax
     * @return array
     */
    protected function getAjaxProxy()
    {
        return [
            'type' => 'ajax',
            'url' => sprintf('/data/%s', strtolower($this->getModelName())),
            'api' => $this->getApi(),
            'reader' => $this->getJsonReader(),
            'writer' => $this->getJsonWriter(),
        ];
    }

    /**
     * Get the model API object.
     *
     * @link http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.data.proxy.Ajax-cfg-api
     * @return array
     */
    private function getApi()
    {
        $modelName = strtolower($this->getModelName());

        return [
            'read' => sprintf('/data/%s', $modelName),
            'update' => sprintf('/data/%s/update', $modelName),
            'create' => sprintf('/data/%s/add', $modelName),
            'destroy' => sprintf('/data/%s/destroy', $modelName),
        ];
    }

    /**
     * Get the model json reader.
     *
     * @link http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.data.reader.Json
     * @return array
     */
    private function getJsonReader()
    {
        return [
            'type' => 'json',
            'root' => strtolower($this->getModelName()),
            'messageProperty' => 'message',
        ];
    }

    /**
     * Get the model json writer.
     *
     * @link http://docs.sencha.com/extjs/4.2.0/#!/api/Ext.data.writer.Json
     * @return array
     */
    private function getJsonWriter()
    {
        return [
            'type' => 'json',
            'root' => strtolower($this->getModelName()),
            'encode' => true,
            'expandData' => true,
        ];
    }
}
