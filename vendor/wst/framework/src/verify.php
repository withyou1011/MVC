<?php

class Verify
{
	// 宽
	protected $width;
	// 高
	protected $height;
	// 图片的类型
	protected $imgType;
	// 字体的类型
	protected $type;
	// 字体的个数
	protected $num;
	// 资源
	protected $img;
	// 画布上的字符串
	protected $getCode;

	// 初始化成员属性
	public function __construct($width = 100, $height = 30, $num = 4, $type = 3, $imgType = 'png')
	{
		$this->width 	= $width;
		$this->height  	= $height;
		$this->imgType 	= $imgType;
		$this->num    	= $num;
		$this->type   	= $type;

		$this->getCode  = $this->getCode();

	}

	// 准备字符串
	public function getCode()
	{
		$string = '';
		switch ($this->type) {
			// 纯数字
			case 1:
				$string = join('', array_rand(range(0, 9), $this->num));
				//var_dump($string);
				break;
			// 纯字母
			case 2:
				$string = implode('', array_rand(array_flip(range('a', 'z')), $this->num));
				//var_dump($string);
				break;
			// 数字字母混合
			case 3:
				// $str = 'abcdefghijklmnnopqrestuvwxyzABCDEFALJRERWRSEFJKSDLFK0123456789';
				// $string = substr(str_shuffle($str), 0, $this->num);
				// var_dump($string);
				for ($i=0; $i<$this->num; $i++) {
					$rand = mt_rand(0,2);
					switch ($rand) {
						case 0:
							$char = mt_rand(48, 57); //数字
							break;
						case 1:
							$char = mt_rand(97, 122); //小写字母
							break;
						case 2:
							$char = mt_rand(65, 90); //大写字母
							break;
					}
					$string .= sprintf('%c', $char);  //拼接转换ACSII值
				}
				//var_dump($string);
				break;
		}
		// 返回字符串
		return $string;

	}

	// 创建画布
	protected function createImg()
	{
		$this->img = imagecreatetruecolor($this->width , $this->height);
	}

	// 背景颜色(浅色)
	protected function bgColor()
	{
		return imagecolorallocate($this->img, mt_rand(150, 255), mt_rand(150, 255), mt_rand(150, 255));
	}
	// 字体颜色(深色)
	protected function fontColor()
	{
		return imagecolorallocate($this->img, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));
	}

	// 填充背景色
	protected function fill()
	{
		return imagefilledrectangle($this->img, 0, 0, $this->width, $this->height, $this->bgColor());
	}

	// 将准备的字符串画在上面
	protected function write()
	{
		for ($i = 0; $i < $this->num; $i++) {
			// 求出X坐标 
			 $x = ceil($this->width / $this->num) * $i + 7;
			// 求出y的坐标
			 $y = mt_rand(5, $this->height - 20);
			// 写字符串
			imagechar($this->img, 5, $x, $y, $this->getCode[$i], $this->fontColor());
		}
	}

	// 画干扰点(像素)
	protected function pixed()
	{
		for ($i = 0; $i < $this->num * $this->height; $i++) {
			imagesetpixel($this->img , mt_rand(0, $this->width), mt_rand(0, $this->height), $this->fontColor());
		}
	}

	// 画干扰线
	protected function arc()
	{
		for ($i = 0; $i < $this->num; $i++) {
			imagearc($this->img, mt_rand(10, $this->width), mt_rand(10, $this->height), mt_rand(10, $this->width), mt_rand(10, $this->height), mt_rand(0, 40), mt_rand(140, 220), $this->fontColor());
		}
	}

	// 输出图片到浏览器
	protected function out()
	{
		
		$header = 'Content-type:image/' . $this->imgType;
		$func = 'image'.$this->imgType;
		if (function_exists($func)) {
			$func($this->img);
			header($header);  // 告知浏览器类型
		} else {
			exit('不支持图片格式');
		}

	}

	// 得到图片
	public function getImg()
	{
		$this->createImg();
		$this->fill();
		$this->arc();
		$this->pixed();
		$this->write();
		$this->out();
	}

	// 销毁图片资源
	public function __destruct()
	{
		imagedestroy($this->img);
	}

}


//$v = new Verify();

//$v->getImg();