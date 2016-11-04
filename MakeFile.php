<?php
if (PHP_SAPI!=='cli') {
	printf('This application only run in cli mode!');
	exit(0);
}

$projectPath=isset($_SERVER['argv'][1])?$_SERVER['argv'][1]:dirname(__FILE__);
$binFileName=isset($_SERVER['argv'][2])?$_SERVER['argv'][2].'.phar':substr($projectPath, strripos($projectPath, DIRECTORY_SEPARATOR )+1).'.phar';
$defaultStub=isset($_SERVER['argv'][3])?$_SERVER['argv'][3]:'main.php';
$buildInFiles=isset($_SERVER['argv'][4])?'/'.$_SERVER['argv'][4].'/':'/.*/';
$is_compress=isset($_SERVER['argv'][5])?$_SERVER['argv'][5]:false;
// create with alias "project.phar"
$phar = new Phar($binFileName);
// add all files in the project
$phar->buildFromDirectory($projectPath,$buildInFiles);
//compress files
if ($is_compress) {
    $phar->compressFiles(Phar::GZ);
    $phar->stopBuffering();
}
$phar->setStub($phar->createDefaultStub($defaultStub));
printf('done!');
exit(0);