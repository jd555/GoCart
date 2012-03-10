<?php ob_start();?>

<?php
$ads_javascript	= ob_get_contents();
ob_end_clean();


$additional_header_info = $ads_javascript;

include('header.php'); ?>
<div id="homepage_welcome">
<h2>Welcome to Docuseek 2!</h2>
<p>New to Docuseek 2? Search for titles from the participating distributors by entering search terms below. If you want to really target your search, try the options on the Advanced Search page. 
<?php if(!$this->Customer_model->is_logged_in(false,false)) 
	echo str_replace('{login}', anchor('secure/login', strtolower(lang('login'))), lang('login_required')) . '</p>';
?>
<p>Need help with searching? Try our Help page.</p>
<p>Have questions about Docuseek 2, your Docuseek 2 account, or a Docuseek 2 purchase? Contact <?php echo anchor('contactform', 'Docuseek support'); ?>.</p>
</div> <!--  id="homepage_welcome" -->
<div id="homepage_boxes">
	<?php 
//	echo base_url() . "<br />\n";
//	echo site_url() . "<br />\n";
	foreach ($boxes as $box)
	{
		echo '<div class="box_container">';
		
		if($box->link != '')
		{
			$target	= false;
			if($box->new_window)
			{
				$target = 'target="_blank"';
			}
			if (strtolower(substr($box->link, 0, 4)) == 'http')
			{
				// assume absolute path
				echo '<a href="'.$box->link.'" '.$target.' >';
			}
			else
			{
				// assume relative page, fill in the rest
				echo '<a href="'. site_url($box->link)  .'" '.$target.' >';
			}
		}
		echo '<img src="'.base_url('uploads/'.$box->image).'" />';
		
		if($box->link != '')
		{
			echo '</a>';
		}

		echo '</div>';
	}
	?>
</div>

<?php include('footer.php'); ?>