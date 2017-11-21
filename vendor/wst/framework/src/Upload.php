<?php
class Upload{
	//路径
	protected $path='./';
	//准许的mime类型
	protected $allowMime=array('image/jpeg','image/png','image/gif','image/wbmp');
	//准许的文件后缀名
	protected $allowSub=array('jpg','png','gif','wbmp','jpeg');
	//准许的大小
	protected $allowSize=2000000;
	//文件的错误号
	protected $errorNum;
	//文件的错误信息
	protected $errorInfo;
	//文件大小
	protected $size;
    //文件的新名字
    protected $newName;
    //文件的原名字
    protected $orgName;
    //随机文件名
    protected $isRandName=true;
    //临时文件名
    protected $tmpName;
    //文件的前缀
    protected $prefix;
    //文件的后缀
    protected $subFix;
    //上传文件的mime类型
    protected $type;

    //初始化成员属性
    public function __construct($array=array()){
        
        //var_dump($array);
        foreach($array as $key=>$val){
            
            $keys=strtolower($key);
            
            //得到当前的类名
            //var_dump(get_class($this));
 
            //得到当前类中所有的成员属性
            //var_dump(get_class_vars(get_class($this)));

            if(!array_key_exists($keys, get_class_vars(get_class($this)))){

                continue;
            }

            //通过一个方法实现批量赋值
            $this->setOption($keys,$val);
            //var_dump($this->path);
        }

    }

    //上传文件的方法  input表单里面的name值
    public function up($fields){
       
        //var_dump($_FILES);
        /*
        array (size=1)
		  'abc' => // $fields
		    array (size=5)
		      'name' => string '3.jpg' (length=5)
		      'type' => string 'image/jpeg' (length=10)
		      'tmp_name' => string 'D:\wamp\tmp\phpA254.tmp' (length=23)
		      'error' => int 0
		      'size' => int 18963
         */
        //var_dump($fields);
        
        //检测路径是否存在
        if(!$this->checkPath()){
            exit('没有上传文件');
        }

        //获取文件的各种信息
        $name=$_FILES[$fields]['name'];
        $type=$_FILES[$fields]['type'];
        $tmpName=$_FILES[$fields]['tmp_name'];
        $error=$_FILES[$fields]['error'];
        $size=$_FILES[$fields]['size'];

        //setFiles
        if($this->setFiles($name,$type,$tmpName,$error,$size)){
            //是否启用随机文件的名字
            $this->newName=$this->createName();
            
           //echo $this->newName;

           if($this->checkMime()&&$this->checkSub()&&$this->checkSize()){
               
                //移动文件
                
                if($this->move()){
                   return $this->newName;
                }
                else{
                	return false;
                }
           }
           else{
           	return false;
           }

        }


    }

    //移动文件
    protected function move(){
    	if(is_uploaded_file($this->tmpName)){
            $this->path=rtrim($this->path,'/').'/'.$this->newName;
            //移动文件
            if(move_uploaded_file($this->tmpName,$this->path)){
            	return true;

            }
            else{
            	$this->setOption('errorNum',-6);
            	return false;
            }
    	}
    	else{
    		return false;
    	}
    }

    //检测mime类型
    protected function checkMime(){
    	if(in_array($this->type,$this->allowMime)){
            return true;
    	}
    	else{
    		$this->setOption('errorNum',-3);
    		return false;
    	}
    }

    //检测后缀
    protected function checkSub(){
    	if(in_array($this->subFix,$this->allowSub)){
            return true;
    	}
    	else{
    		$this->setOption('errorNum',-4);
    		return false;
    	}
    }

    //检测大小
    protected function checkSize(){
    	if($this->size > $this->allowSize){
    		$this->setOption('errorNum',-5);
            return false;
    	}
    	else{
    		return true;
    	}
    }

    //随机文件名 createName()
    
    protected function createName(){
    	if($this->isRandName){

    		//var_dump($this->prefix);

            return $this->prefix.$this->randName();
    	}
    	else{
    		return $this->prefix.$this->orgName;
    	}
    }

    //随机文件名  randName()
    protected function randName(){

    	return uniqid().'.'.$this->subFix;
    }
    
    
    //setFiles
    
    protected function setFiles($name,$type,$tmpName,$error,$size){

    	if($error){
            
            // 1 2 3 4 6 7
            
            $this->setOption('errorNum',$error);
    	}
    	$this->orgName=$name;
    	$this->type=$type;
    	$this->tmpName=$tmpName;
    	$this->size=$size;

        //获取文件的后缀
        //var_dump($name);
        

        $arr=explode('.',$name);
        //var_dump($arr);
        
        //将数组arr最后一个元素出栈赋值给$this->subFix
        $this->subFix=array_pop($arr);

        return true;

    }

    //检测路径
    protected function checkPath(){
    	if(empty($this->path)){
            $this->setOption('errorNum',-1);
            return false;
    	}
    	else{

    		if(file_exists($this->path)&& is_writable($this->path)){
                return true;
    		}
    		else{
    			$this->path=rtrim($this->path,'/').'/';
    			if(mkdir($this->path,0777,true)){
                   return true;
    			}
    			else{
    				$this->setOption('errorNum',-2);
    				return false;
    			}
    		}
    	}
    }


    //设置成员属性 错误号
    protected function setOption($keys,$val){
        
         //var_dump($keys,$val); path  upload
         
         $this->$keys=$val;

      }
    
    //获取错误号
    protected function getErrorNum(){

        $str='';
        switch($this->errorNum){
           case -1:
               $str='没有上传文件';
               break;
           case -2:
               $str='文件夹创建失败';
               break;
           case -3:
               $str='不准许的mime类型';
               break;
           case -4:
               $str='不准许的文件的后缀';
               break;
           case -5:
               $str='不准许的文件的大小';
               break;
           case -6:
               $str='上传失败';
               break;
           case 1:
				$str = '上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。 ';
				break;
			case 2:
				$str = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
				break;
			case 3:
				$str = '文件只有部分被上传';
				break;
			case 4:
				$str = '没有文件被上传';
				break;
			case 6:
				$str = '找不到临时文件夹';
				break;
			case 7:
				$str = '文件写入失败';
				break;

        }
        return $str;
    }
    
    public function __get($name){
         
        if($name='errorInfo'){
            return $this->getErrorNum();
        }
    }
}


