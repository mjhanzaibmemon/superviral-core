<?php
require "config.php";

?>
<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">

    <!-- jQuery library -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.slim.min.js"></script>

    <!-- Latest compiled JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        ul,
        #myUL {
            list-style-type: none;
        }

        #myUL {
            margin: 0;
            padding: 0;
        }

        .caret {
            cursor: pointer;
            -webkit-user-select: none;
            /* Safari 3.1+ */
            -moz-user-select: none;
            /* Firefox 2+ */
            -ms-user-select: none;
            /* IE 10+ */
            user-select: none;
        }

        .caret::before {
            color: black;
            display: inline-block;
            margin-right: 6px;
        }

        .caret-down::before {
            -ms-transform: rotate(90deg);
            /* IE 9 */
            -webkit-transform: rotate(90deg);
            /* Safari */
            transform: rotate(90deg);
        }

        .nested {
            display: none;
        }

        .active {
            display: block;
        }

        #cover-spin {
            position: fixed;
            width: 100%;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.7);
            z-index: 9999;
            display: none;
        }

        @-webkit-keyframes spin {
            from {
                -webkit-transform: rotate(0deg);
            }

            to {
                -webkit-transform: rotate(360deg);
            }
        }

        @keyframes spin {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }

        #cover-spin::after {
            content: '';
            display: block;
            position: absolute;
            left: 48%;
            top: 40%;
            width: 40px;
            height: 40px;
            border-style: solid;
            border-color: black;
            border-top-color: transparent;
            border-width: 4px;
            border-radius: 50%;
            -webkit-animation: spin .8s linear infinite;
            animation: spin .8s linear infinite;
        }
    </style>
</head>

<body>
    <div id="cover-spin"></div>
    <div class="container">
        <h1 class="border-bottom py-3">Etra Group - File management</h1>
        <div class="row">
            <div class="col-md-4">
                <nav id="sidebar">
                    <div class="sidebar-header">
                        <h2 id="mainHdng">Test Server</h2>
                    </div>
                    <input type="hidden" id="dirPath" value="all">
                    <ul id="myUL" style="height: 800px; overflow: auto;">

                    </ul>

                </nav>
            </div>
            <div class="col-md-8 border-left border-right" style="height: 800px; overflow: auto;display: none;" id="fileDivID">
                <div class="row">
                    <div class="col-md-6">
                        <h3 style="cursor:pointer;" id="hdngViewFileID"></h3>
                        <h6 style="cursor:pointer;" id="pathViewFileID"></h6>

                    </div>
                    <div class="col-md-6">
                        <span class="pull-right"><a href="" target="_blank" id="redirectCompareID">Compare with live server <i class="fa fa-arrow-right"></i></a></span>
                    </div>
                </div>
                <span style="cursor:pointer;float:right;" title="Clear Board" onclick="clearBoard();"><i class="fa fa-eraser"></i>Clear Board</span><br />
                <blockquote class="p-3" id="bqID" style="background: #231e1ee0;">
                    <pre style="color:aqua;">
                        <code id="viewFileID" >
                          
                        </code>
                    </pre>
                </blockquote>
            </div>
            <!-- Sidebar -->


        </div>



    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</body>

<script>
    var toggler = document.getElementsByClassName("caret");
    var i;

    for (i = 0; i < toggler.length; i++) {
        toggler[i].addEventListener("click", function() {
            this.parentElement.querySelector(".nested").classList.toggle("active");
            this.classList.toggle("caret-down");
        });
    }

    directoryCall();

    function directoryCall() {
        $("#cover-spin").show();
        var Dir = $("#dirPath").val();
        console.log('sent data');
        $.ajax({
            url: 'gitBackend.php',
            data: {
                Directory: Dir,
                Type: 'ReadFolder'
            },
            type: 'post',
            dataType: 'json',
            success: function(output) {
                console.log(output);



                if (output[0] == "Unauthorised access") {
                    alert("Unauthorised access");
                    $("#cover-spin").hide();
                    return;
                }

                var len = parseInt(output.length) - 1;
                len = parseInt(len);
                    
                console.log(output[len]);
                switch (output[len]) {
                    case "/home/etra/public_html/test/":
                        var i = 0;
                        var htm = "";
                        for (i = 0; i < (len); i++) {
                            htm += '<li><span class="caret" onclick="subDirectory(this);" data-name="' + output[len] + "/" + output[i] + '"><i class="fa fa-folder mr-2" style="color: #F8D775;" ></i>' + output[i] +
                                '</span><ul class="nested" id="' + output[i].replace(".", "") + '"></ul></li>';
                        }
                        $("#myUL").html(htm);
                        break;
                }
                $("#cover-spin").hide();
            }
        });

    }


    function subDirectory(e) {
        $("#cover-spin").show();
        $(e).toggleClass('caret-down');
        var Dir = $(e).attr('data-name');

        $.ajax({
            url: 'gitBackend.php',
            data: {
                Directory: Dir,
                Type: 'ReadFolder'
            },
            type: 'post',
            dataType: 'json',
            success: function(output) {

                if (output[0] == "Unauthorised access") {
                    alert("Unauthorised access");
                    $("#cover-spin").hide();
                    return;
                }
                // console.log(output);
                var len = parseInt(output.length) - 1;
                len = parseInt(len);

                var i = 0;
                var htm = "";
                var splitfileprop;
                var name;
                var lastModifed;
                for (i = 0; i < (len); i++) {
                    // console.log(output[i]);
                    if (output[i].includes("isFile")) {
                        splitfileprop = output[i].split("isFile");
                        name = splitfileprop[0];
                        lastModified = splitfileprop[1];
                     //  console.log(lastModified);
                     let style;
                     if(lastModified == "Modified"){
                        // style = 'style="color:red;cursor:pointer;"';
                        style = 'style="cursor:pointer;"';
                     }else{
                        style = 'style="cursor:pointer;"';
                     }
                        htm += '<li onclick="accessFile(this);" '+ style +' data-name="' + name + '" data-path="' + Dir + "/" + name + '"><i class="fa fa-file-o mr-2"></i>' + name + '</li>';
                    } else {
                        htm += '<li><span class="caret" onclick="subDirectory(this);" data-name="' + output[len] + "/" + output[i] + '"><i class="fa fa-folder mr-2" style="color: #F8D775;" ></i>' + output[i] +
                            '</span><ul class="nested" id="' + output[i] + '"></ul></li>';
                    }


                }
                Dir = /[^/]*$/.exec(Dir)[0];
                Dir = Dir.replace(".", "");
                $("#" + Dir).html(htm);
                $("#" + Dir).toggleClass('active');
                $("#cover-spin").hide();
            }
        });

    }

    function accessFile(e) {
        $("#cover-spin").show();
        var path = $(e).attr('data-path');
        $("#pathViewFileID").html(path);

        var name = $(e).attr('data-name');
        $("#hdngViewFileID").text(name);
        $.ajax({
            url: 'gitBackend.php',
            data: {
                Directory: path,
                Type: 'ReadFile'
            },
            type: 'post',
            dataType: 'json',
            success: function(output) {
                // console.log(output);

                if (output[0] == "Unauthorised access") {
                    alert("Unauthorised access");
                    $("#cover-spin").hide();
                    return;
                }

                $("#viewFileID").text(output[0]);
                $("#bqID").attr("style", "background: #231e1ee0;");
                $("#fileDivID").show();
                $("#cover-spin").hide();
            }

        });
        let encodedpath = window.btoa(path);

        $("#redirectCompareID").attr("href", "compare.php?cpath=" + encodedpath);
    }

    function clearBoard() {
        $("#hdngViewFileID").html("");
        $("#pathViewFileID").html("");

        $("#viewFileID").html("");
        $("#bqID").attr("style", "");
    }
</script>

</html>