<?php

namespace ferventdigital\flow;

use Craft;
use craft\base\Plugin;
use ferventdigital\flow\TestUtility\Utility;
use yii\base\Event;
use yii\queue\Queue;
use yii\queue\PushEvent;

/**
 * Flow plugin
 *
 * @method static Flow getInstance()
 * @author Fervent Digital <support@fervent.digital>
 * @copyright Fervent Digital
 * @license MIT
 */
class Flow extends Plugin
{
    public string $schemaVersion = '1.0.0';

    public static function config(): array
    {
        return [
            'components' => [
                // Define component configs here...
            ],
        ];
    }

    public function init(): void
    {
        parent::init();

        $this->attachEventHandlers();

        Craft::$app->getConfig()->getGeneral()->runQueueAutomatically = false;

        Event::on(
            Queue::class,
            Queue::EVENT_AFTER_PUSH,
            function (PushEvent $event) {
                $phpBinary = getenv('FLOW_PHP_BINARY') ?: PHP_BINDIR . '/php'; 
                $maxWorkers = getenv('FLOW_MAX_WORKERS') ?: 2;
        
                $craftPath = CRAFT_BASE_PATH . DIRECTORY_SEPARATOR . 'craft';
                $logFile = CRAFT_BASE_PATH . '/storage/logs/queue.log';
                $lockFile = CRAFT_BASE_PATH . '/storage/logs/queue.lock';
        
                $runningProcesses = intval(shell_exec("ps aux | grep 'craft queue/run' | grep -v grep | wc -l"));
        
                if ($runningProcesses >= $maxWorkers) {
                    Craft::info("Queue worker limit reached ({$maxWorkers} workers).", __METHOD__);
                    return;
                }
        
                file_put_contents($lockFile, time());
        
                $cmd = "$phpBinary $craftPath queue/run >> $logFile 2>&1 &";
                exec($cmd);
        
                Craft::info("Queue worker started. Active workers: " . ($runningProcesses + 1), __METHOD__);
            }
        );

        Utility::setup($this);
    }

    private function attachEventHandlers(): void
    {
        // Register event handlers here ...
        // (see https://craftcms.com/docs/4.x/extend/events.html to get started)
    }
}
