<?php

/**
 * This file is part of the eZ RepositoryForms package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\RepositoryForms\FieldType;

use EzSystems\RepositoryForms\Data\Content\FieldData;
use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use Symfony\Component\Form\FormInterface;
use InvalidArgumentException;

/**
 * FieldType mappers dispatcher.
 *
 * Adds the form elements matching the given Field Data (Value or Definition) to a given Form.
 */
class FieldTypeFormMapperDispatcher implements FieldTypeFormMapperDispatcherInterface
{
    /**
     * FieldType form mappers, indexed by FieldType identifier.
     *
     * @var FieldTypeFormMapperInterface[]|FieldValueFormMapperInterface[]|FieldDefinitionFormMapperInterface[]
     */
    private $mappers = [];

    /**
     * @var FieldDefinitionFormMapperInterface[]
     */
    private $definitionMappers = [];

    /**
     * @var FieldValueFormMapperInterface[]
     */
    private $valueMappers = [];

    public function addMapper(FieldFormMapperInterface $mapper, $fieldTypeIdentifier)
    {
        if ($mapper instanceof FieldTypeFormMapperInterface) {
            @trigger_error(
                'The FieldTypeFormMapperInterface interface is deprecated in ezsystems/repository-forms 1.1, ' .
                "and will be removed in version 2.0\n" .
                'Use FieldValueFormMapperInterface and FieldDefinitionFormMapperInterface instead',
                E_USER_DEPRECATED
            );
            $this->mappers[$fieldTypeIdentifier] = $mapper;
            $valid = true;
        }

        if ($mapper instanceof FieldValueFormMapperInterface) {
            $this->valueMappers[$fieldTypeIdentifier] = $mapper;
            $valid = true;
        }

        if ($mapper instanceof FieldDefinitionFormMapperInterface) {
            $this->definitionMappers[$fieldTypeIdentifier] = $mapper;
            $valid = true;
        }

        if (!isset($valid)) {
            throw new \InvalidArgumentException(
                'Expecting a FieldValueFormMapperInterface, FieldDefinitionFormMapperInterface or FieldTypeFormMapperInterface'
            );
        }
    }

    public function map(FormInterface $fieldForm, $data)
    {
        if (!$data instanceof FieldDefinitionData && !$data instanceof FieldData) {
            throw new InvalidArgumentException('Invalid data object, valid types are FieldData and FieldDefinitionData');
        }

        $fieldTypeIdentifier = $data->getFieldTypeIdentifier();

        if ($data instanceof FieldDefinitionData) {
            if (isset($this->definitionMappers[$fieldTypeIdentifier])) {
                $this->definitionMappers[$fieldTypeIdentifier]->mapFieldDefinitionForm($fieldForm, $data);
            } elseif (
                isset($this->mappers[$fieldTypeIdentifier]) &&
                $this->definitionMappers[$fieldTypeIdentifier] instanceof FieldDefinitionFormMapperInterface
            ) {
                $this->mappers[$fieldTypeIdentifier]->mapFieldDefinitionForm($fieldForm, $data);
            }

            return;
        }

        if ($data instanceof FieldData) {
            if (isset($this->valueMappers[$fieldTypeIdentifier])) {
                $this->valueMappers[$fieldTypeIdentifier]->mapFieldValueForm($fieldForm, $data);
            } elseif (
                isset($this->mappers[$fieldTypeIdentifier]) &&
                $this->mappers[$fieldTypeIdentifier] instanceof FieldValueFormMapperInterface
            ) {
                $this->mappers[$fieldTypeIdentifier]->mapFieldValueForm($fieldForm, $data);
            }

            return;
        }
    }
}
