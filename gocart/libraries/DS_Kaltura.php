<?php
// ===================================================================================================
//                           _  __     _ _
//                          | |/ /__ _| | |_ _  _ _ _ __ _
//                          | ' </ _` | |  _| || | '_/ _` |
//                          |_|\_\__,_|_|\__|\_,_|_| \__,_|
//
// This file is part of the Kaltura Collaborative Media Suite which allows users
// to do with audio, video, and animation what Wiki platfroms allow them to do with
// text.
//
// Copyright (C) 2006-2011  Kaltura Inc.
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// @ignore
// ===================================================================================================

require_once (APPPATH . 'third_party/Kaltura/KalturaClient.php');

/*
class KalturaConfiguration
{
	const PARTNER_ID = 817202;
	const SECRET = "1234";
	const ADMIN_SECRET = "abcd";
	const SERVICE_URL = "http://www.kaltura.com";
	const UPLOAD_FILE = "DemoVideo.flv";	
}
*/

class DS_Kaltura 
{
	var $CI = null;
	var $uploadfile = '';
	
	public function __construct()
	{
		$this->CI =& get_instance();
		
		$this->uploadfile = 'uploads/DemoVideo.flv';
	}
	
	public function kaltura_hello()
	{
		echo 'Hello world from the DS_Kaltura class!' . "<br>\n";
	}
	
	public function tester()
	{
		echo $this->CI->config->item('partner_id') . "<br />\n";
		if( $this->CI->config->item('partner_id') == '' )
		{
			die("Please fill the partner credentials in Kaltura config file");
		}
		//$this->listAction();
		//$this->multiRequest();
		//$this->add();
		$this->addplaylist('testplayer');
		echo "\nSample code finished successfully.";
	}
	
	private function getKalturaClient($partnerId, $adminSecret, $isAdmin)
	{
		$kConfig = new KalturaConfiguration($partnerId);
		$kConfig->serviceUrl = $this->CI->config->item('service_url');
		$client = new KalturaClient($kConfig);
		
		$userId = $this->CI->config->item('kaltura_user');
		$sessionType = ($isAdmin) ? KalturaSessionType::ADMIN : KalturaSessionType::USER; 
		try
		{
			$ks = $client->generateSession($adminSecret, $userId, $sessionType, $partnerId);
			$client->setKs($ks);
		}
		catch(Exception $ex)
		{
			die("could not start session - check configurations in Kaltura config file");
		}
		
		return $client;
	}

	public function addplaylist($custid = '0', $content = null)
	{
		if(is_null($content))
		{
			show_error('No content passed in');
			return false;
		}
			
		try
		{
			$client = $this->getKalturaClient($this->CI->config->item('partner_id'), $this->CI->config->item('admin_secret'), true);
			$playlist = new KalturaPlaylist();
			$playlist->name = $custid . '_' . time();
			$playlist->description = 'My Test';
			$playlist->userId = 'jd@icarusfilms.com';
			$playlist->type = KalturaEntryType::PLAYLIST;
			$playlist->licenseType = KalturaLicenseType::COPYRIGHTED;
			$playlist->referenceId = 'My reference';
			$playlist->playlistType = KalturaPlaylistType::STATIC_LIST;
			$playlist->playlistContent = $content;
			$results = $client->playlist->add($playlist, true);
			echo 'Added new playlist? ' . $results->id . "<br />\n";

			// $results = $client->playlist->execute($results->id);
			// print_r($results);
			return $results->id;
			
			
		} 	catch (KalturaException $exApi) {
				echo "\nError creating the playlist: " . $exApi->getMessage();
		}
	}
	
	public function addwidget($custid = '0', $playlistid = null)
	{
		if(is_null($playlistid))
		{
			show_error('No playlistid passed in');
			return false;
		}
		try
		{
			$client = $this->getKalturaClient($this->CI->config->item('partner_id'), $this->CI->config->item('admin_secret'), true);
			$srcwidget = null;
			$widget = $client->widget->add($srcwidget);
			echo 'Added new playlist? ' . $results->id . "<br />\n";

			$results = $client->playlist->execute($results->id);
			print_r($results);
			return $results;
			
			
		} 	catch (KalturaException $exApi) {
				echo "\nError creating the playlist: " . $exApi->getMessage();
		}
		
	}
	
	public function listAction()
	{
		try
		{
			$client = $this->getKalturaClient($this->CI->config->item('partner_id'), $this->CI->config->item('admin_secret'), true);
			$results = $client->media->listAction();
			$entry = $results->objects[0];
			echo "\nGot an entry: [{$entry->name}]";
		}
		catch(Exception $ex)
		{
			die($ex->getMessage());
		}
	}

	public function multiRequest()
	{
		try
		{
			$client = $this->getKalturaClient($this->CI->config->item('partner_id'), $this->CI->config->item('admin_secret'), true);
			$client->startMultiRequest();
			$client->baseEntry->count();
			$client->partner->getInfo();
			$client->partner->getUsage(2011);
			$multiRequest = $client->doMultiRequest();
			$partner = $multiRequest[1];
			if(!is_object($partner) || get_class($partner) != 'KalturaPartner')
			{
				throw new Exception("UNEXPECTED_RESULT");
			}
			echo "\nGot Admin User email: [{$partner->adminEmail}]";
		}
		catch(Exception $ex)
		{
			die($ex->getMessage()); 
		}
	}	
	
	public function addmedia()
	{
		try 
		{
			echo "\nUploading test video...";
			$client = $this->getKalturaClient($this->CI->config->item('partner_id'), $this->CI->config->item('admin_secret'), false);
			$filePath = $this->uploadfile;
			
			$token = $client->baseEntry->upload($filePath);
			$entry = new KalturaMediaEntry();
			$entry->name = "my upload entry";
			$entry->mediaType = KalturaMediaType::VIDEO;
			$newEntry = $client->media->addFromUploadedFile($entry, $token);
			echo "\nUploaded a new Video entry " . $newEntry->id;
/*			
			$client->media->delete($newEntry->id);
			try {
				$entry = null;
				$entry = $client->media->get($newEntry->id);
			} catch (KalturaException $exApi) {
				if ($entry == null) {
					echo "\nDeleted the entry (" . $newEntry->id .") successfully!";
				}
			}
*/
		} catch (KalturaException $ex) {
			die($ex->getMessage());
		}	
	}
}
