<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ильдар
 * Date: 20.12.11
 * Time: 22:06
 * To change this template use File | Settings | File Templates.
 */

class App_Controller_Base extends Zend_Controller_Action
{

    protected $_userId;

    public function init()
    {
        if ($this->_getParam('controller') == 'index' && in_array($this->_getParam('action'), array('comeback', 'gettags', 'task', 'calcrating'))) {
            return;
        }
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            $this->_userId = $auth->getIdentity();
            $user = Model_Users::find($this->_userId);
        } elseif (App_Vkontakte::checkAuthKey($this->_getAllParams())) {
            Zend_Session::rememberMe(1209600);
            $session = $auth->getStorage();
            $viewer_id = $this->_getParam('viewer_id');
            $session->write($viewer_id);
            $this->_userId = $viewer_id;
            $user = Model_Users::find($viewer_id);
            if (!$user) {
                $user = Model_Users::getInstance();
                $user->uid = $viewer_id;
                $user->wants_count = 0;
                $user->save();
            }
        }
        if (isset($user) && !is_null($user)) {
            $user->ts = time();
            $user->save();
        }
        $viewer_id = $this->_getParam('viewer_id');
//		Zend_Debug::dump(Model_Users::find($viewer_id));
        if (!isset($user)) {
            $user = Model_Users::find($this->_userId);
            if (isset($user->ban) && $user->ban == 1) {
                exit;
            }
        }
        $this->view->rating = (int)$user->rating;
        $this->view->app_id = App_Vkontakte::getAppID();
        $referer = isset($_SERVER['HTTP_REFERER']) ? pathinfo($_SERVER['HTTP_REFERER']) : '';
        if ($referer) {
            $this->view->referer = $referer['dirname'];
        } else {
            $this->view->referer = '';
        }

        if ($this->_userId) {
            $this->view->userId = $this->_userId;
        } else {
            $this->renderScript('error/redirect.phtml');
            return;
        }

        if ($this->getRequest()->isXMLHttpRequest() || $this->_getParam('_pjax', '')) {
            $this->_helper->layout()->disableLayout();
        }
        $authors = Zend_Registry::get('authors');
        if (in_array($this->_userId, $authors)) {
            $this->view->admin = true;
        } else {
            $this->view->admin = false;
        }
        if (isset($_COOKIE['redirect'])) {
            $redirect = $_COOKIE['redirect'];
            setcookie('redirect', '', time() - 3600, '/');
            $this->_redirect($redirect);
        }
        $this->view->server_name = $_SERVER["SERVER_NAME"];
    }

}