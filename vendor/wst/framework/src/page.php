<?php
class Page
{
	
	//总条数
	protected $total;
	//总页数
	protected $pageCount;
	//每页的显示数
	protected $num;
	//偏移量
	protected $offset;
	//url超链接
	protected $url;
	//当前页
	protected $page;
	
	
	public function __construct($total , $num = 5)
	{
		//处理总条数
		$this->total = $this->getTotal($total);
		
		//echo $this->total;
		//每页显示数
		$this->num = $num;
		//根据总条数和每页显示数求出总页数
		$this->pageCount = $this->getPageCount();
		//求出当前页
		$this->page = $this->getPage();
		
		//echo $this->page;
		
		//echo $this->pageCount;
		//求出来偏移量
		$this->offset = $this->getOffset();


		//echo $this->offset;  你就是一个屌丝啊
		
		//获取URl  
		$this->url = $this->getUrl();
		//echo $this->url;
		
		
		
		
	}
	
	
	//设置url 首页 上一页 下一页 尾页
	
	
	protected function setUrl($page)
	{
		if (strstr($this->url , '?')) {
			return $this->url. '&page=' . $page;
		} else {
			return $this->url. '?page=' . $page;
		}
	}
	//处理首页的问题
	protected function first()
	{
		return $this->setUrl(1);
	}
	
	//处理上一页
	protected function prev()
	{
		$page = (($this->page - 1) < 1) ? 1 : ($this->page - 1);
		
		return $this->setUrl($page);
	}
	
	//下一页
	protected function next()
	{
		$page = (($this->page + 1) > $this->pageCount ) ? $this->pageCount : ($this->page + 1);
		return $this->setUrl($page);
	}
	
	//尾页
	protected function last()
	{
		return $this->setUrl($this->pageCount);
	}
	
	//处理url
	protected function getUrl()
	{
		//var_dump($_SERVER);
		
		//获取文件地址
		$path = $_SERVER['SCRIPT_NAME'];
		
		//echo $path;
		//获取主机名
		$host = $_SERVER['SERVER_NAME'];
		//获取端口号
		$port = $_SERVER['SERVER_PORT'];
		//获取协议
		$scheme = $_SERVER['REQUEST_SCHEME'];
		//获取参数
		$queryString = $_SERVER['QUERY_STRING'];
		
		if (strlen($queryString)) {
			parse_str($queryString , $array);
			//var_dump($array);
			unset($array['page']);
			//var_dump($array);
			
			$path = $path . '?' . http_build_query($array);
			//echo $path;/1710/4/Page.php?username=niuxi&password=123
		}
		$url = $scheme . '://' . $host . ':' . $port . $path;
		return  $url;
	}
	
	//处理当前页
	
	protected function getPage()
	{
		return isset($_GET['page']) ? $_GET['page'] : 1;
	}
	
	//处理偏移量
	
	public function getOffset()
	{
		$start = ($this->page - 1) * $this->num;  //
		
		$limit = 'limit ' . $start . ',' . $this->num;
		
		return $limit;
	}
	
	
	//处理总页数
	protected function getPageCount()
	{
		return ceil($this->total / $this->num);
	}
	
	
	//处理总条数
	protected function getTotal($total)
	{
		return ($total < 1) ? 1 : $total;
	}
	
	
	//暴露给别人使用的
	public function render()
	{
		return [
			'first' => $this->first(),
			'prev' => $this->prev(),
			'next' => $this->next(),
			'last' => $this->last()
		];
	}
	
	
	
}
/*
$page = new Page(30 , 3);


var_dump($page->render());
*/