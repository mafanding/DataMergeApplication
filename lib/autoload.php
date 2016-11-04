<?php
function autoloadClass($className){
	if (file_exists(CLASS_DIR.$className.'.php')) {
		include CLASS_DIR.$className.'.php';
	}
}

spl_autoload_register('autoloadClass');