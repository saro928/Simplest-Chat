<?php

// Proceed if required variables are set
if (isset($_POST['user_id'])) {
    session_start();

    require_once 'DB.php';
    
    // Verify match between incoming ID and session ID
    if ($_POST['user_id'] == $_SESSION['ID']) {
        // Clear Session Rooms
        $_SESSION['rooms'] = array();
        $id = $_POST['user_id'];
        // Get online users query
        $statement = $conn->prepare("SELECT DISTINCT room_id FROM chat_rooms_has_users WHERE user_id = ?");
        $statement->bind_param("i", $id);
        $statement->execute();
        $result = $statement->get_result();
        // Store online users in Assoc Array    
        while ($row = $result->fetch_assoc()) {
            $rooms[] = $row;
            // Store Rooms where user belongs in Session Variable as sequential array
            array_push($_SESSION['rooms'], $row['room_id']);
        }
        $statement->close();  
        echo json_encode($rooms);
    } /*else {
        echo json_encode("USER ID MISMATCH getting online users!");
    }*/    
    
    $conn->close();
}

?>