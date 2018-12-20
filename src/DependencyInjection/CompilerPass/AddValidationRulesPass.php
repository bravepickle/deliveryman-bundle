<?php
/**
 * Date: 2018-12-20
 * Time: 01:13
 */

namespace DeliverymanBundle\DependencyInjection\CompilerPass;

use DeliverymanBundle\ValidationRuleInterface;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

class AddValidationRulesPass implements CompilerPassInterface
{
    const TAG_VALIDATION_RULE = 'deliveryman.validation';
    const VALIDATION_RULE_PREFIX = 'deliveryman.validation';

    public function process(ContainerBuilder $container)
    {
        // TODO: implement me
//        foreach ($container->findTaggedServiceIds(self::TAG_VALIDATION_RULE) as $id => $tags) {
//            $def = $container->getDefinition($id);
//            $class = $container->getParameterBag()->resolveValue($def->getClass());
//            $interfaceName = ValidationRuleInterface::class;
//            if (!is_subclass_of($class, $interfaceName)) {
//                throw new InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, $interfaceName));
//            }
//
//            $commandDefinition = new ChildDefinition('maker.auto_command.abstract');
//            $commandDefinition->setClass(::class);
//            $commandDefinition->replaceArgument(0, new Reference($id));
//            $commandDefinition->addTag('console.command', ['command' => $class::getCommandName()]);
//
//            $container->setDefinition(sprintf('%s.%s', self::VALIDATION_RULE_PREFIX,  ), $commandDefinition);
//        }
    }
}
