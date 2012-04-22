<?php include('header.php'); ?>
	
	<?php if(count($products) == 0) {?>
		<div class="message">
			<?php echo lang('no_products');?>
		</div>
	<?php } else
		echo 'Your search found ' . number_format($count) . ' results.' . "<br />\n"; // count($products) == 0 ?>
	
<?php // print_r($products); ?>

	<?php if(count($products) > 0){ ?>
		<div class="clear"></div>
		<?php 
			$attributes = array('name' => 'search_form_2', 'id' => 'search_form_2', 'class'=>'search_form_2');
			echo form_open('cart/search', $attributes);?>
		<div id="search_form_2_div" class="center">
				<input type="text" name="ckeywords" value="<?php echo $ckeywords; ?> "/>
				<button type="submit"><?php echo lang('form_search');?></button>
				<button type='button' onclick="return RunQuery('search_form_2', '<?php echo site_url('/cart/advsearchoptions'); ?>');return false;"><?php echo lang('modify_search');?></button>
		</div>

		<?php if(!empty($cdisplaywhere)) {?>
			<div class='advsearchterms'>You searched for: <?php echo $cdisplaywhere; ?></div>
		<?php } 
		$pcSortOrder = '<input type="radio" name="sortorder" value="' . C_SORT_TITLE . '" ' . $lctitlechecked . ' onClick="return RunQuery(\'search_form_2\', \'' .  site_url('/cart/search') . '\');">Title' .  
			C_NBSP . 
			'<input type="radio" name="sortorder" value="' . C_SORT_RELEASEDATE . '" ' . $lcreldatechecked . ' onClick="return RunQuery(\'search_form_2\', \'' .  site_url('/cart/search') . '\');">Release year' . 
			C_NBSP . 
			'<input type="radio" name="sortorder" value="' . C_SORT_LENGTH . '" ' . $lclengthchecked . ' onClick="return RunQuery(\'search_form_2\', \'' .  site_url('/cart/search') . '\');">Length';

		// we have keywords to search on, so include relevance as sort option
		if (!empty($ckeywords))
			$pcSortOrder .= C_NBSP . '<input type="radio" name="sortorder" value="' . C_SORT_RELEVANCE . '" ' . $lcrelevancechecked . ' onClick="return RunQuery(\'search_form_2\', \'' .  site_url('/cart/search') . '\');">Relevance';
		?>
		<div class="sortresultsby">Sort results by: <?php echo $pcSortOrder; ?>
		</form>
		<div class="pagination">
		<?php echo $this->pagination->create_links();?>
		</div>

		<?php		

		foreach($products as $product) { ?>
			
			<div class="category_container">		
			<?php			
				$lcText = "";
				$lcText .= '<div class="searchresult_title"><a href="' . site_url($product->slug) . '">' . $product->realtitle . '</a></div>' ;
				if (!empty($product->oneline) )
				{
					$lcText .= '<div class="searchresult_oneline">' . str_replace( '\\' , '' , $product->oneline . '</div>' );
				}

				$cFactoid = "Distributor: " . $distribs[$product->distrib];
				
				
				if (!empty($product->filmyear))
				{
					$cFactoid .= ' Date: ' . $product->filmyear;
				}
				else
				{
					$cFactoid .= ' Date: n/a';
				}

				if ($product->length > 0)
				{
					$cFactoid .= ' Length: ' . ltrim(strval($product->length)) . ' minutes';
				}
				else
				{
					$cFactoid .= ' Length: n/a';
				}

				if (!empty($product->gradelevel))
				{
					$cFactoid .= ' Grade Level: ' . $product->gradelevel;
				}
				if (array_key_exists("rele", $product))
				{
					// try relevance score
					$cFactoid .= '</br>Relevance score: ' . ltrim(sprintf("%5.2f",strval($product->rele)));
				}

				if (!empty($cFactoid))
				{
					$lcText .= '<div class="searchresult_factoid">' . $cFactoid . '</div>';
				}

				// end of row
				echo $lcText . "\n";
			?>
			</div>
			
		<?php 
		} // end foreach; ?>
		
		<div class="gc_pagination">
		<?php echo $this->pagination->create_links();?>
		</div>
	<?php }; // (count($products) > 0) ?>
		
	
	<script type="text/javascript">
	$(document).ready(function(){
		$('.category_container').each(function(){
			$(this).children().equalHeights();
		});
	});
	</script>
<?php include('footer.php'); ?>