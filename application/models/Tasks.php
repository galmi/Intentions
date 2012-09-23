<?php
class Model_Tasks extends App_Model_Base
{
    protected static $_collection = 'tasks';

	protected static $_requirements = array(
		'name' => 'Required',
		'params' => 'Array',
	);

  public static function add($name, $params) {
    $task = self::getInstance();
    $task->name = $name;
    $task->params = $params;
    $task->datetime = time();
    $task->save();
  }

  public static function feed() {
    $tasks = self::all()->limit(20);
    $delete = array();
    foreach ($tasks as $task) {
      $uids = array();
      $params = $task->params;
      $name = $task->name;
      if (in_array($name, array('wants','done'))) {
          $intention = Model_Intentions::fetchOne(array('_id'=>new MongoId($params['intention_id'])), array('description','author'));
          $user = Model_Users::find($params['uid']);
          if ($intention && $user) {
            if ($user->friends){
              foreach($user->friends as $friendUid) {
                $feed = new Model_Feeds();
                $feed->uid = $friendUid;
                $feed->friend = $params['uid'];
                $feed->action = $task->name;
                $feed->ts = $task->datetime;
                $feed->description = $intention->description;
                $feed->intention_id = $intention->getId()->__toString();
                $feed->save();
                $uids[] = $friendUid;
              }
            }
            //отправка автору
            if ($intention->author != $params['uid']) {
              $feed = new Model_Feeds();
              $feed->uid = $intention->author;
              $feed->friend = $params['uid'];
              $feed->action = $task->name;
              $feed->ts = $task->datetime;
              $feed->description = $intention->description;
              $feed->intention_id = $intention->getId()->__toString();
              $feed->save();
              $uids[] = $friendUid;
            }

    //        $feed = new Model_Feeds();
    //        $feed->uid = $params['uid'];
    //        $feed->friend = $params['uid'];
    //        $feed->action = $task->name;
    //        $feed->ts = $task->datetime;
    //        $feed->description = $intention->description;
    //        $feed->intention_id = $intention->getId()->__toString();
    //        $feed->save();
          }
          if ($uids) {
            $userData = App_Vkontakte::api('http://api.vkontakte.ru/api.php', 'getProfiles', array('uids'=>$params['uid']));
            if (isset($userData['response'])) {
              $username = $userData['response'][0]['first_name'].' '.$userData['response'][0]['last_name'];
              $action = ($task->name=='done')?' сделал ':' хочет ';
              $message = 'Ваш друг '.$username.$action.'"'.$intention->description.'"';
            }
            $data = array(
              'uids' => implode(',', $uids),
              'message' => $message
            );
            App_Vkontakte::api('http://api.vkontakte.ru/api.php', 'secure.sendNotification', $data);
            sleep(1);
          }
//Отправка уведомления о комментарии
      } elseif($name=='comment') {
          $intention = Model_Intentions::fetchOne(array('_id'=>new MongoId($params['intention_id'])), array('description','author','wants','done'));
          if ($intention) {
              echo implode(',',array_unique(array_merge((array)$intention->author,$intention->wants,$intention->done)));
              $data = array(
                  'uids' => $intention->author,//implode(',',array_unique(array_merge((array)$intention->author,$intention->wants,$intention->done))),
                  'message' => 'В желании "'.$intention->description.'" появился новый комментарий'
              );
              App_Vkontakte::api('http://api.vkontakte.ru/api.php', 'secure.sendNotification', $data);
              sleep(1);
          }
//Напомнить вернуться в приложение
      } elseif($name=='comeback') {
        $data = array(
          'uids' => '33058161',//$params['uids'],
          'message' => 'Возвращайся, тебя ждут новые желания, друзья и конкурс на голоса!'
        );
        App_Vkontakte::api('http://api.vkontakte.ru/api.php', 'secure.sendNotification', $data);
        sleep(1);
        continue;
      }
      self::remove(array('_id'=>$task->_id));
    }
  }
}