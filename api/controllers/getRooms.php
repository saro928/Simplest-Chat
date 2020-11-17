<?php

// Proceed if required variables are set
if (isset($_POST['user_id'])) {
  session_start();
  
  // Verify match between incoming ID and session ID
  if ($_POST['user_id'] == $_SESSION['ID']) {
    try {
      require_once "../business/Room.php";
  
      $data = Room::getRooms($_POST['user_id']);
  
      echo json_encode($data);
    } catch (Throwable $e) {
      echo json_encode(["Error" => $e->getMessage()]);
    }
  } else {
    echo json_encode(["Error" => "USER ID MISMATCH with session..."]);
  }
} else {
  echo json_encode(["Error" => "Required data not provided..."]);
}

?>