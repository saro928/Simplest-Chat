<?php

// Proceed if required variables are set
if (isset($_POST['user_id']) && isset($_POST['room_id'])) {
  session_start();
  
  $user_id = $_POST['user_id'];
  $room_id = $_POST['room_id'];
  
  // Verify match between incoming ID and session ID AND match between incoming room ID and session room ID
  if ($_SESSION['ID'] == $user_id && $_SESSION['room'] == $room_id) {    
    try {
      require_once "../business/Message.php";
  
      $data = Message::getMessages($user_id, $room_id);
  
      echo json_encode($data);
    } catch (Throwable $e) {
      echo json_encode(["Error" => $e->getMessage()]);
    }
  } else {
    echo json_encode("USER ID MISMATCH getting messages!");
  }
} else {
  echo json_encode(["Error" => "Required data not provided..."]);
}

?>
