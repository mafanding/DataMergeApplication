<?php
/**
 * 
 * @param string $string
 * @return boolean|array
 */
function loadConfigFile($string=''){
	if (empty($string)){
		return false;
	}
	
	if (file_exists($configFile=ROOT_DIR.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.strtolower($string).'.conf.php')) {
		$configs=include $configFile;
		return $configs;
	}else{
		printf('Can`t find config file!');
		exit(0);
	}
}

/**
 * 
 * @param string $directory
 * @param string $ext
 * @return unknown[]
 */
function getDirExcel($directory='',$ext='.xlsx'){
	$arr=[];
	if ($directory==''){
		$directory=getcwd();
	}
	$files=scandir($directory);
	foreach ($files as $file){
		if (stripos($file,$ext)!==false){
			$arr[]=$directory.DIRECTORY_SEPARATOR.$file;
		}
	}
	return $arr;
}

/**
 * 
 * @param unknown $files
 */
function clearFiles($files){
	foreach ($files as $file){
		unlink($file);
	}
}

/**
 * 
 * 2016年11月4日  add mfd
 */
function import($extra_name){
    if (is_dir(EXTRA_DIR.$extra_name)) {
        require_once EXTRA_DIR.$extra_name.DIRECTORY_SEPARATOR.$extra_name.'.php';
    }else{
        throw new Exception('Invalidate extra bag!');
    }
}
