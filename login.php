<!DOCTYPE html>
<html>
<?php
session_start();
include "pages/connection.php";

$message = '';
$message_type = '';

if(isset($_POST['btn_login'])) { 
    $username = mysqli_real_escape_string($con, $_POST['txt_username']);
    $password = mysqli_real_escape_string($con, $_POST['txt_password']);

    $admin = mysqli_query($con, "SELECT * FROM tbluser WHERE username = '$username' AND password = '$password' AND type = 'administrator'");
    $numrow_admin = mysqli_num_rows($admin);

    $staff = mysqli_query($con, "SELECT * FROM tblstaff WHERE username = '$username' AND password = '$password'");
    $numrow_staff = mysqli_num_rows($staff);

    if ($numrow_admin > 0) {
        $row = mysqli_fetch_array($admin);
        $_SESSION['role'] = "Administrator";
        $_SESSION['userid'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $message = "Login successful! Redirecting...";
        $message_type = "success";
    } elseif ($numrow_staff > 0) {
        $row = mysqli_fetch_array($staff);
        $_SESSION['role'] = $row['name'];
        $_SESSION['staff'] = "staff";
        $_SESSION['userid'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $message = "Login successful! Redirecting...";
        $message_type = "success";
    } else {
        $message = "Invalid username or password.";
        $message_type = "error";
    }
}
?>
<head>
    <meta charset="UTF-8">
    <title>DICT-SDN Activity Management and Monitoring System</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <style>
        body {
            position: relative;
            margin: 0;
            height: 100vh;
            overflow: hidden;
            background: url('img/dict1.jpeg') no-repeat center center fixed;
            background-size: cover;
        }
        .centered-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
            z-index: 1;
        }
        .blurred-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            z-index: 0;
        }
        .panel {
            border-radius: 5px;
            border: 1px solid #fff;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.7);
        }
        .alert-msg {
            padding: 10px 15px;
            margin-top: 10px;
            border-radius: 4px;
            font-size: 14px;
            display: none;
        }
        .alert-msg.error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
            display: block;
        }
        .alert-msg.success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
            display: block;
        }
    </style>
</head>
<body class="skin-black">
    <div class="blurred-background"></div>

    <div class="centered-container">
        <div class="container">
            <div class="col-md-4 col-md-offset-4">
                <div class="panel panel-default">
                    <div class="panel-heading" style="text-align:center;">
                        <img src="img/logofinal.png" style="height:150px;"/>
                        <h3 class="panel-title">
                            <strong>
                                DICT-SDN Monitoring System
                            </strong>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <form role="form" method="post">
                            <div class="form-group">
                                <label for="txt_username">Username</label>
                                <input type="text" class="form-control" style="border-radius:0px" name="txt_username" id="txt_username" placeholder="Enter Username" required autocomplete="username">
                            </div>
                            <div class="form-group">
                                <label for="txt_password">Password</label>
                                <input type="password" class="form-control" style="border-radius:0px" name="txt_password" id="txt_password" placeholder="Enter Password" required autocomplete="current-password">
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary" name="btn_login">Log in</button>
                            <?php if($message !== ''): ?>
                                <div class="alert-msg <?php echo $message_type; ?>"><?php echo $message; ?></div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if($message_type === 'success'): ?>
    <script>
        setTimeout(function() {
            window.location.href = "pages/dashboard/dashboard.php";
        }, 1500);
    </script>
    <?php endif; ?>
</body>
</html>
