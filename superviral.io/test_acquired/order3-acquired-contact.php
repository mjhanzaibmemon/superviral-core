<?php

    session_start();
    
    if($_SERVER['REQUEST_METHOD'] == 'POST'){

        $content = file_get_contents('php://input');
        echo $content;
        
    }

?>