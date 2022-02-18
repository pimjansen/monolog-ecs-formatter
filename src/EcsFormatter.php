<?php

declare(strict_types=1);

namespace ECS\Formatter;

use Monolog\Formatter\NormalizerFormatter;
use ECS\Formatter\EcsTypeInterface;
use Throwable;

class EcsFormatter extends NormalizerFormatter
{
    private const ECS_VERSION = '1.8.0';

    private static $logOriginKeys = ['file' => true, 'line' => true, 'class' => true, 'function' => true];

    /**
     * @var array
     *
     * @link https://www.elastic.co/guide/en/ecs/current/ecs-base.html
     */
    protected $tags;

    /** @var bool */
    protected $useLogOriginFromContext = true;

    /**
     * @param array $tags optional tags to enrich the log lines
     */
    public function __construct(array $tags = [])
    {
        parent::__construct('Y-m-d\TH:i:s.uP');
        $this->tags = $tags;
    }

    // public function useLogOriginFromContext(bool $useLogOriginFromContext): self
    // {
    //     $this->useLogOriginFromContext = $useLogOriginFromContext;
    //     return $this;
    // }

    /** @inheritDoc */
    protected function normalize($data, int $depth = 0)
    {
        if ($depth > $this->maxNormalizeDepth) {
            return parent::normalize($data, $depth);
        }

        if ($data instanceof Throwable) {
            die('STOP throwable');
            //return EcsError::serialize($data);
        }

        //if ($data instanceof EcsError) {
        //    return $data->jsonSerialize();
        //}

        return parent::normalize($data, $depth);
    }

    /**
     * {@inheritdoc}
     *
     * @link https://www.elastic.co/guide/en/ecs/1.1/ecs-log.html
     * @link https://www.elastic.co/guide/en/ecs/1.1/ecs-base.html
     * @link https://www.elastic.co/guide/en/ecs/current/ecs-tracing.html
     */
    public function format(array $record): string
    {
        $inRecord = $this->normalize($record);

        // Build Skeleton with "@timestamp" and "log.level"
        $outRecord = [
            '@timestamp' => $inRecord['datetime'],
            'log' => [
                'level'  => $inRecord['level_name'],
                'logger' => $inRecord['channel'],
            ],
            'ecs' => [
                'version' => self::ECS_VERSION,
            ]
        ];

        // Add "message"
        if (isset($inRecord['message']) === true) {
            $outRecord['message'] = $inRecord['message'];
        }

        // Render all type contexts
        foreach ($inRecord['context'] as $key => $context) {
            if (is_array($context) && preg_match('/^PimJansen\\\Monolog\\\Formatter\\\Type\\\.*$/', array_key_first($context))) {
                $outRecord += array_shift($context);
                unset($inRecord['context'][$key]);
            }
        }

        $this->formatContext($inRecord['extra'], /* ref */ $outRecord);
        $this->formatContext($inRecord['context'], /* ref */ $outRecord);

        // Add ECS Tags
        if (empty($this->tags) === false) {
            $outRecord['tags'] = $this->normalize($this->tags);
        }

        return $this->toJson($outRecord) . "\n";
    }

    private function formatContext(array $inContext, array &$outRecord): void
    {
        $foundLogOriginKeys = false;

        // Context should go to the top of the out record
        foreach ($inContext as $contextKey => $contextVal) {
            // label keys should be sanitized
            // if ($contextKey === 'labels') {
            //     $outLabels = [];
            //     foreach ($contextVal as $labelKey => $labelVal) {
            //         $outLabels[str_replace(['.', ' ', '*', '\\'], '_', trim($labelKey))] = $labelVal;
            //     }
            //     $outRecord['labels'] = $outLabels;
            //     continue;
            // }

            // if ($this->useLogOriginFromContext) {
            //     if (isset(self::$logOriginKeys[$contextKey])) {
            //         $foundLogOriginKeys = true;
            //         continue;
            //     }
            // }

            $outRecord[$contextKey] = $contextVal;
        }

        //if ($foundLogOriginKeys) {
        //    $this->formatLogOrigin($inContext, /* ref */ $outRecord);
        //}
    }

    //private function formatLogOrigin(array $inContext, array &$outRecord): void
    //{
    //    $originVal = [];
    //
    //    $fileVal = [];
    //    if (array_key_exists('file', $inContext)) {
    //        $fileName = $inContext['file'];
    //        if (is_string($fileName)) {
    //            $fileVal['name'] = $fileName;
    //        }
    //    }
    //    if (array_key_exists('line', $inContext)) {
    //        $fileLine = $inContext['line'];
    //        if (is_int($fileLine)) {
    //            $fileVal['line'] = $fileLine;
    //        }
    //    }
    //    if (!empty($fileVal)) {
    //        $originVal['file'] = $fileVal;
    //    }
    //
    //    $outFunctionVal = null;
    //    if (array_key_exists('function', $inContext)) {
    //        $inFunctionVal = $inContext['function'];
    //        if (is_string($inFunctionVal)) {
    //            if (array_key_exists('class', $inContext)) {
    //                $inClassVal = $inContext['class'];
    //                if (is_string($inClassVal)) {
    //                    $outFunctionVal = $inClassVal . '::' . $inFunctionVal;
    //                }
    //            }
    //
    //            if ($outFunctionVal === null) {
    //                $outFunctionVal = $inFunctionVal;
    //            }
    //        }
    //    }
    //    if ($outFunctionVal !== null) {
    //        $originVal['function'] = $outFunctionVal;
    //    }
    //
    //    if (!empty($originVal)) {
    //        $outRecord['log']['origin'] = $originVal;
    //    }
    //}
}