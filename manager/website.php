<?php 
define('IN_ECS' ,true);
include_once(dirname(__FILE__) . '/includes/init.php');

if($_REQUEST['act'] == 'list')
{
	$filepath = ROOT_PATH . 'includes/login/';
	
	$openfn = opendir($filepath);
	$arr =array();
	$idx = 0;
	$name = '';
	while($file = readdir($openfn))
	{
		if($file != '.' && $file != '..' && $file != 'functions.php' && $file != 'config' && substr($file , strlen($file)-4) == '.php')
		{
			$arr[$idx]['path'] = $filepath.$file;
			$arr[$idx]['name'] = $file;
			$arr[$idx]['type'] = substr($file , 0 , strlen($file)-4);
			$name.=$arr[$idx]['type'].';';
			
			if(file_exists($filepath.'config/'.$arr[$idx]['type'].'_config.php')) // 检查是否已经安装
			{
				$arr[$idx]['install'] = 1;
			}
			$info = get_info($filepath.$file);
			$arr[$idx]['info'] = $info;
			if(empty($info['name']) || empty($info['author']) || empty($info['email']) || empty($info['qq']) || empty($info['version']))
			{
				unset($arr[$idx]);
			}
			++$idx;
		}
	}
	
	closedir($openfn);
	$smarty->assign('website_name' , $name); // 取回已有插件
	$smarty->assign('action_link' , array('href' => 'website.php?act=init' , 'text' => $_LANG['init']));
	$smarty->assign('ur_here' , $_LANG['ur_here']);
	$smarty->assign('website' , $arr);
	assign_query_info();
	$smarty->display('website.htm');
}
elseif($_REQUEST['act'] == 'install' || $_REQUEST['act'] == 'view')
{
	$view = $_REQUEST['act'] == 'view';
	$type = getChar('type');
	if(!$type) header('Location: website.php?act=list');
	$filepath = ROOT_PATH . 'includes/login/';
	if($view)
	{
		if(file_exists($filepath .'config/'.$type.'_config.php'))
		{
			include_once($filepath .'config/'.$type.'_config.php');
			if($type == 'qq' || $type == 'weibo' || $type == 'taobao') // 为了兼容 其他插件
			{
				$smarty->assign('app_key' , APP_KEY);
				$smarty->assign('app_secret',APP_SECRET);
				$sql = 'SELECT rank_id,rank_name FROM '.$ecs->table('user_rank').' WHERE rank_id=\''.RANK_ID.'\'';
				$smarty->Assign('rank' , $db->getRow($sql));
			}
			else
			{
				$smarty->assign('app_key' , APP_KEY);
				$smarty->assign('app_secret',APP_SECRET);
				$sql = 'SELECT rank_id,rank_name FROM '.$ecs->table('user_rank').' WHERE rank_id=\''.RANK_ID.'\'';
				$smarty->Assign('rank' , $db->getRow($sql));
			}
		}
	}
	
	$smarty->assign('ur_here' , $view ? $_LANG['ur_view'] : $_LANG['ur_install']);
	$smarty->assign('action_link' , array('href'=>'website.php?act=list' , 'text' => $_LANG['webstte_list']));
	$smarty->assign('type' , $type);
	$smarty->assign('act' , $view ? 'update_website' : 'query_install');
	assign_query_info();
	$smarty->display('website_install.htm');
}
elseif($_REQUEST['act']  == 'query_install' || $_REQUEST['act'] == 'update_website')
{
	$type = getChar('type');
	$app_key = getChar('app_key');
	$app_secret = getChar('app_secret');
	$rank_name = getChar('rank_name');
	$rank_id  = getInt('rank_id');
	$query = $_REQUEST['act']  == 'query_install';
	$olb_rank_name = getChar('olb_rank_name');
	if($query || !$rank_id)
	{
		$sql = 'INSERT INTO '.$ecs->table('user_rank').'(`rank_name` , `discount` , `special_rank`,`show_price`) VALUES'.
				"('$rank_name' , '100' , '1','0')";
		$db->query($sql);
		$rank_id = $db->insert_id();
	}
	else
	{
		if($rank_name != $olb_rank_name && $rank_id)
		{
			$sql = 'UPDATE '.$ecs->table('user_rank').' SET `rank_name` = '."'$rank_name' WHERE `rank_id`='$rank_id'";
			$db->query($sql);
		}
	}
	
	
	$commnet = '<?php '.
			   "\r\n // 第三方插件登录信息---------------------\r\n".
			   "define('APP_KEY' , '$app_key'); \r\n".
			   "define('APP_SECRET' , '$app_secret'); \r\n".
			   "define('RANK_ID' , '$rank_id'); \r\n".
			   '?>';
	$filename = ROOT_PATH . 'includes/login/config/'.$type.'_config.php';
	
	file_put_contents($filename , $commnet);
	$link[0] = array('href' => 'website.php?act=list' , 'text' => $_LANG['webstte_list']);
	assign_query_info();
	
	sys_msg(($query ? $_LANG['yes_install'] : $_LANG['yes_update']) , 0 ,  $link);
}
elseif($_REQUEST['act'] == 'uninstall')
{
	$type = getChar('type');
	$filepath = ROOT_PATH . 'includes/login/';
	$link[0] = array('href' => 'website.php?act=list' , 'text' => $_LANG['webstte_list']);
	if(file_exists($filepath .'config/'.$type.'_config.php'))
	{
		include_once($filepath .'config/'.$type.'_config.php');
		if(!defined(RANK_ID))
			$db->query('DELETE FROM '.$ecs->table('user_rank').' WHERE `rank_id`=\''.RANK_ID.'\'');
		@unlink($filepath .'config/'.$type.'_config.php');
		assign_query_info();
		sys_msg($_LANG['yes_uninstall'] , 0 , $link);
	}
	assign_query_info();
	sys_msg($_LANG['no_uninstall'] , 1 , $link);
}
elseif($_REQUEST['act'] == 'init')
{
	$fields = $db->getCol('DESC '.$ecs->table('users'));
	$init = true;
	foreach($fields as $val)
	{
		if($val == 'aite_id')
		{
			$init = false;
			break;
		}
	}
	
	$link[0] = array('href' => 'website.php?act=list' , 'text' => $_LANG['webstte_list']);
	if($init)
		$db->query("ALTER TABLE ".$ecs->table('users')." ADD `aite_id` VARCHAR( 120 ) NOT NULL DEFAULT 'ecshop' AFTER `user_id`");
	assign_query_info();
	sys_msg($_LANG['yes_init'] , 0 , $link);
}

function get_info($filename)
{
	$fp = fopen($filename,'rb');
	$str = fread($fp, 350);
	fclose($fp);
	$str = str_replace('*' , '' ,$str);

	$arr = explode("\n" , $str);
	$ret = array();
	
	foreach($arr as $val)
	{
		$pos = strpos($val, ':');
		if($pos > 0)
		{
			$type = trim(substr($val, 0, $pos), "-\n\r\t ");
            $value = trim(substr($val, $pos+1), "/\n\r\t ");
			//echo $type;
			if($type == 'name')
			{
				$ret['name'] = $value;
			}
			elseif($type == 'author')
			{
				$ret['author'] = $value;
			}
			elseif($type == 'E-mail')
			{
				$ret['email'] = $value;
			}
			elseif($type == 'QQ')
			{
				$ret['qq'] = $value;
			}
			elseif($type == 'VERSION')
			{
				$ret['version'] = $value;
			}
			elseif($type == 'DATE')
			{
				$ret['date'] = $value;
			}
		}
	}
	return $ret;
}


function getInt($name , $def = 0)
{
	return empty($_REQUEST[$name]) ? $def : intval($_REQUEST[$name]);
}

function getChar($name , $def = '')
{
	return empty($_REQUEST[$name]) ? $def : htmlspecialchars(trim($_REQUEST[$name]));
}
?>