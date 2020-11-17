<?php

// Proceed if required variables are set
if (isset($_POST['user_id']) && isset($_POST['room_id'])) {
  session_start();    

  $room_id = $_POST['room_id'];
  
  // Verify match between incoming ID and session ID AND match between incoming room ID and session room ID
  if ($_POST['user_id'] == $_SESSION['ID'] && $room_id == $_SESSION['room']) {
    try {
      require_once "../business/User.php";
  
      $data = User::getOnlineUsers($room_id);
  
      echo json_encode($data);
    } catch (Throwable $e) {
      echo json_encode(["Error" => $e->getMessage()]);
    }
  } else {
    echo json_encode(["Error" => "USER ID MISMATCH getting online users!"]);
  }    
} else {
  echo json_encode(["Error" => "No user_id or room_id provided..."]);
}

?>