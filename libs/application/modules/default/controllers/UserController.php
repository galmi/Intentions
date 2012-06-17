<?php

class UserController extends App_Controller_Base
{

	public function init()
	{
		/* Initialize action controller here */
		parent::init();
	}

	public function indexAction()
	{

	}

	public function appusersAction()
	{
		//массив с именем friendsApp нам отдает вконтакт, в нем содержатся список уидов друзей приложения
		$friendsApp = $this->_getParam('friendsApp', null);
		$userFriendsApp=Model_Users::find($this->_userId);
		if ($userFriendsApp->friends != $friendsApp){
		   $userFriendsApp->friends = $friendsApp;
			$userFriendsApp->save();
		}
	}

	public function wantscountAction()
	{
		$friendsUidsApp = $this->_getParam('friendsUidsApp', null);
		$uids = explode(",", $friendsUidsApp);
		$users = Model_Users::all(array('uid' => array('$in' => $uids)), array('wants_count','uid'));
		$uidWants=array();
        foreach ($users as $user) {
			$uid=$user->uid;
	        $wants_count=$user->wants_count;
			$uidWants[$uid]=$wants_count;
		}
		$this->_helper->json($uidWants);

	}

	public function viewAction()
	{
		$id = $this->_getParam('id','');
    if ($id==$this->_userId) {
      $this->_redirect('/intention/my');
    }
        $user = Model_Users::find($id, array('rating'));
        if ($user) {
            $this->view->rating = (int)$user->rating;
        }
		$this->view->uid=$id;
		$authors = Zend_Registry::get('authors');
		if(in_array($this->_userId, $authors)){
			$this->view->authors=TRUE;
		}else{
			$this->view->authors=FALSE;
		}

	}

	public function myAction() {
		$filter = $this->_getParam('filter', '');
		$uid = $this->_getParam('uid', '');
		if ($this->getRequest()->isXmlHttpRequest() && $filter && $uid) {
			$this->view->intentions = Model_Intentions::my($uid, $filter);
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer('view-list');
		}
		$user=Model_Users::find($this->_userId);
		if(isset($user->wants)){
			$this->view->userWants = $user->wants;
		}
		if(isset($user->done)){
			$this->view->userDone = $user->done;
		}
	}

    public function banAction() {
        $result = 'bad';
        $userId = $this->_getParam('id','');
        if ($userId) {
            $user = Model_Users::find($userId);
            if ($user) {
                $user->ban = 1;
                $user->save();
                $result = 'ok';
            }
        }
        $this->_helper->json($result);
    }

    public function unbanAction() {
        $result = 'bad';
        $userId = $this->_getParam('id','');
        if ($userId) {
            $user = Model_Users::find($userId);
            if ($user) {
                $user->ban = 0;
                $user->save();
                $result = 'ok';
            }
        }
        $this->_helper->json($result);
    }

    public function ratingAction(){
        $this->view->users = Model_Users::ratings();
    }

}

