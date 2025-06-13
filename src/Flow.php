<?php

namespace ferventdigital\flow;

use Craft;
use craft\base\Plugin;
use ferventdigital\flow\TestUtility\Utility;
use yii\base\Event;
use yii\queue\PushEvent;
use craft\controllers\QueueController;
use yii\base\Controller;
use yii\base\ActionEvent;
use craft\queue\Queue as CraftQueue;
/**
 * Flow plugin
 *
 * @method static Flow getInstance()
 * @author Fervent Digital <support@fervent.digital>
 * @copyright Fervent Digital
 */
class Flow extends Plugin
{
    public string $schemaVersion = '1.0.0';

    private string $phpBinary = '/usr/bin/php';
    private int $maxWorkers = 2;

    private const CRAFT_PATH = CRAFT_BASE_PATH . '/craft';
    private const LOG_FILE = CRAFT_BASE_PATH . '/storage/logs/flow-queue.log';

    public function init(): void
    {
        parent::init();

        $this->phpBinary = getenv('FLOW_PHP_BINARY') ?: $this->phpBinary;
        $this->maxWorkers = (int)(getenv('FLOW_MAX_WORKERS') ?: $this->maxWorkers);

        Craft::$app->getConfig()->getGeneral()->runQueueAutomatically = false;

        // Spawn a worker whenever a new job is pushed
        Event::on(
            CraftQueue::class,
            CraftQueue::EVENT_AFTER_PUSH,
            fn(PushEvent $event) => $this->startWorkers(1)
        );

        // Start workers on job retry
        Event::on(
            QueueController::class,
            Controller::EVENT_AFTER_ACTION,
            function (ActionEvent $event) {
                $actionId = $event->action->id;
                if ($actionId === 'retry') {
                    $this->startWorkers(1);
                } elseif ($actionId === 'retry-all') {
                    $this->startWorkers($this->maxWorkers);
                }
            }
        );

        Utility::setup($this);
    }
    /**
     * Spawn a new queue worker (up to FLOW_MAX_WORKERS).
     */
    private function startWorkers(?int $count = null): void
    {
        // Get running queue workers count
        $running = intval(shell_exec("ps aux | grep 'craft queue/run' | grep -v grep | wc -l"));

        $count = $count ?? $this->maxWorkers;

        $available = $this->maxWorkers - $running;
        $toStart   = min($count, $available);

        if ($toStart <= 0) {
            Craft::info("Queue worker limit reached (" . $this->maxWorkers . ").", __METHOD__);
            return;
        }

        for ($i = 0; $i < $toStart; $i++) {
            exec($this->phpBinary . " " . self::CRAFT_PATH . " queue/run >> " . self::LOG_FILE . " 2>&1 &");
        }
        Craft::info("Queue workers started: {$toStart}. Active workers: " . ($running + $toStart), __METHOD__);
    }
}
