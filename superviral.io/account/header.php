<?php

include('../header.php');

$headerbottom = '<div class="darkheader" align="center">
                                    <div class="cnwidth">
                                    <a href="/'.$loclinkforward.'account/dashboard/">Growth history</a>
                                    <a id="toolsTopBarId" href="/'.$loclinkforward.'account/tools/" style="display:none;">Tools</a>
                                    <a href="/'.$loclinkforward.'account/automatic-likes/">Auto Likes</a>
                                    <a href="/'.$loclinkforward.'account/settings/">Settings</a>
                                
                                    </div>
                </div>
                <div class="darkmobileheader" align="center">

                                    
                                    <a class="'.$activelink1.'" href="/'.$loclinkforward.'account/dashboard/">
                                    	<img src="/imgs/account-header/shuttle.svg">
                                    	<img class="active" src="/imgs/account-header/shuttle-active.svg">
                                    	<span>Growth history</span>
                                    </a>
                                    
                                    <a class="'.$activelink4.'" id="toolsID" href="/'.$loclinkforward.'account/tools/" style="display:none;">
                                    	<img src="/imgs/account-header/Tools-Icon.svg" style="width: initial;">
                                    	<img class="active" src="/imgs/account-header/Tools-Icon.svg" style="width: initial;">
                                    	<span>Tools</span>
                                    </a>

                                    <a class="'.$activelink2.'" href="/'.$loclinkforward.'account/automatic-likes/">
                                    	<img src="/imgs/account-header/heart.svg">
                                    	<img class="active" src="/imgs/account-header/heart-active.svg">
                                    	<span>Auto Likes</span>
                                    </a>
                                    
                                    <a class="'.$activelink3.'" href="/'.$loclinkforward.'account/settings/">
                                    	<img src="/imgs/account-header/settings.svg">
                                    	<img class="active" src="/imgs/account-header/settings-active.svg">
                                    	<span>Settings</span>
                                    </a>
                                

                </div>';


$header = $header.$headerbottom;

$footer = '<script>'.$headerscript.'</script>'.$footer;

?>
