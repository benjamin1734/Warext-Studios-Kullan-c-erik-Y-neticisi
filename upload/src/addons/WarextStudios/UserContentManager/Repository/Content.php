<?php

namespace WarextStudios\UserContentManager\Repository;

use WarextStudios\UserContentManager\Content\AbstractHandler;
use XF\Mvc\Entity\Repository;

class Content extends Repository
{
    public const HANDLER_FIELD = 'warext_ucm_handler_class';

    public function getHandler(string $contentType): ?AbstractHandler
    {
        $handlerClass = \XF::app()->getContentTypeFieldValue($contentType, self::HANDLER_FIELD);

        if (!$handlerClass || !class_exists($handlerClass))
        {
            return null;
        }

        $handlerClass = \XF::extendClass($handlerClass);

        if (!is_a($handlerClass, AbstractHandler::class, true))
        {
            return null;
        }

        $handler = new $handlerClass($contentType);

        return $handler->isAvailable() ? $handler : null;
    }

    public function getHandlers(): array
    {
        $handlers = [];
        $registered = \XF::app()->getContentTypeField(self::HANDLER_FIELD);

        foreach ($registered as $contentType => $handlerClass)
        {
            $handler = $this->getHandler($contentType);

            if ($handler)
            {
                $handlers[$contentType] = $handler;
            }
        }

        return $handlers;
    }
}
