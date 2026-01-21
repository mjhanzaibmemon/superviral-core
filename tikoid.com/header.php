<?php

include('db.php');


$headerscript = '
const dropdownBtn = document.querySelector(".header .headerMenuUI");
const dropdownMenu = document.getElementById("myDropdown");

function showHideNavMenu() {
  dropdownMenu.classList.toggle("show");
}

// Close dropdown if clicked outside
  document.addEventListener("click", function(event) {
    var target = event.target;
    if (target !== dropdownBtn && !dropdownBtn.contains(target) && !dropdownMenu.contains(target)) {
      dropdownMenu.classList.remove("show");
    }
  });
';

///
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];


if(empty($currency))$currency = '&dollar;';
///
$header = '     <div class="header" align="center">

            <div class="cnwidth">

                <div class="navigation headerMenuUI"><svg onclick="showHideNavMenu()" class="mobilemenubtn" xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 459 459"><path d="M0 382.5h459v-51H0V382.5zM0 255h459v-51H0V255zM0 76.5v51h459v-51H0z"/></svg>


                <ul class="desktopnav">

                    <li><a title="Buy TikTok Followers" href="/buy-tiktok-followers/">Buy TikTok Followers</a></li>
                    <li><a title="Buy TikTok Likes" href="/buy-tiktok-likes/">Buy TikTok Likes</a></li>
                    <li><a title="Buy TikTok Views" href="/buy-tiktok-views/">Buy TikTok Views</a></li>
                    <li><a title="Track My Order" href="/track-my-order/">Track My Order</a></li>
                    <li><a title="Contact Us" href="/contact-us/">Contact Us</a></li>
                    <li><a style="border: 1px solid #000;border-radius: 5px;padding: 8px 10px 8px 34px; position: relative;" title="login" href="/login/"><svg style="position: absolute;left: 8px;top: 9px;" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 510 510"><path d="M255 0C114.8 0 0 114.8 0 255s114.8 255 255 255 255-114.7 255-255S395.3 0 255 0zM255 76.5c43.4 0 76.5 33.2 76.5 76.5s-33.1 76.5-76.5 76.5c-43.3 0-76.5-33.1-76.5-76.5S211.7 76.5 255 76.5zM255 438.6c-63.7 0-119.8-33.1-153-81.6 0-51 102-79 153-79S408 306 408 357C374.9 405.5 318.8 438.6 255 438.6z"/></svg> My Account</a></li>
                </ul>

                </div>

                <div class="logo logoMin1200">
                  <a title="Tikoid" href="/" style="position:relative;"><img src="/imgs/logo.png"></a>
                </div>

                <a style="float:right;margin-top:12px" class="accountbtn" title="My Account" href="/login/">



                  <svg class="mobilemenubtnnew" xmlns="http://www.w3.org/2000/svg" width="27" height="27" viewBox="0 0 510 510"><path d="M255 0C114.8 0 0 114.8 0 255s114.8 255 255 255 255-114.7 255-255S395.3 0 255 0zM255 76.5c43.4 0 76.5 33.2 76.5 76.5s-33.1 76.5-76.5 76.5c-43.3 0-76.5-33.1-76.5-76.5S211.7 76.5 255 76.5zM255 438.6c-63.7 0-119.8-33.1-153-81.6 0-51 102-79 153-79S408 306 408 357C374.9 405.5 318.8 438.6 255 438.6z"/></svg>



                </a>

            </div>

        </div>


        <div id="myDropdown" class="navcontainer dropdown-content">
                    <a title="Tikoid" href="/">Home</a>
                    <a title="Buy TikTok Followers" href="/buy-tiktok-followers/">Buy TikTok Followers</a>
                    <a title="Buy TikTok Likes" href="/buy-tiktok-likes/">Buy TikTok Likes</a>
                    <a title="Buy TikTok Views" href="/buy-tiktok-views/">Buy TikTok Views</a>
                    <a title="Track My Order" href="/track-my-order/">Track My Order</a>
                    <a title="Frequently Asked Questions" href="/faq/">FAQ</a>
                    <a title="About Us" href="/about-us/">About Us</a>
                    <a title="Contact Us" href="/contact-us/">Contact Us</a>

        </div>';

$footer = ' 

<svg style="width:0;height:0;position:absolute;" aria-hidden="true" focusable="false">
  <linearGradient id="color3" x2="1" y2="1">
    <stop offset="0%" stop-color="#f60f27" />
    <stop offset="100%" stop-color="#eb2196" />
  </linearGradient>
</svg>

    <div class="footer color1" align="center">

            <div class="cnwidth">
                
                <div class="row1">
                    <div class="logo"><img src="/imgs/logo.png"></div>
                    <ul class="footerlinks">

                        <li>Services</li>
                        <li><a title="Buy Tiktok Followers" href="/buy-tiktok-followers/">Buy Tiktok Followers</a></li>
                        <li><a title="Buy Tiktok Likes" href="/buy-tiktok-likes/">Buy Tiktok Likes</a></li>
                        <li><a title="Buy Tiktok Views" href="/buy-tiktok-views/">Buy Tiktok Views</a></li>

                    </ul>
                    <ul class="footerlinks">

                        <li>About Tikoid</li>
                        <li><a title="About Us" href="/about-us/">About Us</a></li>
                        <li><a title="Terms of Service" href="/terms-of-service/">Terms of Service</a></li>

                    </ul>
                    <ul class="footerlinks">

                        <li>Support</li>
                        <li><a title="Track My Order" href="/track-my-order/">Track My Order</a></li>
                        <li><a title="Frequently Asked Questions" href="/faq/">FAQ</a></li>
                        <li><a title="Contact Us" href="/contact-us/">Contact Us</a></li>
                    </ul>
                </div>
                <div class="row2">
                    <p class="copyright">📧 Customer service: <a href="mailto:support@tikoid.com">support@tikoid.com</a></p>
                    <p class="copyright">© 2022 ITH Retail Group Ltd trading as Tikoid. All Rights Reserved.</p>
                    <div class="accepted"></div>
                </div>
            </div>

        </div>



        
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':
        new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=
        \'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,\'script\',\'dataLayer\',\'GTM-MJGT8BN6\');</script>
        <!-- End Google Tag Manager -->        
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-MJGT8BN6"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        </script>



    ';



?>