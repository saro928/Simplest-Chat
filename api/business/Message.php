<?php

class Message {
  public static function getMessages($user_id, $room_id) {
    require_once '../database/DB.php';

    /* Update current user ID status as Online */
    // Prepare, bind and execute
    $statement = $conn->prepare("UPDATE users SET status = 'online', last_seen = CURRENT_TIMESTAMP WHERE id = ?");
    $statement->bind_param("i", $user_id);
    $statement->execute();        
    
    // Get messages query
    $statement = $conn->prepare("SELECT user_id, name, created_at, TIME(created_at) AS time, message FROM users as u JOIN messages AS m ON u.id = m.user_id WHERE room_id = ? ORDER BY created_at DESC LIMIT 20");
    $statement->bind_param("i", $room_id);
    $statement->execute();

    // Store messages in Assoc Array
    if ($result = $statement->get_result()) {
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          $records[] = $row;
        }
        $statement->close();
        $conn->close();

        return $records;
      } else {
        $statement->close();
        $conn->close();

        return "0 Messages";
      }            
    } else {
      $statement->close();
      $conn->close();

      return "Error: " . $conn->error;           
    }
  }

  public static function postMessage($message, $user_id, $room_id) {
    require_once '../database/DB.php';

    // Prepare and bind
    $statement = $conn->prepare("INSERT INTO messages (message, user_id, room_id) VALUES (?, ?, ?)");
    $statement->bind_param("sii", $message, $user_id, $room_id);
    
    if ($statement->execute()) {        
      $data = "Message Sent Successfully!";
    } else {
      $data = "Error: " . $statement->error;
    }

    $statement->close();
    $conn->close();

    return $data;
  }
}

?>