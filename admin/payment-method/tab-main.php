<fieldset>
	<!--<legend></legend>-->
    
	<p><label>Name:</label><input type="text" name="name_1" value="<?=$row[name_1]; ?>"></p><br />
    
    <p><label>Subcharge:</label><input type="text" name="subcharge" value="<?=$row[subcharge]; ?>" style="width:30px">%</p><br />
    
	<p><label>Available:</label>
		<input type="radio" name="active" value="1" <?=set_checked($row[active], 1); ?>>Yes
		<input type="radio" name="active" value="0" <?=set_checked($row[active], 0); ?>>No
	</p>    
    
</fieldset>