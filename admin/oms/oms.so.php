<?
//ttt
//*** Request
	extract($_REQUEST);

//*** Default
	$func_name 		= "Sales Orders";
	$tbl 			= "tbl_order";
	$curr_page 		= "oms.so";
	$edit_page 		= "oms.so.edit";
	$action_page 	= "oms.so.act";
	$_page = "oms.so.create";
	$shipment_page = "oms.shipments.edit";
	$create_order_page = "oms.so.create";
	$page_item 		= 100;



	//if ($srh_progress_id == '')
		//$srh_progress_id = 7;

//*** Return Path
 
//echo get_random_no(9);
//echo date("Y-m-d h:m:s");
//*** Select SQL
	$sql="
		select
			*
		from
			$tbl
		where			
			deleted=0";


//*** Search
	if ($srh_order_date_from){
		$sql .= " and create_date >= '$srh_order_date_from'";
	}else{
		$srh_order_date_from=date("Y-m-01");
		$sql .= " and create_date >= '$srh_order_date_from'";
	}
	
	if ($srh_order_date_to){
		$sql .= " and create_date <= '$srh_order_date_to'";
	}
	
	if ($srh_order_no){
		$sql .= " and order_no like '%$srh_order_no%'";
	}
	
	
	if ($order_status && $order_status != "all"){
		$sql .= " and order_status_id = $order_status";
	}
		
	if ($srh_first_name !=''){
		$sql .= " and customer_firstname like '%$srh_first_name%'";
	}
	
	if ($srh_last_name !=''){
		$sql .= " and customer_lastname like '%$srh_last_name%'";
	}
	
	if ($srh_email !=''){
		$sql .= " and customer_email like '%$srh_email%'";
	}	
	
	if ($srh_sku){

		$sql .= " and exists(select sku from tbl_order_item item where item.order_id = $tbl.id AND item.is_shipped = 0 AND item.sku = '".$srh_sku."')";
	}
//////////////Security Check////////////
	$security_check_array = array(
	"cs_check" => "CS",
	"payment_check" => "Payment",
	"buyer_check" => "Buyer",
	"invoice_check" => "Invoice",
	"packing_check" => "Packing",
	"awb_check" => "AWB",
	"all_check" => "All checked",
	"all_uncheck" => "All un-check",
	);
	if($srh_security_check){
		if($srh_security_check == 'all_uncheck'){
			foreach($security_check_array as $key => $value)
			{
				if($key != "all_check" && $key != "all_uncheck")
					$sql .= " and $key = 0";		
			}
		}else if($srh_security_check == 'all_check'){
			foreach($security_check_array as $key => $value)
			{	
				if($key != "all_check" && $key != "all_uncheck")
					$sql .= " and $key = 1";				
			}		
		}else if($srh_security_check != 'all'){		
			foreach($security_check_array as $key => $value)
			{
				
				
				if($srh_security_check == $key){	
						$sql .= " and $key = 0";
						break;
				}else
					$sql .= " and $key = 1";
				
			}	
		
		}
	}
	

//*** Order by start
	if (empty($_POST["order_by"]))
		$order_by = "id"; 
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

//echo $sql."<br>";
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
	
	
	function form_import(){		
		window.location = "../oms/load_magento_order/download_order.php";	
		
	}	
	
	function form_export_selected(){		
		fr = document.frm
		fr.action = "../oms/oms.so.export_csv.php";
		fr.method = "post";
		fr.target = "_self";
		fr.submit();
	}	
	
	function form_markshipped(){
		fr = document.frm
		fr.action = "../main/main.php?func_pg=mark_shipped";
		fr.method = "post";
		fr.target = "_self";
		fr.submit();

	}
	function form_search_reset(id){

		window.location = "<?=$_SERVER['PHP_SELF']?>?<?=$_SERVER['QUERY_STRING']?>";

	}
	
	function goto_add_new_shipment(id){
		window.location = '../main/main.php?func_pg=<?=$shipment_page?>&order_id='+id+'&prev_page=oms.so';		
	}

	function form_create_order(){
		window.location = '../main/main.php?func_pg=<?=$create_order_page?>';		
	}

	function add_new_remark(id)
	{
		var new_remark = $("input[name=remark_"+id+"]").val();
		if(new_remark != ""){
			$("input[name=remark_"+id+"]").val("");			
			
			
			///////////Ajax update///////////////
			$.post("../oms/add_new_remark_ajax.php",
				{					
					order_id: id,
					new_remark: new_remark
				},
				function(data,status){
					//alert("Data: " + data + "\nStatus: " + status);
					if(status == "success")
					{	$( "#log" ).html("New remark added!");
						$("#remark_record_"+id).html(data);
					}
					else
						$( "#log" ).html("New remark add failed!");
				});
				
				
		}else
			$( "#log" ).html("Can not add blank remark!");
	
	}
	
	
	//////////////check box click//////////////
	$(document).ready(function() {	
		$( "input[type=checkbox]" ).on( "click", function() {
			var checkbox_name = $(this).attr("name").split('_');
			var check_type = checkbox_name[0];
			var order_id = checkbox_name[1];
			var check_value = "";
			if( $(this).is(":checked"))
			{	
				//$( "#log" ).html( $(this).attr("name") + " is checked! type:" + check_type + ", order_id:"+order_id);
				check_value = "checked";
			}else{
				//$( "#log" ).html( $(this).attr("name") + " is un-checked! type:" + check_type + ", order_id:"+order_id);
				check_value = "uncheck";
			}
			
			if(check_type != "cb" && check_type != "srh" && check_type != "select")
			{	$.post("../oms/update_check_process_ajax.php",
				{
					check_type: check_type,
					order_id: order_id,
					check_value: check_value
				},
				function(data,status){
					//alert("Data: " + data + "\nStatus: " + status);
					if(status == "success")
						$( "#log" ).html(check_type+" updated!");
					else
						$( "#log" ).html(check_type+" failed!");
				});
			}
				
			
		});
	});
</script>
<script type="text/javascript">
$(document).ready(function() {
			
	$('#mark_as_shipped').click(function (){
		
		alert('ship me');
	});//end id mark_as_shipped button
});	
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
			
			Order No.:<input type="text" name="srh_order_no" value="<?=$srh_order_no?>">
			
			Status:
			<select name="order_status" id="order_status">
					<option value="all">All</option>
					<?=get_combobox_src('tbl_order_status', 'name', $order_status)?>  
				</select>	
			<!--order_status_id-->
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
		&nbsp;&nbsp; Security Check
		<select name="srh_security_check" id="srh_security_check">
					<option value="all">All</option>
					<?php
						foreach($security_check_array as $key => $value)
						{
							if($srh_security_check == $key)
								echo '<option value="'.$srh_security_check.'" SELECTED>'.$value.'</option>';
							else
								echo '<option value="'.$key.'">'.$value.'</option>';
						}
					?>
					<!--<option value="cs_check" <?echo $srh_security_check=="cs_check"?"SELECTED":""?>>CS</option>
					<option value="payment_check" <?echo $srh_security_check=="payment_check"?"SELECTED":""?>>Payment</option>
					<option value="buyer_check" <?echo $srh_security_check=="buyer_check"?"SELECTED":""?>>Buyer</option>
					<option value="invoice_check" <?echo $srh_security_check=="invoice_check"?"SELECTED":""?>>Invoice</option>
					<option value="packing_check" <?echo $srh_security_check=="packing_check"?"SELECTED":""?>>Packing</option>
					<option value="awb_check" <?echo $srh_security_check=="awb_check"?"SELECTED":""?>>AWB</option>-->
		</select>		
		</div>
        <div id="line-2">            
            
            SKU: <input type="text" name="srh_sku" value="<?=$srh_sku; ?>">
            First Name: <input type="text" name="srh_first_name" value="<?=$srh_first_name; ?>">
            Last Name: <input type="text" name="srh_last_name" value="<?=$srh_last_name; ?>">
            Email: <input type="text" name="srh_email" value="<?=$srh_email; ?>">

        <!-- Member: <? get_lookup("search_member_id", "../lookup/member.php", $search_member_id, $display, "Member Lookup"); ?> -->

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
		<a class="boldbuttons" href="javascript:form_create_order();"><span>Create Order</span></a>		
		<!--<? if ($_SESSION["sys_delete"]) { ?>

			<a class="boldbuttons" href="javascript:del_item('<?=$action_page?>', '<?=$page_item?>')"><span>Delete</span></a> 

        <? } ?>-->
        
        <? if ($_SESSION["sys_write"]==1){ ?>
			<a class="boldbuttons" href="javascript:form_import();"><span>Import Order</span></a>
        <? } ?>
		
		<a class="boldbuttons" href="javascript:form_export_selected();"><span>Export Selected</span></a>
		<SELECT name="order_export_type">
			<option value="csv">CSV</option>
			<option value="xls">2003 EXCEL</option>
			<option value="xlsx">2007/10/13 EXCEL</option>
		</SELECT>
		</div>     
		<br class="clear">
		
	</div>
		<?//if($_SESSION['user_group_id'] != 34){
		  //if($_SESSION['user_group_id'] != 37){
		?>
		<div id="log" align="center"></div>
<table>
	<tr>
        <th width="50"><input type="checkbox" name="select_all" onclick="checkbox_select_all(<?=$page_item?>)"></th>
		<!--<th>Date</th>-->
        <!--<th><? set_sequence("Order Number","invoice_no", $order_by, $ascend, $curr_page) ?></th>-->
        <th>Order Number</th>
        <th>Bill Name</th>
        <th>Magento Total</th>
        <th>ERP Total</th>
        <th>Status</th>
        <th>CS</th>
        <th>Payment</th>
		<th>Buyer</th>
		<th>Invoice</th>
		<th>Packing</th>
		<th>AWB</th>
        <th>Ship Status</th>        
        <th>Remark</th>            
        <th>Action</th>            
        <!--th></th-->
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
				$currency = $row[order_currency_code];
				?>
				<tr <? if($row[order_status_id] == 2){ 
						?> style="background:red;" <? } 
						else if($row[order_status_id] == 3){ 
						?> style="background:green;" <? } 
				
				?> >
                    <td width="50" valign="top"><input type="checkbox" name="cb_<?=$i?>" value="<?=$row[id]?>"> </td>
                    <!--<td valign="top"><?=$row[create_date]?> </td>					-->
                    <td align="left" valign="top">
						<a onclick="goto_page('<?=$edit_page?>', '<?=$row[id]?>')"><?=$row[order_no]; ?></a>
                        <div style="color: #888; font-size:10px;"><?=$row[create_date];?></div>
						<?php if($row[magento_order_id]){ ?>
                        <div style="color: blue; font-size:12px;"><a href="https://www.android-enjoyed.com/index.php/cccadmin/sales_order/view/order_id/<?=$row[magento_order_id]?>/" target="_blank">Goto Magento</a></div>
						<? } ?>
						<?php if(stripos($row[shipping_method], "priority") !== false){ ?>
						<div style="color: black; font-size:10px;">
							!Urgent
						</div>
						<? } ?>						
						
                    </td>
					<td><?=get_order_billing_address($row[id]);?></td>
					<td><?=$currency." ".$row[grand_total];?></td>
					<td valign="top">
					<table style="font-size:8pt;">
					<?php
						///////////find ERP total//////						
						$shipping_cost = $row[base_shipping_incl_tax];
						$subcharge = $row[base_fooman_surcharge_amount];
						echo get_item_list($row[id], $shipping_cost, $subcharge, $currency);	
					?>
					</table>
					</td>
					<td><?=get_field("tbl_order_status", "name", $row[order_status_id]);?></td>
					
			<!-- Check process -->
                    <td align="center" valign="top"><input type="checkbox" class="largerCheckbox" name="cs_<?=$row[id];?>" <? echo $row[cs_check]>0?"checked":""?>  <? if($_SESSION['user_group_id'] != 36 && $_SESSION['user_group_id'] != 32 && $_SESSION['user_group_id'] != 38) echo "disabled" ?> /></td>
                    <td align="center" valign="top"><input type="checkbox" class="largerCheckbox" name="payment_<?=$row[id];?>" <? echo $row[payment_check]>0?"checked":""?> 
					<?php 
						if($_SESSION['user_group_id'] != 36 && $_SESSION['user_group_id'] != 32 && $_SESSION['user_group_id'] != 38) 
							echo "disabled";
						else if($row[cs_check]==0)
							echo "disabled";						
					?> />
					</td>
                    <td align="center" valign="top"><input type="checkbox" class="largerCheckbox" name="buyer_<?=$row[id];?>" <? echo $row[buyer_check]>0?"checked":""?> 
					<?php
						if($_SESSION['user_group_id'] != 36 && $_SESSION['user_group_id'] != 32 && $_SESSION['user_group_id'] != 39) 
							echo "disabled";
						else if($row[cs_check]==0)
							echo "disabled";
						else if($row[payment_check]==0)
							echo "disabled";
					?> />
					</td>
                    <td align="center" valign="top"><input type="checkbox" class="largerCheckbox" name="invoice_<?=$row[id];?>" <? echo $row[invoice_check]>0?"checked":""?> 
					<?php
						if($_SESSION['user_group_id'] != 36 && $_SESSION['user_group_id'] != 32 && $_SESSION['user_group_id'] != 40) 
							echo "disabled";
						else if($row[cs_check]==0)
							echo "disabled";
						else if($row[payment_check]==0)
							echo "disabled";
						else if($row[buyer_check]==0)
							echo "disabled";
					?> />
					</td>
                    <td align="center" valign="top"><input type="checkbox" class="largerCheckbox" name="packing_<?=$row[id];?>" <? echo $row[packing_check]>0?"checked":""?> 
					<?php 
						if($_SESSION['user_group_id'] != 36 && $_SESSION['user_group_id'] != 32 && $_SESSION['user_group_id'] != 40) 
							echo "disabled";
						else if($row[cs_check]==0)
							echo "disabled";
						else if($row[payment_check]==0)
							echo "disabled";
						else if($row[buyer_check]==0)
							echo "disabled";
						else if($row[invoice_check]==0)
							echo "disabled";		
					?> />
					</td>
                    <td align="center" valign="top"><input type="checkbox" class="largerCheckbox" name="awb_<?=$row[id];?>" <? echo $row[awb_check]>0?"checked":""?> 
					<?php 
						if($_SESSION['user_group_id'] != 36 && $_SESSION['user_group_id'] != 32 && $_SESSION['user_group_id'] != 41) 
							echo "disabled";
						else if($row[cs_check]==0)
							echo "disabled";
						else if($row[payment_check]==0)
							echo "disabled";
						else if($row[buyer_check]==0)
							echo "disabled";
						else if($row[invoice_check]==0)
							echo "disabled";
						else if($row[packing_check]==0)
							echo "disabled";
					?> />
					</td>
			<!-- End Check process -->
					
					<?php
					get_ship_status($row[id]);	
					?>					
					<td align="center" valign="top">
						<input type="text" name="remark_<?=$row[id];?>" /><input type="button" onclick="add_new_remark(<?=$row[id];?>)" value="Add New Remark" />
						<div id="remark_record_<?=$row[id];?>">
						<table style="font-size:8pt;">
						<?=get_remark_record($row[id]);?>
						</table>
						</div>
					</td>
					<td><a href="javascript:goto_add_new_shipment(<?=$row[id];?>)">Ship</a></td>
              
                    <!--td valign="top">
                   <?php
                  //$row[id]
					 //if($row['remarks']!='' && $row['order_status_id']=='7'){
						
					 ?>				
				</tr>-->
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
	
function get_purchase_before_count($email, $date){
	
	$sql="
		select 
			id
		from
			tbl_order
		where
			email='$email'
			and create_date<'$date'											
		";
	
	if ($result = mysql_query($sql)){
		
		$num_rows = mysql_num_rows($result);
		
		return $num_rows;
		
	}else
		echo $sql;

}

function get_ship_status($order_id){
	$sql = "SELECT is_shipped FROM tbl_order_item WHERE is_cancel_item=0 AND order_id={$order_id}";
	$ship_status="";
	$had_shipment = false;
	$had_no_shipment = false;
	if ($result=mysql_query($sql)){						
		while ($row=mysql_fetch_array($result)){
			if($row[is_shipped] == 1)
				$had_shipment = true;
			else
				$had_no_shipment = true;
		}
	}
	if($had_shipment == false && $had_no_shipment == true)
		$ship_status = "Un-ship";
	else if($had_shipment == true && $had_no_shipment == false)
		$ship_status = "Fully Shipped";
	else if($had_shipment == false && $had_no_shipment == false)
		$ship_status = "No Item";
	else
		$ship_status = "Partial Shipped";
		
	echo '<td align="center">'.$ship_status.'</td>';
}  
	
function get_order_billing_address($order_id){		
	
	$sql = " select * from tbl_order_billing_address where order_id={$order_id} order by id desc ";
	$bill_name = "";
	if ($result=mysql_query($sql)){
	  
		while ($row=mysql_fetch_array($result)){		
			$bill_name .= $row[first_name].' '.$row[last_name].'<br>'; 
		}
	return $bill_name;
	}else
		return $sql;
}

function get_remark_record($order_id)
{
	$remark = '';
	
	$sql = " select * from tbl_order_remark where order_id={$order_id} order by create_date desc ";			
	if ($result=mysql_query($sql)){	  
		while ($row=mysql_fetch_array($result)){
			$remark .= '<tr>';
			$remark .= "<td width='160'>".$row[create_date]."</td>";
			if ($row[create_by]==0)
				$remark .= "<td width='80'>system</td>";
			else
				$remark .= "<td width='80'>".get_field("sys_user", "name_1", $row[create_by])."</td>";
				
			$remark .= "<td width='250'>".$row[remarks]."</td>";									
            $remark .= "</tr>";
		}		
		return $remark;
	}else
		return $sql;
}		

function get_item_list($order_id, $shipping_cost, $subcharge, $currency){
	
	$sql = " select * from tbl_order_item where is_cancel_item=0 AND order_id={$order_id} order by id asc ";
	
	if ( $result = mysql_query($sql)){
	  $product_info = "";
	  $sum_subtotal = 0;
	  while ($row = mysql_fetch_array($result)){
		  $sum_subtotal = $sum_subtotal+$row[subtotal];
		  $product_info .= '<tr>	
					<td align="left">
	                    '.$row[name].'
                    </td> 					
					<td align="center">
	                    '.round($row[qty_ordered],0).'
                    </td>
				</tr>';
		}
		 $erp_total = $sum_subtotal+$shipping_cost+$subcharge;
		 $product_info .= '<tr>		
					<td colspan="2" align="right">
	                    ERP Total('.$currency.'): '.$erp_total.'
                    </td> 					
				</tr>';
		
		return $product_info;
	}else
		return $sql;
}				  
?>
