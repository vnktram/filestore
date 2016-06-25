<?php
require_once('rest_init.php');
	class API extends REST {
	
		public $data = "";
		private $db = NULL;
		const FOLDER = 'images/';
		public function __construct(){
			parent::__construct();				// Init parent contructor
		}
		
	
		/*
		 * Public method for access api.
		 * This method dynmically call the method based on the query string
		 *
		 */
		public function processApi(){
			
			$func = strtolower(trim(str_replace("/","",$_REQUEST['request'])));
			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else
				$this->response('',404);				// If the method not exist with in this class, response would be "Page not found".
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
		        //echo "File is an image - " . $check["mime"] . ".";
		        $uploadOk = 1;
		    } else {
		        $error = "File is not an image.";
		        $uploadOk = 0;
		    }
			if ($tmp_file["size"] > 50000000) {
				$error = "Sorry, your file is too large.";
				$uploadOk = 0;
			}
			return $uploadOk;
		}

		public function fetch_image_from_url($source_url, $dir)
		{
			$ext = pathinfo($source_url,PATHINFO_EXTENSION);
			$target_file = $dir.uniqid().".".$ext;
			file_put_contents($target_file, fopen($source_url,'r'));//$this->compress_image($key, $target_file, 60);
			$saved_file = $this->compress_image($target_file, $target_file, 60);
			$result = array('status' => 'Success','source_url' => $source_url, 'url' => $_SERVER['SERVER_NAME']."/".$saved_file, 'compressedsize' => filesize($saved_file));
			return $result;
		}
		
		private function push(){
			// Cross validation if the request method is POST else it will return "Not Acceptable" status
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}
			//echo "<div>comes here</div>";
			$body = $_FILES;
			$url = $this->_request["url"];
			// Input validations
			if(!empty($body["csv"])){
				$dir = $this->init_dir();

				if (basename($_FILES["csv"]["name"])) {
					$csv = array_map('str_getcsv', file($_FILES["csv"]["tmp_name"]));
				}
				$response = array();
				foreach ($csv[0] as $key) {
					$result = $this->fetch_image_from_url($key, $dir);
					//$sub_json = $this->json($result);
					array_push($response, $result);
				}
				$this->response($this->json($response),200);
				//print_r($response);
				// $filename = $target_dir.uniqid().".jpg";
				// $file = file_put_contents($filename, fopen("http://www.naturewallpaper.eu/desktopwallpapers/tree/1360x768/bark-with-moss-1360x768.jpg", 'r'));
				// echo "<img src='".$filename."'></img>";
					//$this->response('comes here', 200);
					//$this->response('', 204);	// If no records "No Content" status
			} elseif (!empty($body["image"])) {
				echo "img";
				$dir = $this->init_dir();
				$target_file = $dir .uniqid()."_".basename($_FILES["image"]["name"]);
				
				$tmp_file = $_FILES["image"];

				$is_upload_ok = $this->validate_image($tmp_file);

				if ($is_upload_ok) {
					$saved_file = $this->compress_image($tmp_file["tmp_name"], $target_file, 60);
					$result = array('status' => 'Success', 'url' => $_SERVER['SERVER_NAME']."/".$saved_file, 'compressedsize' => filesize($saved_file));
					$this->response($this->json($result),200);
				}
			} elseif (!empty($url)) {
				print_r($url);
				echo "url";
				$dir = $this->init_dir();
				$result = $this->fetch_image_from_url($url, $dir);
				$this->response($this->json($result),200);
			}
			
			// If invalid inputs "Bad Request" status message and reason
			//$error = array('status' => "Failed", "msg" => "Invalid Email address or Password");
			//$this->response($this->json($error), 400);
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
	
	// Initiiate Library
	
	$api = new API;
	$api->processApi();

?>