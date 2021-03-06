<?php

/**
 * This file is part of the eZ RepositoryForms package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\RepositoryForms\Tests\Form\DataTransformer;

use EzSystems\RepositoryForms\Form\DataTransformer\TranslatablePropertyTransformer;
use PHPUnit_Framework_TestCase;

class TranslatablePropertyTransformerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider transformInvalidValueProvider
     */
    public function testTransformInvalidValue($value)
    {
        $transformer = new TranslatablePropertyTransformer('fre-FR');
        self::assertNull($transformer->transform($value));
    }

    public function transformInvalidValueProvider()
    {
        return [
            ['foo'],
            [true],
            [123],
            [['eng-GB' => 'bar']],
        ];
    }

    /**
     * @dataProvider transformValueProvider
     */
    public function testTransform(array $inputValue, $languageCode, $expected)
    {
        $transformer = new TranslatablePropertyTransformer($languageCode);
        self::assertSame($expected, $transformer->transform($inputValue));
    }

    public function transformValueProvider()
    {
        return [
            [['fre-FR' => 'français', 'eng-GB' => 'english'], 'fre-FR', 'français'],
            [['fre-FR' => 'français', 'eng-GB' => 'english'], 'eng-GB', 'english'],
            [['nor-NO' => 'norsk'], 'nor-NO', 'norsk'],
        ];
    }

    /**
     * @dataProvider reverseTransformProvider
     */
    public function testReverseTransform($inputValue, $languageCode, $expected)
    {
        $transformer = new TranslatablePropertyTransformer($languageCode);
        self::assertSame($expected, $transformer->reverseTransform($inputValue));
    }

    public function reverseTransformProvider()
    {
        return [
            [false, 'fre-FR', null],
            [null, 'fre-FR', null],
            ['français', 'fre-FR', ['fre-FR' => 'français']],
            ['english', 'eng-GB', ['eng-GB' => 'english']],
            ['norsk', 'nor-NO', ['nor-NO' => 'norsk']],
        ];
    }
}
