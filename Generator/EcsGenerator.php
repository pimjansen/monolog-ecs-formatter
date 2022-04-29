<?php

namespace ECS\Formatter\Generator;

use Symfony\Component\Yaml\Yaml;

final class EcsGenerator
{
    private const ECS_SCHEMA = 'https://raw.githubusercontent.com/elastic/ecs/master/generated/ecs/ecs_nested.yml';

    private const ECS_SCHEMA_FILE_NAME = 'ecs-schema';

    public function __invoke()
    {
        $schema = Yaml::parse(
            file_get_contents(self::ECS_SCHEMA)
        );
        $info = [
            'sync-info' => [
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];
        $schema = array_merge($info, $schema);

        try {
            file_put_contents(
                $this->getFileName(),
                json_encode([
                    'schema' => $schema,
                ], JSON_THROW_ON_ERROR) . PHP_EOL,
            );
        } catch (\Exception $exception) {
            echo sprintf('error:%s with code:%s', $exception->getMessage(), $exception->getCode());
        }
    }

    private function getFileName(): string
    {
        return sprintf(
            '../src/Ecs-schema/%s.json',
            self::ECS_SCHEMA_FILE_NAME
        );
    }
}

chdir(dirname(__FILE__));
require '../vendor/autoload.php';
$generator = new EcsGenerator();
$generator();
