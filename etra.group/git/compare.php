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

    <link rel="stylesheet" href="diffChecker/example/styles.css" type="text/css" charset="utf-8" />



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



        <div>

            <div class="row">



                <div class="col-md-12">



                    <h3 style="cursor:pointer;" id="hdngViewTestFileID"></h3>
                    <h6 style="cursor:pointer;" id="pathViewTestFileID"></h6>



                </div>



            </div>

            <div id="bindDiffCode"></div>





        </div>















    </div>



    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>



</body>







<script>

    accessFile();







    function accessFile() {



        $("#cover-spin").show();



        var qString = getParameterByName('cpath');



        var path = window.atob(qString);
        $("#pathViewTestFileID").text(path);



        let fName = /[^/]*$/.exec(path)[0];



        $("#hdngViewTestFileID").text(fName);





        $.ajax({



            url: 'gitBackend.php',



            data: {



                Directory: path,



                Type: 'CompareFile',



                PageType: 'Compare'



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



                if (output[0] == "File Restricted") {

                    alert("File Restricted");

                    $("#cover-spin").hide();

                    return;

                }



                if (output[0] == "") {

                    $("#bindDiffCode").html("<h3 style='color:#64f20b;'>No difference in file</h3>");

                } else {

                    $("#bindDiffCode").html(output[0]);

                }





                $("#cover-spin").hide();



            }







        });



    }



	 function copyFiles(e) {

        $("#cover-spin").show();

        var copyType = $(e).attr("data-tag");

        var qString = getParameterByName('cpath');

        var path = window.atob(qString);

        let fName = /[^/]*$/.exec(path)[0];

        $("#hdngViewTestFileID").text(fName);
        $("#pathViewTestFileID").text(path);


        $.ajax({

            url: 'gitBackend.php',



            data: {

                Directory: path,
                CopyType: copyType,

                Type: 'CopyFile',

            },



            type: 'post',



            dataType: 'json',



            success: function(output) {

                if(output[0] == true){
                    alert("copied successfully");
                    location.reload();

                }else{
                    alert("copied failed");
                }


                $("#cover-spin").hide();
            }


        });

    }


    function getParameterByName(name, url = window.location.href) {



        name = name.replace(/[\[\]]/g, '\\$&');



        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),



            results = regex.exec(url);



        if (!results) return null;



        if (!results[2]) return '';



        return decodeURIComponent(results[2].replace(/\+/g, ' '));



    }

</script>







</html>