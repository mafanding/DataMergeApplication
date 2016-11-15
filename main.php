<?php
/**
 * input_dir output_dir output_name
 */

if (PHP_SAPI!=='cli') {
	printf('This application only run in cli mode!');
	exit(0);
}

include 'lib/constant.php';
include LIB_DIR.'function.php';
include LIB_DIR.'autoload.php';
//require_once EXTRA_DIR.'PHPExcel/PHPExcel.php';

$input_directory=isset($_SERVER['argv'][1])?$_SERVER['argv'][1]:getcwd();
$output_directory=isset($_SERVER['argv'][2])?$_SERVER['argv'][2]:getcwd();
$output_name=isset($_SERVER['argv'][3])?$_SERVER['argv'][3]:time().'_export';

import('PHPExcel');

$CTCSV=new ConvertToCSV();
$CTCSV->xlsx2csv(getDirExcel($input_directory));

$csvfiles=getDirExcel($input_directory,'.csv');

if (!empty($csvfiles)){
	$md=new MergeData();
	$md->exec($csvfiles,$output_directory,$output_name);
	clearFiles($csvfiles);
	printf("done!\r\n");
}else{
	printf("There are none files need to merge!\r\n");
}
exit(0);