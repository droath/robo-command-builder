<?php

namespace Droath\RoboCommandBuilder;

use Robo\Common\ExecOneCommand;
use Robo\Contract\CommandInterface;
use Robo\Exception\AbortTasksException;
use Robo\Exception\TaskException;
use Robo\Task\BaseTask;

/**
 * Define the command builder task.
 */
class CommandBuilderTask extends BaseTask implements CommandInterface
{
    use ExecOneCommand;

    /**
     * The command action.
     *
     * @var string
     */
    protected $action;

    /**
     * The command executable path.
     *
     * @var string
     */
    protected $executable;

    /**
     * The command definition.
     *
     * @var CommandDefinitionInterface
     */
    protected $definition;

    /**
     * The command base constructor.
     *
     * @param string $action
     *   The command action.
     * @param null $pathToConfig
     *   The path to the definition.
     * @param string $pathToBinary
     *   The custom path to the binary.
     *
     * @throws TaskException
     */
    public function __construct($action = null, $pathToConfig = null, $pathToBinary = null)
    {
        $this->action = $action;
        $this->definition = new CommandDefinition($pathToConfig);
        $this->executable = isset($pathToBinary)
            ? $pathToBinary
            : $this->findExecutablePhar($this->definition->getBinary());

        if (!$this->executable) {
            throw new TaskException(
                __CLASS__,
                sprintf('Unable to find the % executable.', $this->definition->getBinary())
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        $command = $this->getCommand();

        $this->printTaskInfo(
            'Running: {command}',
            ['command' => $command]
        );

        return $this->executeCommand($command);
    }

    /**
     * Get the executable command.
     *
     * @return string
     */
    public function getCommand()
    {
        return "{$this->executable} {$this->action}{$this->arguments}";
    }

    /**
     * Resolve the command options and arguments.
     *
     * @param $name
     *   The name of the method.
     * @param $arguments
     *   The method argument values.
     *
     * @return $this
     *
     * @throws AbortTasksException
     */
    public function __call($name, $arguments)
    {
        $name = $this->formatCamelCaseToDashes($name);

        if ($this->setCommandOption($name, $arguments)) {
            return $this;
        }

        if ($this->setCommandArguments($name, $arguments)) {
            return $this;
        }

        throw new AbortTasksException(
            sprintf('The %s command option or argument is invalid.', $name)
        );
    }

    /**
     * Attach executable command arguments.
     *
     * @param $name
     *   The argument name.
     * @param array $arguments
     *   An argument
     *
     * @return bool
     *   Return true if the command argument has been set; otherwise false.
     *
     * @throws AbortTasksException
     */
    protected function setCommandArguments($name, array $arguments)
    {
        if (in_array($name, $this->getActionArguments())) {
            $value = !empty($arguments) ? $arguments[0] : null;

            if (is_array($value)) {
                $value = implode(' ', $value);
            }

            if (!isset($value) || empty($value)) {
                throw new AbortTasksException(
                    sprintf('The %s argument value is required!', $name)
                );
            }
            $this->arg($value);

            return true;
        }

        return false;
    }

    /**
     * Set command options.
     *
     * @param $name
     *   The name of the option.
     * @param array $arguments
     *   An array of option arguments.
     *
     * @return bool
     *   Return true if the command option has been set; otherwise false.
     *
     * @throws AbortTasksException
     */
    protected function setCommandOption($name, array $arguments)
    {
        $value = !empty($arguments) ? $arguments[0] : null;

        foreach ($this->getActionOptions() as $option) {
            if (!isset($option['name']) || $option['name'] !== $name) {
                continue;
            }
            if (!isset($value) && isset($option['default'])) {
                $value = $option['default'];
            }
            $type = isset($option['type']) ? $option['type'] : 'boolean';

            if ($type === 'array' && is_array($value)) {
                $value = implode(',', $value);
            }

            if (empty($value) && in_array($type, ['array', 'string', 'integer'])) {
                throw new AbortTasksException(
                    sprintf('The %s option value is required!', $name)
                );
            }

            $this->option($name, $value);

            return true;
        }

        return false;
    }

    /**
     * Format a camel case string to dashes.
     *
     * @param $method
     *   The camel case method name.
     *
     * @return string
     *   The string with dashes in replace of capital letters.
     */
    protected function formatCamelCaseToDashes($method)
    {
        return strtolower(
            preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $method)
        );
    }

    /**
     * Get the command action options.
     *
     * @return array
     *   An array of allowed action options.
     */
    protected function getActionOptions()
    {
        return $this->definition->getActionOptionDefinitions($this->action);
    }

    /**
     * Get the command action arguments.
     *
     * @return array
     *   An array of allowed action arguments.
     */
    protected function getActionArguments()
    {
        return $this->definition->getActionArgumentDefinitions($this->action);
    }
}
