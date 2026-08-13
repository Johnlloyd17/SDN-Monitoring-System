<!DOCTYPE html>
<html>
<?php
session_start();
include "../connection.php";

$message = '';
$message_type = '';

if(isset($_POST['btn_login']))
{ 
    $username = $_POST['txt_username'];
    $password = $_POST['txt_password'];

    $user = mysqli_query($con, "SELECT * from tblzone where username = '$username' and password = '$password' ");
    $numrow_user = mysqli_num_rows($user);

    if($numrow_user > 0)
    {
        $row = mysqli_fetch_array($user);
        $_SESSION['role'] = "Zone Leader";
        $_SESSION['userid'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $message = "Login successful! Redirecting...";
        $message_type = "success";
    }
    else
    {
        $message = "Invalid username or password.";
        $message_type = "error";
    }
}
?>
<head>
    <meta charset="UTF-8">
    <title>Barangay Information System</title>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <link href="../../css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="../../css/AdminLTE.css" rel="stylesheet" type="text/css" />
    <style>
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
    <div class="container" style="margin-top:30px">
      <div class="col-md-4 col-md-offset-4">
          <div class="panel panel-default">
        <div class="panel-heading" style="text-align:center;">
            <img src="../../img/logo.png" style="height:150px;"/>
          <h3 class="panel-title">
            <strong>
                Barangay Information System
            </strong>
          </h3>
        </div>
        <div class="panel-body">
          <form role="form" method="post">
            <div class="form-group">
              <label for="txt_username">Username</label>
              <input type="text" class="form-control" style="border-radius:0px" name="txt_username" id="txt_username" placeholder="Enter Username" autocomplete="username">
            </div>
            <div class="form-group">
              <label for="txt_password">Password</label>
              <input type="password" class="form-control" style="border-radius:0px" name="txt_password" id="txt_password" placeholder="Enter Password" autocomplete="current-password">
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

    <?php if($message_type === 'success'): ?>
    <script>
        setTimeout(function() {
            window.location.href = "../permit/permit.php";
        }, 1500);
    </script>
    <?php endif; ?>
</body>
</html>
