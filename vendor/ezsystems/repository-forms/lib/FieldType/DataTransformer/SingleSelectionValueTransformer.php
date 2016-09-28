<?php

/**
 * This file is part of the eZ RepositoryForms package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace EzSystems\RepositoryForms\FieldType\DataTransformer;

use eZ\Publish\Core\FieldType\Selection\Value;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * DataTransformer for Selection\Value in single select mode.
 */
class SingleSelectionValueTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if (!$value instanceof Value) {
            return null;
        }

        if ($value->selection === []) {
            return null;
        }

        return $value->selection;
    }

    public function reverseTransform($value)
    {
        if ($value === null) {
            return null;
        }

        return new Value([(int)$value]);
    }
}
