<?php

namespace WarextStudios\UserContentManager\Job;

use WarextStudios\UserContentManager\Service\ResourceBulk;
use WarextStudios\UserContentManager\Service\ThreadBulk;

class BulkOperationFinal2 extends BulkOperationFinal
{
    public function run($maxRunTime)
    {
        $operation = $this->app->em()->find('WarextStudios\\UserContentManager:BulkOperation', $this->data['operation_id']);

        if (!$operation || in_array($operation->status, ['completed', 'failed', 'cancelled'], true))
        {
            return $this->complete();
        }

        $lockToken = $this->acquireLock($operation);
        if ($lockToken === null) { return $this->resume(); }

        $started = microtime(true);
        $originalVisitor = \XF::visitor();

        try
        {
            $actor = $this->app->em()->find('XF:User', $operation->actor_user_id);
            $targetUser = $this->app->em()->find('XF:User', $operation->target_user_id);

            if (!$actor || !$targetUser)
            {
                return $this->failOperation($operation, 'İşlemi başlatan veya hedef kullanıcı bulunamadı.');
            }

            if (!$actor->hasPermission('warextUcm', 'view') || !$actor->hasPermission('warextUcm', 'bulk'))
            {
                return $this->failOperation($operation, 'İşlemi başlatan kullanıcının toplu işlem yetkisi artık yok.');
            }

            if ($operation->action === 'hard_delete' && !$actor->hasPermission('warextUcm', 'hardDelete'))
            {
                return $this->failOperation($operation, 'İşlemi başlatan kullanıcının kalıcı silme yetkisi artık yok.');
            }

            \XF::setVisitor($actor);
            $handler = $this->app->repository('WarextStudios\\UserContentManager:Content')->getHandler($operation->content_type);

            if (!$handler)
            {
                return $this->failOperation($operation, 'İçerik işleyicisi kullanılamıyor.');
            }

            $filters = $this->decodeJson($operation->filters);
            $options = $this->decodeJson($operation->options);
            $finder = $this->buildFinder($operation, $handler, $targetUser, $filters);
            $idColumn = $operation->content_type === 'thread' ? 'thread_id' : 'resource_id';
            $batch = max(10, min(250, (int)$this->data['batch']));

            $items = $finder
                ->where($idColumn, '>', $operation->last_content_id)
                ->order($idColumn, 'ASC')
                ->limit($batch)
                ->fetch();

            if (!$items->count())
            {
                $operation->status = 'completed';
                $operation->updated_date = \XF::$time;
                $operation->save();
                return $this->complete();
            }

            $serviceOptions = $this->prepareServiceOptions($operation, $options);
            $permissionAction = $this->getPermissionAction($operation->action);
            $attempted = 0;

            foreach ($items as $item)
            {
                $contentId = (int)$item->getEntityId();
                $actionError = null;

                if (!$handler->canView($item, $actor) || !$handler->canPerformAction($permissionAction, $item, $actor, $actionError))
                {
                    $operation->skipped_count++;
                }
                else
                {
                    try
                    {
                        $changed = $operation->content_type === 'thread'
                            ? (new ThreadBulk())->apply($item, $operation->action, $serviceOptions)
                            : (new ResourceBulk())->apply($item, $operation->action, $serviceOptions);

                        if ($changed) { $operation->processed_count++; }
                        else { $operation->skipped_count++; }
                    }
                    catch (\Throwable $e)
                    {
                        $operation->failed_count++;
                        $operation->last_error = mb_substr($e->getMessage(), 0, 2000);
                        \XF::logException($e, false, 'Warext UCM job ' . $operation->operation_id . ' content ' . $contentId . ': ');
                    }
                }

                $operation->last_content_id = $contentId;
                $operation->status = 'running';
                $operation->updated_date = \XF::$time;
                $operation->save();
                $attempted++;

                if (microtime(true) - $started >= max(1, (float)$maxRunTime - 0.5))
                {
                    break;
                }
            }

            if ($attempted < $items->count())
            {
                return $this->resume();
            }

            if ($items->count() < $batch)
            {
                $operation->status = 'completed';
                $operation->updated_date = \XF::$time;
                $operation->save();
                return $this->complete();
            }

            return $this->resume();
        }
        catch (\Throwable $e)
        {
            \XF::logException($e, false, 'Warext UCM bulk job ' . $operation->operation_id . ': ');
            return $this->failOperation($operation, $e->getMessage());
        }
        finally
        {
            \XF::setVisitor($originalVisitor);
            $this->releaseLock($operation->operation_id, $lockToken);
        }
    }
}
