<?php
class Model_Notification extends App_Model_Base
{
    protected static $_collection = 'notification';

	protected static $_requirements = array(
		'uids' => 'Validator:Array',
		'params' => 'Required',
	);

  public static function add($notification) {
	  $count=Model_Users::all()->count();
	  for ($i=0; $i < $count; $i+=100){
		  unset($uids);
		  $uidsUsers= Model_Users::all(array( 'uid' => array( '$exists' => true )), array('uid'))->skip($i)->limit(100);
		  $task = new self();
		  $task->params = $notification;
		  foreach($uidsUsers as $user){
			  $uids[]=$user->uid;
		  }
		  $task->uids = $uids;
		  $task->save();
	  }
	  return $i;
  }

  public static function sendNotification() {
    $tasks = self::all()->limit(1);
    foreach ($tasks as $task) {
      $uids = $task->uids;
      $params = $task->params;
      if ($uids) {
        $data = array(
          'uids' => implode(',', $uids),
          'message' => $params
        );
        App_Vkontakte::api('http://api.vkontakte.ru/api.php', 'secure.sendNotification', $data);
        sleep(1);
      }
      self::remove(array('_id'=>$task->_id));
    }
  }
}