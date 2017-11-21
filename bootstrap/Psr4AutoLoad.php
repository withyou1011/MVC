<?php

//实现自动加载
class Psr4Autoload
{	//专门用来存放命名空间
	protected $namespaces=array(); //app/in....
	public function register(){
		spl_autoload_register(array($this,'loadClass'));
		//另一种写法：spl_autoload_register( "Psr4Autoload::loadClass" );
		///另一种写法：spl_autoload_register( array(' Psr4Autoload','loadClass') );
	}
 	//调用不存在的类时自动触发
	public function loadClass($className){
		//echo 'loadClass:'.$className.'<br/>';
		$pos=strrpos($className,'\\');
		//把命名空间截取出来 
		$namespace=substr($className, 0,$pos+1);
		//截取真实的类名
		$realClassName=substr($className,$pos+1);
		$this->mapLoad($namespace,$realClassName);
	}

	//添加命名空间
	public function addNamespaces($namespace,$path){
		$this->namespaces[$namespace][]=$path;
		//var_dump($this->namespaces);
		//var_dump($namespace , $path);
	}
	//mapLoad函数，调用loadClass时自动触发，即调用不存在的类时自动触发
	public function mapLoad($namespace , $realClassName){  //controller  indexcontroller
		if(isset($this->namespaces[$namespace])==false){
			//处理和命名空间和一级目录文件名对应的
			$path=$namespace.$realClassName.'.php';
			$path=str_replace('\\', '/', $path);
			$file=strtolower($path);
			$this->requireFile($path);
		}else{
			//处理和一级目录文件名对应不了的
			//$this->namespaces[$namespace] 为二维数组 $path为二维数组下的值eg
				/*
					array (size=1)
					  'Controller\' => 
						array (size=1)
						  0 => string 'app/controller' (length=14)
				此处 $path='app/controller';
				*/
				//var_dump('mapload不对应的path: '.$path.'<br/>');
				
			foreach($this->namespaces[$namespace] as $path){
				$file=$path.'/'.$realClassName.'.php';
				$this->requireFile($file);
			}
		}
    }

    public function requireFile($path){
    	if(file_exists($path)){
    		include $path;
    		return true;
    	}
    	return false;
    }


}


