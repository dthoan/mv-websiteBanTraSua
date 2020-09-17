<?php 


class Controller {

    private $layout;

    public function __construct(){
        $this->layout = 'layouts/layout.php';
    }

    public function view($path, array $params = [], $returnHtml = false){
        if(!isset($params['typePage'])){
            $params['typePage'] = 'add';
        }

        $paramsContents = $params;
        //Đầu tiên cần phải render html của content body
        $contents = $this->render($path, $paramsContents);

        //render footer
        $paramsFooter = $params;
        $footer = $this->render('layouts/footer.php', $paramsFooter);

        //render header
        $paramsHeader = $params;
        $header = $this->render('layouts/header.php', $paramsHeader);

        //sau đó set content body vào params để thực hiện render layout
        //Bởi vì bên layout mình đang echo $contents, nên phải bắt buộc key = contents
        $paramsLayout = array_merge($params, [
            'contents' => $contents,
            'footer' => $footer,
            'header' => $header,
        ]);
        
        $html = $this->render($this->layout, $paramsLayout);

        if(!$returnHtml){
            echo $html;
            return true;
        }else {
            return $html;
        }
    }

    public function render($path, $params){
        extract($params);
        $path = __DIR__ . '/../views/' . $path;
        if(!file_exists($path)){
            return '';
        }

        // lấy tất cả đường dẫn được chọn
        ob_start();
        require($path);
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }

    public function redirect($url){
        header("Location: " . $url);
        //header("Location: https://google.com");
        return true;
    }

    public function saveFile($files){
        $nameFile               = $files['name'] ?? '';
        $sizeFile               = $files['size'] ?? 0;
        $fileUploadConfig       = config('file_upload');
        $imageExtensionAllow    = $fileUploadConfig['imageExtensionAllow']; //;là những loại file dc phép upload ok
        $imageFileType          = strtolower(pathinfo($nameFile,PATHINFO_EXTENSION)); //Là đuôi của file //PATHINFO_EXTENSION là 1 từ khóa mặc định của php

        $pathUpload             = 'public/uploads/';
        $dateUpload             = date('YmdHis');
        $nameFileUpload         = md5($nameFile . $dateUpload) . '_' . $dateUpload . '.' . $imageFileType;
        //NameFileUpload phải được đảm bảo là chưa từng tồn tại trong thư mục uploads
        //Vậy nên sẽ sử dụng md5 (encrypt) cái tên của file. thêm vào đó là cộng thêm ngày giờ upload nữa
        //đảm bảo không trùng nổi hiểu rồi hihi
        //Hàm in_array là 1 hàm mặc định của php.
        //mục đích để kiểm tra 1 phần tử có tồn tại trong 1 mảng hay không 
        //Ví dụ mảng extension. nó có 5 giá trị. Chỉ cần cái imageFileType là 1 trong 5 giá trị đấy thì pass

        if(!in_array($imageFileType, $imageExtensionAllow)){
            die("Type file not support!");
        }
        // e về đã nha hết tiết rồi 
        //ok
        if($sizeFile > $fileUploadConfig['maxSize']){
            die('Size File to big!');
        }
        
        //upload
        $pathFileSave = $pathUpload . $nameFileUpload;
        $pathMoveFile = __DIR__ . '/../' . $pathFileSave;
        if (move_uploaded_file($files["tmp_name"], $pathMoveFile)) {
            return $pathFileSave;
        } else {
            die("Save File Error!");
        }

    }

    public function isPostMethod(){
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }

    public function handlePostMethod(){
        if(!$this->isPostMethod()){
            die("Not support method GET");
        }
    }

}