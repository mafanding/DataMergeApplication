<?php
/**
 * 
 * @author mfd
 *
 */
class ConvertToCSV {
	
	public function xlsx2csv($files=[]){
		foreach ($files as $file){
			$objReader = PHPExcel_IOFactory::createReader('Excel2007');
			$objPHPExcel = $objReader->load($file);
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
			$objWriter->save(str_replace('.xlsx', '.csv',$file));
			unset($objReader,$objWriter,$objPHPExcel);
			chmod($file,0777);
			unlink($file);
		}
	}
}