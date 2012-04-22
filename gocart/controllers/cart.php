<?php

class Cart extends CI_Controller {
	
	//we collect the categories automatically with each load rather than for each function
	//this just cuts the codebase down a bit
	var $categories	= '';
	
	//load all the pages into this variable so we can call it from all the methods
	var $pages = '';
	
	// determine whether to display gift card link on all cart pages
	//  This is Not the place to enable gift cards. It is a setting that is loaded during instantiation.
	var $gift_cards_enabled; 
	
	var $header_text;
	var $distribs = NULL;
	
	function __construct()
	{
		parent::__construct();
		
		//make sure we're not always behind ssl
		remove_ssl();
		
		$this->load->library('Go_cart');
		$this->load->model(array('Page_model', 'Product_model', 'Digital_Product_model', 'Gift_card_model', 'Option_model', 'Order_model', 'Settings_model', 'Pricelevel_model'));
		$this->load->helper(array('form_helper', 'formatting_helper'));
		
		//fill in our variables
		$this->categories	= $this->Category_model->get_categories_tierd(0);
		$this->pages		= $this->Page_model->get_pages();

		// check if giftcards are enabled
		$gc_setting = $this->Settings_model->get_settings('gift_cards');
		if(!empty($gc_setting['enabled']) && $gc_setting['enabled']==1)
		{
			$this->gift_cards_enabled = true;
		}			
		else
		{
			$this->gift_cards_enabled = false;
		}
		
		$this->distribs = array('BULL' => "Bullfrog Films", 'FRIF' => "Icarus Films", 'FANL' => 'Fanlight Productions');
				
		//load the theme package
		$this->load->add_package_path(APPPATH.'themes/'.$this->config->item('theme').'/');
		
	}

	function index()
	{
		$this->load->model(array('Banner_model', 'box_model'));
		$this->load->helper('directory');

		$data['gift_cards_enabled'] = $this->gift_cards_enabled;
		$data['banners']			= $this->Banner_model->get_homepage_banners(5);
		$data['boxes']				= $this->box_model->get_homepage_boxes(4);
		$data['homepage']			= true;
		$data['boxcount']			= count($data['boxes']);
		$this->load->view('homepage', $data);
		// $this->load->view('testview', $data);
	}

	function testview()
	{
		$this->load->model(array('Banner_model', 'box_model'));
		$this->load->helper('directory');

		$data['gift_cards_enabled'] = $this->gift_cards_enabled;
		$this->load->view('testview', $data);
	}

	function page($id)
	{
		$this->load->model('Page_model');
		$data['page']				= $this->Page_model->get_page($id);
		$data['fb_like']			= true;

		$data['page_title']			= $data['page']->title;
		$data['meta']				= $data['page']->meta;
		$data['seo_title']			= $data['page']->seo_title;
		
		$data['gift_cards_enabled'] = $this->gift_cards_enabled;
		
		$this->load->view('page', $data);
	}
	
	function search($code=false, $page = 0)
	{
		$this->load->model('Search_model');
		$this->load->library('advsearch'); // should be loaded in autoload.php
		$advsearch = new advsearch;
		$data = array();
		
		$this->displaysortorder($data);
		
// echo 'code: ' . 	$code . "<br />\n";
// echo 'post ckeywords: ' . $this->input->post('ckeywords') . "<br />\n";;
		//check to see if we have a search term
		if(!$code)
		{
			// if the term is in post, save it to the db and give me a reference
			$advsearch->ckeywords = $this->input->post('ckeywords');
// print_r($advsearch);
// echo "<br />\n" . '================================' . "<br />\n";
			$code = $this->Search_model->record_term(json_encode($advsearch));
			$this->session->set_userdata('searchcode', $code);
		}
		else
		{
			// if we have the md5 string, get the term
			$this->session->set_userdata('searchcode', $code);
			$term = $this->Search_model->get_term($code);
			$advsearchobj	= json_decode($term);
// print_r($advsearchobj);
/*
switch (json_last_error()) {
     case JSON_ERROR_NONE:
         echo ' - No errors';
     break;
     case JSON_ERROR_DEPTH:
         echo ' - Maximum stack depth exceeded';
     break;
     case JSON_ERROR_STATE_MISMATCH:
         echo ' - Underflow or the modes mismatch';
     break;
     case JSON_ERROR_CTRL_CHAR:
         echo ' - Unexpected control character found';
     break;
     case JSON_ERROR_SYNTAX:
         echo ' - Syntax error, malformed JSON';
     break;
     case JSON_ERROR_UTF8:
         echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
     break;
     default:
         echo ' - Unknown error';
     break;
 }
echo "<br />\n";
*/
			$advsearch->convertjson($advsearchobj);
		}
		
		$advsearch->initsearchoptions($data);
		
		$data['page_title']	= lang('search');
		$data['gift_cards_enabled'] = $this->gift_cards_enabled;
		
//echo 'ckeywords: ' . 	$advsearch->ckeywords . "<br />\n";
		if(empty($advsearch->ckeywords))
		{
			//if there is still no search term throw an error
			$this->load->view('search_error', $data);
		}
		else
		{
			$data['page_title']	= lang('search');
			$data['gift_cards_enabled'] = $this->gift_cards_enabled;
		
			//set up pagination
			$this->load->library('pagination');	
			// $config['base_url']		= base_url() . $this->config->item('index_page') . (strlen($this->config->item('index_page')) > 0 ? '/' : '') . 'cart/search/' . $code . '/';
			$config['base_url']		= site_url('cart/search/' . $code . '/') ;
			
			$config['uri_segment']	= 4;
			$config['per_page']		= 20;
	
			$result					= $advsearch->search($config['per_page'], $page);
			// $result					= $this->Product_model->search_products($data, $config['per_page'], $page);
			$config['total_rows']	= $result['count'];
			$this->pagination->initialize($config);
	
			$data['products']		= $result['products'];
			$data['count']			= $result['count'];
			$data['distribs']		= $this->distribs;

			$this->load->view('category', $data);
		}
	}

	function advsearchoptions($code=false, $page = 0)
	{
		$this->load->model('Search_model');
		$this->load->library('advsearch'); // should be loaded in autoload.php
		$advsearch = new advsearch;
		$data = array();
		
		$data['page_title']			= lang('advsearch');
		$data['gift_cards_enabled']	= $this->gift_cards_enabled;
		$data['advsearchoptions'] = true;

		// eventually retrieve any advanced search options
		$havecode = false;
		if(!$code)
		{
			// if the term is in post, save it to the db and give me a reference
			// see if we have one in the user data
			$code = $this->session->userdata('searchcode');
			if(!$code)
			{
				$advsearch->ckeywords = $this->input->post('keywords');
				$code = $this->Search_model->record_term(json_encode($advsearch));
				$this->session->set_userdata('searchcode', $code);
			}
			else
				$havecode = true;
		}
		else
			$havecode = true;
		
		if ($havecode)	
		{
			// if we have the md5 string, get the term
// echo 'code: ' . $code . "<br />\n";
			$term	= $this->Search_model->get_term($code);
			$advsearchobj = json_decode($term);
// echo 'term: ' . $term . "<br />\n";
// print_r($advsearchobj);
			if (json_last_error() != JSON_ERROR_NONE)
			{		
				switch (json_last_error())
				{
				     case JSON_ERROR_NONE:
				         echo ' - No errors';
				     break;
				     case JSON_ERROR_DEPTH:
				         echo ' - Maximum stack depth exceeded';
				     break;
				     case JSON_ERROR_STATE_MISMATCH:
				         echo ' - Underflow or the modes mismatch';
				     break;
				     case JSON_ERROR_CTRL_CHAR:
				         echo ' - Unexpected control character found';
				     break;
				     case JSON_ERROR_SYNTAX:
				         echo ' - Syntax error, malformed JSON';
				     break;
				     case JSON_ERROR_UTF8:
				         echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
				     break;
				     default:
				         echo ' - Unknown error';
				     break;
				}
				echo "<br />\n";
			 }
			else
				$advsearch->convertjson($advsearchobj);
		}
		else
		{
//echo 'Clear should call this'."<br />\n";			
			// $advsearch->convertjson($advsearchobj);
		}
		$advsearch->initsearchoptions($data);
		$this->load->view('advsearchview', $data);
		
	}	// end of advsearchoptions

	
	function advsearch($code=false, $page = 0)
	{
		// advanced search
		$this->load->library('advsearch'); // should be loaded in autoload.php
		$this->load->model('Search_model');
		
// echo 'calling $this->advsearch->advsearch() from cart' . "<br />\n";
		$advsearch = new advsearch;
		$data = array();
		
		$this->displaysortorder($data);
		
		$advsearch->loadterms();
		
		$havecode = false;

		if(!$code)
		{
			// see if we have one in the user data
			$code = $this->session->userdata('searchcode');
			if(!$code)
			{
				$code = $this->Search_model->record_term(json_encode($advsearch));
			}
			else
				$havecode = true;
		}	
		else
			$havecode = true;
		
		if ($havecode)	
		{
			// if we have the md5 string, set the term
// echo 'code: ' . $code . "<br />\n";
			$term = json_encode($advsearch);
			$this->Search_model->set_term($code,$term);
		}

		// set up pagination
		$this->load->library('pagination');
		$config['base_url']		= site_url('cart/search/' . $code . '/') ;
		$config['uri_segment']	= 4;
		$config['per_page']		= 20;

		// this is the actual search
		$result	= $advsearch->search($config['per_page'], $page);

		$config['total_rows']	= $result['count'];
		$this->pagination->initialize($config);

		$data['term'] = $term;
		
		$advsearch->initsearchoptions($data);
		$data['page_title']	= lang('search');
		$data['gift_cards_enabled'] = $this->gift_cards_enabled;

		$data['products']		= $result['products'];
		$data['count']			= $result['count'];
		$data['distribs']		= $this->distribs;
		
		if (!$result['success'])
			$this->load->view('search_error', $data);
		else
			$this->load->view('category', $data);
	}
	
	function clearadvsearch()
	{
		$this->session->set_userdata('searchcode', false);
		$this->advsearchoptions(false);
	}
	
	function displaysortorder(&$data)
	{
		// display/sort orders
		if($this->input->post('sortorder') != '')
		{
			$data['sortorder'] = $this->input->post('sortorder');
			$this->session->set_userdata('sortorder', $data['sortorder']);
		}
		else
			$data['sortorder'] = $this->session->userdata('sortorder');
		
		$data['lclengthchecked'] = '';
		$data['lcreldatechecked'] = '';
		$data['lcrelevancechecked'] = '';
		$data['lctitlechecked'] = '';
		
		switch ($data['sortorder'])
		{
			case (C_SORT_LENGTH):
				$data['lclengthchecked'] = "checked";
				break;
			case (C_SORT_RELEASEDATE):
				$data['lcreldatechecked'] = "checked";
				break;
			case (C_SORT_RELEVANCE):
				$data['lcrelevancechecked'] = " checked";
				break;
			default:
				$data['lctitlechecked'] = " checked";
				break;
		}
		return;
	}
	
	function category($id, $page=0)
	{
		
		//get the category
		$data['category']			= $this->Category_model->get_category($id);
				
		if (!$data['category'])
		{
			show_404();
		}
		
		$data['subcategories']		= $this->Category_model->get_categories($data['category']->id);
		$data['product_columns']	= $this->config->item('product_columns');
		$data['gift_cards_enabled'] = $this->gift_cards_enabled;
		
		$data['meta']		= $data['category']->meta;
		$data['seo_title']	= $data['category']->seo_title;
		
		$data['page_title']	= $data['category']->name;
		//set up pagination
		$this->load->library('pagination');
		$config['base_url']		= base_url().$data['category']->slug.'/';
		$config['uri_segment']	= 2;
		$config['per_page']		= 20;
		$config['total_rows']	= $this->Product_model->count_products($data['category']->id);
		$this->pagination->initialize($config);
		
		//grab the products using the pagination lib
		$data['products']	= $this->Product_model->get_products($data['category']->id, $config['per_page'], $page);
		foreach ($data['products'] as &$p)
		{
			$p->images	= (array)json_decode($p->images);
			$p->options	= $this->Option_model->get_product_options($p->id);
		}
		
		$this->load->view('category', $data);
	}
	
	function product($id)
	{
		//get the product
		$data['product']	= $this->Product_model->get_product($id);
//print_r($data)		
		if(!$data['product'] || $data['product']->enabled==0)
		{
			show_404();
		}

		// load the digital language stuff
		$this->lang->load('digital_product');
		
		$data['customer'] = $this->go_cart->customer(); // get customer so we know what prices to show
			
		$data['options']	= $this->Option_model->get_product_options($data['product']->id);
		
		$related			= (array)json_decode($data['product']->related_products);
		$data['related']	= array();
		foreach($related as $r)
		{
			$r					= $this->Product_model->get_product($r);
			if($r)
			{
				$r->images			= (array)json_decode($r->images);
				$r->options			= $this->Option_model->get_product_options($r->id);
				$data['related'][]	= $r;
			}
			
		}
		$data['posted_options']	= $this->session->flashdata('option_values');

		$data['page_title']			= $data['product']->name;
		$data['meta']				= $data['product']->meta;
		$data['seo_title']			= $data['product']->seo_title;
			
		if($data['product']->images == 'false')
		{
			$data['product']->images = array();
		}
		else
		{
			$data['product']->images	= array_values((array)json_decode($data['product']->images));
		}

		$data['gift_cards_enabled'] = $this->gift_cards_enabled;
		
		// pricelevel descriptions
		$data['pricelevels'] = $this->config->item('pricelevels');
		$data['distributors'] = $this->config->item('distributors');		
		$this->load->view('product', $data);
	}
	
	
	function add_to_cart()
	{
		// Get our inputs
		$product_id		= $this->input->post('id');
		$quantity 		= $this->input->post('quantity');
		$post_options 	= $this->input->post('option');
		$cartkey		= $this->input->post('cartkey');
		
		
		// Get a cart-ready product array
		$product = $this->Product_model->get_cart_ready_product($product_id, $quantity);

		// need to use customer price list
		$this->load->library('Go_cart');
		$customer = $this->go_cart->customer();
		$product['group_discount_type'] = $customer['group_discount_type'];
		$product['pricelevels'] = $customer['pricelevels'];
		
		//if out of stock purchase is disabled, check to make sure there is inventory to support the cart.
		if(!$this->config->item('allow_os_purchase') && (bool)$product['track_stock'])
		{
			$stock	= $this->Product_model->get_product($product_id);
			
			//loop through the products in the cart and make sure we don't have this in there already. If we do get those quantities as well
			$items		= $this->go_cart->contents();
			$qty_count	= $quantity;
			foreach($items as $item)
			{
				if(intval($item['id']) == intval($product_id))
				{
					$qty_count = $qty_count + $item['quantity'];
				}
			}
			
			if($stock->quantity < $qty_count)
			{
				//we don't have this much in stock
				$this->session->set_flashdata('error', sprintf(lang('not_enough_stock'), $stock->name, $stock->quantity));
				$this->session->set_flashdata('cartkey', $cartkey);
				$this->session->set_flashdata('quantity', $quantity);
				$this->session->set_flashdata('option_values', $post_options);

				redirect($this->Product_model->get_slug($product_id));
			}
		}
		
		// Validate Options 
		// this returns a status array, with product item array automatically modified and options added
		//  Warning: this method receives the product by reference
		if ($product['group_discount_type'] == 'lookup')
		{
			$product['price'] = 0;	// we use a lookup table instead (pricelevels)
			$product['base_price'] = 0;	// we use a lookup table instead (pricelevels)
			$product['weight'] = 0;	// we use a lookup table instead (pricelevels)
		}
		$status = $this->Option_model->validate_product_options($product, $post_options);
/*
$data['product'] = $product;
$this->load->view('testview', $data);
return;
*/	
		// don't add the product if we are missing required option values
		if( ! $status['validated'])
		{
			//if the cartkey does not exist, this will simply be blank
			$this->session->set_flashdata('cartkey', $cartkey);
			$this->session->set_flashdata('quantity', $quantity);
			$this->session->set_flashdata('error', $status['message']);
			$this->session->set_flashdata('option_values', $post_options);			
		
			redirect($this->Product_model->get_slug($product_id));
		
		} else {
		
			//Add the original option vars to the array so we can edit it later
			$product['post_options']	= $post_options;
			$product['cartkey']			= $cartkey;
			$product['is_gc']			= false;
			
			// Add the product item to the cart, also updates coupon discounts automatically
			$this->go_cart->insert($product);
		
			// go go gadget cart!
			redirect('cart/view_cart');
		}
	}
	
	function view_cart()
	{
		
		$data['page_title']	= 'View Cart';
		$data['gift_cards_enabled'] = $this->gift_cards_enabled;
		
		$this->load->view('view_cart', $data);
	}
	
	function remove_item($key)
	{
		//drop quantity to 0
		$this->go_cart->update_cart(array($key=>0));
		
		redirect('cart/view_cart');
	}
	
	function update_cart($redirect = false)
	{
		//if redirect isn't provided in the URL check for it in a form field
		if(!$redirect)
		{
			$redirect = $this->input->post('redirect');
		}
		
		// see if we have an update for the cart
		$item_keys		= $this->input->post('cartkey');
		$coupon_code	= $this->input->post('coupon_code');
		$gc_code		= $this->input->post('gc_code');
			
			
		//get the items in the cart and test their quantities
		$items			= $this->go_cart->contents();
		$new_key_list	= array();
		//first find out if we're deleting any products
		foreach($item_keys as $key=>$quantity)
		{
			if(intval($quantity) === 0)
			{
				//this item is being removed we can remove it before processing quantities.
				//this will ensure that any items out of order will not throw errors based on the incorrect values of another item in the cart
				$this->go_cart->update_cart(array($key=>$quantity));
			}
			else
			{
				//create a new list of relevant items
				$new_key_list[$key]	= $quantity;
			}
		}
		$response	= array();
		foreach($new_key_list as $key=>$quantity)
		{
			$product	= $this->go_cart->item($key);
			//if out of stock purchase is disabled, check to make sure there is inventory to support the cart.
			if(!$this->config->item('allow_os_purchase') && (bool)$product['track_stock'])
			{
				$stock	= $this->Product_model->get_product($product['id']);
			
				//loop through the new quantities and tabluate any products with the same product id
				$qty_count	= $quantity;
				foreach($new_key_list as $item_key=>$item_quantity)
				{
					if($key != $item_key)
					{
						$item	= $this->go_cart->item($item_key);
						//look for other instances of the same product (this can occur if they have different options) and tabulate the total quantity
						if($item['id'] == $stock->id)
						{
							$qty_count = $qty_count + $item_quantity;
						}
					}
				}
				if($stock->quantity < $qty_count)
				{
					if(isset($response['error']))
					{
						$response['error'] .= '<p>'.sprintf(lang('not_enough_stock'), $stock->name, $stock->quantity).'</p>';
					}
					else
					{
						$response['error'] = '<p>'.sprintf(lang('not_enough_stock'), $stock->name, $stock->quantity).'</p>';
					}
				}
				else
				{
					//this one works, we can update it!
					//don't update the coupons yet
					$this->go_cart->update_cart(array($key=>$quantity));
				}
			}
			else
			{
				$this->go_cart->update_cart(array($key=>$quantity));
			}
		}
		
		//if we don't have a quantity error, run the update
		if(!isset($response['error']))
		{
			//update the coupons and gift card code
			$response = $this->go_cart->update_cart(false, $coupon_code, $gc_code);
			// set any messages that need to be displayed
		}
		else
		{
			$response['error'] = '<p>'.lang('error_updating_cart').'</p>'.$response['error'];
		}
		
		
		//check for errors again, there could have been a new error from the update cart function
		if(isset($response['error']))
		{
			$this->session->set_flashdata('error', $response['error']);
		}
		if(isset($response['message']))
		{
			$this->session->set_flashdata('message', $response['message']);
		}
		
		if($redirect)
		{
			redirect($redirect);
		}
		else
		{
			redirect('cart/view_cart');
		}
	}

	
	/***********************************************************
			Gift Cards
			 - this function handles adding gift cards to the cart
	***********************************************************/
	
	function giftcard()
	{
		if(!$this->gift_cards_enabled) redirect('/');
		
		$this->load->helper('utility_helper');
		
		// Load giftcard settings
		$gc_settings = $this->Settings_model->get_settings("gift_cards");
				
		$this->load->library('form_validation');
		
		$data['allow_custom_amount']	= (bool) $gc_settings['allow_custom_amount'];
		$data['preset_values']			= explode(",",$gc_settings['predefined_card_amounts']);
		
		if($data['allow_custom_amount'])
		{
			$this->form_validation->set_rules('custom_amount', 'lang:custom_amount', 'numeric');
		}
		
		$this->form_validation->set_rules('amount', 'lang:amount', 'required');
		$this->form_validation->set_rules('preset_amount', 'lang:preset_amount', 'numeric');
		$this->form_validation->set_rules('gc_to_name', 'lang:recipient_name', 'trim|required');
		$this->form_validation->set_rules('gc_to_email', 'lang:recipient_email', 'trim|required|valid_email');
		$this->form_validation->set_rules('gc_from', 'lang:sender_email', 'trim|required');
		$this->form_validation->set_rules('message', 'lang:custom_greeting', 'trim|required');
		
		if ($this->form_validation->run() == FALSE)
		{
			$data['error']				= validation_errors();
			$data['page_title']			= lang('giftcard');
			$data['gift_cards_enabled']	= $this->gift_cards_enabled;
			$this->load->view('giftcards', $data);
		}
		else
		{
			
			// add to cart
			
			$card['price'] = set_value(set_value('amount'));
			
			$card['id']				= -1; // just a placeholder
			$card['sku']			= lang('giftcard');
			$card['base_price']		= $card['price']; // price gets modified by options, show the baseline still...
			$card['name']			= lang('giftcard');
			$card['code']			= generate_code(); // from the utility helper
			$card['excerpt']		= sprintf(lang('giftcard_excerpt'), set_value('gc_to_name'));
			$card['weight']			= 0;
			$card['quantity']		= 1;
			$card['shippable']		= false;
			$card['taxable']		= 0;
			$card['fixed_quantity'] = true;
			$card['is_gc']			= true; // !Important
			$card['track_stock']	= false; // !Imporortant
			
			$card['gc_info'] = array("to_name"	=> set_value('gc_to_name'),
									 "to_email"	=> set_value('gc_to_email'),
									 "from"		=> set_value('gc_from'),
									 "personal_message"	=> set_value('message')
									 );
			
			// add the card data like a product
			$this->go_cart->insert($card);
			
			redirect('cart/view_cart');
		}
	}
	
	// contact form functions
	function enterSupportEmail()
	{
		// get the support email
		$this->load->helper('captcha');
		$captcha_vals = array(
		    'img_path'		=> './captcha/',
		    'img_url'		=> base_url() . 'captcha/',
		    'img_width'		=> '150',
		    'img_height'	=> 30,
			'font_path'		=> './system/fonts/texb.ttf',
			'text_color'	=> array('red'=>0, 'green' =>0, 'blue' =>0),
		    'expiration'	=> 7200
		    );
// 	    

		$cap = create_captcha($captcha_vals);
		
		$data = array(
		    'captcha_time' => $cap['time'],
		    'ip_address' => $this->input->ip_address(),
		    'word' => $cap['word']
		    );

		$query = $this->db->insert_string('captcha', $data);
		$this->db->query($query);

		$data['cap'] = $cap;
		$data['captcha'] = $cap['image'];
		
		$data['page_title']			= lang('emailsupport');
		$data['gift_cards_enabled']	= $this->gift_cards_enabled;
		$this->load->view('emailsupport', $data);
		
	}
	
	 
	function sendSupportEmail() {
		// config details loaded via config/email.php
		$this->load->library('form_validation');
		
		// check captcha
		// First, delete old captchas
		$expiration = time()-7200; // Two hour limit
		$this->db->query("DELETE FROM captcha WHERE captcha_time < ".$expiration);

		// Then see if a captcha exists:
		$sql = "SELECT COUNT(*) AS count FROM captcha WHERE word = ? AND ip_address = ? AND captcha_time > ?";
		$binds = array($_POST['captcha'], $this->input->ip_address(), $expiration);
		$query = $this->db->query($sql, $binds);
		$row = $query->row();

		if ($row->count == 0)
		{
		    echo "You must submit the word that appears in the image";
		}
		
		
		// field name, error message, validation rule
		$this->form_validation->set_rules('name', 'Name', 'trim|required');
		$this->form_validation->set_rules('emailaddress', 'Email address', 'trim|required|valid_email');
		$this->form_validation->set_rules('subject', 'Subject', 'trim|required');
		$this->form_validation->set_rules('msg', 'Message', 'trim|required');

		if (!$this->form_validation->run())
			$this->enterSupportEmail();
		else
		{
			// validated
			$this->load->library('email');

			// need to grab from email address from the current logged in user info?
			$this->email->from( $this->input->post('emailaddress'),  $this->input->post('name'));
			$this->email->to('jd@docuseek2.com');
			$this->email->subject('FDS: ' . $this->input->post('subject'));
			$this->email->message($this->input->post('msg'));

			if ($this->email->send())
			{
				$this->sentEmailConfirmation();
				// show_error($this->email->print_debugger());
			}
			else
			{
				show_error($this->email->print_debugger());
			}
			
		}
	}
	
	function sentEmailConfirmation()
	{
		$data['page_title']			= lang('emailsenttitle');
		$data['gift_cards_enabled']	= $this->gift_cards_enabled;
		$this->load->view('sent_email_confirmation', $data);
	}

	function playvideo($titleid)
	{
		$data = array('id' => $titleid);
		$this->load->view('player', $data);
	}
}