<?php

/**
 * File containing the ConfigurationSymfonyKernelSystemInfoCollector class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\EzSupportToolsBundle\SystemInfo\Collector;

use EzSystems\EzSupportToolsBundle\SystemInfo\Value;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Collects information about the Symfony kernel we are using.
 */
class ConfigurationSymfonyKernelSystemInfoCollector implements SystemInfoCollector
{
    /**
     * Symfony kernel.
     *
     * @var \Symfony\Component\HttpKernel\Kernel
     */
    private $kernel;

    /**
     * Installed bundles.
     *
     * A hash containing the active bundles, where the key is the bundle name, and the value is the corresponding namespace.
     *
     * Example:
     * array (
     *   'AppBundle' => 'AppBundle\\AppBundle',
     *   'AsseticBundle' => 'Symfony\\Bundle\\AsseticBundle\\AsseticBundle',
     * )
     *
     * @var array
     */
    private $bundles;

    public function __construct(Kernel $kernel, array $bundles)
    {
        $this->kernel = $kernel;
        $this->bundles = $bundles;
    }

    /**
     * Collects information about the Symfony kernel.
     *
     * @return Value\SymfonyKernelSystemInfo
     */
    public function collect()
    {
        ksort($this->bundles, SORT_FLAG_CASE | SORT_STRING);

        return new Value\SymfonyKernelSystemInfo([
            'environment' => $this->kernel->getEnvironment(),
            'debugMode' => $this->kernel->isDebug(),
            'version' => Kernel::VERSION,
            'bundles' => $this->bundles,
            'rootDir' => $this->kernel->getRootDir(),
            'name' => $this->kernel->getName(),
            'cacheDir' => $this->kernel->getCacheDir(),
            'logDir' => $this->kernel->getLogDir(),
            'charset' => $this->kernel->getCharset(),
        ]);
    }
}
