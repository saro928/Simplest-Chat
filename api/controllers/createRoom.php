<?php

// Proceed if required variables are set
if (isset($_POST['users']) && isset($_POST['user_id']) && isset($_POST['room_id'])) {
  session_start();

  $users = $_POST['users'];
  $user_id = $_POST['user_id'];
  $room_id = $_POST['room_id'];
  
  // Verify match between incoming ID and session ID AND match between incoming room ID and session room ID
  if ($_SESSION['ID'] == $user_id && $_SESSION['room'] == $room_id) {
    try {
      require_once "../business/Room.php";
  
      $data = Room::createRoom($user_id, $users);
  
      echo json_encode($data);
    } catch (Throwable $e) {
      echo json_encode(["Error" => $e->getMessage()]);
    }
  } else {
    echo json_encode(["Error" => "USER ID MISMATCH with Session ID!"]);
  }  
} else {
  echo json_encode(["Error" => "No user_id or room_id provided..."]);
}

?>