<?php
define('MODX_API_MODE', true);
define('IN_MANAGER_MODE', true);
include('../../../index.php');
$modx->getSettings();
$loginid = $modx->getLoginUserID('mgr');

if(!empty($loginid)){

    // get module data
    $rs = $modx->db->select('properties', $modx->getFullTableName('site_modules'), 'id = '.intval($_REQUEST['id']), '', '1');
    if ($modx->db->getRecordCount($rs) > 0){
        $properties = $modx->db->getValue($rs);
    }
    
    // load module configuration
    $parameters = array();
    if(!empty($properties)){
        $tmpParams = explode("&",$properties);
        for($x=0; $x<count($tmpParams); $x++) {
            $pTmp = explode("=", $tmpParams[$x]);
            $pvTmp = explode(";", trim($pTmp[1]));
            if ($pvTmp[1]=='list' && $pvTmp[3]!="") $parameters[$pTmp[0]] = $pvTmp[3]; //list default
            else if($pvTmp[1]!='list' && $pvTmp[2]!="") $parameters[$pTmp[0]] = $pvTmp[2];
        }
    }
    
    include_once('classes/management.class.inc.php');
    if (class_exists('GalleryManagement'))
    {
        $manager = new GalleryManagement($parameters,$modx);
        $res = $manager->executeAction();
        if ($res===TRUE)
            echo json_encode(array('result'=>'ok','ref'=>$_SERVER['HTTP_HOST']));
        elseif ($res===FALSE)
            echo json_encode(array('result'=>'error','msg'=>$manager->lang['operation_error']));
        else echo $res;
    }
    else
        $modx->logEvent(1, 3, 'Error loading Portfolio Galleries management module');

}else{
    echo json_encode(array('result'=>'error','msg'=>'Access denied.'));
}

exit();