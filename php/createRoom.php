<?php

// Proceed if required variables are set
if (isset($_POST['users']) && isset($_POST['user_id']) && isset($_POST['room_id'])) {
    session_start();

    require_once 'DB.php';

    $users = $_POST['users'];
    $user_id = $_POST['user_id'];
    $room_id = $_POST['room_id'];
    
    // Verify match between incoming ID and session ID AND match between incoming room ID and session room ID
    if ($_SESSION['ID'] == $user_id && $_SESSION['room'] == $room_id) {
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
        
        // Send back
        echo json_encode($data);
        $statement->close();  
    }   
    $conn->close();
}

?>