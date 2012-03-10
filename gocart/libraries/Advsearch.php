<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

	define("C_PHP_UPLOAD_DIR", "./php_uploads/");

	define("C_SORT_TITLE", "title");
	define("C_SORT_RELEVANCE","rele");
	define("C_SORT_RELEASEDATE","filmyear");
	define("C_SORT_LENGTH","length");

	define("I_COOKIE_ISSUED",1);
	define("I_OSEARCH",2);

	define("C_NBSP", "&nbsp;");

	define("I_RESULTS_PER_PAGE", 10);
	define("I_MAXLINKS", 12);

	define("BANNERCOUNT", 45);	// make sure that this # matches the row count in dsBanner

	define("C_ACCENTCHARS",	'àáâãäåçèéêëìíîïñðòóôõöøùúûüýÿÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÑÒÓÔÕÖØÙÚÛÜÝ‘’“”—–•·¿£');
	define("C_ENGCHARS",    'aaaaaaceeeeiiiinooooooouuuuyyAAAAAACEEEEIIIINOOOOOOUUUUY\'\'""--** #');

	define("LAST_DATE_UPDATED", "12/12/09");

	// ---- misc ----
	$dsBullCode = "BULL";
	$dsFrifCode = "FRIF";
	$dsFanlightCode = "FANL";
	$dsNewdayCode = "NEWD";
	$dsFramelineCode = "FRAME";
	$dsWMMCode = "WMM";
	$dsDCCode = "DC";
	$dsCNewsCode = "CNEWS";

	$dsBullName = "Bullfrog Films";
	$dsFrifName = "Icarus Films";
	$dsFanlightName = "Fanlight Productions";
	$dsNewdayName = "Newday Films";
	$dsFramelineName = "Frameline";
	$dsWMMName = "Women Make Movies";
	$dsDCName = "Direct Cinema";
	$dsCNewsName = "California Newsreel";

	$dsTrue = "true";
	$dsFalse = "false";
	$dsNone = "(any)";

	$dsTrueStr = "1";
	$dsFalseStr = "0";

	class Advsearch
	{
		var $cwhere = ' WHERE 1';		// for convenenience have them as properties of this class
		var $cdisplaywhere = "";

		var $csortorder = "";

		var $ckeywords = "";	// these aren't part of the osearch structure, but are separate fields in dscook
		var $ilowvid = 0;
		var $ihighvid = 9999;

		var $ilowlength = 0;
		var $ihighlength = 999;

		// distributors -- by default, select all
		var $isfrif = true;
		var $isbullfrog = true;
		var $isnewday = false;
		var $isfanlight = false;
		var $isframeline = false;
		var $iswmm = false;
		var $isdc = false;
		var $iscnews = false;

		var $clowreldate = "1900";
		var $chighreldate = "2099";

		var $ctitle = "";
		var $ctitlematch = "starts";

		var $cawards = "";
		var $ccredits = "";

		var $lvlpschool = false;
		var $lvlk3 = false;
		var $lvl46 = false;
		var $lvl79 = false;
		var $lvl1012 = false;
		var $lvlcollege = false;
		var $lvladult = false;

		var $onvideo = false;
		var $on16mm = false;
		var $ondvd = false;
		var $onstream = false;
		
		var $closecaption = false;
		var $subtitled = false;
		var $studyguide = false;
		var $isclassrm = false;
		
		var	$cfieldlist = "a.distrib,a.title,a.length,a.list_video,a.url,a.oneline,a.filmyear,a.gradelevel,a.realtitle, a.titl_num";

		var $csql = "";
		var $csqlct = "";

		var $iresultcount = 0;

		var $distrib = null;

		var $textsearch = "";
		
		public function hello()
		{
			echo 'Hello world!' . "<br>\n";
		}

		public function initsearchoptions()
		{		
			foreach($this as $prop => $val)
			{
				$data[$prop] = $val;
			}
			return $data;
		}


		public function advsearch($code=false, $page = 0)
		{
			// advanced search
			$CI =& get_instance();
			$CI->load->model('Search_model');
			$data['page_title']			= lang('search');
			$data['gift_cards_enabled']	= $CI->gift_cards_enabled;
			//check to see if we have a search term

				//if the term is in post, save it to the db and give me a reference
				// collect form data
				
			$term		= $CI->input->post('term');
			$code		= $CI->Search_model->record_term($term);
			
			$this->orderby		= $CI->input->post('orderby');
			$this->isfrif 		= $CI->input->post('isfrif');
			$this->isbullfrog 	= $CI->input->post('isbullfrog');
			$this->ckeywords 	= $CI->input->post('ckeywords');
			$this->ctitlematch 	= $CI->input->post('ctitlematch');
			$this->ctitle 		= $CI->input->post('ctitle');
			$this->ccredits 	= $CI->input->post('ccredits');
			$this->cawards 		= $CI->input->post('cawards');
			$this->ilowlength 	= $CI->input->post('ilowlength');
			$this->ihighlength 	= $CI->input->post('ihighlength');
			$this->clowreldate 	= $CI->input->post('clowreldate');
			$this->chighreldate	= $CI->input->post('chighreldate');
			$this->lvlpschool 	= $CI->input->post('lvlpschool');
			$this->lvlk3 		= $CI->input->post('lvlk3');
			$this->lvl46 		= $CI->input->post('lvl46');
			$this->lvl79 		= $CI->input->post('lvl79');
			$this->lvl1012 		= $CI->input->post('lvl1012');
			$this->lvlcollege 	= $CI->input->post('lvlcollege');
			$this->lvladult 	= $CI->input->post('lvladult');
			$this->onstream 	= $CI->input->post('onstream');
			$this->ondvd 		= $CI->input->post('ondvd');
			$this->closecaption	= $CI->input->post('closecaption');
			$this->studyguide 	= $CI->input->post('studyguide');
			$this->subtitled 	= $CI->input->post('subtitled');
			$this->isclassrm 	= $CI->input->post('isclassrm');

/*
print_r($_POST);
echo "<br />\n";
print_r($this);
*/
			$data['page_title']	= 'Search';
			$data['gift_cards_enabled'] = $CI->gift_cards_enabled;

			//set up pagination
			$CI->load->library('pagination');
			$config['base_url']		= base_url().'cart/search/'.$code.'/';
			$config['uri_segment']	= 4;
			$config['per_page']		= 20;

			$result					= $CI->Product_model->adv_search_products($this, $config['per_page'], $page);
			$config['total_rows']	= $result['count'];
			$CI->pagination->initialize($config);

			$data['products']		= $result['products'];
			foreach ($data['products'] as &$p)
			{
				$p->images	= (array)json_decode($p->images);
				$p->options	= $CI->Option_model->get_product_options($p->id);
			}
			$data['success'] = true;
			return $data;
		}

		public function advsearch_results()
		{
			$pcSortOrder = '<input type="radio" name="sortorder" value="' . C_SORT_TITLE . '" ' . $lcTitleChecked . ' onClick="return RunQuery(\'search.php?pageno=1\');">Title' .  
				C_NBSP . 
				'<input type="radio" name="sortorder" value="' . C_SORT_RELEASEDATE . '" ' . $lcRelDateChecked . ' onClick="return RunQuery(\'search.php?pageno=1\');">Release year' . 
				C_NBSP . 
				'<input type="radio" name="sortorder" value="' . C_SORT_LENGTH . '" ' . $lcLengthChecked . ' onClick="return RunQuery(\'search.php?pageno=1\');">Length';

			// we have keywords to search on, so include relevance as sort option
			if (!empty($lcKeywords))
				$pcSortOrder .= C_NBSP . '<input type="radio" name="sortorder" value="' . C_SORT_RELEVANCE . '" ' . $lcRelevanceChecked . ' onClick="return RunQuery(\'search.php?pageno=1\');">Score';

			
		}
	}
	
?>