<?php
class Model_Stories extends Model_Base
{
    protected static $_collection = 'stories';

    protected static $_requirements = array(
        'story' => 'Required',
        'value' => 'Required',
        'believe' => 'Array',
        'not_believe' => 'Array'
    );

    public static $values = array(
        'believe' => 'Правда',
        'not_believe' => 'Ложь'
    );

    /**
     * @static
     * @return Model_Users
     */
    public static function getInstance()
    {
        return parent::getInstance();
    }

    public static function add($userId, $data)
    {
        //Добавляем историю
        $story = new self();
        $story->author = $userId;
        $story->story = $data['story'];
        $story->value = $data['value'];
        $story->believe = ($data['value']=='believe')?array($userId):array();
        $story->not_believe = ($data['value']=='not_believe')?array($userId):array();
        $story->save();
        $storyId = $story->getId()->__toString();
        //обавляем юзеру в список историй
        Model_Users::update(array('_id'=>new MongoId($userId)), array('$push'=>array('stories'=>$storyId)));
        return $storyId;
    }

    public static function listStories ($userId, $page=1, $type='all')
    {
        if ($page<1) {
            $page = 1;
        }
        $query = array();
        if ($type=='new') {
            $query = array(
                'believe' => array('$ne'=>$userId),
                'not_believe' => array('$ne'=>$userId)
            );
        } elseif ($type=='my') {
            $query = array(
                'author' => $userId
            );
        }
        $stories = Model_Stories::all($query)->sort(array('_id'=>-1))->limit(10)->skip(($page-1)*10);
        $result = array();
        foreach ($stories as $row) {
            $vote = null;
            $value = null;
            if (in_array($userId, $row->believe)) {
                $vote = 'believe';
                $value = $row->value;
            }
            if (in_array($userId, $row->not_believe)) {
                $vote = 'not_believe';
                $value = $row->value;
            }
            $result[] = array(
                'id' => $row->_id->__toString(),
                'story' => $row->story,
                'author' => $row->author,
                'value' => $value,
                'vote' => $vote,
                'believe_count' => count($row->believe),
                'not_believe_count' => count($row->not_believe)
            );
        }
        return $result;
    }

    public static function vote($userId, $storyId, $value)
    {
        $story = Model_Stories::find($storyId);
        if (!in_array($userId, $story->believe) && !in_array($userId, $story->not_believe))
        {
            Model_Stories::update(array('_id'=>new MongoId($storyId)), array('$push'=>array($value=>$userId)));
            $story = Model_Stories::find($storyId);
        }
        $result = array(
            'value' => $story->value,
            'believe_count' => count($story->believe),
            'not_believe_count' => count($story->not_believe)
        );
        return $result;
    }
}