<?php
/**
 * 
 * @author mfd
 *
 */
class MergeData {
	/**
	 * 
	 * @var array
	 */
	private $_fieldsAlias=[];
	
	/**
	 * 
	 * @var array
	 */
	private $_fillDefaultValue=[];
	
	/**
	 * 
	 * @var array
	 */
	private $_tmpStorageCampany=[];
	
	/**
	 * 
	 * @var array
	 */
	private $_storageArray=[];
	
	function __construct() {
		$this->_fieldsAlias=loadConfigFile('alias');
		$this->_fillDefaultValue=loadConfigFile('defaultvalue');
	}
	
	/**
	 * 
	 * @param unknown $csvfiles
	 * @param unknown $output_directory
	 * @param unknown $output_name
	 */
	public function exec($csvfiles,$output_directory,$output_name){
		//读取到内存
		$this->readContentToArray($csvfiles);
		//分割detailStr字符串为数组
		$this->dealDetailStr();
		//填充数组
		$this->fillStorageArray();
		//导出.csv
		$this->exportCSVFile($output_directory,$output_name);
	}
	
	/**
	 * 
	 * @param array $files
	 */
	private function readContentToArray($files=[]){
		//读取顺序1.店铺信息，2.手机信息，3.普通信息or商品店铺，5.规格信息
		//目前需要注意：1.steoNum3需要改成stepNum3,2.删除列search两列、specName两列,3.转换后所有csv文件需要去除最后一行(已解决)，4.生成的文件可能需要手动转码为UTF-8,5.通过makefile生成phar包，6.对这个函数做细分
		foreach ($files as $file){
			$fh=new SplFileObject($file,'rb');
			$fh->setFlags(SplFileObject::SKIP_EMPTY);
			$is_start=true;
			$is_norm_file=false;
			$is_company_file=false;
			$is_phone_file=false;
			$rows=0;
			while (!$fh->eof()){
				$arr=$fh->fgetcsv();
				if (is_array($arr)&&!empty($arr)){
					array_walk($arr,function (&$value){
						$value=trim($value,'"﻿"');
						$value=rtrim($value,'#');
						$value=trim($value);
					});
						if ($is_start){
							$is_start=false;
							$index=count($arr);
							$fieldsname=[];
							for ($i=0;$i<$index;$i++){
								$fieldsname[$i]=$this->getFieldAlias($arr[$i]);
								if ($fieldsname[$i]==$this->getFieldAlias('webUrl')){
									$key=$i;
								}
								if ($fieldsname[$i]==$this->getFieldAlias('specKindDetailValue')){
									$is_norm_file=true;
								}
								if ($fieldsname[$i]==$this->getFieldAlias('companyUrl')){
									$is_company_file=true;
									$key=$i;
								}
								if ($fieldsname[$i]==$this->getFieldAlias('telPhone')){
									$is_phone_file=true;
									$key=array_keys($fieldsname,$this->getFieldAlias('shopUrl'))[0];
								}
							}
						}else{
							for ($i=0;$i<$index;$i++){
								if ($is_norm_file){
									if ($fieldsname[$i]!=$this->getFieldAlias('webUrl')){
										$this->_storageArray[$arr[$key]]['norm'][$rows][$fieldsname[$i]]=$arr[$i];
									}
								}elseif ($is_company_file){
									$this->_tmpStorageCampany[$arr[$key]][$fieldsname[$i]]=$arr[$i];
								}elseif ($is_phone_file){
									if ($key!=$i){
										$this->_tmpStorageCampany[$arr[$key]][$fieldsname[$i]]=$arr[$i];
									}
								}else{
								    if (empty($fieldsname[$i])&&empty($arr[$i])) {
								        continue;
								    }
									$this->_storageArray[$arr[$key]][$fieldsname[$i]]=$arr[$i];
								}
							}
							if ($is_norm_file){
								$rows++;
								if (array_key_exists($this->getFieldAlias('shopUrl'),$this->_storageArray[$arr[$key]])&&array_key_exists($this->_storageArray[$arr[$key]][$this->getFieldAlias('shopUrl')],$this->_tmpStorageCampany)){
									foreach ($this->_tmpStorageCampany[$this->_storageArray[$arr[$key]][$this->getFieldAlias('shopUrl')]] as $k=>$v){
										if ($k!=$this->getFieldAlias('companyUrl')){
											$this->_storageArray[$arr[$key]][$k]=$v;
										}
									}
								}
							}
						}
				}
			}
			$fh=null;
		}
	}
	
	/**
	 * 
	 */
	private function dealDetailStr(){
		foreach ($this->_storageArray as $key=>&$value){
			if (isset($value[$this->getFieldAlias('detailStr')])){
				$arr=$this->explodeRecursive($value[$this->getFieldAlias('detailStr')]);
				foreach ($arr as $k2=>$v2){
					$this->_storageArray[$key][$k2]=$v2;
				}
				unset($value[$this->getFieldAlias('detailStr')]);
			}
		}
	}
	
	/**
	 * 
	 * @param string $string
	 * @param string $firstdelimiter
	 * @param string $seconddelimiter
	 * @return mixed[]
	 */
	private function explodeRecursive($string='',$firstdelimiter=';',$seconddelimiter=':'){
		$arr=[];
		$tmp=explode($firstdelimiter,$string);
		foreach ($tmp as $value){
			$rtmp=explode($seconddelimiter,$value);
			if (array_key_exists($rtmp[0],$this->_fieldsAlias)){
				$arr[$this->getFieldAlias($rtmp[0])]=$rtmp[1];
			}
		}
		return $arr;
	}
	
	/**
	 * 
	 * @param string $originalFieldname
	 * @return string|mixed
	 */
	private function getFieldAlias($originalFieldname=''){
		$fieldAlias='';
		if (array_key_exists($originalFieldname,$this->_fieldsAlias)){
			$fieldAlias=$this->_fieldsAlias[$originalFieldname];
		}else{
			$fieldAlias=$originalFieldname;
		}
		return $fieldAlias;
	}
	
	/**
	 * 
	 */
	private function fillStorageArray(){
		foreach ($this->_storageArray as &$items){
			foreach ($this->_fillDefaultValue  as $k=>$v){
				if (!is_array($v)){
					if (!array_key_exists($k,$items)){
						$items[$k]=$v;
					}
				}else{
					if (!array_key_exists('norm',$items)){
						foreach ($v as $k2=>$v2){
							$items['norm'][0][$k2]=$v2;
						}
					}
				}
			}
		}
	}
	
	/**
	 * 
	 * @param unknown $output_directory
	 * @param unknown $output_name
	 */
	private function exportCSVFile($output_directory,$output_name){
		$fh=new SplFileObject($output_directory.'/'.$output_name.'.csv','wb');
		$a=$this->_fillDefaultValue;
		$b=array_pop($a);
		ksort($a);
		$a=array_merge($a,$b);
		$a=array_keys($a);
		$fh->fputcsv($a);
		unset($a);
		foreach ($this->_storageArray as $v1){
			$tmpArr=$v1;
			unset($tmpArr['norm']);
			ksort($tmpArr);
			foreach ($v1['norm'] as $v2){
				$tmpArr=array_merge($tmpArr,$v2);
				$fh->fputcsv($tmpArr);
			}
		}
		$fh=null;
	}
}