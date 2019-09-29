<?php

namespace Droath\RoboCommandBuilder;

use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Define the command definition interface.
 */
interface CommandDefinitionInterface extends ConfigurationInterface
{
    /**
     * Get the command definition binary.
     *
     * @return string
     *   The command binary.
     */
    public function getBinary();

    /**
     * Get the command definition commands.
     *
     * @return array
     *   An array of command definitions.
     */
    public function getCommands();

    /**
     * Get the command action definition.
     *
     * @param $action
     *   The command definition action.
     *
     * @return array
     *   An array of command action definitions.
     */
    public function getCommandActionDefinition($action);

    /**
     * Get the command action option definitions.
     *
     * @param $action
     *   The command definition action.
     *
     * @return array
     *   An array of command action option definitions.
     */
    public function getActionOptionDefinitions($action);

    /**
     * Get the command action argument definitions.
     *
     * @param $action
     *   The command definition action.
     *
     * @return array
     *   An array of command action argument definitions.
     */
    public function getActionArgumentDefinitions($action);
}
