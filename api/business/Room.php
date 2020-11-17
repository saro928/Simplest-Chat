<?php

class Room {
  public static function createRoom($user_id, $users) {
    require_once '../database/DB.php';

    // Create a new Room in DB
    $sql = "INSERT INTO chat_rooms (room_id) VALUES (0)";
    $conn->query($sql);

    // Get ID of last created Room
    $id_query = "SELECT LAST_INSERT_ID()";
    $result = $conn->query($id_query);
    if ($row = $result->fetch_array()) {
      $new_room_id = $row['LAST_INSERT_ID()'];
      $data['room'] = $new_room_id;        
    }

    // prepare and bind
    $statement = $conn->prepare("INSERT INTO chat_rooms_has_users (user_id, room_id) VALUES (?, ?)");
    $statement->bind_param("ii", $user_to_insert, $new_room_id);

    // First insert the user who created the room
    $user_to_insert = $user_id;
    $statement->execute();
    
    // Insert users in new chat room
    foreach ($users as $user) {
      // Avoid insertion if the creator of the room selected his own name
      if ($user != $user_id) {
        $user_to_insert = $user;
        $statement->execute();
      }            
    }
    
    $statement->close();  

    $conn->close();

    // Send back
    return $data;
  }

  public static function getRooms($user_id) {
    require_once '../database/DB.php';
    
    // Clear Session Rooms
    $_SESSION['rooms'] = array();

    // Get Rooms query
    $statement = $conn->prepare("SELECT DISTINCT room_id FROM chat_rooms_has_users WHERE user_id = ?");
    $statement->bind_param("i", $user_id);
    $statement->execute();
    $result = $statement->get_result();

    // Store Rooms in Assoc Array    
    while ($row = $result->fetch_assoc()) {
      $rooms[] = $row;
      // Store Rooms where user belongs in Session Variable as sequential array
      array_push($_SESSION['rooms'], $row['room_id']);
    }

    $statement->close();
  
    $conn->close();

    return $rooms;
  }
}

?>