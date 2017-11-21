<?php

$config=include 'config.php';

//var_dump($config);

$model=new Model($config);

//查询代码

$data=$model->fields(['username','id','email'])->table('user')->where('id>10')->group('email')->having('id>10')->order('id')->limit('0,5')->select();

$model->getByUsername('皮皮霞');

//添加
/*
$data2['username']='七月';
$data2['password']='lzl123';
$data2['email']='lzl@qq.com';
$data2['regtime']=time();
$data2['lasttime']=time();

if($_SERVER['REMOTE_ADDR']=='::1'){
    $ip='127.0.0.1';

}
else{
	$ip=$_SERVER['REMOTE_ADDR'];
}
$ip=ip2long($ip);

$data2['regip']=$ip;

$add=$model->insert($data2);
if($add){
   echo '插入成功';
}
else{
	echo '插入失败';
}
*/

//修改
$data3['password']='lzllzl';
$update=$model->where('id=85')->update($data3);

//删除
$del=$model->where('id=83')->del();

//最大值
//echo $model->max('id');

//最小值
//echo $model->min('id');

//总数
//echo $model->total('id');

//求和
//echo $model->sum('id');

//求平均值
echo $model->avg('id');

class Model{
     //链接
     protected $link;
     //主机名
     protected $host;
     //用户名
     protected $user;
     //密码
     protected $pwd;
     //字符集
     protected $charset;
     //库名字
     protected $dbName;
     //表名字
     protected $table='user';
     //表前缀
     protected $prefix;
     //字段
     protected $fields;
     //选项
     protected $options;
     //sql语句
     protected $sql;

     public function __construct($config){
       
         $this->host=$config['DB_HOST'];
         $this->user=$config['DB_USER'];
         $this->pwd=$config['DB_PWD'];
         $this->charset=$config['DB_CHARSET'];
         $this->dbName=$config['DB_NAME'];
         $this->prefix=$config['DB_PREFIX'];
         //数据库链接
         $this->link=$this->connect();

         //获取表名字
         $this->table=$this->getTable();
         //var_dump($this->table);
         
         //字段
         $this->fields=$this->getFields();
         //var_dump($this->fields);
     }

        //最大值
        public function max($fields){

            if(empty($fields)){
               $fields=$this->fields['_pk'];
            }

            $sql="SELECT MAX($fields) as m FROM $this->table";
            
            //echo $sql;
            
            $data=$this->query($sql);
            //var_dump($data);
            
            return $data[0]['m'];
        }

        //最小值
        
        public function min($fields){
            if(empty($fields)){
               $fields=$this->fields['_pk'];
            }
            $sql="SELECT MIN($fields) as m FROM $this->table";

            //echo $sql;
            
            $data=$this->query($sql);

            return $data[0]['m'];
        }

        //总数
        
        public function total($fields){
            if(empty($fields)){
               $fields=$this->fields['_pk'];
            }
            $sql="SELECT COUNT($fields) as c FROM $this->table";
            //echo $sql;

            $data=$this->query($sql);
            return $data[0]['c'];
        }

        //求和
        
        public function sum($fields){
            if(empty($fields)){
               $fields=$this->fields['_pk'];
            }
            $sql="SELECT SUM($fields) as s FROM $this->table";
            //echo $sql;

            $data=$this->query($sql);

            return $data[0]['s'];
        }

        //平均值
        
        public function avg($fields){
            if(empty($fields)){
               $fields=$this->fields['_pk'];
            }

            $sql="SELECT AVG($fields) as a FROM $this->table";
            //echo $sql;

            $data=$this->query($sql);

            return $data[0]['a'];
        }

        //查询
        public function select(){
           //var_dump($this->options);
           
           $sql='SELECT %FIELDS% FROM %TABLE% %WHERE% %GROUP% %HAVING% %ORDER% %LIMIT%';
           
           $sql=str_replace(
               array('%FIELDS%','%TABLE%','%WHERE%','%GROUP%','%HAVING%','%ORDER%','%LIMIT%'),
               array(
                   $this->parseFields(isset($this->options['fields'])?$this->options['fields']:null),
                   $this->parseTable(),
                   $this->parseWhere(),
                   $this->parseGroup(),
                   $this->parseHaving(),
                   $this->parseOrder(),
                   $this->parseLimit()
               	),
               $sql
           	);

           //echo $sql;
           //var_dump($this->parseTable());
           //发送sql语句
           
           $data=$this->query($sql);
          
           //var_dump($data);

           return $data;
        }
      
        //处理limit
        
        protected function parseLimit(){
        	
        	$limit='';

        	if(empty($this->options['limit'])){
               
               $limit='';
        	}
        	else{

        		if(is_string($this->options['limit'][0])){

        			$limit='LIMIT '.$this->options['limit'][0];
        			//var_dump($limit);
                }

                if(is_array($this->options['limit'][0])){
                    //转换成字符串
                    $limit=join(',',$this->options['limit'][0]);

                    $limit='LIMIT '.$limit;

                    //var_dump($limit);
                }
        	}
        	return $limit;
        }

        //处理排序order问题
        
        protected function parseOrder(){
        	$order='';
        	if(empty($this->options['order'])){
                $order='';
        	}
        	else{
        		$order='ORDER BY '.$this->options['order'][0];
        	}
        	return $order;
        }

        //处理having
        
        protected function parseHaving(){
        	$having='';
        	if(empty($this->options['having'])){
               $having='';
        	}
        	else{
        	   $having='HAVING '.$this->options['having'][0];
        	}
        	return $having;
        }

        //处理分组
        
        protected function parseGroup(){
        	$group='';
        	if(empty($this->options['group'])){
               $group='';
        	}
        	else{
        		$group='GROUP BY '.$this->options['group'][0];
        	}
        	return $group;
        }

       

        //处理字段问题 parseFields
        
        protected function parseFields($options){
            
            //var_dump($options);
            
            $fields='';

            if(empty($options)){
                $fields='';
            }
            else{
            	if(is_string($options[0])){
                  // var_dump($options[0]);
                    //转换成数组
                    $fields=explode(',',$options[0]);

                    //var_dump($fields);
                    
                    //与数据库比对取值的交集
                    
                    $tmpArr=array_intersect($fields, $this->fields);
                    //var_dump($tmpArr);
                    
                    //将取交集后的结果转为字符串
                    
                    $fields=join(',',$tmpArr);
                    //var_dump($fields);
            	}

            	if(is_array($options[0])){
                   //var_dump($options[0]);
                   
                   //与数据库比对取值的交集
                   $tmpArr=array_intersect($options[0],$this->fields);
                   //将结果转为字符串
                   $fields=join(',',$tmpArr);
                   //var_dump($fields);
            	}
            }

            return $fields;
        }

       

       
       

        //处理添加字段
        
        protected function parseAddFields($keys){

        	return join(',',$keys);

        }

        //处理添加的值
        
        protected function parseAddValues($values){

            $string='';
            foreach($values as $val){
               $string .='\''.$val.'\',';
            }

            //var_dump($string);
            
            //去除末端逗号,
            
            $string=rtrim($string,',');
            //var_dump($string);
            return $string;
            
        }

       //执行sql语句(添加，修改，删除)
        
        protected function exec($sql,$bool=null){
           if($bool){
              $result=mysqli_query($this->link,$sql);
              if($result){
                 return mysqli_affected_rows($this->link);
              }
              else{
              	return false;
              }
           }

           else{
           	  $result=mysqli_query($this->link,$sql);
           	  if($result){
                 return mysqli_insert_id($this->link);
           	  }
           	  else{
           	  	return false;
           	  }
           }
        }

		 //添加
        public function insert($data){
            
            //var_dump($data);

            if(!is_array($data)){
               return false;
            }

            $sql='INSERT INTO %TABLE%(%FIELDS%) VALUES(%VALUES%)';

            $sql=str_replace(
                 array('%TABLE%','%FIELDS%','%VALUES%'),
                 array(
                     $this->parseTable(),
                     $this->parseAddFields(array_keys($data)),
                     $this->parseAddValues(array_values($data))
                 	),
                 $sql
                 );

            //echo $sql;

            $res=$this->exec($sql);
            return $res;
        }

        //处理update set值
        
        public function parseSet($data){
            //var_dump($data);
            $string='';
            foreach($data as $key=>$val){
                $string .=$key.'='."'$val',";
            }

            $string=rtrim($string,',');

            //var_dump($string);
            return $string;
        }
		  //修改

        public function update($data){
            if(!is_array($data)){
               return false;
            }
            
            $sql='UPDATE %TABLE% SET %SET% %WHERE%';

            $sql=str_replace(
                 array('%TABLE%','%SET%','%WHERE%'),
                 array(
                    $this->parseTable(),
                    $this->parseSet($data),
                    $this->parseWhere()
                 	),
                 $sql
            	);
            //echo $sql;
            
            $res=$this->exec($sql,true);
            return $res;
        }

		 //处理where条件
        
        protected function parseWhere(){
        	$where='';
        	if(empty($this->options['where'])){
               $where='';
        	}
        	else{
        		$where='WHERE '.$this->options['where'][0];
        	}

        	return $where;
        }

        //处理表的问题 parseTable
        
        protected function parseTable(){

        	$table='';
        	if(empty($this->options['table'])){
                $table=$this->table;
        	}
        	else{
        		$table=$this->prefix.$this->options['table'][0];
        	}

        	return $table;
        }
        //删除
        
        public function del(){
        	
        	$sql='DELETE FROM %TABLE% %WHERE%';

        	$sql=str_replace(
                array('%TABLE%','%WHERE%'),
                array(
                   $this->parseTable(),
                   $this->parseWhere()
                	),
                $sql
        		);
        	//echo $sql;

        	$res=$this->exec($sql,true);

        	return $res;
        }
		
		 //通过call方法实现连贯操作
        
        public function __call($func,$args){
            //var_dump($func,$args);
            
            if(in_array($func,['fields','table','where','group','order','limit','having'])){
                $this->options[$func]=$args;
                //var_dump($this->options);
                 return $this;
            }
            else if(strtolower(substr($func,0,5))=='getby'){
            
                $fields=strtolower(substr($func,5));
                //var_dump($fields);
            }
            else{
            	exit('不支持这个方法');
            }
           
        }

        //处理字段
        
        protected function getFields(){
        	$cacheFile='cache/'.$this->table.'.php';
        	//echo $cacheFile;	
        	if(file_exists($cacheFile)){
               return include $cacheFile;
        	}
        	else{

        		 //查看表结构
        		 
        		 $sql='desc '.$this->table;
        		 $data=$this->query($sql);
                 //var_dump($data); //二维数组
                 
        		 $fields=[];
        		 foreach($data as $key=>$val){

                     $fields[]=$val['Field'];
                     
                     
                     if($val['Key']=='PRI'){
                        $fields['_pk']=$val['Field'];
                     }
        		 }
        		 //var_dump($fields);
        		 
        		 //$var=var_export($fields,true);
        		 //var_dump($var);
        		 
        		 $string="<?php \n return ".var_export($fields,true).";?>";

        		 //var_dump($string);

        		 file_put_contents('cache/'.$this->table.'.php',$string);
        		 return $fields;
        	}
        }

        //发送sql语句 query(查询,处理字段查看表结构)
        
        protected function query($sql){
             $result=mysqli_query($this->link,$sql);

             $data=[];
             if($result){
                while($rows=mysqli_fetch_assoc($result)){
                   //var_dump($rows);
                   $data[]=$rows;

                }
             }
             else{
             	return false;
             }
             return $data;
        }

        //处理表名字
        
        protected function getTable(){
        	//两种情况 给了默认值和没有给默认值的情况
        	
        	$table='';

        	if(isset($this->table)){
                $table=$this->prefix.$this->table;
        	}
        	else{
        		$table=$this->prefix.strtolower(substr(get_class($this),0,-5));
        	}

        	return $table;

        }

        //处理数据库链接
        protected function connect(){
           
            $link=mysqli_connect($this->host,$this->user,$this->pwd);
            if(!$link){
                exit('数据库连接失败');
            }

            mysqli_set_charset($link,$this->charset);

            mysqli_select_db($link,$this->dbName);

            return $link;
        }

}