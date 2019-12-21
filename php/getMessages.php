<?php

// Proceed if required variables are set
if (isset($_POST['user_id']) && isset($_POST['room_id'])) {
    session_start();

    require_once 'DB.php';
    
    $user_id = $_POST['user_id'];
    $room_id = $_POST['room_id'];
    
    // Verify match between incoming ID and session ID AND match between incoming room ID and session room ID
    if ($_SESSION['ID'] == $user_id && $_SESSION['room'] == $room_id) {    
        /* Update current user ID status as Online */
        // Prepare, bind and execute
        $statement = $conn->prepare("UPDATE users SET status = 'online', last_seen = CURRENT_TIMESTAMP WHERE id = ?");
        $statement->bind_param("i", $user_id);
        $statement->execute();        
        
        // Get messages query
        $statement = $conn->prepare("SELECT user_id, name, created_at, TIME(created_at) AS time, message FROM users as u 
        JOIN messages AS m ON u.id = m.user_id WHERE room_id = ? ORDER BY created_at DESC LIMIT 20");
        $statement->bind_param("i", $room_id);
        $statement->execute();

        // Store messages in Assoc Array
        if ($result = $statement->get_result()) {
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $records[] = $row;
                }    
                echo json_encode($records);
            } else {
                $error = array(                    
                    "message" => "0 Messages Found!"
                );
                //echo json_encode($error);
                echo json_encode("0 Messages");
            }            
        } else {
            echo "Error: " . $conn->error;           
        }
        $statement->close();        
    } /*else {
        echo json_encode("USER ID MISMATCH getting messages!");
    }*/    
    $conn->close();
} /*else {
    echo json_encode("Variables NOT set!");
}*/

?>
