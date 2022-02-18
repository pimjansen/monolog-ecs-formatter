<?php

namespace ECS\generator;

use Symfony\Component\Yaml\Yaml;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class EcsGenerator
{
    private const RESERVED_TYPE_NAMES = [
        'Interface',
    ];

    /**
     * @param string $string
     * @return string
     */
    public function stringToHungarian(string $string): string
    {
        $string = str_replace('_', '', ucwords($string, '_'));
        $string = str_replace('.', '', ucwords($string, '.'));
        $string = str_replace(' ', '', $string);
        return $string;
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

    /**
     * @param array $fields
     * @return array
     */
    public function getMethodFieldData(array $fields): array
    {
        $methodCollection = [];
        foreach ($fields as $field => $fieldData) {
            $methodCollection[] = [
                'name' => $this->stringToHungarian($field),
                'internal' => $this->formatInternalField($field),
                'type' => $this->elasticTypeToPhp($fieldData['type']),
                'description' => $this->formatDescription($fieldData['description'], 4),
                'example' => $fieldData['example'] ?? null,
            ];
        }

        return $methodCollection;
    }

    /**
     * @param string $text
     * @return string
     */
    private function formatDescription(string $text, int $indent=0): string
    {
        //return $text;
        $indentString = '';
        for ($i=0;$i<=$indent;$i++) {
            $indentString .= ' ';
        }
        return str_replace("\n", "\n".$indentString."* ", $text);
    }

    public function __invoke()
    {
        $loader = new FilesystemLoader('./templates');
        $twig = new Environment($loader, [
            'debug' => true,
        ]);
        $schema = Yaml::parse(file_get_contents($_SERVER['argv'][1]));
        foreach ($schema as $type => $data) {
            $className = $this->stringToHungarian($data['title']);
            echo $className.PHP_EOL;
            if (in_array($className, self::RESERVED_TYPE_NAMES, true)) {
                $className .= 'Type';
                echo 'Classname is reserved word, changing to: '.$className.PHP_EOL;
            }
            $template = $twig->load('class.twig');

            // Output content
            $renderedClassTemplate = $template->render([
                'name' => $data['name'],
                'className' => $className,
                'version' => 'v1.8',
                'description' => $this->formatDescription($data['description']),
                'docsUrl' => 'https://www.elastic.co/guide/en/ecs/current/ecs-'.$data['name'].'.html',
                'methodCollection' => $this->getMethodFieldData($data['fields']),
            ]);
            file_put_contents(
                sprintf(
                    '../src/Type/%s.php',
                    $className
                ),
                $renderedClassTemplate
            );
        }
    }
}

chdir(dirname(__FILE__));
require '../vendor/autoload.php';
$generator = new EcsGenerator();
$generator();
