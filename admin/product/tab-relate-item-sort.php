<?
	## init
	include("../include/init.php");
	include("../include/html_head.php");
	
	##default
	$prev_page = "item-edit";
	$prev_arr = 0;
	$next_arr = 0;
	$int = 0;
	
	## request
	$tbl = "tbl_product_relate_item";
	$cat_id = $_REQUEST['cat_id'];	
	$parent_id = $_REQUEST['id'];
	$photo_id = $_REQUEST['photo_id'];
	$tab = $_REQUEST['tab'];
	$pg_num = $_REQUEST['pg_num'];
	$sort_act = $_REQUEST['sort_act'];

	## check empty
	if (!empty($parent_id) && !empty($sort_act))
	{
	
		$sql = "select * from $tbl where parent_id = {$parent_id} and active = 1 and deleted = 0 order by sort_no";
		
		if ($result = mysql_query($sql)){
		
			while ($row = mysql_fetch_array($result))
			{
		
				$arr[$int][0] = $row[id];
				$arr[$int][1] = $row[sort_no];
			
				$int++;
			
			}
		
		}else
			echo $sql;
		
		
		//$id = 13;
		//$sort_act = 1;
		//echo " ttl count : ".count($arr)."<br>";
		
		for ($i=0; $i < count($arr); $i++)
		{
			
			if ($photo_id == $arr[$i][0])
			{
			
				
				if ($i != 0 && $i != count($arr)-1){
		
					$prev_arr = $i - 1;
					$curr_arr = $i;			
					$next_arr = $i + 1;
		
				}elseif ($i == 0 && $i == count($arr)-1){
				
					$prev_arr = -1;
					$curr_arr = $i;			
					$next_arr = -1;
				
				}elseif ($i == 0){
				
					$prev_arr = -1;
					$curr_arr = $i;			
					$next_arr = $i+1;
				
				}elseif ($i == count($arr)-1){
				
					$prev_arr = $i-1;
					$curr_arr = $i;			
					$next_arr = -1;		
				
				}
				
				//echo "------> arr : $i &nbsp;&nbsp;&nbsp; id : {$arr[$i][0]} &nbsp;&nbsp;&nbsp; sort_no : {$arr[$i][1]} <br> ";
				//echo "******* prev_arr : $prev_arr / next_arr : $next_arr <br> ";		
				
			}
			else
			{
				//echo " arr : $i &nbsp;&nbsp;&nbsp; id : {$arr[$i][0]} &nbsp;&nbsp;&nbsp; sort_no : {$arr[$i][1]} <br> ";
			
			}
		
		}
		
		//echo "<br><br>";
		
		if ($sort_act == 1)
		{
		
			if ($prev_arr > -1)
			{
		
				// echo " - curr_arr > ".$curr_arr."<br>";
				// echo $prev_arr."<br>";
				// echo $arr[$prev_arr][0]."<br>";
				// echo $arr[$prev_arr][1]."<br>";
				
				$sql = " update $tbl set sort_no = {$arr[$prev_arr][1]} where id = {$arr[$curr_arr][0]}";
				//echo $sql."<br>";
				if (!mysql_query($sql)) echo $sql;
				$sql = " update $tbl set sort_no = {$arr[$curr_arr][1]} where id = {$arr[$prev_arr][0]}";
				//echo $sql."<br>";
				if (!mysql_query($sql)) echo $sql;		
				
			}else{
				//echo "start";
			}
			
		}
		else
		{
		
			if ($next_arr > -1)
			{
		
				// echo " + curr_arr > ".$curr_arr."<br>";
				// echo $next_arr."<br>";
				// echo $arr[$next_arr][0]."<br>";
				// echo $arr[$next_arr][1]."<br>";
				
				$sql = " update $tbl set sort_no = {$arr[$next_arr][1]} where id = {$arr[$curr_arr][0]}";
				//echo $sql."<br>";
				if (!mysql_query($sql)) echo $sql;		
				$sql = " update $tbl set sort_no = {$arr[$curr_arr][1]} where id = {$arr[$next_arr][0]}";		
				//echo $sql."<br>";		
				if (!mysql_query($sql)) echo $sql;		
			
			}else{
				//echo "end";
			}
		}
	
	}	
	

?>
<script>
	path = "../main/main.php?func_pg=<?=$prev_page?>";
	path += "&id=<?=$parent_id?>";
	path += "&pg_num=<?=$pg_num?>";
	path += "&tab=<?=$tab?>";
	path += "&cat_id=<?=$cat_id?>";
	window.location = path;
</script>