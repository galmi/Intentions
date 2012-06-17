<?php
class Model_Users extends App_Model_Base
{
    const RATING_WANTS = 10;
    const RATING_DONE = 15;
    const RATING_INVITE = 100;

	protected static $_collection = 'users';

	protected static $_requirements = array(
		'uid' => 'Validator:Int',
		'friends' => 'Validator:Array',
		//'friends' => 'DocumentSet',
		//'friends.$' =>  array('Document:Users', 'AsReference'),
		'wants' => 'Validator:Array',
		'done' =>'Validator:Array',
		//'feeds' => '',
		'wants_count' => array('Required', 'Validator:Int')
	);

	/**
	 * @static
	 * @return Model_Users
	 */
	public static function getInstance()
	{
		return parent::getInstance();
	}

	/**
	 * Поиск юзера по uid
	 * @static
	 * @param $uid
	 * @param array $fields
	 * @return Shanty_Mongo_Document
	 */
	public static function find($uid, array $fields = array())
	{
		$query = array('uid' => $uid);
		return static::one($query, $fields);
	}

	public static function addWants($userId, $intentionId) {

		$user = self::find($userId, array('done','wants','wants_count'));
		//добавляем хотелку в wants
		if (count($user->wants)){
			$wants = $user->wants;
			$wants[] = $intentionId;
			$user->wants = $wants;
		} else {
			$user->wants = array($intentionId);
		}
		$user->wants_count = count($user->wants);
		//удаляем их done
		if (count($user->done)){
			$done = $user->done;
			if(in_array($intentionId, $done)){
				$user->done = array_diff($done, array($intentionId));
			}
		}
		$user->save();

	}

	public static function delWants($userId, $intentionId) {
		$user = self::find($userId, array('wants','wants_count'));
		//удаляем их wants
		if (count($user->wants)){
			$wants = $user->wants;
			if(in_array($intentionId, $wants)){
				$user->wants = array_diff($wants, array($intentionId));
				$user->wants_count = count($user->wants);
			}
		}
		$user->save();
	}


	public static function addDone($userId, $intentionId) {
		$user = self::find($userId, array('done','wants','wants_count'));
		//добавляем в done
			if (count($user->done)){
				$done = $user->done;
				$done[] = $intentionId;
				$user->done = $done;
			} else {
				$user->done = array($intentionId);
			}
		//удаляем их wants
			if (count($user->wants)){
				$wants = $user->wants;
				if(in_array($intentionId, $wants)){
				$user->wants = array_diff($wants, array($intentionId));
				$user->wants_count = count($user->wants);
				}
			}
		$user->save();
		}

	public static function delDone($userId, $intentionId) {
		$user = self::find($userId, array('done'));
		//удаляем их done
		if (count($user->done)){
			$done = $user->done;
			if(in_array($intentionId, $done)){
				$user->done = array_diff($done, array($intentionId));
			}
		}
		$user->save();
	}

    public static function ratings(){
        $users = self::fetchAll(array(),array('uid','rating'))->sort(array('rating'=>-1))->limit(50);
        return $users;
    }

    public static function getBannerv() {
        $banners = array();
//        $banners[] = '
//                   <script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                   var s17 = new SWFObject("http://widgets.fotocash.ru/300x250/300x250_1.swf","bannerv","210","","9");
//                   s17.addParam("allowScriptAccess","always");
//                   s17.addParam("scaleMode","noscale");
//                   s17.addParam("wmode","transparent");
//                   s17.addParam("flashVars","link1=http%3A%2F%2Ffotostrana.ru%2Fstart%2Fquestpet%3Fref_id=371153883");
//                   s17.write("bannerv");
//                   </script>';
//        $banners[] = '
//                   <script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                   var s17 = new SWFObject("http://widgets.fotocash.ru/300x250/300x250_2.swf","bannerv","210","","9");
//                   s17.addParam("allowScriptAccess","always");
//                   s17.addParam("scaleMode","noscale");
//                   s17.addParam("wmode","transparent");
//                   s17.addParam("flashVars","link1=http%3A%2F%2Ffotostrana.ru%2Fstart%2Fquestpet%3Fref_id=371153883");
//                   s17.write("bannerv");
//                   </script>';
//        $banners[] = '
//                    <script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                    var s17 = new SWFObject("http://widgets.fotocash.ru/300x250/300x250_9.swf","bannerv","210","","9");
//                    s17.addParam("allowScriptAccess","always");
//                    s17.addParam("scaleMode","noscale");
//                    s17.addParam("wmode","transparent");
//                    s17.addParam("flashVars","link1=http%3A%2F%2Ffotostrana.ru%2Fstart%2Fgetpet%3FmyPetId%3D23%26ref_id=371153883");
//                    s17.write("bannerv");
//                    </script>';
//        $banners[] = '<script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                    var s17 = new SWFObject("http://widgets.fotocash.ru/300x250/300x250_30.swf","bannerv","210","","9");
//                    s17.addParam("allowScriptAccess","always");
//                    s17.addParam("scaleMode","noscale");
//                    s17.addParam("wmode","transparent");
//                    s17.addParam("flashVars","link1=http%3A%2F%2Ffotostrana.ru%2Fstart%2Fgetpet%3FmyPetId%3D26%26ref_id=371153883");
//                    s17.write("bannerv");
//                    </script>';
//        $banners[] = '<script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                    var s17 = new SWFObject("http://widgets.fotocash.ru/300x250/300x250_31.swf","bannerv","210","","9");
//                    s17.addParam("allowScriptAccess","always");
//                    s17.addParam("scaleMode","noscale");
//                    s17.addParam("wmode","transparent");
//                    s17.addParam("flashVars","link1=http%3A%2F%2Ffotostrana.ru%2Fstart%2Fgetpet%3FmyPetId%3D16%26ref_id=371153883");
//                    s17.write("bannerv");
//                    </script>';
//        $banners[] = '<script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                    var s17 = new SWFObject("http://widgets.fotocash.ru/300x250/300x250_32.swf","bannerv","210","","9");
//                    s17.addParam("allowScriptAccess","always");
//                    s17.addParam("scaleMode","noscale");
//                    s17.addParam("wmode","transparent");
//                    s17.addParam("flashVars","link1=http%3A%2F%2Ffotostrana.ru%2Fstart%2Fgetpet%3FmyPetId%3D23%26ref_id=371153883");
//                    s17.write("bannerv");
//                    </script>';
//        $banners[] = '<script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                    var s17 = new SWFObject("http://widgets.fotocash.ru/300x250/300x250_27.swf","bannerv","210","","9");
//                    s17.addParam("allowScriptAccess","always");
//                    s17.addParam("scaleMode","noscale");
//                    s17.addParam("wmode","transparent");
//                    s17.addParam("flashVars","link1=http%3A%2F%2Ffotostrana.ru%2Fstart%2Fgetpet%3FmyPetId%3D20%26ref_id=371153883");
//                    s17.write("bannerv");
//                    </script>';
//        $banners[] = '<script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                    var s17 = new SWFObject("http://widgets.fotocash.ru/300x250/300x250_28.swf","bannerv","210","","9");
//                    s17.addParam("allowScriptAccess","always");
//                    s17.addParam("scaleMode","noscale");
//                    s17.addParam("wmode","transparent");
//                    s17.addParam("flashVars","link1=http%3A%2F%2Ffotostrana.ru%2Fstart%2Fgetpet%3FmyPetId%3D19%26ref_id=371153883");
//                    s17.write("bannerv");
//                    </script>';
//        $banners[] = '<script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                    var s17 = new SWFObject("http://widgets.fotocash.ru/300x250/300x250_46.swf","bannerv","210","","9");
//                    s17.addParam("allowScriptAccess","always");
//                    s17.addParam("scaleMode","noscale");
//                    s17.addParam("wmode","transparent");
//                    s17.addParam("flashVars","link1=http%3A%2F%2Ffotostrana.ru%2Fstart%2Fquestpet%3Fref_id=371153883");
//                    s17.write("bannerv");
//                    </script>';
//        $banners[] = '<script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                    var s17 = new SWFObject("http://widgets.fotocash.ru/300x250/300x250_47.swf","bannerv","210","","9");
//                    s17.addParam("allowScriptAccess","always");
//                    s17.addParam("scaleMode","noscale");
//                    s17.addParam("wmode","transparent");
//                    s17.addParam("flashVars","link1=http%3A%2F%2Ffotostrana.ru%2Fstart%2Fquestpet%3Fref_id=371153883");
//                    s17.write("bannerv");
//                    </script>';
//        $banners[] = '<script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                    var s17 = new SWFObject("http://widgets.fotocash.ru/300x250/300x250_49.swf","bannerv","210","","9");
//                    s17.addParam("allowScriptAccess","always");
//                    s17.addParam("scaleMode","noscale");
//                    s17.addParam("wmode","transparent");
//                    s17.addParam("flashVars","link1=http%3A%2F%2Ffotostrana.ru%2Fstart%2Fquestpet%3Fref_id=371153883");
//                    s17.write("bannerv");
//                    </script>';
//        $banners[] = '<script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                    var s17 = new SWFObject("http://widgets.fotocash.ru/300x250/300x250_50.swf","bannerv","210","","9");
//                    s17.addParam("allowScriptAccess","always");
//                    s17.addParam("scaleMode","noscale");
//                    s17.addParam("wmode","transparent");
//                    s17.addParam("flashVars","link1=http%3A%2F%2Ffotostrana.ru%2Fstart%2Fquestpet%3Fref_id=371153883");
//                    s17.write("bannerv");
//                    </script>';
//        $banners[] = '<script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                    var s17 = new SWFObject("http://widgets.fotocash.ru/300x250/300x250_51.swf","bannerv","210","","9");
//                    s17.addParam("allowScriptAccess","always");
//                    s17.addParam("scaleMode","noscale");
//                    s17.addParam("wmode","transparent");
//                    s17.addParam("flashVars","link1=http%3A%2F%2Ffotostrana.ru%2Fstart%2Fquestpet%3Fref_id=371153883");
//                    s17.write("bannerv");
//                    </script>';
//        $banners[] = '<script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                    var s17 = new SWFObject("http://widgets.fotocash.ru/300x250/300x250_56.swf","bannerv","210","","9");
//                    s17.addParam("allowScriptAccess","always");
//                    s17.addParam("scaleMode","noscale");
//                    s17.addParam("wmode","transparent");
//                    s17.addParam("flashVars","link1=http%3A%2F%2Ffotostrana.ru%2Fstart%2Fquestpet%3Fref_id=371153883");
//                    s17.write("bannerv");
//                    </script>';
//        $banners[] = '<script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                    var s17 = new SWFObject("http://widgets.fotocash.ru/300x250/300x250_58.swf","bannerv","210","","9");
//                    s17.addParam("allowScriptAccess","always");
//                    s17.addParam("scaleMode","noscale");
//                    s17.addParam("wmode","transparent");
//                    s17.addParam("flashVars","link1=http%3A%2F%2Ffotostrana.ru%2Fstart%2Fquestpet%3Fref_id=371153883");
//                    s17.write("bannerv");
//                    </script>';
//        $banners[] = '<script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                    var s17 = new SWFObject("http://widgets.fotocash.ru/300x250/300x250_62.swf","bannerv","210","","9");
//                    s17.addParam("allowScriptAccess","always");
//                    s17.addParam("scaleMode","noscale");
//                    s17.addParam("wmode","transparent");
//                    s17.addParam("flashVars","link1=http%3A%2F%2Ffotostrana.ru%2Fstart%2Fquestpet%3Fref_id=371153883");
//                    s17.write("bannerv");
//                    </script>';
//        $banners[] = '<script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                    var s17 = new SWFObject("http://start.fotostrana.ru/promo/fotostrana/300x250/300x250_2.swf","bannerv","210","","9");
//                    s17.addParam("allowScriptAccess","always");
//                    s17.addParam("scaleMode","noscale");
//                    s17.addParam("wmode","transparent");
//                    s17.addParam("flashVars","link1=http%3A%2F%2Ffotostrana.ru%2Fgo.php%3Fid%3D226%26ref_id=371153883%26gender=m");
//                    s17.write("bannerv");
//                    </script>';
//        $banners[] = '<script src="http://start.fotostrana.ru/static/js/swfobject.js" type="text/javascript"></script> <div id="bannerv"></div> <script>
//                    var s17 = new SWFObject("http://start.fotostrana.ru/promo/fotostrana/300x250/300x250_4.swf","bannerv","210","","9");
//                    s17.addParam("allowScriptAccess","always");
//                    s17.addParam("scaleMode","noscale");
//                    s17.addParam("wmode","transparent");
//                    s17.addParam("flashVars","ref_id=371153883%26gender=m");
//                    s17.write("bannerv");
//                    </script>';
        $banners[] = '<form name="anketa" method="post" action="http://fotostrana.ru/?ref_id=371153883" target="_blank" style="margin-left:0px;width: 200px;text-align: center;background: #F0F8FF;">
<table style="background: #F0F8FF;"  id="anketa">
 <tbody><tr>
  <td class="text">Я</td>
  <td>
   <select name="sex" class="input_box">
    <option value="1">Парень</option>
    <option value="2" selected="">Девушка</option>
   </select>
  </td>
 </tr>

 <tr>
  <td class="text">Ищу</td>
  <td>
   <select name="ssex" class="input_box">
    <option value="1">Парня</option>
    <option value="2">Девушку</option>
   </select>
  </td>
 </tr>

 <tr>
  <td colspan="2" class="text">
   в возрасте
   от <input name="bage" type="text" class="input_box" size="3" maxlength="2" value="19">
   до <input name="tage" type="text" class="input_box" size="3" maxlength="2" value="27">
  </td>
 </tr>

 <tr>
  <td colspan="2" class="text">
   <input type="submit" value="Найти">
  </td>
 </tr>

</tbody></table>
</form>';
        return $banners[rand(0,count($banners)-1)];
    }

    public static function getBannerh() {
        $banners = array();
        $banners[] = '<script type="text/javascript"><!--
                        google_ad_client = "ca-pub-7013266992778346";
                        /* Мир желаний468х60 */
                        google_ad_slot = "4286971544";
                        google_ad_width = 468;
                        google_ad_height = 60;
                        //-->
                        </script>
                        <script type="text/javascript"
                        src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
                        </script>';
        return $banners[rand(0,count($banners)-1)];
    }
}