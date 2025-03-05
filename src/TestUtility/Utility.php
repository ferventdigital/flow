<?php 

namespace ferventdigital\flow\TestUtility;

use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterTemplateRootsEvent;
use craft\services\Utilities;
use craft\web\View;
use ferventdigital\flow\Flow;
use yii\base\Event;

class Utility extends \craft\base\Utility
{

    /**
     * Returns the utility’s unique identifier.
     */
    public static function id(): string
    {
        return 'flow-test';
    }

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return 'Flow Test';
    }


    public static function iconPath(): ?string
    {
        return __DIR__ . '/icon-mask.svg';
    }

    /**
     * Returns the utility's content HTML.
     */
    public static function contentHtml(): string
    {
        /** @var \craft\queue\Queue $queue */
        $queue  = \Craft::$app->getQueue();
        $plugin = Flow::getInstance();

        $checks = [
            'Queue runner concurrency' => getenv('FLOW_MAX_WORKERS') ?: 2,
            'Reserved jobs'            => $queue->getTotalReserved(),
            'Jobs waiting'             => $queue->getTotalWaiting(),
            'Jobs failed'              => $queue->getTotalFailed(),
        ];

        return \Craft::$app->getView()->renderTemplate('flow/template', ['checks' => $checks]);
    }


    public static function setup(Flow $plugin): void
    {
        if (!\Craft::$app->getRequest()->getIsCpRequest()) {
            return;
        }

        // Register the Utility
        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES, function (RegisterComponentTypesEvent $event): void {
            $event->types[] = Utility::class;
        });

        // Tune the template path
        Event::on(View::class, View::EVENT_REGISTER_CP_TEMPLATE_ROOTS, function (RegisterTemplateRootsEvent $e) use ($plugin): void {
            $e->roots[$plugin->getHandle()] = __DIR__;
        });

        // Tune the controller mapping
        $plugin->controllerMap['test'] = [
            'class' => Controller::class,
        ];

    }
}