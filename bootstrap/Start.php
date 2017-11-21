<?php
class  Start{
	static $loader;
	//初始化框架的方法
	public static function run(){
		include 'bootstrap/Psr4AutoLoad.php';
		$namespaces=include 'config/namespaces.php';
		//var_dump($namespaces);
		/*var_dump($namespaces)
		array (size=3)
		  'Controller\' => string 'app/controller' (length=14)
		  'Model\' => string 'app/model' (length=9)
		  'View\' => string 'app/view' (length=8)
		 */
		self::$loader = new Psr4Autoload();
		self::$loader->register();
		self::addNamespaces($namespaces);
		
	} 
	public static function addNamespaces($namespaces){
		//var_dump($namespaces);
		foreach($namespaces as $path=>$namespace){
			//调用的是psr4里边的addNamespaces；
			//var_dump($path);
			self::$loader->addNamespaces($namespace,$path);
		}
	}

	//http://localhost/teacher/6/mvc/index.php?m=user&a=suibian
	//处理路由的问题 m是指类名前不带Controller的， a是指对应类中的方法名
	public static function route(){
		$_GET['m']=isset($_GET['m'])?$_GET['m']:'Index';
		$_GET['a']=isset($_GET['a'])?$_GET['a']:'index';
		$_GET['m']=ucfirst($_GET['m']);
		$controller='Controller\\'.$_GET['m'].'Controller';
		//eg:Controller\UserController 
		$c=new $controller();
		call_user_func(array($c,$_GET['a']));
	}
}
