<?php

// Include the database connection file
include 'database.php';


header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");


// Check if the request is an OPTIONS request
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Respond to the preflight request
    header('Access-Control-Allow-Origin: *');
    header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    http_response_code(200);
    exit;
}

$skip = FALSE;

// Check if the request is a GET or POST request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // Extract the endpoint from the REQUEST_URI
    $endpoint = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Trim the leading slash from the endpoint
    $endpoint = ltrim($endpoint, '/');

    // Determine the endpoint and set the query accordingly
    switch ($endpoint) {
        case 'users.php/listAll':
            // Query to select all items from the database
            $query = "SELECT * FROM players";
            break;

        case 'users.php/teamSearch':
                // Extract the team name from the query parameters
                $teamName = $_GET['team']?? 'Any'; // Default to 'Any' if no team is specified
                // Query to select all users from the database where teamName matches the selected team
                $query = "SELECT * FROM players WHERE teamName = '$teamName'";
                break;

        case 'users.php/firstSearch':
            // Extract the team name from the query parameters
            $firstName = $_GET['firstName']?? 'Any';
            // Query to select all users from the database where firstName matches the selected team
            $query = "SELECT * FROM players WHERE firstName = '$firstName'";
            break;

        case 'users.php/specificSearch':
            // Extract the team name from the query parameters
            $teamName = $_GET['team']?? 'Any'; // Default to 'Any' if no team is specified
            $firstName = $_GET['firstName']?? 'Any';
            // Query to select all users from the database where teamName matches the selected team
            $query = "SELECT * FROM players WHERE teamName = '$teamName' AND firstName = '$firstName'";
            break;


        case 'users.php/login':
            // Extract the username and password from the query parameters
            $skip = TRUE;
            $userName = $_GET['user']?? '';
            $passWord = $_GET['pass']?? '';
            
            // Query to select the admin user from the database where username matches the provided credentials
            $query = "SELECT * FROM adminTable WHERE userStore = '$userName'";
            
            // Execute the query
            $result = $mysqli->query($query);
            
            // Check if the query was successful
            if ($result) {
                // Fetch the result as an associative array
                $data = $result->fetch_all(MYSQLI_ASSOC);
                
                // Assuming the hashed password is stored in the 'passStore' column
                $hashFromData = $data[0]['passStore']?? ''; // Fetch the hashed password from the first row of the result
                
                if (password_verify($passWord, $hashFromData)){
                    // Close the database connection
                    $mysqli->close();
                    
                    // Set the content type header
                    header('Content-Type: application/json');
                    
                    // Return a success message as JSON
                    echo json_encode(array('success' => 'Login successful'));
                } else {
                    // If password verification fails, return an error response
                    http_response_code(401); // Unauthorized
                    echo json_encode(array('error' => 'Invalid credentials'));
                }
            }
            else {
                // If the query fails, return an error response
                http_response_code(500);
                echo json_encode(array('error' => 'Failed to fetch data'));
            }
            break;
        
        default:
            // If the endpoint is not recognized, return a 404 error
            http_response_code(404);
            echo json_encode(array('error' => 'Endpoint not found'));
            exit();
            
    }

    if ($skip == FALSE){
        // Execute the query
        $result = $mysqli->query($query);

        // Check if the query was successful
        if ($result) {
            // Fetch the result as an associative array
            $data = $result->fetch_all(MYSQLI_ASSOC);

            // Close the database connection
            $mysqli->close();

            // Set the content type header
            header('Content-Type: application/json');

            // Return the data as JSON
            echo json_encode($data);
        } else {
            // If the query fails, return an error response
            http_response_code(500);
            echo json_encode(array('error' => 'Failed to fetch data'));
        }
    }
} 

elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle POST requests

    // Extract the endpoint from the REQUEST_URI
    $endpoint = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Trim the leading slash from the endpoint
    $endpoint = ltrim($endpoint, '/');

    // Determine the endpoint and set the query accordingly
    switch ($endpoint) {
        case 'users.php/userEntry':
            // Retrieve the POST data (First Name, Last Name, Game, and Team Name)
            $firstName = $_POST['firstName'];
            $lastName = $_POST['lastName'];
            $game = $_POST['game'];
            $teamName = $_POST['teamName'];

            // Validate the data (you should perform proper validation and authentication here)

            // Insert the data into the database
            $query = "INSERT INTO players (firstName, lastName, game, teamName) VALUES (?,?,?,?)";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("ssss", $firstName, $lastName, $game, $teamName);

            if ($stmt->execute()) {
                // If the query was successful, return a success message
                $response = array('message' => 'Data inserted successfully');
            } else {
                // If the query fails, return an error response
                $response = array('error' => 'Failed to insert data');
            }

            // Set the content type header
            header('Content-Type: application/json');

            // Return the response as JSON
            echo json_encode($response);

            $stmt->close();
            break;

        default:
            // If the endpoint is not recognized, return a 404 error
            http_response_code(404);
            echo json_encode(array('error' => 'Endpoint not found'));
            exit();
    }
}


elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Handle POST requests

    // Extract the endpoint from the REQUEST_URI
    $endpoint = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Trim the leading slash from the endpoint
    $endpoint = ltrim($endpoint, '/');

    // Determine the endpoint and set the query accordingly
    switch ($endpoint) {
        case 'users.php/userEdit':
            // Extract data from the request
            parse_str(file_get_contents("php://input"), $put_vars);
            $id = intval($put_vars['id']); // Ensure ID is treated as an integer
            $firstName = $put_vars['firstName'];
            $lastName = $put_vars['lastName'];
            $game = $put_vars['game'];
            $teamName = $put_vars['teamName'];

            $sql = "UPDATE players
                    SET
                        firstName = '$firstName',
                        lastName = '$lastName',
                        game = '$game',
                        teamName = '$teamName'
                    WHERE
                        ID = '$id'";

            // Prepare the SQL statement
            //$sql = "UPDATE players SET firstName=$firstName, lastName=$lastName, game=$game, teamName=$teamName WHERE ID=$id";

            
            // Prepare the statement
            if ($stmt = $mysqli->prepare($sql)) {
                // Bind the parameters

            
                // Execute the statement
                if ($stmt->execute()) {

                    // Return the response as JSON
                    echo "record added!";
                } else {
                    // Return the response as JSON
                    echo "error";
                }

                // Close the statement
                $stmt->close();
            } else {

                // Return the response as JSON
                echo "error";

            }

            // Close the connection
            $mysqli->close();
            break;

        default:
            // If the endpoint is not recognized, return a 404 error
            http_response_code(404);
            echo "error";
            exit();
    }
}
 

elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Handle DELETE requests

    // Extract the endpoint from the REQUEST_URI
    $endpoint = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    // Trim the leading slash from the endpoint
    $endpoint = ltrim($endpoint, '/');

    // Determine the endpoint and set the query accordingly
    switch ($endpoint) {
        case 'users.php/userDelete':
            // Extract data from the request
            parse_str(file_get_contents("php://input"), $delete_vars);
            $id = intval($delete_vars['id']); // Ensure ID is treated as an integer

            $sql = "DELETE FROM players WHERE ID = '$id'";

            // Prepare the SQL statement
            if ($stmt = $mysqli->prepare($sql)) {
                // Execute the statement
                if ($stmt->execute()) {
                    // Return the response as JSON
                    echo "record deleted!";
                } else {
                    // Return the response as JSON
                    echo "error";
                }

                // Close the statement
                $stmt->close();
            } else {
                // Return the response as JSON
                echo "error";
            }

            // Close the connection
            $mysqli->close();
            break;

        default:
            // If the endpoint is not recognized, return a 404 error
            http_response_code(404);
            echo "error";
            exit();
    }
}





 else {
    // If the request method is not GET or POST, return a method not allowed error
    http_response_code(405);
    echo json_encode(array('error' => 'Method not allowed'));
}

?>
