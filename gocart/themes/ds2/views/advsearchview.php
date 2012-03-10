<?php include('header.php');?>
<form name="frmSearch" action="advsearch" method="post">
	<strong>Advanced Search</strong> provides additional search options. <strong>Basic search</strong> provides a keyword search only. Advanced search allows you to search for videos by grade level, format, release date and more.
	<p>Enter your search criteria below (as many as you like), then click the Find Titles button. (Need help on searching? <?php echo anchor('help','Click here'); ?>.)</p>

<!--			
			<p><input id="btnSearch" name="btnSearch" type="submit" value="Find titles">&nbsp;&nbsp;<input id="btnClear" name="btnReset" type="button" value="Clear" title="Clear search terms" onClick="return ClearQuery('true');"></p>
<div id='distribs'>
-->
<p>
				<input <?php echo $isbullfrog=='true' ? "checked" : "" ?> id="isbullfrog" name="isbullfrog" type="checkbox" value="true">&nbsp;<A href="participating-distributors">Bullfrog Films</a><br>
					<input <?php echo $isfrif=='true' ? "checked" : "" ?> id="isfrif" name="isfrif" type="checkbox" value="true">&nbsp;<A href="participating-distributors">Icarus Films</a><br>
<!-- 						
				<input <?php echo $isDC ? "checked" : "" ?> id="chkDC" name="chkDC" type="checkbox" value="DC">&nbsp;<A href="distrib.php#dc">Direct Cinema Ltd</a><br>
				<input <?php echo $isFrameline ? "checked" : "" ?> id="chkFrameline" name="chkFrameline" type="checkbox" value="FRAME">&nbsp;<A href="distrib.php#frameline">Frameline</a><br>
				<input <?php echo $isWMM ? "checked" : "" ?> id="chkWMM" name="chkWMM" type="checkbox" value="WMM">&nbsp;<A href="distrib.php#wmm">Women Make Movies</a></td>
			<td valign="top">
				<input <?php echo $isCNews ? "checked" : "" ?> id="chkCNews" name="chkCNews" type="checkbox" value="CNEWS">&nbsp;<A href="distrib.php#cnews">California Newsreel</a><br>															
				<input <?php echo $isNewday ? "checked" : "" ?> id="chkNewday" name="chkNewday" type="checkbox" value="NEWD">&nbsp;<A href="distrib.php#newday">New Day Films</a><br>
				<input <?php echo $isFanlight ? "checked" : "" ?> id="chkFanlight" name="chkFanlight" type="checkbox" value="FANL">&nbsp;<A href="distrib.php#fanlight">Fanlight Productions</a></td>
-->
</p>
<label for='ckeywords'>Keywords</label>
<input id="ckeywords" type='text' name="ckeywords" size="38" value="<?php echo $ckeywords; ?>" /><br />
<span class="teenytext">See </span><a href="help.php"><span class="teenylink">Search Help</span></a><span class="teenytext"> for information on keyword searching.</span>
	<select name="ctitlematch" size="1">
		<option <?php echo $ctitlematch == "starts" ? "selected" : "" ?> value="starts">Starts with</option>
		<option <?php echo $ctitlematch == "contains" ? "selected" : "" ?> value="contains">Contains</option>
		<option <?php echo $ctitlematch == "soundex" ? "selected" : "" ?> value="soundex">Sounds like</option>
		<option <?php echo $ctitlematch == "equals" ? "selected" : "" ?> value="equals">Is</option>
	</select>
	<input id="ctitle" name="ctitle" size="38" value="<?php print $ctitle ?>">
	<p>
	<label for='ccredits'>Credits contain: </label>
	<input id="ccredits" name="ccredits" size="38"  value="<?php print $ccredits ?>" />
	</p>
	<p>
	<label for='cawards'>Awards contain: </label>
	<input id="cawards" name="cawards" size="38"  value="<?php print $cawards ?>" />
	</p>
	<p>
	<label for='ilowlength'>Length</label> between&nbsp; 
	<input id="ilowlength" name="lowLength" size="4" value="<?php print $ilowlength ?>" />&nbsp;and&nbsp;<input align="right" id="ihighlength" name="ihighlength" size="4"  value="<?php print $ihighlength ?>" />&nbsp;minutes
	</p>
	<p>
	<label for='lowRelDate'>Year</label> between&nbsp; <input id="lowRelDate" name="lowRelDate" size="4" value="<?php print $clowreldate ?>">&nbsp;and&nbsp; <input align="right" id="chighreldate" name="chighreldate" size="4" value="<?php print $chighreldate ?>" />
	</p>
	<p>
	<label for='gradelevel'>Grade level</label>
	<input id="checkbox2" name="chkpschool" type="checkbox" value="true" <?php echo $lvlpschool ? "checked" : "" ?>>&nbsp;Preschool&nbsp;
	<input id="lvlk3" name="lvlk3" type="checkbox" value="true" <?php echo $lvlk3 ? "checked" : "" ?>>&nbsp;1 - 3
	<input id="lvl46" name="lvl46" type="checkbox" value="true" <?php echo $lvl46 ? "checked" : "" ?>>&nbsp;4 - 6
	<input id="lvl79" name="lvl79" type="checkbox" value="true" <?php echo $lvl79 ? "checked" : "" ?>>&nbsp;7 - 9
	<input id="lvl1012" name="lvl1012" type="checkbox" value="true" <?php echo $lvl1012 ? "checked" : "" ?>>&nbsp;10 - 12
	<input id="lvlcollege" name="lvlcollege" type="checkbox" value="true" <?php echo $lvlcollege ? "checked" : "" ?>>&nbsp;College
	<input id="lvladult" name="lvladult" type="checkbox" value="true" <?php echo $lvladult ? "checked" : "" ?>>&nbsp;Adult
	</p>
	<p>
		<label>Format</label>
		<input id="onstream" name="onstream" type="checkbox" value="true" <?php echo $onstream ? "checked" : "" ?>>&nbsp;Streaming
		<input id="ondvd" name="ondvd" type="checkbox" value="true" <?php echo $ondvd ? "checked" : "" ?>>&nbsp;On DVD
	</p>
	<p>
		<label>Miscellaneous</label>
		<input id="closecaption" name="closecaption" type="checkbox" value="true" <?php echo $closecaption ? "checked" : "" ?>>&nbsp;Closed-captioned
		<input id="studyguide" name="studyguide" type="checkbox" value="true" <?php echo $studyguide ? "checked" : "" ?>>&nbsp;Study guide available
		<input id="subtitled" name="subtitled" type="checkbox" value="true" <?php echo $subtitled ? "checked" : "" ?>>&nbsp;Sub-titled
		<input id="isclassrm" name="isclassrm" type="checkbox" value="true" <?php echo $isclassrm ? "checked" : "" ?>>&nbsp;Classroom version
<p>
<?php 
echo form_submit('btnsearch','Find titles');
echo form_reset('btnclear','Clear');
?>
</p>
<?php include('footer.php');?>