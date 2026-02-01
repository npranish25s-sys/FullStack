<?php
$password_input = 'admin123';
$hash_from_db = '$2y$10$E9t1LQ7oF7l/O6v1gZ0J9u1x1d0CzgZQ8vYj8Ev1Cw1pQ1/6Bz0X2';

if (password_verify($password_input, $hash_from_db)) {
    echo "Login successful!";
} else {
    echo "Login failed!";
}
?>
