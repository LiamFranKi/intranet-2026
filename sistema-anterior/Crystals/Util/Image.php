<?php
class Image{
	private $filename;
	private $width = 100, $height = 100;
	private $b_red,$b_green,$b_blue;
	private $f_red,$f_green,$f_blue;
	private $image;
	private $font = 5;
	
	// constructs the object
	// may be: String filename -> creates a image from file
	// 		   Array ( width , height) -> creates a image from a dimensions
	
	function __construct($data){
		if(is_string($data)){
			$this->filename = $data;
		}elseif(is_array($data)){
			$this->width = $data[0];
			$this->height = $data[1];
		}
		// creates the image
		$this->createImage();
	}
	
	function createImage(){
		if(!empty($this->filename)){
			if(file_exists($this->filename)){
				$iData = pathinfo($this->filename);
				switch(strtolower($iData['extension'])){
					case 'jpg':
					case 'jpeg':
						$this->image = imagecreatefromjpeg($this->filename);
					break;
					
					case 'gif':
						$this->image = imagecreatefromgif($this->filename);
					break;
					
					case 'png':
						$this->image = imagecreatefrompng($this->filename);
					break;
					
					case 'bmp':
						$this->image = imagecreatefromwbmp($this->filename);
					break;
				}
				$iData = getimagesize($this->filename);
				$this->width = $iData[0];
				$this->height = $iData[1];
			}else{
				echo 'El archivo '.$this->filename.' no existe';
				return false;
			}
		}else{
			$this->image = imagecreate($this->width,$this->height);
		}
	}
	
	// - - - Resize Methods - - - //

	function resize($dimension, $prop = false){
		$iData = $this->getSize();
		if(is_array($dimension)){
			$width = $dimension[0];
			$height = $dimension[1];
		}elseif(is_string($dimension)){
			switch($dimension){
				case 'small':
					$width = 100;
					$height = 100;
				break;
				
				case 'medium':
					$width = ceil($iData[0]/2);
					$height = ceil($iData[1]/2);
				break;
				
				case 'normal':
				default:
					$width = $iData[0];
					$height = $iData[1];
				break;
			}
		}elseif(is_int($dimension)){
			$width = $dimension;
			$height = $dimension;
		}else{
			$width = $iData[0];
			$height = $iData[1];
		}
		
		if($prop){
			$ratio = ($iData[0] / $width);
			$height = ($iData[1] / $ratio);
		}
		
		//$image = new Image(Array($width,$height));
		$image = imagecreatetruecolor($width, $height);
		imagecopyresampled($image,$this->image,0,0,0,0,$width,$height,$this->width,$this->height);
		$this->image = $image;
	}
	
	function getSize(){
		return getimagesize($this->filename);
	}
	
	// - - - Point Methods - - - //
	
	function getWidth(){
		return $this->width;
	}
	
	function getHeight(){
		return $this->height;
	}
	// - - - Draw Methods - - - //
	
	function setFont($font){
		$this->font = $font;
	}
	
	function drawPoint($x,$y,$col){
		$this->drawLine($x,$y,$x,$y,$col);
	}
	
	function drawString($string,$x,$y){
		imagestring($this->image,$this->font,$x,$y,$string,1);
	}
	
	function drawLine($x1,$y1,$x2,$y2,$col){
		imageline($this->image,$x1,$y1,$x2,$y2,$col);
	}
	
	// - - - Color Methods - - - //
	
	function setBackground($red,$green,$blue){
		imagecolorallocatealpha($this->image, $red, $green, $blue, 1);
	}
	
	function setColor($red,$green,$blue){
		imagecolorallocate($this->image, $red, $green, $blue);
	}
	
	// - - - Render the final image - - - //
	
	function getImage(){
		return $this->image;
	}
	
	function render($filename = null){
		
		if(isset($filename)){
			$iData = pathinfo($filename);
			switch(strtolower($iData['extension'])){
				case 'jpg':
				case 'jpeg':
					imagejpeg($this->image,$filename);
				break;
				
				case 'gif':
					imagegif($this->image,$filename);
				break;
				
				case 'png':
					imagepng($this->image,$filename);
				break;
				
				case 'bmp':
					imagewbmp($this->image,$filename);
				break;
				
			}
		}else{
			
			header('Content-Type: image/gif');
			imagegif($this->image);
		}
	}
}
?>
