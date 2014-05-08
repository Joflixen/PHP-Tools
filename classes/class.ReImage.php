<?php
/* ######################## *
 *    Image Manipulation    *
 * Written by:  James Mouat *
 *   Modified: 22/11/11     *
 ***************************/
/* Used to translate Images on-the-fly to multiple formats


/*****************************************************/ 

class ReImage{
	private $imgpath = "";
	private $imgdest = "";
	private $imginfo = array();
	private $src_image = false;
	private $out_image = false;
	private $settings = array( 
		'width' => 0, 
		'height' => 0, 
		'quality' => 100,
		'scalefill' => 0,
		'perform' => ""
	);

	# @parram (string)	- Path to the original image file
	# @parram (array)	- Array of options provided by settings file
	public function __construct($image_path, $dest_path){
		$this->imgpath = $image_path;
		$this->imgdest = $dest_path;
		$this->imginfo = getimagesize($image_path);
		$this->imginfo['type'] = substr($this->imginfo['mime'], strpos($this->imginfo['mime'], '/')+1 );
		$this->__load();
	}

	private function __load(){
		switch($this->imginfo['type']){
			case "jpeg":
				$this->src_img = imagecreatefromjpeg($this->imgpath);
				break;
				
			case "png":
				$this->src_img = imagecreatefrompng($this->imgpath);
				break;
		}
	}
	
	## @parram (string)	- Image switch requested
	## @parram (array)	- Contents of the the Group code settings stored in ini.image.php
	public function create($switch, $transform_settings){
		# handle Defaults
		$this->settings = array_merge($this->settings, $transform_settings);
		if ($this->settings['width']<1)	{ $this->settings['width']	= $this->imginfo[0]; }
		if ($this->settings['height']<1){ $this->settings['height']	= $this->imginfo[1]; }
		
		$this->out_image = imagecreatetruecolor($this->settings['width'], $this->settings['height']);
		
		## Perform the Functions Requested
		if ($this->settings['width']>1 || $this->settings['height']>1){
			## Create New Image for Output
			self::__scale();
		} else {
			## No Scaling to perform - Copy Source into Target!
			$this->out_image = $this->src_img;
		}
		
		foreach(explode(',', $this->settings['perform']) as $action){
			switch ($action){
				case "greyscale":
					self::__greyscale();
					break;
				
			}
		}

		## OUTPUT IMAGE
		switch($this->imginfo['type']){
			case "jpeg":
				imagejpeg($this->out_image, $this->imgdest, $this->settings['quality']);
				break;
				
			case "png":
				#note: PNG compression quality is 0=None to 9=best
				$quality = floor($this->settings['quality']*0.09);
				imagepng($this->out_image, $this->imgdest, $quality);
				break;
		}
	}
	
	
	## Resize Image
	private function __scale(){
		if ($this->settings['width']!=$this->imginfo[0] || $this->settings['height']!=$this->imginfo[1]){
			$src_x = 0; $src_w = $this->imginfo[0];
			$src_y = 0;	$src_h = $this->imginfo[1];
			## The Default is to Scale-in-aspect, the following is usually done.
			if (!intval($this->settings['scalefill'])){
				if ($this->settings['width'] > $this->settings['height']){
					## Target Image is Wider than it is Taller
					$aspect = $this->settings['height']/$this->settings['width'];
					$src_h = $this->imginfo[1]*$aspect;
					$src_y = ($this->imginfo[1]-$src_h)/2;
				} else {
					## Target Image is Taller than it is Wider
					$aspect = $this->settings['width']/$this->settings['height'];
					$src_w = $this->imginfo[0]*$aspect;
					$src_x = ($this->imginfo[0]-$src_w)/2;
				}
			}
			# Perform Image Scale
			imagecopyresampled($this->out_image, $this->src_img, 0, 0, $src_x, $src_y, 
				$this->settings['width'], $this->settings['height'], $src_w, $src_h 
			);
		}
	}
	

	private function __greyscale($img = null) {
		if ($img===null) $img = &$this->out_image;
		$palette = array();
		for ($c=0; $c<256; $c++){
			$palette[$c] = imagecolorallocate($img, $c, $c, $c);
		}
		
		for ($x=0; $x < imagesx($img); $x++){
			for ($y=0; $y < imagesy($img); $y++){
				$rgb = imagecolorat($img, $x, $y);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				$c = self::__yiq($r,$g,$b);
				imagesetpixel( $img, $x, $y, $palette[$c] );
			}
		}
	}


	private function __yiq($r, $g, $b){
		return (0.199*$r + 0.587*$g + 0.114*$b);
	}
	
}