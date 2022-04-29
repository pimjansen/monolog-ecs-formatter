<?php

namespace ECS\Formatter;

final class EcsHelper
{
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
     * @param string $type
     * @return string
     */
    public function elasticTypeToPhp(string $type): string
    {
        switch ($type) {
            case 'keyword':
                return 'string';
            case 'number':
            case 'long':
                return 'int';
            default:
                return $type;
        }
    }

    public function getSchemaFields(array $schema): array
    {
        $fieldDataCollection = [];
        foreach ($schema['fields'] as $field => $fieldData) {
            $fieldDataCollection[] = $this->filedNameFormatter($field);
        }

        return $fieldDataCollection;
    }

    public function getSchema(): array
    {
        try {
            $schemaFileContent = file_get_contents('src/Ecs-schema/ecs-schema.json');
            if ($schemaFileContent === false) {
                throw new \RuntimeException('Failed to open stream: No such file or directory');
            }
        } catch (\Exception $exception) {
            if ($exception instanceof \RuntimeException) {
                shell_exec('php Generator/EcsGenerator.php');
            }
        }

        $schemaContentArray = json_decode($schemaFileContent, true, 512, JSON_THROW_ON_ERROR);
        $sixMonthsFromTimeGap = date("Y-m-d H:i:s", strtotime("+6 months"));
        if ($schemaContentArray['schema']['sync-info']['created_at'] > $sixMonthsFromTimeGap) {
            throw new \Exception('ecs schema is out of sync! please run the generator command.');
        }

        return $schemaContentArray['schema'];
    }

    public function unsetFromInRecord(string $fullDotedPath, array &$array): void
    {
        $path = $this->getArrayPath($fullDotedPath);

        eval("unset(\$array{$path});");
    }

    public function setToOutRecord(string $fullDotedPath, $value, array &$array): void
    {
        $path = $this->getArrayPath($fullDotedPath);

        eval("\$array{$path} = \$value;");
    }

    public function getArrayPath(string $fullDotedPath): string
    {
        $endpoints = explode('.', $fullDotedPath);

        return "['" . implode("']['", $endpoints) . "']";
    }

    public function emptyUnsetRecursively(&$inRecord): void
    {
        foreach ($inRecord as $key => $item) {
            if (is_array($item) === true && empty($item) === false) {
                $this->emptyUnsetRecursively($item);
            }
            if (empty($item) === true) {
                unset($inRecord[$key]);
            }
        }
    }
}