<?php
	require('macros.php');
	class counter{
		private $file;
		private $hits;
	 
		// 构造函数
		public function __construct($name = 'counter'){
	 
			// 存放计数的文件
			$this->file =  "../db/count.txt";
	 
			// 判断文件是否存在，不存在则创建
			if(file_exists($this->file))
			{
				$this->hits = file_get_contents($this->file);
			}
			else
			{
				file_put_contents($this->file, "1");
				$this->hits = 1;
			}
		}
	 
		// 获取计数
		public function add(){
						$this->hits++;
						$this->hits = $this->hits % 10000;
						file_put_contents($this->file, $this->hits);
		}
		
		public function get() {
			return $this->hits;
		}
 
	}
	
	$ver = new counter();
	$ver->add();
	$dbOrder = new SQLite3(DATABASE_ORDER);
	if (!$dbOrder) {
		header("HTTP/1.1 ERR_COULD_NOT_CONECT_DB 'ERR_COULD_NOT_CONECT_DB'");
	  	die(ERR_COULD_NOT_CONECT_DB);
	}
	$sql=sprintf("select %s.%s,%s.%s,%s.%s,%s.%s from %s,%s where %s.%s=%s.%s",
				  ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_ID,
				  ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_DISH_ID,
				  TABLE_ORDER_TABLE, TABLE_ORDER_TABLE_COLUM_TABLE_ID,
				  ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_QUANTITY,
				  TABLE_ORDER_TABLE, ORDER_DETAIL_TABLE,
				  TABLE_ORDER_TABLE, TABLE_ORDER_TABLE_COLUM_ID,
				  ORDER_DETAIL_TABLE, ORDER_DETAIL_TABLE_COLUM_ORDER_ID);
	$resultSet = $dbOrder->query($sql);
	if ($resultSet) {
		$i = 0;
		while($row = $resultSet->fetchArray()) {
			$item = array('id' => $row[0],
			 			  'did' => $row[1],
						  'tid' => $row[2],
						  'quan' => $row[3]);
			$dishes[$i] = $item;
			$i++;
			// echo "-----------------------<br/>";
			// echo "id:$row[0]<br/>";
			// echo "dish_id:$row[1]<br/>";
			// echo "price:$row[2]<br/>";
			// echo "quantity:$row[3]<br/>";
			// echo "order_id:$row[4]<br/>";
		}
		$TodoDishesObj = array('ver' => $ver->get(),
							   'dishes' => $dishes );
		$jsonString = json_encode($TodoDishesObj);
		echo "$jsonString";
	} else {
		die(ERR_DB_QUERY);
	}
?>