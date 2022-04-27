<?php 

use libs\app\images\EditImage as EditImage;

EditImage::from('../files_to_upload/power_girl.png')
  ->path('../public/img/testing3.png')
  ->resize('150x*')
  ->save();