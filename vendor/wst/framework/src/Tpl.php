<?php
class Tpl
{
	//模板文件路径
	protected $tplPath;
	//缓存文件路径
	protected $cachePath;
	//缓存生命周期
	protected $lifetime;
	//保存分配过来的变量
	protected $var=[];
	//初始化
	public function __construct($tplPath='./view/',$cachePath='./cache/',$lifetime=3600)
	{
		$this->tplPath=$this->checkPath($tplPath);
		$this->cachePath=$this->checkPath($cachePath);
		$this->lifetime=$lifetime;
	}
	//检查目录
	public function checkPath($dir)
	{
		$dir=rtrim($dir,'/').'/';
		if(!file_exists($dir) || !is_dir($dir))
		{
			//如果目录不存在的话创建目录
			return mkdir($dir,0777,true);
		}
		if(!is_readable($dir) || !is_writable($dir))
		{
			//如果没有读写权限的话授权
			return chmod($dir,0777);
		}
		return $dir;
	}
	//保存分配变量方法
	public function assign($key,$value)
	{
		return $var[$key]=$value;
	}
	//display 方法 $isExcute 代表是否包含
	public function display($tplFile,$isExecute)
	{
		if(empty($tplFile))
		{
			die('没有模板文件');
		}
		$tplFilePath=rtrim($this->tplPath,'/').'/'.$tplFile;
		if(!file_exists($tplFilePath))
		{
			exit('模板文件不存在');
		}
		//根据模板文件生成缓存文件
		$cacheFile=rtrim($this->cachePath,'/').'/'.md5($tplFile).'.php';
		if(!file_exists($cacheFile))
		{
			//去模板文件中，将所有模板语言替换成php的语法
			$file=$this->complie($cacheFile);
			file_put_contents($cacheFile, $file);
		}else{
			//缓存文件存在时判断文件是否过期
			$isTimeout=(filetime($cacheFilePath));
			$isChange=filemtime($tplFilePath)>filemtime($cacheFile)?true:false;
			if($isTimeout|| $isChange)
			{
				//生成缓存文件
				$file=$this->complie($tplFilePath);
				file_put_contents($cacheFile,$file);
			}
			//判断是否需要包含
			if($isExecute)
			{
				extract($this->vars);
				include $cacheFile;
			}
		}
	}
	public function complie($tplFilePath)
	{
		//将模板文件全部读进来，生成字符串
		$file=file_get_contents($tplFilePath);
		//按照规则 正则替换
		$keys=[
			'__%%__'           =>'<?php echo \1;?>',
			'${%%}'            =>'<?php echo \1;?>',
			'{$%%}'            =>'<?=$\1?>',
			'{if %%}'          =>'<?php is(\1):?>',
			'{else}'           =>'<?php else:?>',
			'{elseif %%}'      =>'<?php elseif(\1):?>',
			'{/if}'            =>'<?php endif;?>',
			'{switch %% case %%}' =>'<?php switch(\1):case \2;?>',
			'{case %%}'        =>'<?php case \1:?>',
			'{continue}'       =>'<?php continue;?>',
			'{break}'          =>'<?php break;?>',
			'{default}'        =>'<?php default:?>',
			'{/switch}'  	   => '<?php endswitch;?>',
			'{while %%}' 	   => '<?php while(\1):?>',
			'{/while}'	 	   => '<?php endwhile;?>',
			'{for %%}'   	   => '<?php for(\1):?>',
			'{/for}'    	   => '<?php endfor;?>',
			'{foreach %%}' 	   => '<?php foreach(\1):?>',
			'{/foreach}' 	   => '<?php endforeach;?>',
			'{$%%++}'	 	   => '<?php $\1++;?>',
			'{$%%--}'	 	   => '<?php $\1--;?>',
			'{/*}'		 	   => '<?php /*',
			'{*/}'		 	   => '*/?>',
			'{section}'		   => '<?php ',
			'{/section}'	   => '?>',
			'{$%% = $%%}'	   => '<?php $\1 = $\2;?>',
			'{include %%}' 	   => '<?php include "\1";?>'
		];
		foreach($keys as $key=>$value)
		{
			//将特殊字符 如果里面有#进行转义
			$key=preg_quote($key,'#');
			//定义正则表达式，#作为定界符
			$pattern='#'.str_replace('%%', '(.+)', $key).'#imsU';
			//stripos 查找include首次出现的位置
			if(stripos($pattern,'include'))
			{
				$file=preg_replace_callback($pattern,[$this,'parseInclude'], $file);
			}else{
				$file=preg_replace($pattern,$value,$file);
			}
		}
		return $file;
	}
	//处理include包含文件里面的数据
	protected function parseInclude($data)
	{
		$fileName=trim($data[1],'\'"');
		$this->display($fileName,false);
		$filePath=rtrim($this->cachePath,'/').'/'.md5($fileName).'.php';
		$string='<?php include "'.$filePath.'";?';
		return $string;
	}
}