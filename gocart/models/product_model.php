<?php
Class Product_model extends CI_Model
{
		
	// we will store the group discount formula here
	// and apply it to product prices as they are fetched 
	var $group_discount_formula = false;
	
	function __construct()
	{
		parent::__construct();
		
		// check for possible group discount 
		$customer = $this->session->userdata('customer');
		if(isset($customer['group_discount_formula'])) 
		{
			$this->group_discount_formula = $customer['group_discount_formula'];
		}
	}

	function get_products($category_id = false, $limit = false, $offset = false)
	{
		//if we are provided a category_id, then get products according to category
		if ($category_id)
		{
			$result = $this->db->select('category_products.*')->from('category_products')->join('products', 'category_products.product_id=products.id')->where(array('category_id'=>$category_id, 'enabled'=>1))->order_by('sequence', 'ASC')->limit($limit)->offset($offset)->get()->result();
			
			//$this->db->order_by('sequence', 'ASC');
			//$result	= $this->db->get_where('category_products', array('enabled'=>1,'category_id'=>$category_id), $limit, $offset);
			//$result	= $result->result();

			$contents	= array();
			$count		= 0;
			foreach ($result as $product)
			{

				$contents[$count]	= $this->get_product($product->product_id);
				$count++;
			}

			return $contents;
		}
		else
		{
			//sort by alphabetically by default
			$this->db->order_by('name', 'ASC');
			$result	= $this->db->get('products');
			//apply group discount
			$return = $result->result();
			if($this->group_discount_formula) 
			{
				foreach($return as &$product) {
					eval('$product->price=$product->price'.$this->group_discount_formula.';');
				}
			}
			return $return;
		}

	}

	function count_products($id)
	{
		return $this->db->select('product_id')->from('category_products')->join('products', 'category_products.product_id=products.id')->where(array('category_id'=>$id, 'enabled'=>1))->count_all_results();

	}

	function get_product($id, $sub=true)
	{
		$result	= $this->db->get_where('products', array('id'=>$id))->row();
		if(!$result)
		{
			return false;
		}
		
		$result->categories = $this->get_product_categories($result->id);
		
		$result->extras = $this->get_product_extras($result->id);
		
		// group discount?
		if($this->group_discount_formula) 
		{
			eval('$result->price=$result->price'.$this->group_discount_formula.';');
		}
		return $result;
	}

	function get_product_categories($id)
	{
		$cats	= $this->db->where('product_id', $id)->get('category_products')->result();
		
		$categories = array();
		foreach ($cats as $c)
		{
			$categories[] = $c->category_id;
		}
		return $categories;
	}

	function get_slug($id)
	{
		return $this->db->get_where('products', array('id'=>$id))->row()->slug;
	}

	function check_slug($str, $id=false)
	{
		$this->db->select('slug');
		$this->db->from('products');
		$this->db->where('slug', $str);
		if ($id)
		{
			$this->db->where('id !=', $id);
		}
		$count = $this->db->count_all_results();

		if ($count > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function save($product, $options=false, $categories=false)
	{
		if ($product['id'])
		{
			$this->db->where('id', $product['id']);
			$this->db->update('products', $product);

			$id	= $product['id'];
		}
		else
		{
			$this->db->insert('products', $product);
			$id	= $this->db->insert_id();
		}

		//loop through the product options and add them to the db
		if($options !== false)
		{
			$obj =& get_instance();
			$obj->load->model('Option_model');

			// wipe the slate
			$obj->Option_model->clear_options($id);

			// save edited values
			$count = 1;
			foreach ($options as $option)
			{
				$values = $option['values'];
				unset($option['values']);
				$option['product_id'] = $id;
				$option['sequence'] = $count;

				$obj->Option_model->save_option($option, $values);
				$count++;
			}
		}
		
		if($categories !== false)
		{
			if($product['id'])
			{
				//get all the categories that the product is in
				$cats	= $this->get_product_categories($id);
				
				//eliminate categories that products are no longer in
				foreach($cats as $c)
				{
					if(!in_array($c, $categories))
					{
						$this->db->delete('category_products', array('product_id'=>$id,'category_id'=>$c));
					}
				}
				
				//add products to new categories
				foreach($categories as $c)
				{
					if(!in_array($c, $cats))
					{
						$this->db->insert('category_products', array('product_id'=>$id,'category_id'=>$c));
					}
				}
			}
			else
			{
				//new product add them all
				foreach($categories as $c)
				{
					$this->db->insert('category_products', array('product_id'=>$id,'category_id'=>$c));
				}
			}
		}
		
		//return the product id
		return $id;
	}
	
	function delete_product($id)
	{
		// delete product 
		$this->db->where('id', $id);
		$this->db->delete('products');

		//delete references in the product to category table
		$this->db->where('product_id', $id);
		$this->db->delete('category_products');
		
		// delete coupon reference
		$this->db->where('product_id', $id);
		$this->db->delete('coupons_products');

	}

	function add_product_to_category($product_id, $optionlist_id, $sequence)
	{
		$this->db->insert('product_categories', array('product_id'=>$product_id, 'category_id'=>$category_id, 'sequence'=>$sequence));
	}

	function search_products($term, $limit=false, $offset=false)
	{
		$results		= array();

		//I know this is the round about way of doing things and is not the fastest. but it is thus far the easiest.

		//this one counts the total number for our pagination
		$this->db->like('name', $term);
		$this->db->or_like('description', $term);
		$this->db->or_like('excerpt', $term);
		$this->db->or_like('sku', $term);
		$results['count']	= $this->db->count_all_results('products');

		//this one gets just the ones we need.
		$this->db->like('name', $term);
		$this->db->or_like('description', $term);
		$this->db->or_like('excerpt', $term);
		$this->db->or_like('sku', $term);
		$results['products']	= $this->db->get('products', $limit, $offset)->result();
		return $results;
	}

	// Build a cart-ready product array
	function get_cart_ready_product($id, $quantity=false)
	{
		$db_product			= $this->get_product($id);
		if( ! $db_product)
		{
			return false;
		}
		
		$product = array();
		
		if ($db_product->saleprice == 0.00) { 
			$product['price']	= $db_product->price;
		}
		else
		{
			$product['price']	= $db_product->saleprice;
		}
		
		$product['base_price'] 		= $product['price']; // price gets modified by options, show the baseline still...
		$product['id']				= $db_product->id;
		$product['name']			= $db_product->name;
		$product['sku']				= $db_product->sku;
		$product['excerpt']			= $db_product->excerpt;
		$product['weight']			= $db_product->weight;
		$product['shippable']	 	= $db_product->shippable;
		$product['taxable']			= $db_product->taxable;
		$product['fixed_quantity']	= $db_product->fixed_quantity;
		$product['track_stock']		= $db_product->track_stock;
		$product['options']			= array();
		
		// Some products have n/a quantity, such as downloadables	
		if (!$quantity || $quantity <= 0 || $db_product->fixed_quantity==1)
		{
			$product['quantity'] = 1;
		} else {
			$product['quantity'] = $quantity;
		}

		
		// attach list of associated downloadables
		$product['file_list']	= $this->Digital_Product_model->get_associations_by_product($id);
		
		return $product;
	}
	
	function get_product_extras($id)
	{
		// get data from the ds2_titles table
		$tdata	= $this->db->where('product_id', $id)->get('titles')->result();
		
		$extras = array();
		foreach ($tdata as $prop=>$val)
		{
			$extras[$prop] = $val;
		}
		return $extras;
		
	}
	
	function adv_search_products($advsearch, $limit=false, $offset=false)
	{
		// $advsearch is an Advsearch 
		$results		= array();

		$this->db->start_cache();
		
		$this->getdistribs($advsearch);
		
		if ($advsearch->ccredits != '')
			$this->db->like('ds2_titles.credits',$advsearch->ccredits);
			
		if ($advsearch->cawards != '')
			$this->db->like('awards',$advsearch->cawards);

		if ($advsearch->ckeywords != '')
			$this->getkeywords($advsearch->ckeywords);
		
		if ($advsearch->clowreldate != '' || $advsearch->chighreldate != '' )
			$this->getreleasedates($advsearch);
			
		if (!empty($advsearch->ctitle))
			$this->gettitle($advsearch);
			
		$this->getlength($advsearch);
		$this->getgradelevel($advsearch);
		$this->getformat($advsearch);
		$this->getmisc($advsearch);
		
		$this->db->join('ds2_titles', 'ds2_titles.product_id = ds2_products.id');
		
		$this->db->stop_cache();
		
		$results['count']	= $this->db->count_all_results('products');

		//this one gets just the ones we need.
		// do we need to fill the where clause again?
		$this->db->order_by('name');
		
		$results['products']	= $this->db->get('products', $limit, $offset)->result();
		
		$this->db->flush_cache();
		
		// echo $this->db->last_query() . "<br />\n";
		
		return $results;
		
	}
	
	function getdistribs(&$advsearch)
	{

		if (($advsearch->isfrif == 'true' && $advsearch->isbullfrog == 'true') || ($advsearch->isfrif != 'true' && $advsearch->isbullfrog != 'true'))
		{
			// all distribs, so no criteria
			return;
		}
		
		$this->db->where('(' . 
			(($advsearch->isfrif == 'true') ? '`distrib` = \'FRIF\'' : '0') .
			' OR ' . 
			(($advsearch->isbullfrog == 'true') ? '`distrib` = \'BULL\'' : '0') .
			')');
		return;
		
	}
	
	function getkeywords($ckeywords)
	{
		// at some point, we can try the natural language query
		$ckeywordclause = '(' . 
			'`name` LIKE \'%' . $ckeywords . '%\' OR ' .
			'`description` LIKE \'%' . $ckeywords . '%\' OR ' .
			'`excerpt` LIKE \'%' . $ckeywords . '%\' OR ' .
			'`sku` LIKE \'%' . $ckeywords . '%\' OR ' .
			'ds2_titles.credits LIKE \'%' . $ckeywords . '%\' OR ' .
			'ds2_titles.transcript LIKE \'%' . $ckeywords . '%\' OR ' .
			'ds2_titles.reviews LIKE \'%' . $ckeywords . '%\' OR ' .
			'ds2_titles.cataloging LIKE \'%' . $ckeywords . '%\')';
		
		$this->db->where($ckeywordclause);
		
	}
	
	function getreleasedates(&$advsearch)
	{
		//
		// dates
		//
		$clowreldate = $advsearch->clowreldate;
		$chighreldate = $advsearch->chighreldate;
		$lcwhereclause = '';
		$lcdispwhere = '' ;
		
		if (empty($clowreldate))
		{
			$clowreldate = "1900";
		}
		elseif (strlen(trim($clowreldate)) == 2)
		{
			// only two digits for year
			if (intval($clowreldate) + 2000 > date('Y', time()))
			{
				$clowreldate = "19" . trim($clowreldate);
			}
			else
			{
				$clowreldate = "20" . trim($clowreldate);
			}
		}
		elseif (strlen(trim($clowreldate)) == 4 && between(intval($clowreldate), 1850, date('Y', time())))
		{
			// okay
		}
		else
		{
			// ??
			$clowreldate = "1900";
		}

		if (empty($chighreldate))
				$chighreldate = "2099";
		elseif (strlen(trim($chighreldate)) == 2)
		{
			// only two digits for year
			if (intval($chighreldate) + 2000 > date('Y', time()))
			{
				$chighreldate = "19" . trim($chighreldate);
			}
			else
			{
				$chighreldate = "20" . trim($chighreldate);
			}
		}
		elseif (intval($chighreldate) < intval($clowreldate))
		{
			// high year is before low year
			$chighreldate = "2099";
		}
		elseif (strlen(trim($chighreldate)) == 4 && intval($chighreldate) >=1850 && intval($chighreldate) <= 2099)
		{
			// okay
		}
		else
		{
			// ??
			$chighreldate = "2099";
		}		
		
		if (($clowreldate != "1900" || $chighreldate != "2099"))
		{
			if (($clowreldate == "1900" && $chighreldate != "2099"))
			{
				$lcwhereclause .= "(LENGTH(filmyear)>0 AND filmyear <= '" . $chighreldate . "')";
				$lcdispwhere .= "Release year is before " .  strval(intval($chighreldate) + 1) . '<BR>';
			}
			else
			{
				if (($clowreldate != "1900" && $chighreldate == "2099"))
				{
					$lcwhereclause .= "filmyear >= '" . $clowreldate . "'";
					$lcdispwhere .= "Release year is after " .  strval(intval($clowreldate) - 1) . '<BR>';
				}
				else // ($clowreldate != "1900" && $chighreldate != "2099");
				{
					$lcwhereclause .= "filmyear BETWEEN '" . $clowreldate . "' AND '" . $chighreldate . "'";
					$lcdispwhere .= "Release year is between " .  $clowreldate . " and " . $chighreldate . "<BR>";
				}
			}
		}
		$advsearch->cdisplaywhere .= $lcdispwhere;
		$this->db->where($lcwhereclause);
		
	}

	function getlength(&$advsearch)
	{
		$ilowlength = max($advsearch->ilowlength,0);
		$ihighlength = empty($advsearch->ihighlength) ? 999 : $advsearch->ihighlength;
		$lcdispwhere = '';
		$lcwhereclause = '';
		
		if (($ilowlength > 0 || $ihighlength < 999))
		{
			// we need to check length
			if (($ilowlength > 0 && $ihighlength == 999))
			{
				// low length only
				$lcwhereclause .= " `length` >= " . strval($ilowlength);
				$lcdispwhere .= "Length is " .  strval($ilowlength) . " minutes or more<BR>";
			}
			else
			{
				if (($ilowlength == 0 && $ihighlength < 999))
				{
					// high length only
					$lcwhereclause .= "`length` BETWEEN 1 AND " . strval($ihighlength);	// ignore 0 length titles
					$lcdispwhere .= "Length is " .  strval($ihighlength) . " minutes or less<BR>";
				}
				else
				{
					// high and low length
					$lcwhereclause .= " `length` BETWEEN " . strval($ilowlength) . ' AND ' .  strval($ihighlength);
					$lcdispwhere .= "Length is between " . strval($ilowlength) . " minutes and " .  strval($ihighlength) . " minutes<BR>";
				}
			}
			$this->db->where($lcwhereclause);
			$advsearch->cdisplaywhere .= $lcdispwhere;
		}
	}
	
	function getgradelevel(&$advsearch)
	{
		//
		// grade levels
		//
		$lcgradelevelclause = '';
		$lcdispgradelevel = "";
		if ($advsearch->lvlpschool)
		{
			$lcgradelevelclause .= "lvlpschool";
			$lcdispgradelevel .= "Pre-school";
		}
		if ($advsearch->lvlk3)
		{
			if (!empty($lcgradelevelclause))
			{
				$lcgradelevelclause .= " OR ";
				$lcdispgradelevel .= " OR ";
			}	
			$lcgradelevelclause .= "lvlk3";
			$lcdispgradelevel .= "Grades K - 3";
		}
		if ($advsearch->lvl46)
		{
			if (!empty($lcgradelevelclause))
			{
				$lcgradelevelclause .= " OR ";
				$lcdispgradelevel .= " OR ";
			}	
			$lcgradelevelclause .= "lvl46";
			$lcdispgradelevel .= "Grades 4 - 6";
		}
		if ($advsearch->lvl79)
		{
			if (!empty($lcgradelevelclause))
			{
				$lcgradelevelclause .= " OR ";
				$lcdispgradelevel .= " OR ";
			}	
			$lcgradelevelclause .= "lvl79";
			$lcdispgradelevel .= "Grades 7 - 9";
		}
		if ($advsearch->lvl1012)
		{
			if (!empty($lcgradelevelclause))
			{
				$lcgradelevelclause .= " OR ";
				$lcdispgradelevel .= " OR ";
			}	
			$lcgradelevelclause .= "lvl1012";
			$lcdispgradelevel .= "Grades 10 - 12";
		}
		if ($advsearch->lvlcollege)
		{
			if (!empty($lcgradelevelclause))
			{
				$lcgradelevelclause .= " OR ";
				$lcdispgradelevel .= " OR ";
			}	
			$lcgradelevelclause .= "lvlcollege";
			$lcdispgradelevel .=  "College";
		}	
		
		if ($advsearch->lvladult)
		{
			if (!empty($lcgradelevelclause))
			{
				$lcgradelevelclause .= " OR ";
				$lcdispgradelevel .= " OR ";
			}	
			$lcgradelevelclause .= "lvladult";
			$lcdispgradelevel .= "Adult";
		}
		
		if (!empty($lcgradelevelclause)	)
		{
			$lcgradelevelclause = "(" . $lcgradelevelclause . ")";
			$this->db->where($lcgradelevelclause);
			$advsearch->cdisplaywhere .= 'AND ' . "Grade level is " . $lcdispgradelevel . '<br>';
		}	
		
	}
	
	function getformat(&$advsearch)
	{
		//
		// format
		//
		$lcformatclause = '';
		$lcdispformat = "";
		if ($advsearch->onvideo)
		{
			$lcformatclause = "onvideo";
			$lcdispformat = "video";
		}
		if ($advsearch->ondvd)
		{
			if (!empty($lcformatclause))
			{
				$lcformatclause .= " OR ";
				$lcdispformat .= " OR ";
			}	
			$lcformatclause .= "ondvd";
			$lcdispformat .= "DVD";
		}
		if ($advsearch->onstream)
		{
			if (!empty($lcformatclause))
			{
				$lcformatclause .= " OR ";
				$lcdispformat .= " OR ";
			}	
			$lcformatclause .= "onstream";
			$lcdispformat .= "Streaming";
		}
		if ($advsearch->on16mm)
		{
			if (!empty($lcformatclause))
			{
				$lcformatclause .= " OR ";
				$lcdispformat .= " OR ";
			}	
			$lcformatclause .= "on16mm";
			$lcdispformat .= "16mm";
		}
		if (!empty($lcformatclause))
		{
			$lcformatclause = "(" . $lcformatclause . ")";
			$this->db->where($lcformatclause);
			$advsearch->cdisplaywhere .= ' AND ' . "Format is " . $lcdispformat . '<br>';
		}
		
	}
	
	function getmisc(&$advsearch)
	{
		
		//
		// miscellaneous
		//
		$lcdispwhere = '';
		
		if ($advsearch->closecaption)
		{
			$this->db->where('closecapti');
			$lcdispwhere .= ' AND ' . "Closed-captioned" . '<br>';
		}
		if ($advsearch->subtitled)
		{
			$this->db->where('subtitled');
			$lcdispwhere .= ' AND ' . "Sub-titled" . '<br>';
		}
		if ($advsearch->studyguide)
		{
			$this->db->where('studyguide');
			$lcdispwhere .= ' AND ' . "Study Guide is available" . '<br>';
		}
		if ($advsearch->isclassrm)
		{
			$this->db->where('isclassrm');
			$lcdispwhere .= ' AND ' . "Classroom version is available" . '<br>';
		}

		if ($lcdispwhere != '')
		{
			$lcdispwhere = (substr(ltrim($lcdispwhere),0,3) == "AND" ? substr(ltrim($lcdispwhere),4) : ltrim($lcdispwhere));
			$advsearch->cdisplaywhere .= ' AND ' . $lcdispwhere . '<br>';
		}
	}

	function gettitle(&$advsearch)
	{
		
		switch ($advsearch->ctitlematch)
		{
		 	case "starts":
				$this->db->where("(LEFT(UPPER(title)," . strval(strlen($advsearch->ctitle)) . ") = '" . trim(strtoupper($advsearch->ctitle)) . "' OR left(UPPER(realtitle)," . strval(strlen($advsearch->ctitle)) . ") = '" . strtoupper($advsearch->ctitle) . "')");
				$advsearch->cdisplaywhere .= 'Title starts with "' . trim($advsearch->ctitle) . '"<br>';
				break;
			case "contains":
				$this->db->where("LOCATE('" . strtoupper(trim($advsearch->ctitle)) . "', UPPER(realtitle)) > 0");
				$advsearch->cdisplaywhere .= 'Title contains "' . trim($advsearch->ctitle) . '"<br>';
				break;
			case "equals":
				$this->db->where("(RTRIM(UPPER(title)) = '" . strtoupper(trim($advsearch->ctitle)) . "' OR UPPER(rtrim(realtitle)) = '" . strtoupper(trim($advsearch->ctitle)) . "')");
				$advsearch->cdisplaywhere .= 'Title equals "' . trim($advsearch->ctitle) . '"<br>';
				break;
			case "soundex":
				$this->db->where("sndxtitle = '" . soundex(trim($advsearch->ctitle)) . "' OR sndxrtitle = '" .soundex(trim($advsearch->ctitle)) . "'" );
				//$lcwhereclause .=  "(SOUNDEX('" . $advsearch->ctitle . "') = sndxtitle OR SOUNDEX('" . $advsearch->ctitle . "') = sndxrtitle)";
				$advsearch->cdisplaywhere .= 'Title sounds like "' . trim($advsearch->ctitle) . '"<br>';
				break;
		}

	}
}