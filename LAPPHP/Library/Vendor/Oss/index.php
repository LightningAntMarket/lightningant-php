<?php

function classLoader($class)
{
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . DIRECTORY_SEPARATOR .'src'. DIRECTORY_SEPARATOR . $path . '.php';
	
//	echo $file;
    if (file_exists($file)) {
        require_once $file;
    }
}
spl_autoload_register('classLoader');
use OSS\OssClient;



function deleteObj($name){
	
$OssClient = new OssClient('Nibl6BAZWAWH5vZt','Ek8MkSDalEdrrx3Izbg2MLNu1PtWpd','http://oss-cn-beijing.aliyuncs.com', $isCName = false, $securityToken = NULL);

$OssClient->deleteObject('yibaisong',$name);	
}


