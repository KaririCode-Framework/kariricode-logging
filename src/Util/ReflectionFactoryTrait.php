<?php

declare(strict_types=1);

namespace KaririCode\Logging\Util;

use KaririCode\Logging\Exception\InvalidConfigurationException;
use ReflectionClass;

/**
 * Trait ReflectionFactoryTrait.
 *
 * Provides methods for creating instances of classes using reflection and managing configurations.
 */
trait ReflectionFactoryTrait
{
    /**
     * Creates an instance of the specified class with the given parameters.
     *
     * @param string $class the fully qualified class name
     * @param array $parameters an array of parameters to pass to the constructor
     *
     * @throws InvalidConfigurationException if the class doesn't exist or is not instantiable
     * @throws \ReflectionException if there's an error during reflection
     *
     * @return object the created instance
     */
    public function createInstance(string $class, array $parameters = []): object
    {
        $reflectionClass = $this->getReflectionClass($class);
        $filteredParameters = $this->filterConstructorParameters($reflectionClass, $parameters);

        return $reflectionClass->newInstanceArgs($filteredParameters);
    }

    /**
     * Gets a ReflectionClass instance after validating the class.
     *
     * @param string $class the fully qualified class name
     *
     * @throws InvalidConfigurationException if the class doesn't exist or is not instantiable
     */
    protected function getReflectionClass(string $class): \ReflectionClass
    {
        if (!class_exists($class)) {
            throw new InvalidConfigurationException("Class does not exist: $class");
        }

        $reflectionClass = new \ReflectionClass($class);

        if (!$reflectionClass->isInstantiable()) {
            throw new InvalidConfigurationException("Class is not instantiable: $class");
        }

        return $reflectionClass;
    }

    /**
     * Filters the parameters to match the constructor's expected parameters.
     *
     * @param \ReflectionClass $reflectionClass the reflection class
     * @param array $parameters the parameters to filter
     *
     * @throws InvalidConfigurationException if a required parameter is missing
     *
     * @return array the filtered parameters
     */
    protected function filterConstructorParameters(\ReflectionClass $reflectionClass, array $parameters): array
    {
        $constructor = $reflectionClass->getConstructor();
        if (!$constructor) {
            return [];
        }

        $constructorParameters = $constructor->getParameters();
        $filteredParameters = [];

        foreach ($constructorParameters as $param) {
            $paramName = $param->getName();
            if (isset($parameters[$paramName])) {
                $filteredParameters[] = $parameters[$paramName];
            } elseif ($param->isDefaultValueAvailable()) {
                $filteredParameters[] = $param->getDefaultValue();
            } elseif ($param->allowsNull()) {
                $filteredParameters[] = null;
            } else {
                throw new InvalidConfigurationException("Missing required parameter: $paramName");
            }
        }

        return $filteredParameters;
    }

    /**
     * Gets the class from a configuration map.
     *
     * @param array $map the configuration map
     * @param string $key the key to look up in the map
     *
     * @throws InvalidConfigurationException if the class configuration is invalid or the class doesn't exist
     *
     * @return string the fully qualified class name
     */
    protected function getClassFromMap(array $map, string $key): string
    {
        if (!isset($map[$key])) {
            throw new InvalidConfigurationException("Configuration not found for key: $key");
        }

        return $this->validateAndExtractClass($map[$key], $key);
    }

    /**
     * Validates and extracts the class from a configuration value.
     *
     * @param mixed $config the configuration value
     * @param string $key the configuration key (for error reporting)
     *
     * @throws InvalidConfigurationException if the class configuration is invalid or the class doesn't exist
     *
     * @return string the validated class name
     */
    protected function validateAndExtractClass($config, string $key): string
    {
        $class = is_string($config) ? $config : ($config['class'] ?? null);

        if (!is_string($class) || !class_exists($class)) {
            throw new InvalidConfigurationException("Invalid class configuration for key: $key");
        }

        return $class;
    }

    /**
     * Gets configuration from a map for a specific key.
     *
     * @param array $map the configuration map
     * @param string $key the key to look up in the map
     * @param string $configKey the configuration key to retrieve (default: 'with')
     * @param array $default the default value if the configuration is not found
     *
     * @return array the configuration array
     */
    protected function getConfigFromMap(array $map, string $key, string $configKey = 'with', $default = []): array
    {
        return $map[$key][$configKey] ?? $default;
    }

    /**
     * Merges multiple configurations.
     *
     * @param array ...$configs The configurations to merge.
     *
     * @return array the merged configuration
     */
    protected function mergeConfigurations(array ...$configs): array
    {
        return array_merge(...$configs);
    }

    /**
     * Gets the component configuration by merging default and channel-specific configs.
     *
     * @param string $componentType the type of the component
     * @param string $componentName the name of the component
     * @param array $channelConfig the channel-specific configuration
     * @param array $defaultConfig the default configuration
     *
     * @return array the merged component configuration
     */
    protected function getComponentConfig(string $componentType, string $componentName, array $channelConfig, array $defaultConfig): array
    {
        $channelComponentConfig = $channelConfig[$componentType][$componentName] ?? [];

        return $this->mergeConfigurations($defaultConfig, $channelComponentConfig);
    }

    /**
     * Extracts the merged configuration from a key-value pair.
     *
     * @param mixed $key the configuration key
     * @param mixed $value the configuration value
     *
     * @return array an array containing the extracted class and configuration
     */
    protected function extractMergedConfig($key, $value): array
    {
        if ($this->isSimpleHandlerConfig($key)) {
            return [$value, []];
        }

        return [$key, $value['with'] ?? []];
    }

    /**
     * Checks if the given key represents a simple handler configuration.
     *
     * @param mixed $key the configuration key to check
     *
     * @return bool true if it's a simple handler configuration, false otherwise
     */
    protected function isSimpleHandlerConfig($key): bool
    {
        return is_int($key);
    }
}
