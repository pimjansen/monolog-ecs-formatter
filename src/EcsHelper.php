<?php

namespace ECS\Formatter;

class EcsHelper
{
    public function __construct()
    {
    }

    /**
     * @param string $string
     * @return string
     */
    public function stringToHungarian(string $string): string
    {
        $string = str_replace('_', '', ucwords($string, '_'));
        return str_replace(array('.', ' '), '', ucwords($string, '.'));
    }

    /**
     * @param string $string
     * @return string
     */
    public function filedNameFormatter(string $string): string
    {
        $string = str_replace('_', '.', $string);
        return substr($string, strpos($string, ".") + 1);
    }

    /**
     * @param string $field
     * @return string
     */
    public function formatInternalField(string $field): string
    {
        $field = str_replace(".", "']['", $field);
        return $field;
    }

    /**
     * @param string $type
     * @return string
     */
    public function elasticTypeToPhp(string $type): string
    {
        switch($type) {
            case 'keyword':
                return 'string';
            case 'number':
            case 'long':
                return 'int';
            default:
                return $type;
        }
    }

    public function getFieldData(array $fields): array
    {
        $fieldDataCollection = [];
        foreach ($fields as $field => $fieldData) {
            $fieldDataCollection[] = [
                'name' => $this->filedNameFormatter($field),
                'type' => $this->elasticTypeToPhp($fieldData['type']),
            ];
        }

        return $fieldDataCollection;
    }

    public function dotSeparatedStringToArray(&$output, string $string): void
    {
        $keys = explode('.', $string);

        while ($key = array_shift($keys)) {
            $output = &$output[$key];
        }
    }

}