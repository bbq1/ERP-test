<?
//*** Request
	extract($_REQUEST);

//*** Default
	$func_name 		= "PO";
	$tbl 			= "tbl_po";
	$curr_page 		= "wms.po.list";
	$edit_page 		= "wms.po.edit";
	$action_page 		= "wms.po.act";
	$page_item 		= 25;




	if ($srh_progress_id == '')
		//$srh_progress_id = 7;


//*** Return Path
 

//echo get_random_no(9);

    echo "";

//*** Select SQL
	$sql="
		select
			*
		from
			$tbl a
		where
			status=0
			and active=1
			and deleted=0
			and po_no <> ''";


//*** Search
	if ($srh_order_date_from){
		$sql .= " and create_date >= '$srh_order_date_from'";
	}/*else{
		$srh_order_date_from=date("Y-m-d");
		$sql .= " and create_date >= '$srh_order_date_from'";
	}*/
	
	if ($srh_order_date_to){
		$sql .= " and create_date <= '$srh_order_date_to'";
	}
	
	if ($srh_order_no){
		$sql .= " and po_no like '%".addslashes($srh_order_no)."%'";
	}
	
	if ($srh_supplier_id != '' && $srh_supplier_id!='all'){
		$sql .= " and supplier_id = $srh_supplier_id";
	}
	
	if ($search_member_id > 0){
		$sql .= " and member_id = $search_member_id";
	}
	
	if ($srh_sku){
		$sql .= " and exists(select null from tbl_erp_product d, tbl_po_item e where a.id = e.po_id and d.id = e.product_id and d.sku like '%".addslashes($srh_sku)."%')";
	}

	if ($srh_name){
		$sql .= " and exists(select null from tbl_erp_product f, tbl_po_item g where a.id = g.po_id and f.id = g.product_id and f.name like '%".addslashes($srh_name)."%')";
	}

	if ($srh_bal_qty != '' && $srh_bal_qty!='all'){

		$sql .= " and total_balqty $srh_bal_qty";
	}


	if (!empty($_GET["srh_product_id"])){

		$sql .= " and exists(select null from tbl_po_item h where a.id = h.po_id and h.balqty > 0 and active = 1 and deleted = 0 and h.product_id = ".$_GET["srh_product_id"].")";
	}

//echo "bb".$_GET["srh_product_id"]."aa".$sql;
//*** Order by start
	if (empty($_POST["order_by"]))
		$order_by = "create_date"; 
	else
		$order_by = $_POST["order_by"];
	
	if (empty($_POST["ascend"]))
		$ascend = "desc";
	else
		$ascend = $_POST["ascend"];
	
	if (!empty($order_by))
	{
		$sql .="
			order by
				$order_by
				$ascend
		";
	}

//*** Order by end


if ($result=mysql_query($sql))
{

	$num_rows = mysql_num_rows($result);
	
	if ($num_rows > 0 )
		$pbar = paging_bar($page_item, $num_rows);

?>

<script>
	function form_search(){

		fr = document.frm;

		fr.action = "<?=$_SERVER['PHP_SELF']?>?<?=$_SERVER['QUERY_STRING']?>";
		fr.method = "post";
		fr.target = "_self";
		fr.submit();

	}
	
	function form_search_reset(){

		window.location = "<?=$_SERVER['PHP_SELF']?>?<?=$_SERVER['QUERY_STRING']?>";

	}

</script>

<form name="frm">
<input type="hidden" name="tbl" value="<?=$tbl?>">
<input type="hidden" name="act" value="">
<input type="hidden" name="order_by" value="<?=$order_by?>">
<input type="hidden" name="ascend" value="<?=$ascend?>">
<input type="hidden" name="del_id" value="">
<input type="hidden" name="thispage" value="<?=$_SERVER['QUERY_STRING']?>">
<div id="list">
	<div id="title"><?=$func_name?></div>
    <div id="search">
		<!-- First Line -->
		<div id="line-1">
			
			PO No:<input type="text" name="srh_order_no" value="<?=$srh_order_no?>">
			Supplier:
			<select name="srh_supplier_id">
				<option value="all" <?=set_selected("all", $srh_supplier_id); ?>>All</option>
				<?=get_combobox_src("tbl_supplier", "name", $_POST['srh_supplier_id'], " sort_no asc "); ?>
			</select>
            Date:
        <input type="text" id="srh_order_date_from" name="srh_order_date_from" value="<?=date_empty($srh_order_date_from)?>" readonly="readonly">
        <a href="#" id="btn_srh_order_date_from"><img src="../images/calendar.jpg" border="0"></a>
        <script type="text/javascript">//<![CDATA[

			var cal = Calendar.setup({
				onSelect: function(cal) { cal.hide() }
			});
			cal.manageFields("btn_srh_order_date_from", "srh_order_date_from", "%Y-%m-%d");

        //]]></script>
        <span> - </span>
		<input type="text" id="srh_order_date_to" name="srh_order_date_to" value="<?=date_empty($srh_order_date_to)?>" readonly="readonly">
        <a href="#" id="btn_srh_order_date_to"><img src="../images/calendar.jpg" border="0"></a>
        <script type="text/javascript">//<![CDATA[

			  var cal = Calendar.setup({
				  onSelect: function(cal) { cal.hide() }
			  });
			  cal.manageFields("btn_srh_order_date_to", "srh_order_date_to", "%Y-%m-%d");

        //]]></script>

		</div>
        <div id="line-2">            
	SKU:<input type="text" name="srh_sku" value="<?=$srh_sku?>">
	Product Name:<input type="text" name="srh_name" value="<?=$srh_name?>">
	Stock in Status:
	<select name="srh_bal_qty">
		<option value="all" <?=set_selected("all", $srh_bal_qty); ?>>All</option>
		<option value="&#62;0" <?=set_selected(">0", $srh_bal_qty); ?>>Pending</option>
		<option value="&#60;&#61;0" <?=set_selected("<=0", $srh_bal_qty); ?>>Finished</option>
		
	</select>

        <input type="button" value="SEARCH" onclick="form_search()">
        <input type="button" value="RESET" onclick="form_search_reset()">
		</div>
		<!-- End of Second Line -->
		
    </div>
  
    
    
	<div id="tool">
		<div id="paging">
			<?	
				echo $pbar[1];
				echo $pbar[2];
				echo $pbar[4];
				echo $pbar[3];
				echo $pbar[5];

			?>
		</div>
        <div id="button"><? //include("../include/list_toolbar.php");	?>
		
		<? if ($_SESSION["sys_delete"]) { ?>

			<a class="boldbuttons" href="javascript:del_item('<?=$action_page?>', '<?=$page_item?>')"><span>Delete</span></a>

        <? } ?>
        
		</div>
		<br class="clear">
		
	</div>

<table>
	<tr>
        <th width="50"><input type="checkbox" name="select_all" onclick="checkbox_select_all(<?=$page_item?>)"></th>
        <th><? set_sequence("PO Number","po_no", $order_by, $ascend, $curr_page) ?></th>
        <th>Order Date</th>
	<th>Supplier</th>
	<th>Total Qty</th>
	<th>Pending Qty</th>
        <th>Total Amount</th>        
	</tr>
	<?

	if ($num_rows > 0)
	{
	
		$order_count = 0;

		mysql_data_seek($result, $pbar[0]);
	
		for($i=0; $i < $page_item ;$i++)
		{
		
			if ($row = mysql_fetch_array($result))
			{
					
		
				?>
		<tr >
 
                    <td width="50" valign="top"><input type="checkbox" name="cb_<?=$i?>" value="<?=$row[id]?>"> </td>
                    <td align="left" valign="top" onclick="goto_page('<?=$edit_page?>', '<?=$row[id]?>')">
			<div style=" text-decoration: underline; color:blue;"><?=$row[po_no]; ?></div>
                    </td>
		    <td align="left" valign="top">
		    	<?=$row[create_date]; ?>
		    </td>		
                    <td align="left" valign="top">         
		        <?=get_field("tbl_supplier","name",$row["supplier_id"]) ?>
                    </td>
                    <td align="right" valign="top">
			<?=get_sum("tbl_po_item","po_id",$row["id"],"qty");?>
                    </td>
                    <td align="right" valign="top">
			<?=$row["total_balqty"];?>
                    </td>
                    <td align="right" valign="top">
			<?=number_format(get_po_total_amount($row["id"]), 2);?>
                    </td>       

		</tr>
<?
	
			}
	
		}
	
	}

?>
</table>
<br class="clear" />
</div>
</form>	
<?	
	
}
else
	echo $sql;
	
?>