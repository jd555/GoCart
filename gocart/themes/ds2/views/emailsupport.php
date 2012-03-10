<?php include('header.php');?>
<div id="support_email">
	<?php echo form_open('cart/sendSupportEmail'); ?>
		
	<?php
		$name_data = array(
				'name' => 'name',
				'id' => 'name',
				'value' => set_value('name'),
				'placeholder' => 'Your name'
		);
		$emailaddress_data = array(
				'name' => 'emailaddress',
				'id' => 'emailaddress',
				'placeholder' => lang('email')	// start with stored user email address
		);
		$subject_data = array(
				'name' => 'subject',
				'id' => 'subject',
				'value' => set_value('subject'),
				'placeholder' => 'Subject'
		);
		$msg_data = array(
				'name' => 'msg',
				'id' => 'msg',
				'value' => set_value('msg')
		);
	?>
	<p>
		<label for='name'>Name: </label><?php echo form_input($name_data); ?>
	</p>
	<p>
		<label for='emailaddress'>Email address: </label><?php echo form_input($emailaddress_data); ?>
	</p>
	<p>
		<label for='subject'>Subject: </label><?php echo form_input($subject_data); ?>
	</p>
	<p>
		<label for='msg'>Message: </label><?php echo form_textarea($msg_data); ?>
	</p>
	
	<p>
	<?php echo $captcha; ?>	
	<input type="text" name="captcha" value="" />
	</p>
	
	<p><?php echo form_submit('submit', 'Send Email'); ?>
	
	<?php echo form_close();?>
	
	<?php echo validation_errors('<p class="error">'); ?>
		
</div> <!-- end of support_email div -->
<br/>

</div>
<?php include('footer.php');?>
