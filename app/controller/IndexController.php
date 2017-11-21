<?php
namespace Controller;
use Framework\Model as model;
class IndexController extends Controller
{
	public function index()
	{	
		echo '这是首页<br />';
		parent::kongzhiqi();
		$m=new model();
		$m->del();
	}
}
