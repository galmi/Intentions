<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Ильдар
 * Date: 22.12.11
 * Time: 21:07
 * To change this template use File | Settings | File Templates.
 */

abstract class App_Model_Base extends Shanty_Mongo_Document
{
  private static $_instances = array();

  protected static $_storage = array();

  protected static $_db;

//  public function __construct($data = array(), $config = array())
//  {
//    $class = get_called_class();
//    if (array_key_exists($class, self::$_instances))
//      trigger_error("Tried to construct  a second instance of class \"$class\"", E_USER_WARNING);
//    parent::__construct($data = array(), $config = array());
//  }

  public static function setDbName($dbName) {
    self::$_db = $dbName;
  }

  /**
   * @static
   * @abstract
   * @return Shanty_Mongo_Document
   */
  public static function getInstance()
  {
    $class = get_called_class();
    if (array_key_exists($class, self::$_instances) === false)
      self::$_instances[$class] = new $class();
    return self::$_instances[$class];
  }

}
