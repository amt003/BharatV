$alter_query = "ALTER TABLE elections 
                ADD COLUMN ward_id INT NOT NULL AFTER Description,
                ADD FOREIGN KEY (ward_id) REFERENCES wards(ward_id)";

if ($conn->query($alter_query) === TRUE) {
    echo "Table modified successfully";
} else {
    echo "Error modifying table: " . $conn->error;
}