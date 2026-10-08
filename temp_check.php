<?php require "pages/connection.php"; $result=mysqli_query($con,"DESCRIBE inventory"); while($r=mysqli_fetch_assoc($result)){echo $r["Field"]."\n";} ?>
