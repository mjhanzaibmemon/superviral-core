<?php

include('../header.php');

$headerbottom = '<div class="darkheader" align="center">
                                    <div class="cnwidth accHeaderMax1024">
                                    <a href="/account/orders/">Growth history</a>
                                    <a href="/account/settings/">Settings</a>
                                
                                    </div>
                </div>
                <div class="darkmobileheader" align="center">

                                    
                                    <a class="'.$activelink1.'" href="/account/orders/">
                                    	<img src="/imgs/account-header/shuttle.svg">
                                    	<img class="active" src="/imgs/account-header/shuttle-active.svg">
                                    	<span>Growth history</span>
                                    </a>

                                    <a class="'.$activelink3.'" href="/account/settings/">
                                    	<img src="/imgs/account-header/settings.svg">
                                    	<img class="active" src="/imgs/account-header/settings-active.svg">
                                    	<span>Settings</span>
                                    </a>
                                

                </div>';


$header = $header.$headerbottom;

$footer = '<script>'.$headerscript.'</script>'.$footer;

?>
