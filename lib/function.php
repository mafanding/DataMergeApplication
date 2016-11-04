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

/**
 * 
 * 2016年11月4日  add mfd
 */
function utfconvert($strFrom,$charset,$isfromUtf=false){
    if (!trim($strFrom)) return $strFrom;
    $fileGBU = fopen(dirname(__FILE__).'/default/'.($isfromUtf?'utf2'.$charset:$charset.'2utf').'.dat', "rb");
    $strBuf = fread($fileGBU, 2);
    $intCount = ord($strBuf{0}) + 256 * ord($strBuf{1});
    $strRet = "";
    $intLen = strlen($strFrom);
    for ($i = 0; $i < $intLen; $i++) {
        if (ord($strFrom{$i}) > 127) {
            $strCurr = substr($strFrom, $i, $isfromUtf?3:2);
            if($isfromUtf){
                $intGB = $this->utf82u($strCurr);
            }else{
                $intGB = hexdec(bin2hex($strCurr));
            }
            $intStart = 1;
            $intEnd = $intCount;
            while ($intStart < $intEnd - 1) {
                $intMid = floor(($intStart + $intEnd) / 2);
                $intOffset = 2 + 4 * ($intMid - 1);
                fseek($fileGBU, $intOffset);
                $strBuf = fread($fileGBU, 2);
                $intCode = ord($strBuf{0}) + 256 * ord($strBuf{1});
                if ($intGB == $intCode) {
                    $intStart = $intMid;
                    break;
                }
                if ($intGB > $intCode) $intStart = $intMid;
                else $intEnd = $intMid;
            }
            $intOffset = 2 + 4 * ($intStart - 1);
            fseek($fileGBU, $intOffset);
            $strBuf = fread($fileGBU, 2);
            $intCode = ord($strBuf{0}) + 256 * ord($strBuf{1});
            if ($intGB == $intCode) {
                $strBuf = fread($fileGBU, 2);
                if($isfromUtf){
                    $strRet .= $strBuf{1}.$strBuf{0};
                }else{
                    $intCodeU = ord($strBuf{0}) + 256 * ord($strBuf{1});
                    $strRet .= $this->u2utf8($intCodeU);
                }
            } else {
                $strRet .= "??";
            }
            $i+=$isfromUtf?2:1;
        } else {
            $strRet .= $strFrom{$i};
        }
    }
    fclose($fileGBU);
    return $strRet;
}