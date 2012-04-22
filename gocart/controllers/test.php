<?php
class Test extends CI_Controller {

	
	public function __construct()
    {
		parent::__construct();
    
	}
	
	/* trap any missing functionality */
	public function _remap($method, $params = array())
	{
		if (method_exists($this, $method))
		{
			return call_user_func_array(array($this, $method), $params);
		}
		show_404();
	}
	 
	 
	public function index()
	{
		echo 'Test stuff page<br />';
	
		echo 'BASE_URL: ' . base_url() . "<br>\n";
		echo 'SITE_URL: ' . site_url() . "<br>\n";
		echo 'BASEPATH: ' . BASEPATH . "<br />\n";
		echo 'APPPATH: ' . APPPATH . "<br />\n";
		echo 'FCPATH: ' . FCPATH . "<br />\n";
		
		// var_dump($this->cache->cache_info());
/*		
		echo '<form><select>' . C_COUNTRYLIST . '</select><br />
		<select>' . C_STATELIST . '</select></form>';
		
		$cachedinfo = $this->cache->get('mailtypeid', 'fds');	
		$addrtypeid = $cachedinfo['mailtypeid'];
		echo '7fb8412ebcbce1b674f3a7fae03ef462' . "<br />\n";;
		echo md5('MAILtypeid') . "<br />\n";
		echo md5('mailtypeid') . "<br />\n";
		
		print_r($cachedinfo);
		// echo 'addrtypeid: ' . $addrtypeid . "<br />\n";
*/
	}
	
	public function writeExcel()
	{
		$this->load->helper('phpexcel');

	    $objPHPExcel = new PHPExcel();
	    $objPHPExcel->getProperties()->setTitle('title')
	          ->setDescription('description');

		// Assign cell values
		$objPHPExcel->setActiveSheetIndex(0);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', 'cell value here 111');

		$objPHPExcel->createSheet();
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet()->setCellValue('A1', 'cell value here 222');
		// Save it as an excel 2003 file
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
		$objWriter->save(FCPATH . 'download/nameoffile.xls');

	}
	
	public function readExcel()
	{
		
	}
	
	
	public function loadview()
	{
		$this->load->view(APPPATH . 'themes/ds2/views/testview');
		
	}
	
	public function kaltura_test()
	{
		$custid = '11111';
		//$this->load->library('ds_kaltura');
		//$dsk = new DS_Kaltura;
		//$newplaylist_id = $dsk->addplaylist($custid, '0_ptnoetb7');
		$data = array();
		//$data['playlist_id'] = $newplaylist_id;
		$data['content_id'] = '0_ptnoetb7';
		$this->load->view('player', $data);
	}
	
}
?>