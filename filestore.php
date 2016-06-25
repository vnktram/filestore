<?php
require_once('rest_init.php');
	class API extends REST {
	
		public $data = "";
		private $db = NULL;
		const FOLDER = 'images/';
		public function __construct(){
			parent::__construct();				// Init parent contructor
		}
		
	
		public function processApi(){
			
			$func = strtolower(trim(str_replace("/","",$_REQUEST['request'])));
			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else
				$this->response('',404);
		}
		
		public function compress_image($source_url, $destination_url, $quality) {

			$info = getimagesize($source_url);

	    		if ($info['mime'] == 'image/jpeg')
	        			$image = imagecreatefromjpeg($source_url);

	    		elseif ($info['mime'] == 'image/gif')
	        			$image = imagecreatefromgif($source_url);

	   		elseif ($info['mime'] == 'image/png')
	        			$image = imagecreatefrompng($source_url);

	    		imagejpeg($image, $destination_url, $quality);
			return $destination_url;
		}

		public function init_dir () {
			$target_dir = self::FOLDER;
			if(!file_exists($target_dir)){
				mkdir($target_dir);
			}
			return $target_dir;
		}

		public function validate_image ($tmp_file) {
		    $uploadOk = 0;
		    $check = getimagesize($tmp_file["tmp_name"]);
		    if($check !== false) {
		        $uploadOk = 1;
		    }
		    if ($tmp_file["size"] > 50000000) {
			$uploadOk = 0;
		    }
		    return $uploadOk;
		}

		public function fetch_image_from_url($source_url, $dir)
		{
			$ext = pathinfo($source_url,PATHINFO_EXTENSION);
			$target_file = $dir.uniqid().".".$ext;
			file_put_contents($target_file, fopen($source_url,'r'));
			$saved_file = $this->compress_image($target_file, $target_file, 60);
			$result = array('status' => 'Success','source_url' => $source_url, 'url' => $_SERVER['SERVER_NAME']."/".$saved_file, 'compressedsize' => filesize($saved_file));
			return $result;
		}
		
		private function push(){

			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}

			$body = $_FILES;
			$url = $this->_request["URL"];
			// Input validations
			if(!empty($body["CSV"])){
				$dir = $this->init_dir();

				if (basename($_FILES["CSV"]["name"])) {
					$csv = array_map('str_getcsv', file($_FILES["CSV"]["tmp_name"]));
				}
				$response = array();
				foreach ($csv[0] as $key) {
					$result = $this->fetch_image_from_url($key, $dir);

					array_push($response, $result);
				}
				$this->response($this->json($response),200);


			} elseif (!empty($body["IMAGE"])) {

				$dir = $this->init_dir();
				$target_file = $dir .uniqid()."_".basename($_FILES["IMAGE"]["name"]);
				
				$tmp_file = $_FILES["IMAGE"];

				$is_upload_ok = $this->validate_image($tmp_file);

				if ($is_upload_ok) {
					$saved_file = $this->compress_image($tmp_file["tmp_name"], $target_file, 60);
					$result = array('status' => 'Success', 'url' => $_SERVER['SERVER_NAME']."/".$saved_file, 'compressedsize' => filesize($saved_file));
					$this->response($this->json($result),200);
				}
			} elseif (!empty($url)) {
				$dir = $this->init_dir();
				$result = $this->fetch_image_from_url($url, $dir);
				$this->response($this->json($result),200);
			}		
		}
		

		
		/*
		 *	Encode array into JSON
		*/
		private function json($data){
			if(is_array($data)){
				return json_encode($data);
			}
		}
	}
	
	// Initiate Library
	
	$api = new API;
	$api->processApi();

?>
