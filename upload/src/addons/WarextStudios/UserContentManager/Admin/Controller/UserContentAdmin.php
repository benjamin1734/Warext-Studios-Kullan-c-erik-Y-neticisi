<?php

namespace WarextStudios\UserContentManager\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\ParameterBag;

class UserContentAdmin extends AbstractController
{
    public function actionIndex()
    {
        $this->assertAdminPermission('user');

        return $this->redirect($this->buildLink('users'));
    }

    public function actionManage(ParameterBag $params)
    {
        $this->assertAdminPermission('user');

        $user = $this->assertRecordExists('XF:User', $params->user_id);
        $url = \XF::app()->router('public')->buildLink('canonical:kullanici-icerikleri', $user);

        return $this->redirect($url);
    }
}
