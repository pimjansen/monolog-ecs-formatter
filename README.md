# monolog-ecs-formatter

## Generate

the following command generates the ecs-schema from the following endpoint:
`https://raw.githubusercontent.com/elastic/ecs/master/generated/ecs/ecs_nested.yml`

```shell
 mkdir src/Ecs-schema & php Generator/EcsGenerator.php
```

## Expected Format

### Input

```shell
$logger->info('A message that you would like to log!', [
   'agent' => [
            'id' => '836926ca-d443-4cb8-9caf-852b95dcfff9',
            'name' => 'company-foo-bar',
            'type' => 'filebeat',
            'version' => '7.16.0',
            'hostname' => 'AboveBuff-nl',
            'build' => [
                'original' => 'original value',
                'wrong_Placed_key' => 'wrong_Placed_key_value',
            ],
            'a_not_known_fields' => [
                'field_A' => [
                    'name' => 'fooA'
                ],
                'field_B' => 'fooB' 
            ],
            'b_not_known_field' => 'foobar'
        ],
]);
```

### Output

```json
{
  "@timestamp": "2022-04-29T12:40:52.543194+02:00",
  "log": {
    "level": "INFO",
    "logger": "MyLogger"
  },
  "ecs": {
    "version": "1.8.0"
  },
  "message": "a message that you would like to log!",
  "agent": {
    "id": "836926ca-d443-4cb8-9caf-852b95dcfff9",
    "name": "company-foo-bar",
    "type": "filebeat",
    "version": "7.16.0",
    "build": {
      "original": "original value"
    }
  },
  "context": [],
  "level": 200,
  "level_name": "INFO",
  "channel": "MyLogger",
  "datetime": "2022-04-29T12:40:52.543194+02:00",
  "extra": [],
  "labels": {
    "agent": {
      "hostname": "AboveBuff-nl",
      "build": {
        "wrong_Placed_key": "wrong_Placed_key_value"
      },
      "a_not_known_fields": {
        "field_A": {
          "name": "fooA"
        },
        "field_B": "fooB"
      },
      "b_not_known_field": "foobar"
    }
  }
}
```
