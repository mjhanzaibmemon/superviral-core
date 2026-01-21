<?php

include_once('db.php');

if(isset($_SERVER['HTTP_X_FORWARDED_PROTO'])){

    if ($_SERVER['HTTP_X_FORWARDED_PROTO']=="http") {
    $url = "https://" . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $url);
            exit;
        }

    }


///
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];


$headerscript = '
document.addEventListener("DOMContentLoaded", function () {
  try {
    const startTime = new Date();
    const mobileMenuToggler = document.querySelector(
      ".headernew .mobilenavigationbtnnew"
    );
    const mobileMenudropdown = document.getElementById("myDropdownnew");
    mobileMenuToggler.addEventListener("click", () => {
      mobileMenudropdown.classList.toggle("show");
    //   const endTime = new Date();
    //   var timeDiff = endTime - startTime; //in ms

      //console.log(Math.floor(Date.now() / 100));
    //   console.log(timeDiff);
    });
  } catch (error) {
    console.error(error);
  }
});
';

function formatNumber($num) {
    if ($num >= 1000 && $num < 1000000) {
        return round($num / 1000) . 'K';
    } elseif ($num >= 1000000) {
        return round($num / 1000000) . 'M';
    } else {
        return $num;
    }
}

$nonwwlink = str_replace($_SERVER['HTTP_HOST'].$loclink,$_SERVER['HTTP_HOST'],'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

$footerlanguageshow = 'English ('.strtoupper($loc).' - '.$locas[$loc]['currencypp'].') - <a class="footerchangelanguagehref" title="American English (US)" href="'.str_replace($_SERVER['HTTP_HOST'],$_SERVER['HTTP_HOST'],$nonwwlink).'">English (US - USD)</a>
/ <a class="footerchangelanguagehref" title="British English (GB)" href="'.str_replace($_SERVER['HTTP_HOST'],$_SERVER['HTTP_HOST'].'/uk',$nonwwlink).'">English (UK - GBP)</a>';


/*

OLD FOOTER LINKS THAT CONTAIN /us/ 

if($loc=='ww'){

    $footerlanguageshow = 'English (EN) - 
    <a class="footerchangelanguagehref" title="English (EN)" href="https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'">English (EN)</a>
    / <a class="footerchangelanguagehref" title="American English (US)" href="https://'.$_SERVER['HTTP_HOST'].'/'.$_SERVER['REQUEST_URI'].'">English (US - USD)</a>
    / <a class="footerchangelanguagehref" title="British English (GB)" href="https://'.$_SERVER['HTTP_HOST'].'/uk'.$_SERVER['REQUEST_URI'].'">English (UK - GBP)</a>

    ';

}else{

}
*/


if($blogsection=='1')unset($footerlanguageshow);

    $header = ' 

      
    <script type="application/ld+json">
    {
    "@context": "https://schema.org/",
    "@type": "Organization",
    "url": "https://superviral.io/",
    "logo": {
        "@type": "ImageObject",
        "url": "https://superviral.io/imgs/logo.png"
    },
    "name": "Superviral",
    "email": "support@superviral.io"
    }
    </script>
    <script type="application/ld+json">
    {
        "@context": "https://schema.org/",
        "@type": "WebSite",
        "name": "Superviral",
        "url": "https://superviral.io/{loclocation}",
        "inLanguage": "{contentlanguage}",
        "description": "{home_metadesc}"
    }
    </script>
    
    <script>
            var measurementId = "'. $measurementId .'";
    </script>

    <script>
        // Load Google Tag Manager
        (function(w,d,s,l,i){
            w[l]=w[l]||[];
            w[l].push({\'gtm.start\': new Date().getTime(), event:\'gtm.js\'});
            var f=d.getElementsByTagName(s)[0],
                j=d.createElement(s), dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';
            j.async=true;
            j.src=\'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;
            f.parentNode.insertBefore(j,f);
        })(window,document,\'script\',\'dataLayer\',"'. $gtmId .'"); 
    </script>


    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id='. $gtmId .'" height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>

    <script>
        // Dropdown
        try {
            document.addEventListener("DOMContentLoaded", function() {
                const dropdownButtons = document.querySelectorAll(".services-dropdown .btn-toggle");
                const mobileDropdownButtons = document.querySelectorAll(".dropdown-content .services-dropdown .services-menu");
        
                dropdownButtons.forEach(function(button) {
                    button.addEventListener("click", function(event) {
                        event.stopPropagation();
                        const menu = this.nextElementSibling;
                        closeAllMenusExcept(menu);
                        toggleMenu(menu, this.querySelector("svg"), this);
                    });
                });
        
                mobileDropdownButtons.forEach(function(button) {
                    button.addEventListener("click", function(event) {
                        event.stopPropagation();
                        const menu = this.parentElement.nextElementSibling;
                        closeAllMenusExcept(menu);
                        toggleMenu(menu, this.querySelector("svg"), this);
                    });
                });
        
                function toggleMenu(menu, svg, button) {
                    if (menu) {
                        const isMenuOpen = menu.style.display === "block" || getComputedStyle(menu).display === "block";
                        menu.style.display = isMenuOpen ? "none" : "block";
        
                        if (svg) {
                            svg.classList.toggle("rotated", !isMenuOpen);
                        }
        
                        if (button) {
                            button.classList.toggle("menu-open", !isMenuOpen);
                        }
        
                        const mobileLinksWrapper = button.parentElement.querySelector(".services-dropdown .services-menu");
                        if (mobileLinksWrapper) {
                            mobileLinksWrapper.style.backgroundColor = isMenuOpen ? "transparent" : "#F5F5F5";
                            mobileLinksWrapper.style.borderBottom = isMenuOpen ? "1px solid #ebebeb" : "none";
                        }
                    }
                }
        
                function closeAllMenusExcept(exceptMenu) {
                    const menus = document.querySelectorAll(".services-dropdown .menu");
                    menus.forEach(function(menu) {
                        if (menu !== exceptMenu) {
                            menu.style.display = "none";
                            const svg = menu.parentElement.querySelector(".btn-toggle svg");
                            if (svg) {
                                svg.classList.remove("rotated");
                            }
                            const btnToggle = menu.parentElement.querySelector(".btn-toggle");
                            btnToggle.classList.remove("menu-open");
                        }
                    });
                }
        
                document.body.addEventListener("click", function(event) {
                    const target = event.target;
                    if (!target.closest(".services-dropdown") && !target.closest(".dropdown-content")) {
                        const menus = document.querySelectorAll(".services-dropdown .menu");
                        menus.forEach(function(menu) {
                            menu.style.display = "none";
                            const svg = menu.parentElement.querySelector(".btn-toggle svg");
                            if (svg) {
                                svg.classList.remove("rotated");
                            }
                            const btnToggle = menu.parentElement.querySelector(".btn-toggle");
                            btnToggle.classList.remove("menu-open");
                        });
                    }
                });
        
            });
        } catch (error) {
            console.error(error);
        }

    </script>
    
    
    <div class="headernew" align="center">

        <div class="cnwidth">

            <button class="mobilenavigationbtnnew" aria-label="mobile navigation menu toggler">

                <svg class="mobilemenubtnnew" xmlns="http://www.w3.org/2000/svg" width="25" height="25"
                    viewBox="0 0 124 124">
                    <path d="M112 6H12C5.4 6 0 11.4 0 18s5.4 12 12 12h100c6.6 0 12-5.4 12-12S118.6 6 112 6z" />
                    <path
                        d="M112 50H12C5.4 50 0 55.4 0 62c0 6.6 5.4 12 12 12h100c6.6 0 12-5.4 12-12C124 55.4 118.6 50 112 50z" />
                    <path d="M112 94H12c-6.6 0-12 5.4-12 12s5.4 12 12 12h100c6.6 0 12-5.4 12-12S118.6 94 112 94z" />
                </svg>

            </button>


            <div class="navigationnew">

                <ul class="desktopnavnew">

                    <li>
                        <div class="services-dropdown">

                            <button class="btn-toggle">Instagram Services

                                <span class="icon">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="14" viewBox="0 0 9 14"
                                        fill="none">

                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M2.23918 1.52539L1.47461 2.29869L6.01829 7.03254L5.53354 7.53744L5.53623 7.53469L1.49932 11.7405L2.25234 12.5254C3.36829 11.3632 6.48346 8.11769 7.52461 7.03254C6.75131 6.22624 7.50543 7.01219 2.23918 1.52539Z"
                                            fill="#242424" stroke="#242424" />

                                    </svg>

                                </span>

                            </button>

                            <div class="menu">
                                <div class="links-wrapper">
                                    <ul class="row-l1">
                                        <li class="link">
                                            <a href="'.$loclink.'{hhref1}">


                                                <div class="spriteContainer follower"></div>


                                                <span>{hlink1}</span>

                                            </a>
                                        </li>
                                        <li class="link">
                                            <a href="'.$loclink.'{hhref2}">


                                                <div class="spriteContainer likes"></div>


                                                <span>{hlink2}</span>

                                            </a>
                                        </li>
                                        <li class="link">
                                            <a href="'.$loclink.'{hhref3}">

                                                <div class="spriteContainer views"></div>


                                                <span>{hlink3}</span>
                                            </a>
                                        </li>
                                    </ul>
                                    <ul class="row-l2">
                                        <li class="link">
                                            <a href="'.$loclink.'{hhref11}">


                                                <div class="spriteContainer comments"></div>


                                                <span>{hlink11}</span>

                                            </a>
                                        </li>
                                        <li class="link">
                                            <a href="'.$loclink.'/automatic-instagram-likes/">

                                                <div class="spriteContainer al"></div>

                                                <span>Automatic Instagram Likes</span>

                                            </a>

                                        </li>
                                    </ul>
                                </div>
                            </div>

                        </div>

                    </li>

                    <li>

                        <div class="services-dropdown">

                            <button class="btn-toggle">TikTok Services

                                <span class="icon">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="14" viewBox="0 0 9 14"
                                        fill="none">

                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M2.23918 1.52539L1.47461 2.29869L6.01829 7.03254L5.53354 7.53744L5.53623 7.53469L1.49932 11.7405L2.25234 12.5254C3.36829 11.3632 6.48346 8.11769 7.52461 7.03254C6.75131 6.22624 7.50543 7.01219 2.23918 1.52539Z"
                                            fill="#242424" stroke="#242424" />

                                    </svg>

                                </span>

                            </button>

                            <div class="menu">

                                <div class="links-wrapper">

                                    <ul class="row-l1">


                                        <li class="link">

                                            <a href="'.$loclink.'{hhref17}">
                                                <div class="spriteContainer follower"></div>
                                                <span>{hlink17}</span>
                                            </a>

                                        </li>

                                        <li class="link">

                                            <a href="'.$loclink.'{hhref18}">

                                                <div class="spriteContainer likes"></div>
                                                <span>{hlink18}</span>

                                            </a>

                                        </li>

                                        <li class="link">

                                            <a href="'.$loclink.'{hhref22}">

                                                <div class="spriteContainer free-package"></div>
                                                <span>{hlink22}</span>

                                            </a>

                                        </li>

                                    </ul>

                                    <ul class="row-l2">

                                        <li class="link">

                                            <a href="'.$loclink.'{hhref19}">
                                                <div class="spriteContainer views"></div>
                                                <span>{hlink19}</span>
                                            </a>

                                        </li>

                                        <li class="link">

                                            <a href="'.$loclink.'{hhref23}">
                                                <div class="spriteContainer free-package"></div>
                                                <span>{hlink23}</span>
                                            </a>

                                        </li>

                                    </ul>

                                </div>

                            </div>

                        </div>

                    </li>

                    <li>

                        <div class="services-dropdown">

                            <button class="btn-toggle">Free Instagram Tools

                                <span class="icon">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="14" viewBox="0 0 9 14"
                                        fill="none">

                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M2.23918 1.52539L1.47461 2.29869L6.01829 7.03254L5.53354 7.53744L5.53623 7.53469L1.49932 11.7405L2.25234 12.5254C3.36829 11.3632 6.48346 8.11769 7.52461 7.03254C6.75131 6.22624 7.50543 7.01219 2.23918 1.52539Z"
                                            fill="#242424" stroke="#242424" />

                                    </svg>

                                </span>

                            </button>

                            <div class="menu">

                                <div class="links-wrapper">

                                    <ul class="row-l1">


                                        <li class="link">

                                            <a href="'.$loclink.'{hhref13}">
                                                <div class="spriteContainer vid_dl"></div>
                                                <span> {hlink13}</span>
                                            </a>

                                        </li>

                                        <li class="link">

                                            <a href="'.$loclink.'{hhref14}">
                                                <div class="spriteContainer story_dl"></div>
                                                <span> {hlink14}</span>
                                            </a>

                                        </li>

                                        <li class="link" style="'.$hideUK.'">

                                            <a href="'.$loclink.'{hhref20}">
                                                <div class="spriteContainer free-package"></div>
                                                <span> {hlink20}</span>
                                            </a>

                                        </li>

                                    </ul>


                                    <ul class="row-l2">

                                        <li class="link">

                                            <a href="'.$loclink.'{hhref15}">
                                                <div class="spriteContainer viewer"></div>
                                                <span> {hlink15}</span>
                                            </a>

                                        </li>


                                        <li class="link">

                                            <a href="'.$loclink.'{hhref16}">
                                                <div class="spriteContainer counter"></div>
                                                <span> {hlink16}</span>
                                            </a>

                                        </li>

                                        <li class="link">

                                            <a href="'.$loclink.'{hhref21}">
                                                <div class="spriteContainer free-package"></div>
                                                <span> {hlink21} </span>
                                            </a>

                                        </li>

                                    </ul>

                                </div>

                            </div>

                    </li>

                    <li>

                        <div class="services-dropdown">

                            <button class="btn-toggle">Resources

                                <span class="icon">

                                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="14" viewBox="0 0 9 14"
                                        fill="none">

                                        <path fill-rule="evenodd" clip-rule="evenodd"
                                            d="M2.23918 1.52539L1.47461 2.29869L6.01829 7.03254L5.53354 7.53744L5.53623 7.53469L1.49932 11.7405L2.25234 12.5254C3.36829 11.3632 6.48346 8.11769 7.52461 7.03254C6.75131 6.22624 7.50543 7.01219 2.23918 1.52539Z"
                                            fill="#242424" stroke="#242424" />

                                    </svg>

                                </span>

                            </button>

                            <div class="menu" style="left: -2px">

                                <div class="links-wrapper" style="gap: 0">

                                    <ul class="row-l1">

                                        <li class="link">

                                            <a href="'. $loclink .'/blog/">
                                                <div class="spriteContainer blog"></div>
                                                <span> {hlink8} </span>
                                            </a>

                                        </li>

                                        <li class="link">

                                            <a href="'. $loclink .'{hhref5}">
                                                <div class="spriteContainer faq-icon"></div>
                                                <span> {hlink5} </span>
                                            </a>

                                        </li>

                                    </ul>

                                    <ul class="row-l2">
                                        <li class="link">
                                            <a href="'. $loclink .'{hhref4}">
                                                <div class="spriteContainer track"></div>
                                                <span> {hlink4} </span>
                                            </a>
                                        </li>
                                        <li class="link">
                                            <a href="'. $loclink .'{hhref9}">
                                                <div class="spriteContainer tos"></div>
                                                <span> {hlink9} </span>
                                            </a>
                                        </li>
                                    </ul>

                                </div>

                            </div>

                        </div>

                    </li>

                    <li>

                        <div class="services-dropdown">

                            <a title="{htitle6}" href="'.$loclink.'{hhref6}">{hlink6}</a>

                        </div>

                    </li>

                    <li>
                        <div class="services-dropdown">
                            <a title="{htitle7}" href="'.$loclink.'{hhref7}">{hlink7}</a>
                        </div>
                    </li>

                    <li>



                        <a href="'. $loclink .'/login/" class="btn-toggle" aria-label="Login">

                            <svg width="30" height="30" viewBox="0 0 30 30" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="0.75" y="0.75" width="28.5" height="28.5" rx="14.25" stroke="black"
                                    stroke-width="1.5" />
                                <ellipse cx="15" cy="12.5" rx="6" ry="5.5" fill="black" />
                                <path
                                    d="M15.2927 20C9.79678 20 7 21.9772 7 27.5L15.2927 30L24 26.6667C24 21.1438 20.7886 20 15.2927 20Z"
                                    fill="black" />
                            </svg>


                        </a>



                    </li>




                </ul>

            </div>




            <div class="logonew"><a title="Superviral" href="'.$loclink.'/" style="position:relative;">

                    <svg xmlns="http://www.w3.org/2000/svg" id="logo" x="0" y="0" viewBox="0 0 567.3 114"
                        style="enable-background:new 0 0 567.3 114;version:1">
                        <linearGradient id="SVGID_1_" gradientUnits="userSpaceOnUse" x1="11.9" y1="17.7" x2="90.6"
                            y2="96.3">
                            <stop offset="0" stop-color="#DA4453" />
                            <stop offset="0.55" stop-color="#89216B" />
                            <stop offset="1" stop-color="#4A00E8" />
                        </linearGradient>
                        <path
                            d="M18.5 92.7c-2.5-0.2-5.1-0.2-7.5-0.7C2.5 90.3-2.2 81.6 1 73.5c1.3-3.2 3-6.2 4.9-9.1 3.2-5 3.3-9.8 0-14.8 -1.7-2.6-3.3-5.3-4.5-8.1 -4.3-9.8 1.9-19.6 12.5-20 2.9-0.1 5.9-0.1 8.8 0 5.4 0.1 9.3-2.1 11.9-6.9 1.5-2.8 3.1-5.6 4.9-8.2 3.6-5.3 8.6-7.2 14.9-6C57.9 1 60.6 3 62.6 5.8c1.7 2.4 3.3 4.9 4.5 7.5 2.9 6 7.6 8.5 14.2 8.2 3.4-0.2 6.9-0.2 10.3 0.5 8.4 1.6 13.1 10.3 9.9 18.4 -1.2 2.9-2.7 5.8-4.5 8.4 -3.7 5.5-3.8 10.8 0 16.4 1.6 2.3 3 4.9 4.1 7.5 4.2 9.6-1.9 19.4-12.4 19.9 -2.9 0.1-5.9 0.1-8.8 0 -5.5-0.1-9.5 2.1-12.1 7 -1.5 2.8-3.1 5.6-4.9 8.2 -3.5 5.2-8.5 7.1-14.7 6 -3.6-0.7-6.3-2.7-8.4-5.6 -1.6-2.3-3.1-4.6-4.3-7.1 -3-6.4-7.9-9.1-14.8-8.5 -0.7 0.1-1.5 0-2.3 0C18.5 92.6 18.5 92.7 18.5 92.7z"
                            style="clip-rule:evenodd;fill-rule:evenodd;fill:url(#SVGID_1_)" />
                        <path
                            d="M159.1 43.7c-2.7 0-4.7 1.8-4.7 4.3 0 2.6 0.3 2.8 8.6 5.4 10.7 3.3 14.1 7.2 14.1 14.7 0 9.6-8 17.1-18.2 17.1 -10.1 0-17.4-5.4-18.6-17.1h12.3c1.2 4.3 3.2 6.1 6.8 6.1 3.1 0 5.5-2.1 5.5-4.9 0-2.9-0.5-3.7-8.6-6.4 -10.1-3.3-14.1-7.5-14.1-14.9 0-8.6 7.4-15.3 16.9-15.3 8.6 0 16.3 5.6 16.7 15h-11.9C163.4 45 161.7 43.7 159.1 43.7L159.1 43.7zM218.5 84v-4.8c-4.6 4.4-8.4 6-14.6 6 -11.8 0-19.4-6.8-19.4-24.5V33.8h12.2v24.6c0 13.3 3.8 15.8 9.6 15.8 4.1 0 7.4-1.7 9.3-4.6 1.4-2.3 1.9-5.3 1.9-12V33.8h12.2V84H218.5L218.5 84zM267.9 85.2c-6.2 0-11-1.8-15.6-5.9v21.5H240v-67h11.2v5.9c3.9-4.5 9.8-7.1 16.7-7.1 14.7 0 25.4 11 25.4 26.1C293.3 73.9 282.6 85.2 267.9 85.2L267.9 85.2zM266.4 43.7c-8.5 0-14.9 6.5-14.9 15.1 0 8.8 6.4 15.3 15.1 15.3 8.1 0 14.4-6.6 14.4-15.1C281 50.3 274.7 43.7 266.4 43.7L266.4 43.7zM351.5 64.2h-39.4c1.5 6.1 6.8 9.9 14.1 9.9 5.1 0 8.1-1.4 11.2-5h13.3c-3.4 10.3-14 16-24.2 16 -15.5 0-27.6-11.4-27.6-26.1 0-14.8 11.8-26.5 26.8-26.5 15.2 0 26.3 11.4 26.3 27C352 61.4 351.9 62.5 351.5 64.2L351.5 64.2zM325.9 43.7c-7.3 0-12.2 3.5-14.1 10H340C338.4 47.2 333.4 43.7 325.9 43.7L325.9 43.7zM372.6 56.1V84h-12.2V33.8h11.2v4.9c3.2-4.6 5.8-6.1 11-6.1h0.9v11.6C376.2 44.4 372.6 48.3 372.6 56.1L372.6 56.1zM416.9 84h-9.7l-20.5-50.2h13.6L412.1 67l11.6-33.3h13.8L416.9 84 416.9 84zM443.6 26.5V14.5h12.2v12.1H443.6L443.6 26.5zM455.7 84h-12.2V33.8h12.2V84L455.7 84zM478.2 56.1V84H466V33.8h11.2v4.9c3.2-4.6 5.8-6.1 11-6.1h0.9v11.6C481.8 44.4 478.2 48.3 478.2 56.1L478.2 56.1zM534.3 84v-6.5c-4.9 5.4-9.7 7.7-16.9 7.7 -14.9 0-25.6-11-25.6-26.1 0-15.3 10.8-26.5 25.9-26.5 7.4 0 12.4 2.4 16.6 7.9v-6.7h11.2V84H534.3L534.3 84zM519 43.7c-8.7 0-14.9 6.5-14.9 15.7 0 8.8 6.2 14.8 15.1 14.8 9.3 0 14.8-6.5 14.8-14.9C533.9 50.1 527.7 43.7 519 43.7L519 43.7zM555.1 84V17h12.2v67H555.1L555.1 84z"
                            fill="#231F20" />
                    </svg>
                    <span
                        style="position: absolute;right: -3px;bottom: -2px;font-size: 11px;color: #1a73e7;font-weight: bold;">{hsince}</span>
                </a></div>

            <a class="accountbtn" title="{hlinkaccount}" href="'.$loclink.'{hhref10}">

                <svg class="mobilemenubtnnew" xmlns="http://www.w3.org/2000/svg" width="27" height="27"
                    viewBox="0 0 510 510">
                    <path
                        d="M255 0C114.8 0 0 114.8 0 255s114.8 255 255 255 255-114.7 255-255S395.3 0 255 0zM255 76.5c43.4 0 76.5 33.2 76.5 76.5s-33.1 76.5-76.5 76.5c-43.3 0-76.5-33.1-76.5-76.5S211.7 76.5 255 76.5zM255 438.6c-63.7 0-119.8-33.1-153-81.6 0-51 102-79 153-79S408 306 408 357C374.9 405.5 318.8 438.6 255 438.6z" />
                </svg>

            </a>

        </div>

    </div>


    <div id="myDropdownnew" class="navcontainer dropdown-content">
        <div class="services-dropdown">

            <div class="services-menu">
                <span>Instagram Services</span>
                <div class="icon">

                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="14" viewBox="0 0 9 14" fill="none">

                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M2.23918 1.52539L1.47461 2.29869L6.01829 7.03254L5.53354 7.53744L5.53623 7.53469L1.49932 11.7405L2.25234 12.5254C3.36829 11.3632 6.48346 8.11769 7.52461 7.03254C6.75131 6.22624 7.50543 7.01219 2.23918 1.52539Z"
                            fill="#242424" stroke="#242424" />

                    </svg>

                </div>

            </div>
        </div>

        <div class="menu">

            <ul>
                <li class="link">

                    <a href="'.$loclink.'{hhref1}">
                        <div class="spriteContainer follower"></div>
                        <span>{hlink1}</span>
                    </a>

                </li>

                <li class="link">

                    <a href="'.$loclink.'{hhref2}">

                        <div class="spriteContainer likes"></div>
                        <span>{hlink2}</span>

                    </a>

                </li>

                <li class="link">

                    <a href="'.$loclink.'{hhref3}">

                        <div class="spriteContainer views"></div>
                        <span>{hlink3}</span>

                    </a>

                </li>

                <li class="link">

                    <a href="'.$loclink.'{hhref11}">
                        <div class="spriteContainer comments"></div>
                        <span>{hlink11}</span>
                    </a>

                </li>

                <li class="link">

                    <a href="'.$loclink.'/automatic-instagram-likes/">

                        <div class="spriteContainer al"></div>
                        <span>Automatic Instagram Likes</span>

                    </a>
                </li>

            </ul>

        </div>


        <div class="services-dropdown">

            <div class="services-menu">

                <span>TikTok Services</span>

                <div class="icon">

                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="14" viewBox="0 0 9 14" fill="none">

                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M2.23918 1.52539L1.47461 2.29869L6.01829 7.03254L5.53354 7.53744L5.53623 7.53469L1.49932 11.7405L2.25234 12.5254C3.36829 11.3632 6.48346 8.11769 7.52461 7.03254C6.75131 6.22624 7.50543 7.01219 2.23918 1.52539Z"
                            fill="#242424" stroke="#242424" />

                    </svg>

                </div>
            </div>
        </div>

        <div class="menu">

            <ul>
                <li class="link">

                    <a href="'.$loclink.'{hhref17}">
                        <div class="spriteContainer follower"></div>
                        <span>{hlink17}</span>
                    </a>

                </li>

                <li class="link">

                    <a href="'.$loclink.'{hhref18}">

                        <div class="spriteContainer likes"></div>
                        <span>{hlink18}</span>

                    </a>

                </li>

                <li class="link">

                    <a href="'.$loclink.'{hhref19}">
                        <div class="spriteContainer views"></div>
                        <span>{hlink19}</span>
                    </a>

                </li>

                <li class="link">

                    <a href="'.$loclink.'{hhref22}">
                        <div class="spriteContainer free-package"></div>
                        <span>{hlink22}</span>
                    </a>

                </li>

                <li class="link">

                    <a href="'.$loclink.'{hhref23}">
                        <div class="spriteContainer free-package"></div>
                        <span>{hlink23}</span>
                    </a>

                </li>

            </ul>

        </div>


        <div class="services-dropdown">

            <div class="services-menu">

                <span>Free Instragram Tools</span>

                <div class="icon">

                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="14" viewBox="0 0 9 14" fill="none">

                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M2.23918 1.52539L1.47461 2.29869L6.01829 7.03254L5.53354 7.53744L5.53623 7.53469L1.49932 11.7405L2.25234 12.5254C3.36829 11.3632 6.48346 8.11769 7.52461 7.03254C6.75131 6.22624 7.50543 7.01219 2.23918 1.52539Z"
                            fill="#242424" stroke="#242424" />

                    </svg>

                </div>
            </div>
        </div>

        <div class="menu">

            <ul>

                <li class="link">

                    <a href="'.$loclink.'{hhref13}">
                        <div class="spriteContainer vid_dl"></div>
                        <span> {hlink13}</span>
                    </a>

                </li>

                <li class="link">

                    <a href="'.$loclink.'{hhref14}">
                        <div class="spriteContainer story_dl"></div>
                        <span> {hlink14}</span>
                    </a>

                </li>

                <li class="link">

                    <a href="'.$loclink.'{hhref15}">
                        <div class="spriteContainer viewer"></div>
                        <span> {hlink15}</span>
                    </a>
                </li>


                <li class="link">

                    <a href="'.$loclink.'{hhref16}">
                        <div class="spriteContainer counter"></div>
                        <span> {hlink16}</span>
                    </a>
                </li>


                <li class="link">

                    <a href="'.$loclink.'{hhref20}">
                        <div class="spriteContainer free-package"></div>
                        <span> {hlink20} </span>
                    </a>

                </li>

                <li class="link">

                    <a href="'.$loclink.'{hhref21}">
                        <div class="spriteContainer free-package"></div>
                        <span> {hlink21} </span>
                    </a>

                </li>

            </ul>

        </div>

        <div class="services-dropdown">

            <div class="services-menu">
                <span>Resources</span>

                <div class="icon">

                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="14" viewBox="0 0 9 14" fill="none">

                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M2.23918 1.52539L1.47461 2.29869L6.01829 7.03254L5.53354 7.53744L5.53623 7.53469L1.49932 11.7405L2.25234 12.5254C3.36829 11.3632 6.48346 8.11769 7.52461 7.03254C6.75131 6.22624 7.50543 7.01219 2.23918 1.52539Z"
                            fill="#242424" stroke="#242424" />

                    </svg>

                </div>
            </div>
        </div>

        <div class="menu">

            <ul>

                <li class="link">

                    <a href="'. $loclink .'/blog/">
                        <div class="spriteContainer blog"></div>
                        <span> {hlink8} </span>
                    </a>

                </li>

                <li class="link">

                    <a href="'.$loclink.'{hhref5}">
                        <div class="spriteContainer faq-icon"></div>
                        <span> {hlink5} </span>
                    </a>
                </li>

                <li class="link">

                    <a href="'.$loclink.'{hhref4}">
                        <div class="spriteContainer track"></div>
                        <span> {hlink4} </span>
                    </a>

                </li>

                <li class="link">

                    <a href="'.$loclink.'{hhref9}">
                        <div class="spriteContainer tos"></div>
                        <span> {hlink9} </span>
                    </a>

                </li>

            </ul>

        </div>

        <div class="mobileLinks-wrapper">
            <a title="{htitle4}" href="'.$loclink.'{hhref4}"><span>{hlink4}</span></a>
        </div>

        <div class="mobileLinks-wrapper">
            <a title="{htitle6}" href="'.$loclink.'{hhref6}"><span>{hlink6}</span></a>
        </div>

        <div class="mobileLinks-wrapper">
            <a title="{htitle7}" href="'.$loclink.'{hhref7}"><span>{hlink7}</span></a>
        </div>

    </div>';


$footer = ' 

<svg style="width:0;height:0;position:absolute;" aria-hidden="true" focusable="false">
  <linearGradient id="color3" x2="1" y2="1">
    <stop offset="0%" stop-color="#4a00e0" />
    <stop offset="100%" stop-color="#8e2de2" />
  </linearGradient>
</svg>

    <div class="footer color1" align="center">

        <div class="cnwidth">
                
            <div class="row1">
                    <div class="logo"><svg><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo"></use></svg></div>
                    
               
                <div class="linksContainer">
                   
               <ul class="footerlinks" aria-label="Footer Navigation Links">
                    <li>{htitlemain}</li>
                    <li><a aria-label="{htitle1} Link" href="'.$loclink.'{hhref1}">{hlink1}</a></li>
                    <li><a aria-label="{htitle2} Link" href="'.$loclink.'{hhref2}">{hlink2}</a></li>
                    <li><a aria-label="{htitle3} Link" href="'.$loclink.'{hhref3}">{hlink3}</a></li>
                    <li><a aria-label="{htitle11} Link" href="'.$loclink.'{hhref11}">{hlink11}</a></li>
                    <li><a aria-label="Automatic Instagram Likes Link" href="'.$loclink.'/automatic-instagram-likes/">Get Automatic Instagram Likes</a></li>
                </ul>


              <ul class="footerlinks">
                    <li>{htitlemain5}</li>
                    <li><a title="{htitle17}" href="'.$loclink.'{hhref17}" aria-label="{htitle17} Link">{hlink17}</a></li>
                    <li><a title="{htitle18}" href="'.$loclink.'{hhref18}" aria-label="{htitle18} Link">{hlink18}</a></li>
                    <li><a title="{htitle19}" href="'.$loclink.'{hhref19}" aria-label="{htitle19} Link">{hlink19}</a></li>
                </ul>

                <ul class="footerlinks">
                    <li>{htitlemain4}</li>
                    <li><a title="{htitle13}" href="'.$loclink.'{hhref13}" aria-label="{htitle13} Link">{hlink13}</a></li>
                    <li><a title="{htitle14}" href="'.$loclink.'{hhref14}" aria-label="{htitle14} Link">{hlink14}</a></li>
                    <li><a title="{htitle15}" href="'.$loclink.'{hhref15}" aria-label="{htitle15} Link">{hlink15}</a></li>
                    <li><a title="{htitle16}" href="'.$loclink.'{hhref16}" aria-label="{htitle16} Link">{hlink16}</a></li>
                    <li><a title="{htitle20}" href="'.$loclink.'{hhref20}" aria-label="{htitle20} Link">{hlink20}</a></li>
                    <li><a title="{htitle21}" href="'.$loclink.'{hhref21}" aria-label="{htitle21} Link">{hlink21}</a></li>
                    <li><a title="{htitle22}" href="'.$loclink.'{hhref22}" aria-label="{htitle22} Link">{hlink22}</a></li>
                    <li><a title="{htitle23}" href="'.$loclink.'{hhref23}" aria-label="{htitle23} Link">{hlink23}</a></li>
                </ul>

                <ul class="footerlinks">
                    <li>{htitlemain2}</li>
                    <li><a title="{htitle8}" href="'. $loclink .'/blog/" aria-label="{htitle8} Link">{hlink8}</a></li>
                    <li><a title="{htitle6}" href="'.$loclink.'{hhref6}" aria-label="{htitle6} Link">{hlink6}</a></li>
                    <li><a title="{htitle9}" href="'.$loclink.'{hhref9}" aria-label="{htitle9} Link">{hlink9}</a></li>
                </ul>

                <ul class="footerlinks">
                    <li>{htitlemain3}</li>
                    <li><a title="{htitle4}" href="'.$loclink.'{hhref4}" aria-label="{htitle4} Link">{hlink4}</a></li>
                    <li><a title="{htitle5}" href="'.$loclink.'{hhref5}" aria-label="{htitle5} Link">{hlink5}</a></li>
                    <li><a title="{htitle7}" href="'.$loclink.'{hhref7}" aria-label="{htitle7} Link">{hlink7}</a></li>
                </ul>
                </div>
            </div>
          
            <div class="row2">
                    <p class="copyright">'.$footerlanguageshow.'</p>
                </div>
                <div class="row2">
                    <p class="copyright">{footersupport}</p>
                    <p class="copyright">'.$locas[$loc]['footercopyright'].'</p>
                    <div class="payment-logos">
                    <div class="accepted"></div>
                    </div>
                </div>
                
        </div>

    </div>
    
        ';




/*

        <script src="https://www.google.com/recaptcha/api.js"></script>
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':
        new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=
        \'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,\'script\',\'dataLayer\',"'. $gtmId .'");</script>
        <!-- End Google Tag Manager -->        
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id='. $gtmId .'"
        height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        </script>


*/

        $contentq = mysql_query("SELECT * FROM `content` WHERE brand='sv' AND ((`country` = '{$locas[$loc]['sdb']}' AND `page` = 'home' AND `name`='metadesc') OR (`country` = '{$locas[$loc]['sdb']}' AND `page` = 'home' AND `name`='metadesc')) LIMIT 1");
        while($cinfo = mysql_fetch_array($contentq)){$header = str_replace('{home_metadesc}',$cinfo['content'],$header);}


?>
