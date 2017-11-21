<?php
namespace Controller;
use Framework\Image as Img;
class UserController{
	public function suibian(){
		echo '随便<br/>';
	}
	public function info(){
		echo '这是用户详情<br/>';
		$obj=new Img();
		$obj->water();
	}
}