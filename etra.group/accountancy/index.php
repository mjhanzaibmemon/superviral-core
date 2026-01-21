<?php

require_once '../sm-db.php';


if ($_POST['submit'] == "Create Account") {

    die('Authorised acccess only');
    
    $email = addslashes($_POST['email']);
    $password = addslashes($_POST['password']);
    $confirm_password = addslashes($_POST['confirm_password']);

    $added = time();

    if($password == $confirm_password) {

        if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $sqlCheck = "SELECT * FROM etra_accounts WHERE email ='$email'";
            $checkSql = mysql_query($sqlCheck);
            if(mysql_num_rows($checkSql) > 0){

                    $msg = "Account already exist, Login please or contact support";
            }else{
                $sql = "INSERT INTO etra_accounts (`email`, `password`, added, is_active) VALUES ('$email', '$hashed_password', $added, 0)";
    
                $runSql = mysql_query($sql);
                if ($runSql) {
                    $msg = "Account created successfully!, Please contact support to active your account";
                } else {
                    $msg = "Error: 4024";
                }
            }
    
           
    
        } else {
            $msg = "Invalid email or password.";
        }
    

        }else{
            $msg =  "Password & confirm password doesn't match";
        }
}

if($_POST['submit'] == "Login"){

    session_start(); 
    $email = addslashes($_POST['email']);
    $password = addslashes($_POST['password']);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($password)) {

        $sql = "SELECT * FROM etra_accounts WHERE email = '$email' AND is_active = 1 limit 1";
        $runSql = mysql_query($sql);

        // Check if a user with the entered email exists
        if (mysql_num_rows($runSql) > 0) {
            // Fetch user data
            $user = mysql_fetch_array($runSql);

            // Verify the password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];

                header("Location: /accountancy/checkstats/");
                exit();
            } else {
                $msg =  "Invalid password.";
            }
        } else {
            $msg =  "Please get in touch.";
        }

    } else {
        $msg = "Invalid email or password.";
    }
}


if(addslashes($_GET['type']) == 'login'){
    $style = "display:none;";
}else{
    $style1 = "display:none;";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Account</title>
</head>
<body style="display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;font-family: Arial, sans-serif;">

    <form action="" method="post" style="padding: 80px; border: 1px solid #ddd; border-radius: 5px; width: 250px; text-align: center;<?=$style?>" >
    
    <?= $msg ?>   
    <h2 style="margin-bottom: 20px;">Create Account</h2>

        <label for="email" style="display: block; margin-bottom: 10px;">Email:</label>
        <input type="email" id="email" name="email" required style="width: 100%; padding: 5px; margin-bottom: 15px;">

        <label for="password" style="display: block; margin-bottom: 10px;">Password:</label>
        <input type="password" id="password" name="password" required style="width: 100%; padding: 5px; margin-bottom: 20px;">

        <label for="password" style="display: block; margin-bottom: 10px;">Confirm Password:</label>
        <input type="password" id="password" name="confirm_password" required style="width: 100%; padding: 5px; margin-bottom: 20px;">

        <input type="submit" name="submit" value="Create Account" style="padding: 5px 0; width: 106%; border: 1px solid #ddd; border-radius: 5px; cursor: pointer;">
        <a href="?type=login" style="display: block; margin-bottom: 10px;margin-top:20px;">Login here</a>

    </form>

    <form action="" method="post" style="padding: 80px; border: 1px solid #ddd; border-radius: 5px; width: 250px; text-align: center;<?=$style1?>" >
    <?= $msg ?>   
    <h2 style="margin-bottom: 20px;">Login</h2>

        <label for="email" style="display: block; margin-bottom: 10px;">Email:</label>
        <input type="email" id="email" name="email" required style="width: 100%; padding: 5px; margin-bottom: 15px;">

        <label for="password" style="display: block; margin-bottom: 10px;">Password:</label>
        <input type="password" id="password" name="password" required style="width: 100%; padding: 5px; margin-bottom: 20px;">

        <input type="submit" name="submit" value="Login" style="padding: 5px 0; width: 106%; border: 1px solid #ddd; border-radius: 5px; cursor: pointer;">
        <a href="/accountancy/" style="display: block; margin-bottom: 10px;margin-top:20px;">Register</a>

    </form>

</body>
</html>

