<?php 

/**
 *
 * @author Andrei Coelho
 * @version 0.2
 *
 */

namespace libs\app\images;

class ImageBuilderException extends \Exception {

    public function __construct(int $code, string $msg = null){
        parent::__construct($this -> generateMessage($code, $msg), $code);
    }

    private function generateMessage(int $code, string $msg){
        switch ($code) {
            case 0:
                return "The GD library is not installed or loaded";
            case 1:
                return "This file: '$msg' is not a image or not exists";
            case 2:
                return "Too few arguments to function ImageBuilder::resize(), 
                        $msg passed. Minimum arguments required are 1.";
            case 3:
                return "The passed value '$msg' is not standard for sizes.";
            case 4:
                return "This file: '$msg' is not a customizable image. Use images with PNG or JPG extensions.";
            case 5:
                return "Image resource must be a GD libary resource";
            default:
                return "This file: '$msg' is not a image or not exists";
        }
    }

}