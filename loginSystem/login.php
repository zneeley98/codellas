<?php
// Initialize the session
session_start();

// Code for reCaptcha
// Set reCaptcha Variables
$reCaptchaVal = "";

// Check the post and see if ask Google what value the user is getting from interacting with the site
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Build POST request:
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_secret = '6Lc7Cb0UAAAAAEYFNQkPzlrav9ZspKcNV4OxR3he';
    $recaptcha_response = $_POST["recaptcha_response"];

    // Make and decode POST request:
    $recaptcha = file_get_contents($recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $recaptcha = json_decode($recaptcha);
    if($recaptcha->success==true){
    // Take action based on the score returned:
        if ($recaptcha->score >= 0.5) {
            $reCaptchaVal = "human";
        } else {
            // Redirect bot to index
            header("location: index.php");
        }
    }
}
 
// Check if the user is already logged in, if yes then redirect him to welcome page
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: welcome.php");
    exit;
}
 
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$username = $password = "";
$username_err = $password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if username is empty
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    // Check to see if the username is in the user table else see if its in the shelter table
    // Prepare a select statement
    $sql = "SELECT id FROM users WHERE username = ?";
    
    if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $username);
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Store results
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    // If username is in the user table 
                    if(empty($username_err) && empty($password_err) && $reCaptchaVal == "human"){
                        // Prepare a select statement
                        $sql = "SELECT id, username, password, userID FROM users WHERE username = ?";

                        if($stmt = mysqli_prepare($link, $sql)){
                            // Bind variables to the prepared statement as parameters
                            mysqli_stmt_bind_param($stmt, "s", $param_username);

                            // Set parameters
                            $param_username = $username;

                            // Attempt to execute the prepared statement
                            if(mysqli_stmt_execute($stmt)){
                                // Store result
                                mysqli_stmt_store_result($stmt);

                                // Check if username exists, if yes then verify password
                                if(mysqli_stmt_num_rows($stmt) == 1){                    
                                    // Bind result variables
                                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $userID);
                                    if(mysqli_stmt_fetch($stmt)){
                                        if(password_verify($password, $hashed_password)){
                                            // Password is correct, so start a new session
                                            session_start();

                                            // Store data in session variables
                                            $_SESSION["loggedin"] = true;
                                            $_SESSION["id"] = $id;
                                            $_SESSION["username"] = $username;
                                            $_SESSION["userID"] = $userID;
                                            $_SESSION["accountType"] = "user";

                                            // Redirect user to welcome page
                                            header("location: welcome.php");
                                        } else{
                                            // Display an error message if password is not valid
                                            $password_err = "The password you entered was not valid.";
                                        }
                                    }
                                } else{
                                    // Display an error message if username doesn't exist
                                    $username_err = "No account found with that username.";
                                }
                            } else{
                                echo "Oops! Something went wrong. Please try again later.";
                            }
                        }

                        // Close statement
                        mysqli_stmt_close($stmt);
                    }
                
                // Assume the user is in the shelters table
                } else {
                    // Assume the user is in the shelters table
                        if(empty($username_err) && empty($password_err) && $reCaptchaVal == "human"){
                        // Prepare a select statement
                        $sql = "SELECT id, username, password, shelterID FROM shelters WHERE username = ?";

                        if($stmt = mysqli_prepare($link, $sql)){
                            // Bind variables to the prepared statement as parameters
                            mysqli_stmt_bind_param($stmt, "s", $param_username);

                            // Set parameters
                            $param_username = $username;

                            // Attempt to execute the prepared statement
                            if(mysqli_stmt_execute($stmt)){
                                // Store result
                                mysqli_stmt_store_result($stmt);

                                // Check if username exists, if yes then verify password
                                if(mysqli_stmt_num_rows($stmt) == 1){                    
                                    // Bind result variables
                                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $shelterID);
                                    if(mysqli_stmt_fetch($stmt)){
                                        if(password_verify($password, $hashed_password)){
                                            // Password is correct, so start a new session
                                            session_start();

                                            // Store data in session variables
                                            $_SESSION["loggedin"] = true;
                                            $_SESSION["id"] = $id;
                                            $_SESSION["username"] = $username;
                                            $_SESSION["shelterID"] = $shelterID;
                                            $_SESSION["accountType"] = "shelter";

                                            // Redirect user to welcome page
                                            header("location: welcome.php");
                                        } else{
                                            // Display an error message if password is not valid
                                            $password_err = "The password you entered was not valid.";
                                        }
                                    }
                                } else{
                                    // Display an error message if username doesn't exist
                                    $username_err = "No account found with that username.";
                                }
                            } else{
                                echo "Oops! Something went wrong. Please try again later.";
                            }
                        }

                        // Close statement
                        mysqli_stmt_close($stmt);
                    }
                }
            }
        }

    // Close connection
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
    <script src="https://www.google.com/recaptcha/api.js?render=6Lc7Cb0UAAAAAIMgxbAXd9kLcVhLPeapc8zsouu7"></script>
</head>
<body>
    <div class="wrapper">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
            <input type="hidden" value="" name="recaptcha_response" id="recaptchaResponse"/><br>
        </form>
    </div>
    <script>
        grecaptcha.ready(function () {
            grecaptcha.execute('6Lc7Cb0UAAAAAIMgxbAXd9kLcVhLPeapc8zsouu7', { action: 'contact' })
                .then(function (token) {
                var recaptchaResponse = document.getElementById('recaptchaResponse');
                console.log(recaptchaResponse);
                recaptchaResponse.value = token;
            });
        });
    </script>       
</body>
</html>