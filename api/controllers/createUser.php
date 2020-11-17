<?php

// Proceed if required variables are set
if (isset($_POST['userName'])) {
  try {
    require_once "../business/User.php";

    // Clean html tags in user name input
    $user = htmlentities($_POST['userName']);

    $data = User::createUser($user);

    echo json_encode($data);
  } catch (Throwable $e) {
    echo json_encode(["Error" => $e->getMessage()]);
  }
} else {
  echo json_encode(["Error" => "No username provided..."]);
}

?>