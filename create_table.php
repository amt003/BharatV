<?php
include 'db.php';
if(!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}



// $t="TRUNCATE TABLE parties";
$t="CREATE TABLE IF NOT EXISTS candidate_applications(
application_id INT PRIMARY KEY AUTO_INCREMENT,
user_id INT,
FOREIGN KEY(user_id) REFERENCES users(user_id),
party_id INT,
FOREIGN KEY(party_id) REFERENCES parties(party_id),
application_form VARCHAR(255) NOT NULL,
ward_id INT,
FOREIGN KEY(ward_id) REFERENCES wards(ward_id),
application_status ENUM('pending','approved','rejected') DEFAULT 'pending',
submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ;";
// $t="Truncate TABLE parties
//  ;";
// $t="CREATE TABLE IF NOT EXISTS admin(
// admin_id INT PRIMARY KEY AUTO_INCREMENT ,
// name VARCHAR(255) not null,
// email  VARCHAR(255) not null unique,
// password varchar(255) not null)";


// -- Add index for faster token lookups
// CREATE INDEX idx_verification_token ON users(verification_token);
// $t="CREATE TABLE IF NOT EXISTS parties(
//     party_id INT PRIMARY KEY AUTO_INCREMENT,
//     party_name VARCHAR(100) UNIQUE NOT NULL,
//     party_symbol VARCHAR(255) NOT NULL,,
//     password VARCHAR(255) NOT NULL    );";
//     $t="CREATE TABLE IF NOT EXISTS users (
// user_id	INT	PRIMARY KEY AUTO_INCREMENT,
// name VARCHAR(100) NOT NULL,
// email VARCHAR(100) UNIQUE NOT NULL,
// dob DATE NOT NULL,
// password VARCHAR(255)	NOT NULL,
// role	ENUM('voter', 'candidate')	NOT NULL,
// phone VARCHAR(15)	NOT NULL,
// address	VARCHAR(255) NOT NULL,
// ward_id	INT,	
// FOREIGN KEY(ward_id) REFERENCES wards(ward_id),
// aadhaar_number VARCHAR(12) UNIQUE NOT NULL,
// aadhaar_file VARCHAR(255) NOT NULL,
// approved_by_admin	BOOLEAN	DEFAULT FALSE,
// created_at	TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// // );";
// $t = "INSERT INTO parties (party_name, party_symbol, password, email) VALUES
//     ('Independent Candidate', 'independent_symbol.png', '" . password_hash('independent123', PASSWORD_DEFAULT) . "', 'independent@gmail.com'),
//     ('Indian National Congress', 'congress_symbol.jpg', '" . password_hash('congress123', PASSWORD_DEFAULT) . "', 'congress@gmail.com'),
//     ('Bharatiya Janata Party', 'bjp_symbol.jpg', '" . password_hash('bjp123', PASSWORD_DEFAULT) . "', 'bjp@gmail.com'),
//     ('Communist Party of India', 'cpi_symbol.png', '" . password_hash('cpi123', PASSWORD_DEFAULT) . "', 'cpi@gmail.com')";
// $t="INSERT INTO admin (name, email, password) VALUES 
// ('Admin User', 'admin123@gmail.com','admin12345')";
// $t="ALTER TABLE parties ADD COLUMN email VARCHAR(255) NOT NULL unique";

    if(mysqli_query($conn,$t)){
        echo"<br>Table Created";
    }else{
        echo"<br>Table Not Created". mysqli_error($conn);
    }
    

   
mysqli_close($conn);
?>
