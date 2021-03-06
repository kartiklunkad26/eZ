<?php

/**
 * This file is part of the eZ RepositoryForms package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
 * @version //autogentag//
 */
namespace EzSystems\RepositoryFormsBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use LogicException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register Limitation form mappers.
 */
class LimitationFormMapperPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ezrepoforms.limitation_form_mapper.registry')) {
            return;
        }

        $registry = $container->findDefinition('ezrepoforms.limitation_form_mapper.registry');

        foreach ($container->findTaggedServiceIds('ez.limitation.formMapper') as $id => $attributes) {
            foreach ($attributes as $attribute) {
                if (!isset($attribute['limitationType'])) {
                    throw new LogicException(
                        'ez.limitation.formMapper service tag needs a "limitationType" attribute to identify which LimitationType the mapper is for. None given.'
                    );
                }

                $registry->addMethodCall('addMapper', [new Reference($id), $attribute['limitationType']]);
            }
        }
    }
}
