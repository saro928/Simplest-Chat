<?php

// Proceed if required variables are set
if (isset($_POST['current_room']) && isset($_POST['target_room']) && isset($_POST['user_id'])) {
  session_start();

  // Verify match between incoming ID and session ID AND match between incoming room ID and session room ID
  if ($_POST['user_id'] == $_SESSION['ID'] && $_POST['current_room'] == $_SESSION['room']) {        
    // Check if target room exists in Array of rooms the user belongs to
    if (in_array($_POST['target_room'], $_SESSION['rooms'])) {
      // Update session variable for Room
      $_SESSION['room'] = $_POST['target_room'];

      // This is just Info for the console
      $data['room'] = $_SESSION['room'];
      $data['rooms'] = $_SESSION['rooms'];

      // Send back new Current Room to the page
      echo json_encode($data);
    } else {
      echo json_encode(["Error" => "This User does not belong to the target Room..."]);
    }  
  } else {
    echo json_encode(["Error" => "Mismatch on session id or session Room..."]);
  }
} else {
  echo json_encode(["Error" => "Required data not provided..."]);
}

?>