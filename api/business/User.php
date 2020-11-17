<?php

class User {
  public static function createUser($username) {
    session_start();
    require_once '../database/DB.php';
    
    // Prepare and bind
    $statement = $conn->prepare("INSERT INTO users (name, status) VALUES (?, 'online')");
    $statement->bind_param("s", $username);    

    if ($statement->execute()) {    
      // When a new user is created a Trigger in the DB inserts their user_id inside chat_rooms_has_users table in room # 1, General Room, so we start the chat inside that room
      $_SESSION['room'] = 1;
      $data['room'] = $_SESSION['room'];

      $id_query = "SELECT LAST_INSERT_ID()";
      $result = $conn->query($id_query);
      if ($row = $result->fetch_array()) {
        $data['id'] = $row['LAST_INSERT_ID()'];    
      }
      // Create ID session variable for validation in all php files
      $_SESSION['ID'] = $data['id'];        
      // Send back user id and room id in Array
      $statement->close();

      $conn->close();

      return $data;
    } else {
      $conn->close();
      return "Error: " . $statement->error;
    }
  }

  public static function getOnlineUsers($room_id) {
    require_once '../database/DB.php';

    // Get online users query
    $statement = $conn->prepare("SELECT u.id, name FROM users AS u JOIN chat_rooms_has_users AS c ON u.id = c.user_id WHERE status = 'online' AND room_id = ? ORDER BY name");
    $statement->bind_param("i", $room_id);
    $statement->execute();
    $result = $statement->get_result();

    // Store online users in Assoc Array
    $users = array();
    $ids = array();  
    while ($row = $result->fetch_assoc()) {             
      array_push($users, $row);            
      array_push($ids, $row['id']);
    }        

    // Array with id and name
    $data['users'] = $users;
    // Array with only ids to compare with array of ids of current users in page
    $data['ids'] = $ids;
    $statement->close();

    $conn->close();

    return $data;
  }
}

?>