<?php

if (isset($_POST['user_id'])) {
    session_start();

    require_once 'DB.php';
    
    $user_id = $_POST['user_id'];
    
    // Verify match between incoming ID and session ID
    if ($_SESSION['ID'] == $user_id) {    
        /* Update current user ID status as Online */
        // Prepare, bind and execute
        $statement = $conn->prepare("UPDATE users SET status = 'online', last_seen = CURRENT_TIMESTAMP WHERE id = ?");
        $statement->bind_param("i", $user_id);
        $statement->execute();
        
        // Get messages query
        $sql = "SELECT user_id, name, created_at, TIME(created_at) AS time, message FROM users as u JOIN messages AS m ON u.id = m.user_id ORDER BY created_at DESC LIMIT 20;";
        // Store messages in Assoc Array
        if ($result = $conn->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $records[] = $row;
            }    
            echo json_encode($records);
        } else {
            echo "Error: " . $conn->error;
        }
    } /*else {
        echo json_encode("USER ID MISMATCH getting messages!");
    }*/
    
    $statement->close();
    $conn->close();
}

?>
