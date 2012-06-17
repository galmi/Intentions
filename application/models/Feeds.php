<?php
class Model_Feeds extends App_Model_Base
{
  protected static $_collection = 'feeds';

	protected static $_requirements = array(
	);

  public static function get($userId, $page) {
    $result = array();
    $feed = self::all(array('uid'=>$userId))->sort(array('ts'=>-1))->limit(10)->skip(($page-1)*10);
    foreach($feed as $row) {
      $result[] = array(
        'uid' => $row->uid,
        'friend' => $row->friend,
        'ts' => $row->ts,
        'action' => $row->action,
        'description' => $row->description,
        'intention_id' => $row->intention_id
      );
    }
    return $result;
  }
}