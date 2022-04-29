<?php 

/**
 *
 * @author Andrei Coelho
 * @version 0.2
 *
 */


                                                                         
/*-------------------------------------------------------------------------|
|                                EditImage                                 |
|--------------------------------------------------------------------------|
|                                                                          |
|    This is the main class. Use this class for create amazing Images!     |
|                                                                          |
|--------------------------------------------------------------------------*/



namespace libs\app\images;

use libs\app\images\ImageBuilderException as ImageBuilderException;
use libs\app\images\Build as Build;
use libs\app\images\Image as Image;

class EditImage {


	/**
	* The list os Image instances 
	*
	* @var array
	*
	*/
	private $images = [];

	/**
	* Is used to create other new Images
	*
	* @var string
	*
	*/
	private $from;

	/**
	* This is a control for alias
	*
	* @var boolean
	*
	*/
	private $mod = false;



	# constructor
	private function __construct(string $from, $alias = 0)
	{
		if (!extension_loaded('gd'))
			throw new ImageBuilderException(0, $from);

		$this -> from = $from;
		$this -> images[$alias] = self::create_image($from);
	}


	/**
	* Create a new instance of BuildImage
	*
	* @param  string  $from
	* @param  array   $alias
	* @return BuildImage
	*/
	public static function from(string $from, string $alias = null) : EditImage
	{
		return $alias ? new EditImage($from, $alias) : new EditImage($from);
	}


	/**
	* Create more copies 
	*
	* @param  mixed (array or integer)  $argument
	* @return this   object
	*/
	public function copy($value)
	{	
		$this -> mod = true;

		# if argument is integer, create new copies with number sent
		if(is_int($value) && $value > 0)
		{
			for ($i=0; $i < $value; $i++) 
				$this -> images[] = self::create_image($this->from);
		} 
		# if the argument is array, create new copies using aliased value
		else if(is_array($value))
		{
			foreach ($value as $alias)
				if(!is_bool($alias))
				$this -> images[$alias] = self::create_image($this->from);
		}

		return $this;
	}


	/**
	* Change the path where the images will be saved
	*
	* @param  string   $size
	* @return this     object
	*/
	public function path(string $path)
	{
		foreach ($this->images as $img){

			if(!($info = self::read_path($path))) throw new ImageBuilderException(6);
			
			$img -> change_info($info, !$this -> mod);

		}

		return $this;
	}

	private function path_image(array $values)
	{	
		foreach ($values as $alias => $value) {
			
			if(!($info = self::read_path($path))) throw new ImageBuilderException(6);
			
			if(isset($this->images[$alias])) 
				$this->images[$alias] -> change_info($info, !$this -> mod);

		}	
	}

	public function save()
	{
		foreach ($this -> images as $alias => $image)
		{
			Build::image(
				$image, 
				$alias
			);
		}
		
	}



	/*                       *
	*------------------------*
	*    ACTIONS   METHODS   *
	*------------------------*
	*                        */

	/**
	* Set new size of all Images 
	*
	* @param  string   $size
	* @param  mixed    $alias
	* @return this     object
	*/
	public function resize(string $size, $alias = false)
	{	
		if(!self::is_size_values($size)) throw new ImageBuilderException(3, $size);

		if($alias){
			$this->images[$alias] -> resize($size);
			return $this;
		}
		foreach ($this->images as $image)
			$image -> resize($size);
		
		return $this;
	}


	private function resize_image(array $values)
	{	
		foreach ($values as $alias => $value) {
			
			if(!self::is_size_values($value)) throw new ImageBuilderException(3, $value);
			
			if(isset($this->images[$alias])) 
				$this->images[$alias] -> resize($value);
		}	
	}


	/**
	* Set crop of all Images 
	*
	* @param  string     $values
	* @param  mixed      $alias
	* @return this       object
	*/
	public function crop(string $values, $alias = false)
	{	
		if(!self::is_crop_values($values)) throw new ImageBuilderException(3, $values);

		if($alias){
			$this->images[$alias] -> crop($values);
			return $this;
		}
		foreach ($this->images as $image)
			$image -> crop($values);
		
		return $this;
	}


	private function crop_image(array $values)
	{	
		foreach ($values as $alias => $value) {

			if(!self::is_crop_values($values)) throw new ImageBuilderException(3, $values);
			
			if(isset($this->images[$alias])) 
				$this->images[$alias] -> crop($value);
		}
	}


	/**
	* Flip all Images 
	*
	* @param  string   $size
	* @param  mixed    $alias
	* @return this     object
	*/
	public function flip(string $flip, $alias = false)
	{	
		if($alias){
			$this->images[$alias] -> flip($flip);
			return $this;
		}
		foreach ($this->images as $image)
			$image -> flip($flip);
		
		return $this;
	}


	private function flip_image(array $values)
	{	
		foreach ($values as $alias => $value) {

			if(isset($this->images[$alias])) 
				$this->images[$alias] -> flip($value);
		}
	}


	/**
	* Use this method for change especifics Images
	*
	* @param  string   $method
	* @param  array    $vars
	* @return this     object
	*/
	public function use(string $method, array $vars)
	{
		$call = strtolower($method."_image");

		if(!method_exists($this, $call)) throw new Exception($method, 6);

		$this -> $call($vars);

		return $this;
	}




	/*                       *
	*------------------------*
	*     FILTER  METHODS    *
	*------------------------*
	*                        */

	/**
	* Reverses all colors of the image 
	*
	* @param  mixed    $alias
	* @return this     object
	*/
	public function negate($alias = false)
	{	
		if($alias){
			$this->images[$alias] -> negate();
			return $this;
		}
		foreach ($this->images as $img)
			$img -> negate();

		return $this;
	}


	private function negate_image(array $values)
	{	
		foreach ($values as $alias => $value) {

			if(isset($this->images[$alias])) 
				$this->images[$alias] -> negate();
		}
	}


	/**
	* Blurs the image using the Gaussian method
	*
	* @param  mixed    $alias
	* @return this     object
	*/
	public function gaussian(int $level, $alias = false)
	{	
		if($alias){
			$this->images[$alias] -> gaussian();
			return $this;
		}
		foreach ($this->images as $img)
			$img -> gaussian($level);

		return $this;
	}


	private function gaussian_image(array $values)
	{	
		foreach ($values as $alias => $value) {
			
			if(isset($this->images[$alias])) 
				$this->images[$alias] -> gaussian();
		}
	}


	/**
	* Converts the image into grayscale
	*
	* @param  mixed    $alias
	* @return this     object
	*/
	public function grayscale($alias = false)
	{	
		if($alias){
			$this->images[$alias] -> grayscale();
			return $this;
		}
		foreach ($this->images as $img)
			$img -> grayscale();

		return $this;
	}


	private function grayscale_image(array $values)
	{	
		foreach ($values as $alias => $value) {
			
			if(isset($this->images[$alias])) 
				$this->images[$alias] -> grayscale();
		}
	}


	/**
	* Changes the brightness of the image
	*
	* @param  int      $level
	* @param  mixed    $alias
	* @return this     object
	*/
	public function brightness(int $level, $alias = false)
	{	
		if($alias){
			$this->images[$alias] -> brightness($level);
			return $this;
		}
		foreach ($this->images as $img)
			$img -> brightness($level);

		return $this;
	}


	private function brightness_image(array $values)
	{	
		foreach ($values as $alias => $value) {
			
			if(isset($this->images[$alias])) 
				$this->images[$alias] -> brightness($value);
		}
	}


	/**
	* Makes the image smoother
	*
	* @param  int      $level
	* @param  mixed    $alias
	* @return this     object
	*/
	public function smooth(int $level, $alias = false)
	{	
		if($alias){
			$this->images[$alias] -> smooth($level);
			return $this;
		}
		foreach ($this->images as $img)
			$img -> smooth($level);

		return $this;
	}


	private function smooth_image(array $values)
	{	
		foreach ($values as $alias => $value) {
			
			if(isset($this->images[$alias])) 
				$this->images[$alias] -> smooth($value);
		}
	}



	/**
	* Applies pixelation effect to the image
	*
	* @param  int      $level
	* @param  mixed    $suavization
	* @param  mixed    $alias
	* @return this     object
	*/
	public function pixelate(int $level, $suavization = true, $alias = false)
	{	
		if($alias){
			$this->images[$alias] -> pixelate([$level, (int)$suavization]);
			return $this;
		}
		foreach ($this->images as $img)
			$img -> pixelate([$level, (int)$suavization]);

		return $this;
	}


	private function pixelate_image(array $values)
	{	
		foreach ($values as $alias => $value) {
			
			if(isset($this->images[$alias]) && is_array($value)){
				$vals = count($value) == 2 ? $value : [$value[0], true];
				$this->images[$alias] -> pixelate($vals);
			}
				
		}
	}



	/**
	* Changes the contrast of the image
	*
	* @param  int      $level
	* @param  mixed    $alias
	* @return this     object
	*/
	public function contrast(int $level, $alias = false)
	{	
		if($alias){
			$this->images[$alias] -> contrast($level);
			return $this;
		}
		foreach ($this->images as $img)
			$img -> contrast($level);

		return $this;
	}


	private function contrast_image(array $values)
	{	
		foreach ($values as $alias => $value) {
			
			if(isset($this->images[$alias])){
				$this->images[$alias] -> contrast($value);
			}
				
		}
	}


	/**
	* Embosses the image.
	*
	* @param  int      $level
	* @param  mixed    $alias
	* @return this     object
	*/
	public function emboss($alias = false)
	{	
		if($alias){
			$this->images[$alias] -> emboss();
			return $this;
		}
		foreach ($this->images as $img)
			$img -> emboss();

		return $this;
	}


	private function emboss_image(array $values)
	{	
		foreach ($values as $alias => $value) {
			
			if(isset($this->images[$alias])){
				$this->images[$alias] -> contrast($value);
			}
				
		}
	}


	/**
	* Applies scatter effect to the image
	*
	* @param  mixed    $alias
	* @return this     object
	*/
	

	/* 
	// scatter php 7.4
	public function scatter(int $level1, int $level2, $alias = false)
	{	
		if($alias){
			$this->images[$alias] -> scatter([$level1, $level2]);
			return $this;
		}
		foreach ($this->images as $img)
			$img -> scatter([$level1, $level2]);
		return $this;
	}
	private function scatter_image(array $values)
	{	
		foreach ($values as $alias => $value) {
			
			if(isset($this->images[$alias]) && is_array($value) && count($value) === 2)
				$this->images[$alias] -> scatter([(int)$value[0], (int)$value[1]]);
		}
	}
	
	
	*/



	/*                       *
	*------------------------*
	*       AUX METHODS      *
	*------------------------*
	*                        */

	private static function create_image(string $from)
	{
		if (!(@$info = getimagesize($from)))
			throw new ImageBuilderException(1, $from);

		$mime = self::generate_mime($info['mime']);

		return new Image(self::create_resource($from, $mime), self::read_path($from, $mime), [$info[0], $info[1]]);
	}

	private static function create_resource(string $from, string $mime)
	{
		switch ($mime) {
			case 'jpg':
			case 'jpeg':
				return \imagecreatefromjpeg($from);

			case 'png':
				$res = \imagecreatefrompng($from);
				imagefill($res,0,0,imagecolorallocate($res, 255, 255, 255)); 
				\imagealphablending($res, false);
				\imagesavealpha($res, true);
				return $res;

			default:
				return false;
		}
	}

	private static function read_path(string $path, $mi = false)
	{
		preg_match('/[^\s]+[^\s\/]+\.[\w]{3,4}/', $path, $result);

		if(count($result) == 0)return false;

		$vars = explode('/', $result[0]);
		$full = array_pop($vars);
		$name = substr($full, 0, -4);
		$mime = $mi === false ? @end(explode('.', $full)) : $mi;
		$path = count($vars) > 0 ? implode('/', $vars).'/' : "";
		
		return [$path, $name, $mime];

	}

	private static function is_size_values(string $size)
	{
		return preg_match('/((^\d{2,}x\d{2,}$)|(^\*x\d{2,}$)|(^\d{2,}x\*$)|(^\_x\d{2,}$)|(^\d{2,}x\_$))/i', trim($size));
	}

	private static function is_crop_values(string $info)
	{
		return preg_match('/(center|left|right|top|bottom|\d{2,})\s(center|left|right|top|bottom|\d{2,})\s((\d{2,}x\d{2,})|(\*x\d{2,})|(\d{2,}x\*)|(\_x\d{2,})|(\d{2,}x\_))/', trim($info));
	}

	private static function is_url(string $from)
	{
		return preg_match('/(https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|www\.[a-zA-Z0-9][a-zA-Z0-9-]+[a-zA-Z0-9]\.[^\s]{2,}|https?:\/\/(?:www\.|(?!www))[a-zA-Z0-9]+\.[^\s]{2,}|www\.[a-zA-Z0-9]+\.[^\s]{2,})/', trim($from));
	}

	private static function generate_mime($type)
	{
		switch($type){
			case "image/jpeg":
				return "jpeg";
			case "image/jpg":
				return "jpg";
			case "image/png":
				return "png";
			default: return "jpg";
		}
	}

}