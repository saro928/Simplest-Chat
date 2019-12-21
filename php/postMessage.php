<?php

// Proceed if required variables are set and message text is not empty or contain spaces only
if (isset($_POST['user_id']) && isset($_POST['message']) && isset($_POST['room_id']) && !ctype_space($_POST['message'])) {
    session_start();

    require_once 'DB.php';    
    
    // Clean html tags in message
    $message = htmlentities($_POST['message']);
    $id = $_POST['user_id'];
    $room = $_POST['room_id'];
    
    // Verify match between incoming ID and session ID AND match between incoming room ID and session room ID
    if ($_SESSION['ID'] == $id && $_SESSION['room'] == $room) {
        // Prepare and bind
        $statement = $conn->prepare("INSERT INTO messages (message, user_id, room_id) VALUES (?, ?, ?)");
        $statement->bind_param("sii", $message, $id, $room);
        
        if ($statement->execute()) {        
            echo "Message Sent Successfully!";
        } else {
            echo "Error: " . $statement->error;
        }
        $statement->close();
    } /*else {
        echo "USER ID MISMATCH sending message!";
    }*/    
    
    $conn->close();
}

?>