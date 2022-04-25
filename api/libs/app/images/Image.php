<?php 

/**
 *
 * @author Andrei Coelho
 * @version 0.2
 *
 */



/*-------------------------------------------------------------------------|
|                              	  Image                                    |
|--------------------------------------------------------------------------|
|                                                                          |
|       This class is a template of how the file will be generated!        |
|All information to generate the file is contained in objects of this class|
|                                                                          |
|--------------------------------------------------------------------------*/


namespace libs\app\images;	

use libs\app\images\ImageBuilderException as ImageBuilderException;


class Image {

	/**
	* The resource of GD Image used by Build
	*
	* @var resource $resource
	*
	*/
	public $resource;

	public $actions = []; # all action methods are saves here
	public $filters = []; # all filter methods are saves here
	public $modify  = false;
	
	/**
	* The informations of Image
	*
	* @var string $path, $name, $mime
	*
	*/
	public $path, $name, $mime;

	public $sizes = [];

	public function __construct($resource, array $info, array $sizes)
	{
		$this -> resource = $resource;
		$this -> path     = $info[0];
		$this -> name     = $info[1];
		$this -> mime     = $info[2];
		$this -> sizes    = $sizes;
	}

	public function change_info(array $info, bool $mod)
	{	
		$this -> path   = $info[0];
		$this -> name   = $info[1];
		$this -> mime   = $info[2];

		$this -> modify = $mod;
	}

	/*                       *
	*------------------------*
	*    ACTIONS   METHODS   *
	*------------------------*
	*                        */

	public function resize(string $size)
	{	
		$this -> actions['resize'] = $size;
	}

	public function crop(string $values)
	{
		$this -> actions['crop'] = $values;
	}

	public function flip(string $flip)
	{
		$this -> actions['flip'] = $flip;
	}

	/*                       *
	*------------------------*
	*     FILTER  METHODS    *
	*------------------------*
	*                        */

	public function negate(){
		$this -> filters['negate'] = true;
	}

	public function grayscale(){
		$this -> filters['grayscale'] = true;
	}

	public function brightness(int $level){
		$this -> filters['brightness'] = $level;
	}

	public function gaussian(int $level){
		$this -> filters['gaussian'] = $level;
	}

	public function smooth(int $level){
		$this -> filters['smooth'] = $level;
	}

	public function pixelate(array $vars){
		$this -> filters['pixelate'] = $vars;
	}

	public function contrast(int $level){
		$this -> filters['contrast'] = $level;
	}

	public function emboss(){
		$this -> filters['emboss'] = true;
	}

	public function scatter(array $vars){
		$this -> filters['scatter'] = $vars;
	}


}
