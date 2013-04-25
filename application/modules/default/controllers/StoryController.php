<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ildar
 * Date: 02.12.12
 * Time: 20:10
 * To change this template use File | Settings | File Templates.
 */
class StoryController extends App_Controller_Base
{

    public function indexAction()
    {

    }

    public function addAction()
    {
        $result = array('success'=>false,'error'=>'Нечего добавлять');
        $story = htmlspecialchars(trim($this->_getParam('story','')));
        $value = $this->_getParam('value','');
        if ($story && in_array($value, array('believe', 'not_believe'))) {
            $storyId = Model_Stories::add($this->_userId, array(
                'story' => $story,
                'value' => $value
            ));
            $result = array('success'=>true, 'data'=>array('story_id' => $storyId));
        }
        $this->_helper->json($result);
    }

    public function listAction()
    {
        $page = (int)$this->_getParam('page',1);
        $type = $this->_getParam('type','all');
        $stories = Model_Stories::listStories($this->_userId, $page, $type);
        $this->_helper->json(array(
            'success' => true,
            'data' => $stories
        ));
    }

    public function voteAction()
    {
        $result = array('success'=>false);
        $id = $this->_getParam('id','');
        $value = $this->_getParam('value','');
        if ($id && in_array($value, array('believe','not_believe'))) {
            $data = Model_Stories::vote($this->_userId, $id, $value);
        }
        if (isset($data)) {
            $result = array(
                'success' => true,
                'data' => $data
            );
        }
        $this->_helper->json($result);
    }

    public function deleteAction()
    {
        $result = array('success'=>false,'message'=>'Ошибка');
        if ($this->view->admin) {
            $id=$this->_getParam('id','');
            $story=Model_Stories::find($id);
            if ($story) {
                $story->delete();
                $result = array('success'=>true,'message'=>'OK');
            }
        }
        $this->_helper->json($result);
    }

}
