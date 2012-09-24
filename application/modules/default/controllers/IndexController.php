<?php

class IndexController extends App_Controller_Base
{

	public function init()
	{
		/* Initialize action controller here */
		parent::init();
	}

	public function indexAction()
	{
//    $this->view->feed = Zend_Json::encode(Model_Feeds::get($this->_userId,1));
        $this->view->bannerv = Model_Users::getBannerv();
	}

  public function feedAction() {
    $page = $this->_getParam('page',1);
    $type = $this->_getParam('type','my');
    if ($type=='new') {
      $this->_helper->json(Model_Intentions::getNew($page));
    } else {
      $this->_helper->json(Model_Feeds::get($this->_userId,$page));
    }
  }

  public function autocompleteAction(){
    $query = $this->_getParam('query','');
    $suggestions = array();
    $data = array();
    if ($query){
      $results = Model_Intentions::all(array('description'=>new MongoRegex('/'.$query.'/i')), array('description','_id'))->limit(10);
      foreach($results as $row) {
        $suggestions[] = $row->description;
        $data[] = $row->_id->__toString();
      }
    }

    $this->_helper->json(
      array(
        'query' => $query,
        'suggestions' => $suggestions,
        'data' => $data
      )
    );
  }

  public function authorsAction() {
	  $authors = Zend_Registry::get('authors');
	  $this->view->authors=$authors;
	  if(in_array($this->_userId, $authors)){
		  $this->view->authorsCheck=TRUE;
	  }else{
		  $this->view->authorsCheck=FALSE;
	  }
  }

    public function newcommentAction() {
        $intentionId = $this->_getParam('intention_id','');
        if ($this->_userId && $intentionId) {
            Model_Tasks::add('comment', array(
                                          'intention_id' => $intentionId
                                        ));
        }
        $this->_helper->json(null);
    }

  public function taskAction() {
    Model_Tasks::feed();
    exit;
  }

	public function notificationAction(){
		$authors = Zend_Registry::get('authors');
		if(in_array($this->_userId, $authors)){
		}else{
			exit;
		}
	}
	public function notificationinitAction(){
		$authors = Zend_Registry::get('authors');
		if(in_array($this->_userId, $authors)){
			$notification=$this->_getParam('notification');
			$this->view->notification=$notification;
			$i=Model_Notification::add($notification);
			$this->view->i = $i;
		}else{
			exit;
		}
	}
	public function sendnotificationAction(){
		Model_Notification::sendNotification();
		exit;
	}
	public function gettagsAction() {
		Model_Intentions::updateTags();
        echo "done";
		exit;
	}

//1 шаг. Проходимся по таблице intentions. В каждом желании находим автора.
// Считаем количество людей, которые присоединились к желанию, кроме самого автора. за каждого присоединившегося начисляем 100 единиц рейтинга.
//2 шаг. Проходимся по таблице юзеров. У каждого юзера считаем количество его желаний, которые он хочет. За каждое хотение начисляем 10 единиц рейтинга. За каждое выполненное хотение начисляем 15 единиц рейтинга.
//3 шаг. Все цифры суммируем, получается общий рейтинг юзера
  public function calcratingAction(){
      $rating = array();
      $intentions = Model_Intentions::all(array(), array('author','wants','done'));
      foreach ($intentions as $row) {
          if ($row) {
              $exclude = array($row->author);
              $wants = array_diff(isset($row->wants)?$row->wants:$exclude, $exclude);
              $done = array_diff(isset($row->done)?$row->done:$exclude, $exclude);
              $rating[$row->author] = (count($wants) + count($done))*Model_Users::RATING_INVITE;//(isset($row->wants)?count($row->wants):0 + isset($row->done)?count($row->done):0) * 100;
          }
      }
      $users = Model_Users::all(array(), array('wants','done','uid'));
      foreach($users as $row) {
          if ($row) {
              if (!isset($rating[$row->uid]) && count($row->wants) && count($row->done)) {
                  $rating[$row->uid] = 0;
              }
              if (isset($rating[$row->uid])){
                $rating[$row->uid] = $rating[$row->uid] + count($row->wants)*Model_Users::RATING_WANTS + count($row->done)*Model_Users::RATING_DONE;//isset($rating[$row->uid])?$rating[$row->uid]:0 + (isset($row->wants)?count($row->wants):0)*10 + (isset($row->done)?count($row->done):0)*15;
              }
          }
      }
      if ($rating) {
          foreach($rating as $uid=>$rating) {
              $user = Model_Users::find((string)$uid);
              if ($user) {
                  $user->rating = $rating;
                  $user->save();
              }
          }
      }
      echo 'ok';
      exit;
  }

  /*
   * Уведомление старым юзерам что надо вернуться
   */
  public function comebackAction() {
    $time = mktime(date('H'), date('i'), date('s'), date('n'), date('j')-7, date('Y'));
    $users = Model_Users::all(array('ts'=>array('$lt' => $time)), array('uid','ts'))->limit(50);
    $uids = array();
    $currentTime = time();
    if ($users) {
      foreach($users as $user) {
        $user = Model_Users::find($user->uid);
        if ($user) {
          $user->ts = $currentTime;
          $user->save();
          $uids[] = $user->uid;
        }
      }
      if ($uids) {
        Model_Tasks::add('comeback', array(
          'uids' => implode(',', $uids)
        ));
      }
    }
    exit;
  }
}

