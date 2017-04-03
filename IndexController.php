<?php


class IndexController extends ControllerBase
{
    public function indexAction()
    {

    }

    public function uploadFileToSystemAction()
    {
        
        if ($this->request->isPost() == true) {
            $post_img_name = $this->request->getJsonRawBody();
            $img_url      = $post_img_name->img_name;
            ini_set("allow_url_fopen", true);
            
            $data = fopen('clip_image002.jpg', "r");
            echo 131232134; 
            return  parent::jsonContent($data);
        }else{
            return  parent::jsonContent('test');
        }
        
    }
    
    
    public function uploadImgForUserProfileAction()
    {
        // Check if the user has uploaded files
        if ($this->request->hasFiles() == true) {

            $baseLocation = 'img/';

            $id = 0;
            if ($this->session->has("id")) {
                $id = $this->session->get("id");
            }

            foreach ($this->request->getUploadedFiles() as $file) {
                //Move the file into the application
                if ($_GET['type'] == 'photo') {
                    $baseLocation = __DIR__.'/../../img/photos/';
                    $file_name = 'photo_' . $id . '.' . $file->getExtension();
                }
                
                $file->moveTo($baseLocation . $file_name);
            }
        }
    }

    public function drawFbImgAction(){
        if ($this->request->isPost() == true) {
            $post_img_name = $this->request->getJsonRawBody();
            $img_name      = addslashes($this->filter->sanitize($post_img_name->img_name, ['striptags', 'trim']));
            $fb_draw      = addslashes($this->filter->sanitize($post_img_name->fb_draw, ['striptags', 'trim']));
            //////////  картинка для фб, которую юзер выбрал (бублик с вариантами ответа)
            if($fb_draw == 'draw_pie'){
                $response_type = $post_img_name->f_response_type;
                
                $path_to_font_georgia = __DIR__.'/../../img/2census_img/georgia.ttf';
                //////////// вопрос

                //////////  варианты ответа
                if($response_type == 'yes-no') {
                   $responses = array('Yes', 'No');
                } elseif($response_type == 'agree-disagree'){
                   $responses = array('Agree', 'Disagree');
                } elseif($response_type == 'create-custom'){
                   $responses     = $post_img_name->f_responses;
                }
                
                $count_responses = count($responses);
                $font_size      = 15;
                
                if( $count_responses >= 2 && $count_responses <= 5){
                    $img_count_name = '2.png';
                    $x = 45;
                    $height_text = (335 - ($count_responses*$x))/2;
                    $top_radiobutton = 3;
                }elseif( $count_responses >= 6 && $count_responses <= 10){
                    $img_count_name = '6.png';
                    $x = 23;
                    $height_text = (335 - ($count_responses*$x))/2;
                    $top_radiobutton = -3;
                }
                
                $max_width_text_array = array();
                foreach ($responses as $z=>$w){
                    $box = imagettfbbox($font_size, 0, $path_to_font_georgia, $w);
                    $width_text = $box[2]-$box[0];
                    $max_width_text_array[] = $width_text;
                    
                }
                $max_width_text = max($max_width_text_array);
                $center_width_text = (300/2 - $max_width_text/2);
                if($center_width_text < 0){
                    $center_width_text = 0;
                }
                //////////
                
                $draw = new ImagickDraw();
                $draw->setFontSize($font_size);
                $draw->setFont( $path_to_font_georgia );
                $i = 0;
                if(file_exists(__DIR__.'/../../img/2census_img/'.$img_count_name)){
                    $img              = new Imagick (__DIR__.'/../../img/2census_img/'.$img_count_name);
                    $img_radiobutton  = new Imagick (__DIR__.'/../../img/2census_img/radiobutton.jpg');
                } else {
                    $status = 'no pictures';
                }
                $width         = $img->getImageWidth();
                $height        = $img->getImageHeight();
                $canvas = new Imagick();
                $canvas->newImage(800, 300, "#fcfcfc");
                
                $top_pict = 50;
                $top_response = 15;
                if($number_stroke){
                    $top_pict = 60;
                    $top_response = 20;
                    $top_radiobutton += 3;    
                }
                
                $canvas->compositeImage($img, Imagick :: COMPOSITE_DEFAULT , 140, $top_pict);
                
                foreach($responses as $k=>$v){
                    $array_str = str_split($v);
                    $bbox = imagettfbbox($font_size, 0, $path_to_font_georgia, $v);
                    $str_output = '';
                    if($bbox[2] >= 300){
                        $str = '';
                        foreach($array_str as $key=>$value){
                             $str .= $value;
                             $px_str = imagettfbbox($font_size, 0, $path_to_font_georgia, $str);
                             if($px_str[2] <= 300){
                                $str_output .= $value;
                             } else {
                                $str_output .= '...';
                                break;
                             }
                        }
                    } else {
                        $str_output = $v;
                    }
                    $draw->annotation(420 + $center_width_text, $height_text  + $i + $top_response, $str_output); 
                    $canvas->compositeImage($img_radiobutton, Imagick :: COMPOSITE_DEFAULT , 400 + $center_width_text, $height_text + $i + $top_radiobutton);
                    $i += $x;
                }
                $draw->setFontSize($ask_font_size);
                $draw->setTextInterlineSpacing(5);


                
                $canvas->drawImage($draw);
                $canvas->writeImage(__DIR__.'/../../img/post_pictures/fb_draw_story_img/'.$img_name);
                $status = 'yes';
             
            }
            //////////  картинка для фб, которую юзер выбрал (с картинок в истории)
            if($fb_draw == 'pictures_from_history') {
                $structure = str_replace($_SERVER['HTTP_ORIGIN'],__DIR__.'/../..',$img_name);
                $img_name = pathinfo($structure)['basename'];
                //$structure = __DIR__.'/../../../img/post_pictures/editor_post_photo/';
                if(file_exists($structure)){
                $img           = new Imagick ($structure);
                $height_contnt = intval(0);
                $width_contnt  = intval(718);
                $width         = $img->getImageWidth();
                $height        = $img->getImageHeight();
                if($width < 200 && $height < 200){
                    $canvas = new Imagick();
                    $canvas->newImage(200, 200, "white");
                    $canvas_width  = intval((200 - $width)/2);
                    $canvas_height = intval((200 - $height)/2);
                    $canvas->compositeImage($img, Imagick :: COMPOSITE_DEFAULT , $canvas_width, $canvas_height);
                    $canvas->writeImage(__DIR__.'/../../img/post_pictures/fb_draw_story_img/'.$img_name);
                } else {
                    $img->scaleImage($width_contnt, 0);
                    $img->writeImage(__DIR__.'/../../img/post_pictures/fb_draw_story_img/'.$img_name);
                }
                 $status        = 'yes';
                } 
            }
        } else {
            $status = 'no';
        }
        return  parent::jsonContent($status);
    }
    
    public function updateStoryPicturesAction(){
        /////////////////  запуск апдейта картинок под фб на историях
         $data = Posts::updateStoryPictures();
         foreach($data as $k1=>$v1){
           $data_response = Metatag::selectResponseMetatag($v1['id'], $v1['response_type_id']);
           $responses = array();
           if($v1['response_type_id'] == 2){
                foreach($data_response as $k2=>$v2){
                     $responses[] = $v2['decription'];
                }
           } elseif($v1['response_type_id'] == 1){
                foreach($data_response as $k2=>$v2){
                     $responses[] = ucfirst ($v2['decription']);
                }
           }

            $img_name = uniqid().'-'.uniqid().'.png';
          
            $count_responses = count($responses);
            if( $count_responses >= 2 && $count_responses <= 5){
                $img_count_name = '2.png';
                $font_size      = 17;
                $x = 45;
                $height_text = (335 - ($count_responses*$x))/2;
            }
            if( $count_responses >= 6 && $count_responses <= 10){
                $img_count_name = '6.png';
                $font_size      = 14;
                $x = 23;
                $height_text = (335 - ($count_responses*$x))/2;
            }
            
            $max_width_text_array = array();
            foreach ($responses as $z=>$w){
                $box = imagettfbbox($font_size, 0, __DIR__.'/../../img/2census_img/prestige.ttf', $w);
                $width_text = $box[2]-$box[0];
                $max_width_text_array[] = $width_text;
                
            }
            $max_width_text = max($max_width_text_array);
            $center_width_text = (270/2 - $max_width_text/2);
            if($center_width_text < 0){
                $center_width_text = 0;
            }
            
            $draw = new ImagickDraw();
            $draw->setFontSize($font_size);
            $i = 0;
            if(file_exists(__DIR__.'/../../img/2census_img/'.$img_count_name)){
                $img              = new Imagick (__DIR__.'/../../img/2census_img/'.$img_count_name);
                $img_radiobutton  = new Imagick (__DIR__.'/../../img/2census_img/radiobutton.jpg');
            } else {
                $status = 'no pictures';
            }
            $width         = $img->getImageWidth();
            $height        = $img->getImageHeight();
            $canvas = new Imagick();
            $canvas->newImage(800, 300, "#fcfcfc");
            $canvas->compositeImage($img, Imagick :: COMPOSITE_DEFAULT , 140, 35);
            foreach($responses as $k=>$v){
                $array_str = str_split($v);
                $bbox = imagettfbbox($font_size, 0, __DIR__.'/../../img/2census_img/prestige.ttf', $v);
                $str_output = '';
                if($bbox[2] >= 310){
                    $str = '';
                    foreach($array_str as $key=>$value){
                         $str .= $value;
                         $px_str = imagettfbbox($font_size, 0, __DIR__.'/../../img/2census_img/prestige.ttf', $str);
                         if($px_str[2] <= 310){
                            $str_output .= $value;
                         } else {
                            $str_output .= '...';
                            break;
                         }
                    }
                } else {
                    $str_output = $v;
                }
                $draw->annotation(420 + $center_width_text, $height_text  + $i, $str_output); 
                $canvas->compositeImage($img_radiobutton, Imagick :: COMPOSITE_DEFAULT , 405 + $center_width_text, $height_text - 13 + $i);
                $i += $x;
            }
            $canvas->drawImage($draw);
            $canvas->writeImage(__DIR__.'/../../img/post_pictures/fb_draw_story_img/'.$img_name);
            Posts::update_fb_picture_puth($v1['id'], $img_name);
         }
         echo "OK";
    }
    
    public function getCurrentDateAction(){
        $current_date = new DateTime;
        return  parent::jsonContent($current_date);
    }
    
    public function uploadEditorAction()
    {
        if ($this->request->hasFiles() == true) {
            $id = 0;
            if ($this->session->has("id")) {
                $id = $this->session->get("id");
            }
            foreach ($this->request->getUploadedFiles() as $file) {               
                $baseLocation = __DIR__.'/../../img/post_pictures/tmp/';
                $time = time();
                //$file_name = 'photo_' . $time . '.' . $file->getExtension();
                $file_name = str_replace(' ', '_', $id.'_'.uniqid().'_'.$file->getName());
            
                $file->moveTo($baseLocation . $file_name);
            }
            if(!file_exists($baseLocation.$file_name)){
                $result = 'NO';
            } else {
                $result = $file_name;
            }
            
            return  parent::jsonContent($result);
        }
    } 
    
    public function cropImgEditorAction(){
        if ($this->request->isPost() == true) {
            $post_img_name = $this->request->getJsonRawBody();
            $img_name      = str_replace(' ', '_', addslashes($this->filter->sanitize($post_img_name->img_name, ['striptags', 'trim'])));
            $block_width   = intval($post_img_name->block_width);
            $block_height  = intval($post_img_name->block_height);
            $width         = intval($post_img_name->width);
            $height        = intval($post_img_name->height);
            $x             = intval($post_img_name->x);
            $y             = intval($post_img_name->y);
            //////  обработка-кроплинг картинки
            if(file_exists(__DIR__.'/../../img/post_pictures/tmp/'.$img_name)){
                $img           = new Imagick (__DIR__.'/../../img/post_pictures/tmp/'.$img_name);
                $width         = ($img->getImageWidth()/$block_width)*$width;
                $height        = ($img->getImageHeight()/$block_height)*$height;
                $x             = ($img->getImageWidth()/$block_width)*$x;
                $y             = ($img->getImageHeight()/$block_height)*$y;
                $img->cropImage($width,$height,$x,$y);
                
                $structure = __DIR__.'/../../img/post_pictures/editor_post_photo/'.date("Y_m").'/';
                $structure_for_original_pictures = __DIR__.'/../../img/post_pictures/original_pictures/'.date("Y_m").'/';
                if(!mkdir($structure, 0755, true)){
                    $img->writeImage($structure.$img_name);
                    $img->writeImage($structure_for_original_pictures.$img_name);
                } else {
                    $img->writeImage($structure.$img_name);
                    $img->writeImage($structure_for_original_pictures.$img_name);
                }
                
                 $status        = $structure;
            } else {
                $status         = 'not upload';
            }
            
        } else {
            $status        = 'no';
        }
        return  parent::jsonContent($status); 
    }
    
    public function resizeEditorAction(){
        if ($this->request->isPost() == true) {
            $post_img_name = $this->request->getJsonRawBody();
            $img_name      = str_replace(' ', '_', addslashes($this->filter->sanitize($post_img_name->img_name, ['striptags', 'trim'])));
            $structure     = str_replace(' ', '_', addslashes($this->filter->sanitize($post_img_name->structure, ['striptags', 'trim'])));
            //////  обработка-ресайз картинки
            if(file_exists(__DIR__.'/../../img/post_pictures/tmp/'.$img_name)){
                if(file_exists($structure.$img_name)){
                    $path      = $structure;
                } else {
                    $path      = __DIR__.'/../../img/post_pictures/tmp/';
                    copy(__DIR__.'/../../img/post_pictures/tmp/'.$img_name,  __DIR__.'/../../img/post_pictures/original_pictures/'.date("Y_m").'/'.$img_name);
                }  
                $img           = new Imagick ($path.$img_name);
                $height_contnt = intval(0);
                $width_contnt  = intval(718);
                $width         = $img->getImageWidth();
                $height        = $img->getImageHeight();
                if($width > 718){
                    $img->scaleImage($width_contnt, 0);
                }
                if(is_dir($structure)){
                    $img->writeImage($structure.$img_name);
                } else {
                    $structure = __DIR__.'/../../img/post_pictures/editor_post_photo/'.date("Y_m").'/';
                    if(!mkdir($structure, 0755, true)){
                        $img->writeImage($structure.$img_name);
                    } else {
                        $img->writeImage($structure.$img_name);
                    }
                }
                 $dir_name = __DIR__.'/../../';
                 $status        = str_replace($dir_name, '', $structure);
                 unlink(__DIR__.'/../../img/post_pictures/tmp/'.$img_name);
                 
            } else {
                $status         = 'not upload';
            }     
        } else {
                $status        = 'no';
        }
        return  parent::jsonContent($status);
    }
    
    public function deleteImgEditorAction(){
    	if ($this->request->isPost() == true) {
    		$post_img_name = $this->request->getJsonRawBody();
    		$img_name      = str_replace(' ', '_', addslashes($this->filter->sanitize($post_img_name->img_name, ['striptags', 'trim'])));
    		unlink(__DIR__.'/../../'.$img_name);
    		$status = 'ok';
    	} else {
    		$status = 'error';
    	}
    	
    	return parent::jsonContent($status);
    }
    
    public function uploadEditorEmailAction()
    {
        if ($this->request->hasFiles() == true) {
            foreach ($this->request->getUploadedFiles() as $file) {
                $baseLocation = __DIR__.'/../../img/email_img/';
                $time = time();
                //$file_name = 'photo_' . $time . '.' . $file->getExtension();
                $file_name = str_replace(' ', '_', uniqid().'_'.$file->getName());

                $file->moveTo($baseLocation . $file_name);
            }
            if(!file_exists($baseLocation.$file_name)){
                $result = 'NO';
            } else {
                $result = $file_name;
            }

            return  parent::jsonContent($result);
        }
    }
}
