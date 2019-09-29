<?php

namespace Droath\RoboCommandBuilder;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

/**
 * Define the command definition configuration.
 */
class CommandDefinition implements CommandDefinitionInterface
{
    /**
     * @var array
     */
    protected $definition;

    /**
     * Command definition constructor.
     *
     * @param $pathToConfig
     *   The path to the command definition configuration.
     */
    public function __construct($pathToConfig)
    {
        $this->definition = (new Processor())->processConfiguration($this, [
            'definition' => $this->loadFile($pathToConfig)
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getBinary()
    {
        return $this->definition['binary'];
    }

    /**
     * {@inheritDoc}
     */
    public function getCommands()
    {
        return $this->definition['commands'];
    }

    /**
     * {@inheritDoc}
     */
    public function getCommandActionDefinition($action)
    {
        $commands = $this->getCommands();

        return isset($commands[$action])
            ? $commands[$action]
            : [];
    }

    /**
     * {@inheritDoc}
     */
    public function getActionOptionDefinitions($action)
    {
        $definition = $this->getCommandActionDefinition($action);

        return isset($definition['options'])
            ? $definition['options']
            : [];
    }

    /**
     * {@inheritDoc}
     */
    public function getActionArgumentDefinitions($action)
    {
        $definition = $this->getCommandActionDefinition($action);

        return isset($definition['arguments'])
            ? $definition['arguments']
            : [];
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeDefinition = new TreeBuilder('definition');

        $rootNode = $treeDefinition->getRootNode();
        $rootNode
            ->children()
                ->scalarNode('binary')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('commands')
                    ->isRequired()
                    ->normalizeKeys(false)
                    ->arrayPrototype()
                        ->children()
                            ->arrayNode('options')
                                ->arrayPrototype()
                                    ->beforeNormalization()
                                        ->ifString()
                                        ->then(function ($v) { return ['name' => $v]; })
                                    ->end()
                                    ->children()
                                        ->scalarNode('name')
                                            ->isRequired()
                                        ->end()
                                        ->scalarNode('type')
                                            ->validate()
                                                ->ifNotInArray(['array', 'integer', 'string', 'boolean'])
                                                ->thenInvalid('Invalid type has been defined!')
                                            ->end()
                                        ->end()
                                        ->scalarNode('default')->end()
                                    ->end()
                                ->end()
                            ->end()
                            ->arrayNode('arguments')
                                 ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeDefinition;
    }

    /**
     * Load the command definition configuration.
     *
     * @param $pathToConfig
     *   The configuration file path.
     *
     * @return array
     *   The command definition configuration.
     */
    protected function loadFile($pathToConfig)
    {
        if (!file_exists($pathToConfig)) {
            throw new \InvalidArgumentException(
                sprintf('The %s command definition configuration path does not exist.', $pathToConfig)
            );
        }

        return Yaml::parseFile($pathToConfig);
    }
}
