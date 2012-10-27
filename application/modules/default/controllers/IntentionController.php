<?php

class IntentionController extends App_Controller_Base
{

	public function init()
	{
		/* Initialize action controller here */
		parent::init();
	}

	public function indexAction()
	{

	}

	public function addAction()
	{
    $result = false;
    $data = array(
		  'description' => trim($this->_getParam('description', '')),
      'done' => (boolean)$this->_getParam('done', false)
    );
    if ($this->_userId && $data['description']) {
     $result =  Model_Intentions::add($this->_userId, $data);
    }
	$redirect=$this->_getParam('redirect', false);
	if(isset($redirect)){
		$this->_setParam('id', $result);
		$this->_forward($redirect,'intention');
	}else{
        $this->_helper->json($result);
		}
	}


	public function findAction(){

	}

	public function viewAction()
	{
		$id=$this->_getParam('id');
		$intention=Model_Intentions::find($id);
    if ($intention) {
      $this->view->intentionName = $intention->description;
      $this->view->author = $intention->author;
      $this->view->id = $id;
      $this->view->uid = $this->_userId;
      if (isset($intention->color) && isset($intention->super)) {
        $super = (int)$intention->super + 60*60*24;
        if ($super>time()) {
            $this->view->color = $intention->color;
        }
      }
      if(isset($intention->wants)){
        $this->view->iWants_count = count($intention->wants);
        $this->view->WantsUsers = $intention->wants;
        shuffle($this->view->WantsUsers);
        if(in_array($this->_userId,$intention->wants)){
          $this->view->iWants = ' on';
        }
      }else{
        $this->view->iWants = '';
        $this->view->iWants_count = 0;
      }
      if(isset($intention->done)){
        $this->view->iDone_count = count($intention->done);
        $this->view->DoneUsers = $intention->done;
        shuffle($this->view->DoneUsers);
        if(in_array($this->_userId,$intention->done)){
          $this->view->iDone = ' on';
        }
      }else{
        $this->view->iDone = '';
        $this->view->iDone_count = 0;
      }
    } else {
      $this->_redirect('/');
    }
	}

  public function myAction() {
    $filter = $this->_getParam('filter', '');
    if ($this->getRequest()->isXmlHttpRequest() && $filter) {
      $this->view->intentions = Model_Intentions::my($this->_userId, $filter);
      $this->_helper->layout()->disableLayout();
      $this->_helper->viewRenderer('my-list');
    }
  }

  public function doneAction() {
    $result = false;
    $intentionId = $this->_getParam('id','');
    $done = $this->_getParam('done','');
    if ($intentionId && ($done=='0' || $done=='1')) {
      $intention = Model_Intentions::fetchOne(array('_id' => new MongoId($intentionId)));
      $user = Model_Users::find($this->_userId);
      //Добавляем Я хочу, убираем Я сделал
      if ($done=='0') {
        //удаляем у юзера
        if (in_array($intentionId, $user->done)) {
          $user->addOperation('$pull', "done", $intentionId);
        }
        if (!in_array($intentionId, $user->wants)) {
          $wants = $user->wants;
          $wants[] = $intentionId;
          $user->wants = $wants;
	      $user->wants_count=count($wants);
          $taskName = 'wants';
        }
        //Удаляем у желания
        if (in_array($this->_userId, $intention->done)) {
          $intention->addOperation('$pull', "done", $this->_userId);
        }
        if (!in_array($this->_userId, $intention->wants)) {
          $wants = $intention->wants;
          $wants[] = $this->_userId;
          $intention->wants = $wants;
        }
        $intention->save();
        $user->save();
        $result = true;
      }
      elseif ($done=='1') {
        //удаляем у юзера
        if (in_array($intentionId, $user->wants)) {
          $user->addOperation('$pull', "wants", $intentionId);
        }
        if (!in_array($intentionId, $user->done)) {
          $done = $user->done;
          $done[] = $intentionId;
          $user->done = $done;
          $taskName = 'done';
        }
        if (in_array($this->_userId, $intention->wants)) {
          $intention->addOperation('$pull', "wants", $this->_userId);
	        $user->wants_count=$user->wants_count -1;
        }
        if (!in_array($this->_userId, $intention->done)) {
          $done = $intention->done;
          $done[] = $this->_userId;
          $intention->done = $done;
        }
        $intention->save();
        $user->save();
        $result = true;
      }

      if (isset($taskName)) {
        Model_Tasks::add($taskName, array(
                                    'uid' => $this->_userId,
                                    'intention_id' => $intentionId
                                    ));
      }
    }
    $this->_helper->json($result);
  }

  public function removeAction() {
    $result = false;
    $intentionId = $this->_getParam('id','');
    if ($intentionId) {
      $intention = Model_Intentions::fetchOne(array('_id' => new MongoId($intentionId)));
      $user = Model_Users::find($this->_userId);
      if (in_array($this->_userId, $intention->done)) {
        $intention->addOperation('$pull', "done", $this->_userId);
      }
      if (in_array($this->_userId, $intention->wants)) {
        $intention->addOperation('$pull', "wants", $this->_userId);
      }
      if (in_array($intentionId, $user->done)) {
        $user->addOperation('$pull', 'done', $intentionId);
      }
      if (in_array($intentionId, $user->wants)) {
        $user->addOperation('$pull', 'wants', $intentionId);
	    $user->wants_count=$user->wants_count -1;
      }
      $intention->save();
      $user->save();
      $result = true;
    }
    $this->_helper->json($result);
  }

	public function wantsviewAction() {
		$result = false;
		$intentionId = $this->_getParam('id','');
		$wants = $this->_getParam('wants','');
		if ($intentionId && ($wants=='0' || $wants=='1')) {
			$intention = Model_Intentions::fetchOne(array('_id' => new MongoId($intentionId)));
			$user = Model_Users::find($this->_userId);
			//Добавляем Я хочу, убираем Я сделал
			if ($wants=='0') {
				//удаляем у юзера
				if (in_array($intentionId, $user->wants)) {
					$user->addOperation('$pull', "wants", $intentionId);
					$user->wants_count=$user->wants_count -1;
                    if (isset($user->rating)) {
                        $user->rating -= Model_Users::RATING_WANTS;
                    }
				}
				//Удаляем у желания
				if (in_array($this->_userId, $intention->wants)) {
					$intention->addOperation('$pull', "wants", $this->_userId);
				}
				$intention->save();
				$user->save();
				$result = true;
			}
			elseif ($wants=='1') {
				//удаляем у юзера
				if (in_array($intentionId, $user->done)) {
					$user->addOperation('$pull', "done", $intentionId);
                     if (isset($user->rating)) {
                        $user->rating -= Model_Users::RATING_DONE;
                     }
				}
				if (!in_array($intentionId, $user->wants)) {
					$wants = $user->wants;
					$wants[] = $intentionId;
					$user->wants = $wants;
					$user->wants_count=count($wants);
					$taskName = 'wants';
                    if (isset($user->rating)) {
                        $user->rating += Model_Users::RATING_WANTS;
                    } else {
                        $user->rating = Model_Users::RATING_WANTS;
                    }
				}
				if (in_array($this->_userId, $intention->done)) {
					$intention->addOperation('$pull', "done", $this->_userId);
				}
				if (!in_array($this->_userId, $intention->wants)) {
					$wants = $intention->wants;
					$wants[] = $this->_userId;
					$intention->wants = $wants;
				}
				$intention->save();
				$user->save();
				$result = true;
			}
			if (isset($taskName)) {
				Model_Tasks::add($taskName, array(
					'uid' => $this->_userId,
					'intention_id' => $intentionId
				));
			}
		}
		$this->_helper->json($result);
	}

	public function doneviewAction() {
		$result = false;
		$intentionId = $this->_getParam('id','');
		$done = $this->_getParam('done','');
		if ($intentionId && ($done=='0' || $done=='1')) {
			$intention = Model_Intentions::fetchOne(array('_id' => new MongoId($intentionId)));
			$user = Model_Users::find($this->_userId);
			//Добавляем Я хочу, убираем Я сделал
			if ($done=='0') {
				//удаляем у юзера
				if (in_array($intentionId, $user->done)) {
					$user->addOperation('$pull', "done", $intentionId);
                    if (isset($user->rating)) {
                        $user->rating -= Model_Users::RATING_DONE;
                    }
				}
				//Удаляем у желания
				if (in_array($this->_userId, $intention->done)) {
					$intention->addOperation('$pull', "done", $this->_userId);
				}
				$intention->save();
				$user->save();
				$result = true;
			}
			elseif ($done=='1') {
				//удаляем у юзера
				if (in_array($intentionId, $user->wants)) {
					$user->addOperation('$pull', "wants", $intentionId);
					$user->wants_count=$user->wants_count -1;
                    if (isset($user->rating)) {
                        $user->rating -= Model_Users::RATING_WANTS;
                    }
				}
				if (!in_array($intentionId, $user->done)) {
					$done = $user->done;
					$done[] = $intentionId;
					$user->done = $done;
					$taskName = 'done';
                    if (isset($user->rating)) {
                        $user->rating += Model_Users::RATING_DONE;
                    } else {
                        $user->rating = Model_Users::RATING_DONE;
                    }
				}
				if (in_array($this->_userId, $intention->wants)) {
					$intention->addOperation('$pull', "wants", $this->_userId);
				}
				if (!in_array($this->_userId, $intention->done)) {
					$done = $intention->done;
					$done[] = $this->_userId;
					$intention->done = $done;
				}
				$intention->save();
				$user->save();
				$result = true;
			}

			if (isset($taskName)) {
				Model_Tasks::add($taskName, array(
					'uid' => $this->_userId,
					'intention_id' => $intentionId
				));
			}
		}
		$this->_helper->json($result);
	}

	public function searchAction() {
		$query=$this->_getParam('query', '');
		if(empty($query)){
			$this->_forward('index','index');
		}else{
			$this->view->intentions = Model_Intentions::searchIntentions($query);
			$this->view->query=$query;
			$user=Model_Users::find($this->_userId);
			if(isset($user->wants)){
				$this->view->userWants = $user->wants;
			}
			if(isset($user->done)){
				$this->view->userDone = $user->done;
			}
			$check=Model_Intentions::all(array('description'=>$query), array('description','_id'));
			if($check->count()){
				foreach($check as $row) {
					$intentionId = $row->_id->__toString();
				}
				$this->_setParam('id', $intentionId);
				$this->_forward('view','intention');
			}
			elseif(!isset($this->view->intentions)){
				$this->_setParam('description', $query);
				$this->_setParam('done', 0);
				$this->_setParam('redirect', 'view');
				$this->_forward('add','intention');
			}
		}
	}

    public function superAction(){
        $result = array(
                        'error' => 'Внутренняя ошибка, повторите позже'
                    );
        $id = $this->_getParam('id','');
        $color = $this->_getParam('color','');
        $votes = 100;
        if ($id && $color) {
            $intention = Model_Intentions::find($id);
            if ($intention) {
                $result = App_Vkontakte::api(App_Vkontakte::$apiUrl, 'secure.getBalance', array('uid'=>$this->_userId));
                if (isset($result['response']) && $result['response']>=$votes) {
                    $result = App_Vkontakte::api(App_Vkontakte::$apiUrl, 'secure.withdrawVotes', array('uid'=>$this->_userId, 'votes'=>$votes));
                    if (!isset($result['error']) && isset($result['response']) && $result['response']==$votes) {
                        $result = array();
                        $intention->super = time();
                        $intention->color = $color;
                        $intention->save();
                    }
                }
            }
        }
        $this->_helper->json($result);
    }

  public function tenderAction() {
    $this->view->tenderpage = 1;
  }

    public function deleteAction()
    {
        if ($this->view->admin) {
            $id=$this->_getParam('id');
            $intention=Model_Intentions::find($id);
            if ($intention) {
                $intention->delete();
            }
        }
        $this->_redirect('/');
    }
}

