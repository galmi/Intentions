<?php
class Model_Intentions extends App_Model_Base
{
    protected static $_collection = 'intentions';

	protected static $_requirements = array(
		//'id' => array('Required', 'Validator:Int'),
		'description' => 'Required',
		//'tags' => '',
		'wants' => 'Array',
		'done' => 'Array'
	);

    /**
   * @static
   * @return Model_Intentions
   */

  public static function getInstance(){
    return parent::getInstance();
  }

	public static function getTags($description){
		//$tags=explode(" ", mb_strtolower($description,'UTF-8'));
		$tags=preg_split("/[^\w\pL-]/u", mb_strtolower($description,'UTF-8'));
		foreach($tags as $key => $value){
			if(mb_strlen($value,'UTF-8') < 4 ){
				unset($tags[$key]);
			}
		}
		shuffle($tags);
        var_dump($tags);
		return $tags;
	}

  public static function add($userId, $data) {
    //создаем новую хотелку
    $intention = Model_Intentions::getInstance();
    $intention->author = $userId;
    $intention->description = $data['description'];
	$tags=Model_Intentions::getTags($data['description']);
	$intention->tags = $tags;
    if ($data['done']){
      $intention->done = array($userId);
    } else {
      $intention->wants = array($userId);
    }
    $intention->save();
    $intentionId = $intention->getId();
    $intentionId = $intentionId->__toString();
    //добавляем юзеру в список хотелок
    $user = Model_Users::find($userId, array('done','wants','rating'));
    if ($data['done']){
      if (count($user->done)){
        $done = $user->done;
        $done[] = $intentionId;
        $user->done = $done;
      } else {
        $user->done = array($intentionId);
      }
      $taskName = 'done';
      $rating = Model_Users::RATING_DONE;
    } else {
      if (count($user->wants)){
        $wants = $user->wants;
        $wants[] = $intentionId;
        $user->wants = $wants;
      } else {
        $user->wants = array($intentionId);
      }
      $taskName = 'wants';
      $rating = Model_Users::RATING_WANTS;
    }
    if (!isset($user->rating)) {
        $user->rating = $rating;
    } else {
        $user->rating += $rating;
    }
    //обновляем список хотелок у юзера
    $user->wants_count = count($user->wants);
    $user->save();
    //Создаем задачу для оповещения всех друзей
    Model_Tasks::add($taskName, array(
      'uid' => $userId,
      'intention_id' => $intentionId
    ));
    return $intentionId;
  }

  public static function my($userId, $filter=null){
    $result = array();
    $intentions = Model_Users::find($userId, array('wants', 'done'));
    if ($filter=='wants' && isset($intentions->wants)) {
      $intentionWants = array_map(function($value){return new MongoId($value);}, $intentions->wants);
    }
    elseif ($filter == 'done' && isset($intentions->done)) {
      $intentionDone = array_map(function($value){return new MongoId($value);}, $intentions->done);
    }
    else {
      if (isset($intentions->wants)) {
        $intentionWants = array_map(function($value){return new MongoId($value);}, $intentions->wants);
      }
      if (isset($intentions->done)) {
        $intentionDone = array_map(function($value){return new MongoId($value);}, $intentions->done);
      }
    }
    if (isset($intentionDone)) {
      $result['done'] = Model_Intentions::all(array('_id' => array('$in' => $intentionDone)), array('_id', 'description'));
    }
    if (isset($intentionWants)) {
      $result['wants'] = Model_Intentions::all(array('_id' => array('$in' => $intentionWants)), array('_id', 'description'));
    }
    return $result;
  }

	public static function searchIntentions($query){
		$result = array();
		if ($query){
			$count = Model_Intentions::all(array('description'=>new MongoRegex('/'.$query.'/i')), array('description','_id'))->count();
			if($count < 20){
				$result1 = Model_Intentions::all(array('description'=>new MongoRegex('/'.$query.'/i')), array('description','_id'))->limit(20);
				$tags=Model_Intentions::getTags($query);
				unset($query);
				foreach($tags as $value){
					$query['tags']['$in'][]= $value;
				}
				if($count != 0){
					foreach($result1 as $row){
						$query['$and'][]= array("_id" => array('$ne' => 'ObjectId("'.$row->_id->__toString().'")'));
					}
				}
				$result2 = Model_Intentions::all($query)->limit(20 - $count);
				$result=array_merge(iterator_to_array($result1),iterator_to_array($result2));
			}else{
				$result = Model_Intentions::all(array('description'=>new MongoRegex('/'.$query.'/i')), array('description','_id'))->limit(20);
			}

		}
		return $result;

	}

	public static function checkIntentions($query){
		$result = 0;
		if ($query){
			$result = Model_Intentions::all(array('description'=>$query), array('description','_id'));
			if($result->count()){

			}
		}
		return $result;

	}

  public static function getNew($page) {
    $result = array();
    $exclude = array();
    $super = self::all(array('super'=>array('$gt'=>(time() - 60*60*24))))->sort(array('super'=>-1));
    if ($super && $super->count()) {
        foreach($super as $row) {
            $exclude[] = $row->getId();
            if ($page==1) {
                $result[] = array(
                  'uid' => $row->uid,
                  'friend' => $row->author,
                  'ts' => $row->getId()->getTimestamp(),
                  'action' => 'want',
                  'description' => $row->description,
                  'intention_id' => $row->getId()->__toString(),
                  'color' => $row->color
                );
            }
        }
    }
    $feed = self::all(array('_id'=>array('$nin'=>$exclude)))->sort(array('_id'=>-1))->limit(10)->skip(($page-1)*10);
    foreach($feed as $row) {
      $result[] = array(
          'uid' => $row->uid,
        'friend' => $row->author,
        'ts' => $row->getId()->getTimestamp(),
        'action' => 'want',
        'description' => $row->description,
        'intention_id' => $row->getId()->__toString(),
      );
    }
    return $result;
  }

	public static  function updateTags(){
		$query=self::all(array( 'tags' => array( '$exists' => false )))->limit(40);
        var_dump($query);
		foreach($query as $row){
			$intentions = self::find($row->_id);
			$intentions->tags = self::getTags($row->description);
			$intentions->save();
		}
	}
}