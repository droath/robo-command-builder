The robo command builder is a utility that allows commands to be defined in a definition file. Once the commands have been defined they'll be able to be called via a PHP method.

The command definition allows for the following directives:
 
  * binary - string (required)
  * commands - array (required)
  
Each command action will be able to define their arguments and/or options. The `arguments` definition is a single key array. 

command.yml

```yaml
...
commands:
  upload :
    arguments:
      - file
```
Which you would be able to use within a Robo task class.
```php
<?php

$pathToConfig = __DIR__ . '/command.yml';

$task $this->task(
    CommandBuilder::class, 'upload', $pathToConfig, null
);

$task->file(/path/to/file)->run();
````

The `options` definition can be either a single key array, which defaults to a boolean type. If you need to define more options then you'll need to use an array of objects. The following parameters exist in the option object:

* name - The option name (required)
* type - The option type (optional - defaults to boolean)
* default - The option default value (optional)

**Option Types:**
   * array 
   * string
   * integer
   * boolean

```yaml
binary: ddev
commands:
    import-db:
        options:
            - { "name": "progress" }
            - { "name": "extract-path" }
            - { "name": "src", "type": "string" }
```
