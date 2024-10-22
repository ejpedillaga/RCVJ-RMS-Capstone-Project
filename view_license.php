<?php
include 'connection.php'; 
$conn = connection();

if (isset($_GET['userid']) && isset($_GET['licenseIndex'])) {
    $userid = $_GET['userid'];
    $licenseIndex = $_GET['licenseIndex'];

    // Fetch the attachment for the specific license based on userid and index
    $query = "SELECT attachment FROM certification_license_table WHERE userid = ? ORDER BY year_issued DESC LIMIT ?, 1"; // Adjust the query as needed
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $userid, $licenseIndex);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $attachment = $row['attachment']; // Assuming this is the column name for the image attachment

        // Convert the binary data to base64
        $imageData = base64_encode($attachment);
        $src = 'data:image/jpeg;base64,' . $imageData; // Change 'jpeg' if your image format is different

        // Output HTML for the image
        echo '<html>
                <head>
                <title>View License Attachment</title>
                    <style>
                        body {
                            display: flex;
                            justify-content: center;
                            align-items: center; 
                            height: 100vh; 
                            margin: 0; 
                            background-color: rgba(0, 0, 0, 0.85); 
                        }
                        img {
                            border-radius: 10px; 
                            height: 500px; 
                            object-fit: cover; 
                            box-shadow: rgba(0, 0, 0, 0.07) 0px 1px 2px, rgba(0, 0, 0, 0.07) 0px 2px 4px,
                                        rgba(0, 0, 0, 0.07) 0px 4px 8px, rgba(0, 0, 0, 0.07) 0px 8px 16px,
                                        rgba(0, 0, 0, 0.07) 0px 16px 32px, rgba(0, 0, 0, 0.07) 0px 32px 64px;
                        }
                    </style>
                </head>
                <body>
                    <img src="' . $src . '" alt="License Image">
                </body>
              </html>';
    } else {
        echo "License not found.";
    }
} else {
    echo "Invalid request.";
}
?>