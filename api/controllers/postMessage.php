<?php

// Proceed if required variables are set and message text is not empty or contain spaces only
if (isset($_POST['user_id']) && isset($_POST['message']) && isset($_POST['room_id']) && !ctype_space($_POST['message'])) {
  session_start();

  // Clean html tags in message
  $message = htmlentities($_POST['message']);
  $user_id = $_POST['user_id'];
  $room_id = $_POST['room_id'];
  
  // Verify match between incoming ID and session ID AND match between incoming room ID and session room ID
  if ($_SESSION['ID'] == $user_id && $_SESSION['room'] == $room_id) {
    try {
      require_once "../business/Message.php";
  
      $data = Message::postMessage($message, $user_id, $room_id);
  
      echo json_encode($data);
    } catch (Throwable $e) {
      echo json_encode(["Error" => $e->getMessage()]);
    }
  } else {
    echo json_encode(["Error" => "USER ID MISMATCH sending message!"]);
  }
} else {
  echo json_encode(["Error" => "Required data not provided..."]);
}

?>