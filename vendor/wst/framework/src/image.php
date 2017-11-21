<?php
class Image{

	//路径
	public $path='./';

	//初始化路径
	public function __construct($path='./'){
       
        $this->path=rtrim($path,'/').'/';
	}

	public function water($dst,$src,$prefix='water_',$position=10,$opacity=100,$isRandName=true){

        //大图图片路径
        $dst=$this->path.$dst;

        //小图图片路径
        $src=$this->path.$src;
        //exit($src);
        //判断路径是否存在
        if(!file_exists($dst)){
            exit('大图路径不存在');
        }
        if(!file_exists($src)){
            exit('小图路径不存在');
        }

        //获取文件信息
        
        $dstInfo=self::getImageinfo($dst);
        //var_dump($dstInfo);
        
        $srcInfo=self::getImageinfo($src);
        //var_dump($srcInfo);
        
        //比较大小
        if(!$this->checkSize($dstInfo,$srcInfo)){
            exit('小图大于了大图的宽高');
        }

        //获取位置
        $position=self::getPosition($dstInfo,$srcInfo,$position);

        //得到的是一个数组
        //var_dump($position);
        
        
        //打开图片
        $dstRes=self::openImg($dst,$dstInfo);

        /*
        $dst  gyy.jpg

        $dstInfo 图片信息  
         */
        $srcRes=self::openImg($src,$srcInfo);

        //合并图片做准备
        $newRes=self::mergeImg($dstRes,$srcRes,$srcInfo,$position,$opacity);

        //是否是随机的文件名
        if($isRandName){
           $path=$this->path.$prefix.uniqid().$dstInfo['name'];
        }
        else{
        	$path=$this->path.$prefix.$dstInfo['name'];
        }

        //保存图片
        //var_dump($newRes);
        
        self::saveImg($path,$newRes,$dstInfo);

        //销毁图片
        
        imagedestroy($dstRes);
        imagedestroy($srcRes);
	}

    //保存图片
    
    public static function saveImg($path,$newRes,$dstInfo){
       
        switch($dstInfo['mime']){
            case 'image/jpeg':
            case 'image/jpg':
            case 'image/pjpeg':
                imagejpeg($newRes,$path);
                break;
            case 'image/png':
            case 'image/x-png':
                imagepng($newRes,$path);
                break;
            case 'image/wbmp':
            case 'image/bmp':
                imagewbmp($newRes,$path);
                break;
            case 'image/gif':
                imagegif($newRes,$path);
                break;
        }
    }

    //开始合并图片
    public static function mergeImg($dstRes,$srcRes,$srcInfo,$position,$opacity){
         
         imagecopymerge($dstRes,$srcRes, $position['x'],$position['y'],0,0,$srcInfo['width'],$srcInfo['height'],$opacity);

         return $dstRes;
    }

    //打开图片
    public static function openImg($path,$info){
       
        switch($info['mime']){
           case 'image/jpeg':
           case 'image/jpg':
           case 'image/pjpeg':
               $res=imagecreatefromjpeg($path);
               break;
           case 'image/png':
           case 'image/x-png':
               $res=imagecreatefrompng($path);
               break;
           case 'image/wbmp':
           case 'image/bmp':
               $res=imagecreatefromwbmp($path);
               break;
           case 'image/gif':
               $res=imagecreatefromgif($path);
               break;
        }

        return $res;

    }

    //处理位置问题
    
    public static function getPosition($dstInfo,$srcInfo,$position){

    	switch($position){
            case 1:
               $x=0;
               $y=0;
               break;
            case 2:
               $x=($dstInfo['width']-$srcInfo['width'])/2;
               $y=0;
               break;
            case 3:
               $x=$dstInfo['width']-$srcInfo['width'];
               $y=0;
               break;
            case 4:
				$x = 0;
				$y = ($dstInfo['height'] - $srcInfo['height']) / 2;
				break;
			case 5:
				$x = ($dstInfo['width'] - $srcInfo['width']) / 2;
				$y = ($dstInfo['height'] - $srcInfo['height']) / 2;
				break;
			case 6:
				$x = $dstInfo['width'] - $srcInfo['width'];
				$y = ($dstInfo['height'] - $srcInfo['height']) / 2;
				break;
			case 7:
				$x = 0;
				$y = $dstInfo['height'] - $srcInfo['height'];
				break;
			case 8:
				$x = ($dstInfo['width'] - $srcInfo['width']) / 2;
				$y = $dstInfo['height'] - $srcInfo['height'];
				break;
			case 9:
				$x = $dstInfo['width'] - $srcInfo['width'];
				$y = $dstInfo['height'] - $srcInfo['height'];
				break;
		    default:
		       $x=mt_rand(0,$dstInfo['width']-$srcInfo['width']);
		       $y=mt_rand(0,$dstInfo['height']-$srcInfo['height']);  
    	}

    	return [
             'x'=>$x,
             'y'=>$y
    	];
    }

    //处理比较大小
    
    public function checkSize($dstInfo,$srcInfo){
        //判断宽
        if($dstInfo['width']<$srcInfo['width']){
            return false;
        }
        //判断高
        if($dstInfo['height']<$dstInfo['height']){
            return false;
        }

        return true;
    }

	//获取图片信息函数
	public static function getImageinfo($path){
       
         $data=getimagesize($path);

         //var_dump($data);
         //大图信息
         /*
           array (size=7)
			  0 => int 400
			  1 => int 400
			  2 => int 2
			  3 => string 'width="400" height="400"' (length=24)
			  'bits' => int 8
			  'channels' => int 3
			  'mime' => string 'image/jpeg' (length=10)
          */
         //把信息存到数组info里
         $info['width']=$data[0];
         $info['height']=$data[1];
         $info['mime']=$data['mime'];
         $info['name']=basename($path);

         return $info;
	}


	//缩略图
	public function thumb($img, $width, $height, $prefix = 'thumb_')
	{
		if (!file_exists($img)) {
			exit('文件路径不正在');
		}
		
		$info = self::getImageInfo($img);
		$newSize = self::getNewSize($width,$height,$info);
		$res = self::openImg($img, $info);
		$newRes = self::kidOfImage($res,$newSize,$info);
		$newPath = $this->path.$prefix.$info['name'];
		self::saveImg($newPath,$newRes,$info);
		imagedestroy($newRes);
		return $newPath;
	}
	
	
	private static function kidOfImage($srcImg, $size, $imgInfo)
	{
		$newImg = imagecreatetruecolor($size["width"], $size["height"]);		
		$otsc = imagecolortransparent($srcImg);
		if ( $otsc >= 0 && $otsc < imagecolorstotal($srcImg)) {
			 $transparentcolor = imagecolorsforindex( $srcImg, $otsc );
				 $newtransparentcolor = imagecolorallocate(
				 $newImg,
				 $transparentcolor['red'],
					 $transparentcolor['green'],
				 $transparentcolor['blue']
			 );

			 imagefill( $newImg, 0, 0, $newtransparentcolor );
			 imagecolortransparent( $newImg, $newtransparentcolor );
		}

	
		imagecopyresized( $newImg, $srcImg, 0, 0, 0, 0, $size["width"], $size["height"], $imgInfo["width"], $imgInfo["height"] );
		imagedestroy($srcImg);
		return $newImg;
	}
	
	private static function getNewSize($width, $height, $imgInfo)
	{	
		//将原图片的宽度给数组中的$size["width"]
		$size["width"] = $imgInfo["width"];   
		//将原图片的高度给数组中的$size["height"]
		$size["height"] = $imgInfo["height"];  
		
		if($width < $imgInfo["width"]) {
			//缩放的宽度如果比原图小才重新设置宽度
			$size["width"] = $width;             
		}

		if ($width < $imgInfo["height"]) {
			//缩放的高度如果比原图小才重新设置高度
			$size["height"] = $height;       
		}

		if($imgInfo["width"]*$size["width"] > $imgInfo["height"] * $size["height"]) {
			$size["height"] = round($imgInfo["height"] * $size["width"] / $imgInfo["width"]);
		} else {
			$size["width"] = round($imgInfo["width"] * $size["height"] / $imgInfo["height"]);
		}

		return $size;
	}
	

}

$img=new Image();
$img->water('sz.jpg','yx.jpg');

$img->thumb('tyx.jpg',100,100,'thumb_1');
$img->thumb('tyx.jpg',200,200,'thumb_2');