<?php
include "../config.php";
session_start();

$user_name = $mobile = $email = $user_id = $password = $confirm_password = "";
$user_name_err = $mobile_err = $email_err = $user_id_err = $password_err = $confirm_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $error = 0;

    if (empty($_POST["user_name"])) {
        $user_name_err = "user_name is required.";
        $error = 1;
    } else {
        $user_name = test_input($_POST["user_name"]);
        if (!preg_match("/^[a-zA-Z-' ]*$/", $user_name)) {
            $user_name_err = "Only letters and spaces allowed.";
            $error = 1;
        }
    }

    if (empty($_POST["mobile"])) {
        $mobile_err = "Mobile number is required.";
        $error = 1;
    } else {
        $mobile = test_input($_POST["mobile"]);
        if (!preg_match("/^(91)[6789]\d{9}$/", $mobile)) {
            $mobile_err = "Invaid format";
            $error = 1;
        } else {
            $stmt = $conn->prepare("SELECT id FROM register WHERE mobile_no = ?");
            $stmt->bind_param("s", $mobile);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $mobile_err = "Mobile number already registered.";
                $error = 1;
            }
            $stmt->close();
        }
    }

    if (empty($_POST["email"])) {
        $email_err = "Email is required";
        $error = 1;
    } else {
        $email = test_input($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Invaid format";
            $error = 1;
        } else {
            $stmt = $conn->prepare("SELECT id FROM register WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $email_err = "This Email already registered.";
                $error = 1;
            }
            $stmt->close();

        }
    }

    if (empty($_POST["user_id"])) {
        $user_id_err = "User ID is required.";
        $error = 1;
    } else {
        $user_id = test_input($_POST["user_id"]);
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $user_id)) {
            $user_id_err = "User ID must be 3-20 characters, using letters, numbers, or underscore.";
            $error = 1;
        } else {
            $stmt = $conn->prepare("SELECT id FROM register WHERE user_id = ?");
            $stmt->bind_param("s", $user_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $user_id_err = "User ID already registered.";
                $error = 1;
            }
            $stmt->close();
        }
    }

    if (empty($_POST["password"])) {
        $password_err = "Password is required.";
        $error = 1;
    } else {
        $password = test_input($_POST["password"]);
        if (strlen($password) < 6) {
            $password_err = "Password must be at least 6 characters.";
            $error = 1;
        }
    }

    if (empty($_POST["confirm_password"])) {
        $confirm_password_err = "Please confirm password.";
        $error = 1;
    } else {
        $confirm_password = test_input($_POST["confirm_password"]);
        if ($password != $confirm_password) {
            $confirm_password_err = "Passwords do not match.";
            $error = 1;
        }
    }

    if ($error === 0) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO register (user_name, mobile_no, email, user_id, password_hash) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $user_name, $mobile, $email, $user_id, $password_hash);

        if ($stmt->execute()) {
            $user_id_from_db = $conn->insert_id; // the new user's ID

            // Generate a unique channel name based on user_name
            $base_channel_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $user_name);
            if (empty($base_channel_name)) {
                $base_channel_name = 'user' . $user_id_from_db;
            }
            $channel_name = $base_channel_name;
            $counter = 1;

            // Ensure channel_name is unique (because of UNIQUE constraint)
            while (true) {
                $check_sql = "SELECT id FROM channels WHERE channel_name = ?";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("s", $channel_name);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                if ($check_result->num_rows == 0) {
                    $check_stmt->close();
                    break;
                }
                $check_stmt->close();
                $channel_name = $base_channel_name . '_' . $counter;
                $counter++;
            }

            // Insert the channel
            $channel_sql = "INSERT INTO channels (user_id, channel_name, profile_pic) VALUES (?, ?, ?)";
            $channel_stmt = $conn->prepare($channel_sql);
            $default_pic = null; // or 'images/default-channel.png'
            $channel_stmt->bind_param("iss", $user_id_from_db, $channel_name, $default_pic);

            if ($channel_stmt->execute()) {
                $channel_id = $conn->insert_id;

                // Create folder structure for this channel
                $base_dir = "uploads/" . $channel_name . "/";
                $subdirs = [
                    'channel_pic',
                    'videos',
                    'videos/thumbnails',
                    'shorts',
                    'shorts/thumbnails'
                ];
                foreach ($subdirs as $sub) {
                    $path = $base_dir . $sub;
                    if (!is_dir($path)) {
                        mkdir($path, 0755, true);
                    }
                }
            }
            $channel_stmt->close();

            // Registration successful, redirect to login
            $_SESSION['registered'] = true;
            header("Location: login.php");
            exit;
        } else {
            $_SESSION['registered'] = false;
            echo "Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
}

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


?>

<!DOCTYPE html>
<html>

<head>
    <title>Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../images/logo-tab.png">
    <script src="js/jquery.min.js"></script>
</head>

<body>
    <div class="form-container">
        <div class="left">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <a href="../index.php"><i class="fas fa-home"></i></a>
                <h1 class="gradient-text"><b>Register</b></h1>

                <label for="user_name">User Name:</label>
                <span class="error">
                    <?php echo $user_name_err; ?>
                </span>
                <input type="text" id="user_name" name="user_name" placeholder="Enter your User Name"
                    value="<?php echo $user_name; ?>"><br><br>

                <label for="mobile">Mobile Number:</label>
                <span class="error">
                    <?php echo $mobile_err; ?>
                </span>
                <input type="text" id="mobile" maxlength="12" name="mobile" placeholder="Enter your mobile number"
                    value="<?php echo $mobile; ?>">
                <h5>(format: 91[6 or 7 or 8 or 9]xxxxxxxxx)</h5>

                <label for="email">Email:</label>
                <span class="error">
                    <?php echo $email_err; ?>
                </span>
                <input type="text" id="email" name="email" placeholder="Enter your email"
                    value="<?php echo $email; ?>"><br><br>

                <label for="User_ID">User ID:</label>
                <span class="error">
                    <?php echo $user_id_err; ?>
                </span>
                <input type="text" id="User_ID" name="user_id" placeholder="Enter your user id"
                    value="<?php echo $user_id; ?>"><br><br>

                <label for="password">Password:</label>
                <span class="error">
                    <?php echo $password_err; ?>
                </span>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    <i class="far fa-eye" id="togglePassword"></i>
                </div>

                <label for="confirm_password">Confirm Password:</label>
                <span class="error">
                    <?php echo $confirm_password_err; ?>
                </span>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password"
                        placeholder="Confirm your password" required>
                    <i class="far fa-eye" id="toggleConfirmPassword"></i>
                </div>

                <h4 style="text-align: center;">Already Registered? <a href="login.php">Login</a></h4>

                <button type="submit" class="btn">Submit</button>
            </form>
        </div>
        <div class="right">
            <img src="../images/man with register.png" alt=" register">
        </div>
    </div>
    <script>

        document.addEventListener('DOMContentLoaded', function () {
            // Password field toggle
            const togglePassword = document.getElementById('togglePassword');
            const password = document.getElementById('password');
            if (togglePassword && password) {
                togglePassword.addEventListener('click', function () {
                    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                    password.setAttribute('type', type);
                    this.classList.toggle('fa-eye-slash');
                });
            }

            // Confirm Password field toggle
            const toggleConfirm = document.getElementById('toggleConfirmPassword');
            const confirmPassword = document.getElementById('confirm_password');
            if (toggleConfirm && confirmPassword) {
                toggleConfirm.addEventListener('click', function () {
                    const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
                    confirmPassword.setAttribute('type', type);
                    this.classList.toggle('fa-eye-slash');
                });
            }
        });
    </script>
</body>

</html>