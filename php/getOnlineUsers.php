<?php

// Proceed if required variables are set
if (isset($_POST['user_id']) && isset($_POST['room_id'])) {
    session_start();

    require_once 'DB.php';

    $room_id = $_POST['room_id'];
    
    // Verify match between incoming ID and session ID AND match between incoming room ID and session room ID
    if ($_POST['user_id'] == $_SESSION['ID'] && $room_id == $_SESSION['room']) {
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
        echo json_encode($data);
    } else {
        echo json_encode("USER ID MISMATCH getting online users!");
    }    
    
    $conn->close();
}

?>