<?php

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $content = file_get_contents('php://input');
    session_start();
    if(!empty($content)){
        if(isset($_POST["threeDSMethodData"])){
            $_SESSION["method_url_completion"] = 1;
            $_SESSION["threeDSMethodData"] = $_POST["threeDSMethodData"];
        }else{
            unset($_SESSION["threeDSMethodData"]);
            $_SESSION["method_url_completion"] = 2;
        }	
    }else{
        $_SESSION["method_url_completion"] = 3;
        $_SESSION["threeDSMethodData"] = "";
    }

}
?>