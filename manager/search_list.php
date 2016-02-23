<?php
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
$smarty->assign('lists',get_list());
$smarty->assign('lang', $_LANG);
$act = $_GET['act'];
if($act == 'remove')
{
    $id = !empty($_GET['id']) ? intval($_GET['id']) : '';
    $sql = "DELETE FROM " . $ecs->table('search_list') .
        " WHERE id = '$id'";
    $db->query($sql);
    clear_cache_files();
    $url = 'search_list.php';

    ecs_header("Location: $url\n");
    exit;
}elseif($act == 'edit')
{
    $id = !empty($_GET['id']) ? intval($_GET['id']) : '';
    $sql = "SELECT keyword FROM" . $GLOBALS['ecs']->table('search_list')."WHERE id = ".$id;
    $keyword=$db->getOne($sql);
    $smarty->assign('keyword',$keyword);
    $smarty->assign('id',$id);
    $smarty->display('search_edit.html');
    exit;
}elseif($act == 'edit2')
{
    $id = !empty($_GET['id']) ? intval($_GET['id']) : '';
    $keyword = $_POST['keyword'];
    $sql = "UPDATE " . $ecs->table('search_list') . " SET " .
        "keyword = '$keyword' " ."WHERE id = ".$id;
    $db->query($sql);
    clear_cache_files();
    $link[] = array('href' => 'search_list.php', 'text' => '返回列表');
    sys_msg('修改成功', 0, $link);
}elseif($act == 'add')
{
    $smarty->display('search_add.html');
    exit;
}elseif($act == 'add2')
{
    $sql = "SELECT id FROM" . $GLOBALS['ecs']->table('search_list')."ORDER BY id desc";
    $id = intval($db->getOne($sql));
    $ids = $id + '1';
    $keyword = $_POST['keyword'];
    $sql = "INSERT INTO " . $ecs->table('search_list') . " (id,keyword)" .
        "VALUES ('$ids','$keyword')";
    $db->query($sql);
    clear_cache_files();
    $link[] = array('href' => 'search_list.php', 'text' => '返回列表');
    sys_msg('添加成功', 0, $link);
}
$smarty->display('search_list.html');


function get_list()
{
    $sql = "SELECT * FROM" . $GLOBALS['ecs']->table('search_list');
    $lists = $GLOBALS['db']->getAll($sql);
    return $lists;
}