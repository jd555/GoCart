<?php include('header.php'); ?>

<div id="social_sharing">
	<!-- AddThis Button BEGIN -->
	<div class="addthis_toolbox addthis_default_style ">
	<a class="addthis_button_preferred_1"></a>
	<a class="addthis_button_preferred_2"></a>
	<a class="addthis_button_preferred_3"></a>
	<a class="addthis_button_preferred_4"></a>
	<a class="addthis_button_compact"></a>
	<a class="addthis_counter addthis_bubble_style"></a>
	</div>
	<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=xa-4e4ed7263599fdd0"></script>
	<!-- AddThis Button END -->
</div>

<div id="product_left">
	<div id="product_image">
		<?php
		//get the primary photo for the product
		$photo	= '<img src="'.base_url('images/nopicture.png').'" alt="'.lang('no_image_available').'"/>';

		if(count($product->images) > 0 )
		{	
			$primary	= $product->images[0];
			foreach($product->images as $image)
			{
				if(isset($image->primary))
				{
					$primary	= $image;
				}
			}

			$photo	= '<a href="'.base_url('uploads/images/medium/'.$primary->filename).'" rel="gallery" title="'.$primary->caption.'"><img src="'.base_url('uploads/images/small/'.$primary->filename).'" alt="'.$product->slug.'"/></a>';
		}
		echo $photo;
	
	
		if(!empty($primary->caption)):?>
		<div id="product_caption">
			<?php echo $primary->caption;?>
		</div>
		<?php endif;?>
	</div>

	<!-- put pricing info here -->
	<?php echo form_open('cart/add_to_cart');?>

	<input type="hidden" name="cartkey" value="<?php echo $this->session->flashdata('cartkey');?>" />
	<input type="hidden" name="id" value="<?php echo $product->id?>"/>

		<div class="product_section">
<!--			<div class="product_sku"><?php echo lang('sku');?>: <?php echo $product->sku; ?></div> -->
			<div class='product_specs'>
				<label>Distributor:</label> <?php echo $distributors[$product->extras[0]->distrib] . '<br/>';?> 
				<label>Length:</label> <?php echo $product->extras[0]->length . ' minutes<br/>';?> 
				<label>Release date:</label> <?php echo $product->extras[0]->rel_date . '<br/>';?>
				<?php if($product->extras[0]->color!='')echo '<label>'.$product->extras[0]->color . '</label><br/>';?>
			</div>
<!--		
					<?php if($product->saleprice > 0):?>
						<span class="price_slash">price: <?php echo format_currency($product->price); ?></span>
						<span class="price_sale">SALE: <?php echo format_currency($product->saleprice); ?></span>
					<?php else: ?>
						<span class="price_reg">Price: <?php echo format_currency($product->price); ?></span>
					<?php endif;?>
				<?php if($customer['group_id']==0) { ?>
					<span class="price_reg">Price: <?php echo format_currency($product->price); ?></span>
				<?php } else {
					if($customer['pricelevels']['option1']!=0) { ?>
						<span class="price_reg"><?php echo $pricelevels['option1']; ?>: <?php echo format_currency($customer['pricelevels']['option1']); }?></span>
					<?php if($customer['pricelevels']['option2']!=0) { ?>
						<br><span class="price_reg"><?php echo $pricelevels['option2']; ?>: <?php echo format_currency($customer['pricelevels']['option2']); } ?></span>
					<?php if($customer['pricelevels']['option3']!=0) { ?>
						<br><span class="price_reg"><?php echo $pricelevels['option3']; ?>: <?php echo format_currency($customer['pricelevels']['option3']); } ?></span>
					<?php if($customer['pricelevels']['option4']!=0) { ?>
						<br><span class="price_reg"><?php echo $pricelevels['option4']; ?>: <?php echo format_currency($customer['pricelevels']['option4']); } ?></span>
					<?php if($product->price>0) ?>
						<br><span class="price_reg">DVD purchase: <?php echo format_currency($product->price); ?></span>

				<?php }?>
-->
		</div>

		<?php if($this->Customer_model->is_logged_in(false, false)) {?>				
		<?php if(count($options) > 0): ?>
			<div class="product_section">
			<!-- <h2><?php echo lang('available_options');?></h2> -->
			<?php	
			foreach($options as $option):
				$required	= '';
				if($option->required)
				{
					$required = ' <span class="red">*</span>';
				}
				?>
				<div class="option_container">
					<div class="option_name"><?php echo $option->name.$required;?></div>
					<?php
					/*
					this is where we generate the options and either use default values, or previously posted variables
					that we either returned for errors, or in some other releases of Go Cart the user may be editing
					and entry in their cart.
					*/

					//if we're dealing with a textfield or text area, grab the option value and store it in value
					if($option->type == 'checklist')
					{
						$value	= array();
						if($posted_options && isset($posted_options[$option->id]))
						{
							$value	= $posted_options[$option->id];
						}
					}
					else
					{
						$value	= $option->values[0]->value;
						if($posted_options && isset($posted_options[$option->id]))
						{
							$value	= $posted_options[$option->id];
						}
					}

					if($option->type == 'textfield'):?>

						<input type="textfield" id="input_<?php echo $option->id;?>" name="option[<?php echo $option->id;?>]" value="<?php echo $value;?>" />

					<?php elseif($option->type == 'textarea'):?>

						<textarea id="input_<?php echo $option->id;?>" name="option[<?php echo $option->id;?>]"><?php echo $value;?></textarea>

					<?php elseif($option->type == 'droplist'):?>
						<select name="option[<?php echo $option->id;?>]">
							<option value=""><?php echo lang('choose_option');?></option>

						<?php foreach ($option->values as $values):
							$selected	= '';
							if($value == $values->id)
							{
								$selected	= ' selected="selected"';
							}?>

							<option<?php echo $selected;?> value="<?php echo $values->id;?>">
								<?php echo($values->price != 0)?'('.format_currency($values->price).') ':''; echo $values->name;?>
							</option>

						<?php endforeach;?>
						</select>
					<?php elseif($option->type == 'radiolist'):
							foreach ($option->values as $values):

								$checked = '';
								/*
								if($value == $values->id)
								{
									$checked = ' checked="checked"';
								}
								*/
								if($values->value == 'option1')
								{
									$checked = ' checked="checked"';
								}
								?>

								<div>
								<input<?php echo $checked;?> type="radio" name="option[<?php echo $option->id;?>]" value="<?php echo $values->id;?>"/>
								<?php 
								echo $values->name;
								if (array_key_exists($values->value,$customer['pricelevels']))
								{									
									if($customer['pricelevels'][$values->value]!=0)
									{ 
										echo ': ' . format_currency($customer['pricelevels'][$values->value]);
									}
								}
								else
								{
									if ($values->price!=0)
									{ 
										echo ': ' . format_currency($values->price);
									}
								}
								?>
								</div>
							<?php endforeach;?>

					<?php elseif($option->type == 'checklist'):
						foreach ($option->values as $values):

							$checked = '';
							if(in_array($values->id, $value))
							{
								$checked = ' checked="checked"';
							}?>
							<div class="gc_option_list">
							<input<?php echo $checked;?> type="checkbox" name="option[<?php echo $option->id;?>][]" value="<?php echo $values->id;?>"/>
							<?php echo($values->price != 0)?'('.format_currency($values->price).') ':''; echo $values->name;?>
							</div>
						<?php endforeach ?>
					<?php endif;?>
					</div>
			<?php endforeach;?>
		</div>
		<?php endif; ?>
		<?php } // is logged in? displaying purchase options ?>
		<div style="text-align:center; overflow:hidden;">
			<?php if(!$this->config->item('allow_os_purchase') && ((bool)$product->track_stock && $product->quantity <= 0)) : ?>
				<h2 class="red"><?php echo lang('out_of_stock');?></h2>				
			<?php else: ?>
				<?php if((bool)$product->track_stock && $product->quantity <= 0):?>
					<div class="red"><small><?php echo lang('out_of_stock');?></small></div>
				<?php endif; ?>
				<?php if(!$product->fixed_quantity) : ?>
					<?php echo lang('quantity') ?> <input class="product_quantity" type="text" name="quantity" value=""/>
				<?php endif; ?>
				<?php if ($this->Customer_model->is_logged_in(false,false)) {?>
					<input class="add_to_cart_btn" type="submit" value="<?php echo lang('form_add_to_cart');?>" /> 
				<?php } else echo str_replace('{login}', anchor('secure/login', strtolower(lang('login'))), lang('login_required')) ; ?>
					
			<?php endif;?>
		</div>
	
</div>


	<div class="product_section">	
	
	</div>
		
	</form>
	<div id="product_right">	
	<div class="tabs">
		<ul>
			<li><a href="#description_tab"><?php echo lang('tab_description');?></a></li>
			<li><a href="#reviews_tab"><?php echo lang('tab_reviews');?></a></li>
			<li><a href="#transcript_tab"><?php echo lang('tab_transcript');?></a></li>
			<li><a href="#credits_tab"><?php echo lang('tab_credits');?></a></li>
			<li><a href="#cataloging_tab"><?php echo lang('tab_cataloging');?></a></li>
			<li><a href="#preview_tab"><?php echo lang('tab_preview');?></a></li>
		</ul>
		<div id="description_tab">
			<?php echo $product->description; ?>
		</div>
		<div id="reviews_tab">
			<h2>Reviews go here</h2>
		</div>
		<div id="transcript_tab">
			<h2>Transcript info goes here</h2>
		</div>
		<div id="credits_tab">
			<h2>Credits info goes here</h2>
		</div>
		<div id="cataloging_tab">
			<h2>Cataloging info goes here</h2>
		</div>
		<div id="preview_tab">
			<h2>Preview viewer goes here</h2>
			Also trailer link?
		</div>
	</div>

</div>

<script type="text/javascript"><!--
$(function(){ 
	$('.tabs').tabs();
	
	$('a[rel="gallery"]').colorbox({ width:'80%', height:'80%', scalePhotos:true });
	
	$('#related_tab').width($('#description_tab').width());

	var w	= parseInt(($('#related_tab').width()/4)-33);

	$('.category_box').width();
	$('.category_container').each(function(){
		$(this).children().equalHeights();
	});	
});
//--></script>

<?php include('footer.php'); ?>