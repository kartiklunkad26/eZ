<?php

/**
 * File containing the IdentifierBasedTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzSupportToolsBundle\Tests\SystemInfo\Registry;

use EzSystems\EzSupportToolsBundle\SystemInfo\Registry\IdentifierBased;

class IdentifierBasedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \EzSystems\EzSupportToolsBundle\SystemInfo\Registry\IdentifierBasedEzcPhpSystemInfoCollector
     */
    private $registry;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject[]|\EzSystems\EzSupportToolsBundle\SystemInfo\Collector\SystemInfoCollector[]
     */
    private $testItems;

    public function setUp()
    {
        $this->testItems = [
            'foo' => $this->getMock('EzSystems\EzSupportToolsBundle\SystemInfo\Collector\SystemInfoCollector'),
            'bar' => $this->getMock('EzSystems\EzSupportToolsBundle\SystemInfo\Collector\SystemInfoCollector'),
        ];

        $this->registry = new IdentifierBased();
    }

    /**
     * Test adding items to the registry, and getting items from it.
     *
     * @covers \EzSystems\EzSupportToolsBundle\SystemInfo\Registry\IdentifierBased::getItem()
     */
    public function testAddAndGetItems()
    {
        $this->registry = new IdentifierBased($this->testItems);

        self::assertSame($this->testItems['foo'], $this->registry->getItem('foo'));
        self::assertSame($this->testItems['bar'], $this->registry->getItem('bar'));
    }

    /**
     * Test exception when registry item is not found.
     *
     * @covers \EzSystems\EzSupportToolsBundle\SystemInfo\Registry\IdentifierBased::getItem()
     *
     * @expectedException \eZ\Publish\Core\Base\Exceptions\NotFoundException
     */
    public function testGetItemNotFound()
    {
        $this->registry->getItem('notfound');
    }

    /**
     * Test replacing an item in the registry.
     *
     * @covers \EzSystems\EzSupportToolsBundle\SystemInfo\Registry\IdentifierBased::getItem()
     */
    public function testReplaceItem()
    {
        $this->registry = new IdentifierBased($this->testItems);

        $replaceItems = [
            'foo' => $this->getMock('EzSystems\EzSupportToolsBundle\SystemInfo\Collector\SystemInfoCollector'),
        ];

        $this->registry = new IdentifierBased($replaceItems);

        self::assertNotSame($this->testItems['foo'], $this->registry->getItem('foo'));
        self::assertSame($replaceItems['foo'], $this->registry->getItem('foo'));
    }

    /**
     * Test getting all registered identifiers.
     *
     * @covers \EzSystems\EzSupportToolsBundle\SystemInfo\Registry\IdentifierBased::getIdentifiers()
     * @depends testAddAndGetItems
     */
    public function testGetIdentifiers()
    {
        $this->registry = new IdentifierBased($this->testItems);

        $expectedIdentifiers = array_keys($this->testItems);
        sort($expectedIdentifiers);

        $actualIdentifiers = $this->registry->getIdentifiers();
        sort($actualIdentifiers);

        self::assertEquals($expectedIdentifiers, $actualIdentifiers);
    }
}
