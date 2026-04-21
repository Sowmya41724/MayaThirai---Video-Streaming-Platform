<?php
include "../config.php";
session_start();

$user_name = $password = "";
$user_name_err = $password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $error = 0;

    if (empty($_POST["user_name"])) {
        $user_name_err = "username is required.";
        $error = 1;
    } else {
        $user_name = test_input($_POST["user_name"]);
    }

    if (empty($_POST["password"])) {
        $password_err = "Password is required.";
        $error = 1;
    } else {
        $password = test_input($_POST["password"]);
    }

    if ($error === 0) {
        $sql = "SELECT id, user_name, user_id, password_hash FROM register WHERE user_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $user_name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user["password_hash"])) {
                session_regenerate_id(true);
                $_SESSION["id"] = $user["id"];
                $_SESSION["user_name"] = $user["user_name"];
                $_SESSION["user_id"] = $user["user_id"];

                $channel_sql = "SELECT id, channel_name, profile_pic FROM channels WHERE user_id = ? ORDER BY id ASC LIMIT 1";
                $channel_stmt = $conn->prepare($channel_sql);
                $channel_stmt->bind_param("i", $user["id"]);
                $channel_stmt->execute();
                $channel_result = $channel_stmt->get_result();
                if ($channel_row = $channel_result->fetch_assoc()) {
                    $_SESSION['channel_id'] = $channel_row['id'];
                    $_SESSION['channel_name'] = $channel_row['channel_name'];
                    $_SESSION['channel_profile_pic'] = $channel_row['profile_pic'] ?? 'images/default-channel.png';
                } else {
                    // No channel exists – redirect to create_channel.php
                    header("Location: ../create_channel.php");
                    exit;
                }
                $channel_stmt->close();
                header("Location: ../index.php");
                exit;
            } else {
                $password_err = "Invalid password.";
            }
        } else {
            $user_name_err = "No account found with that username/email.";
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
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="../images/logo-tab.png">
    <script src="js/jquery.min.js"></script>
</head>

<body>
    <div class="form-container form-reverse">
        <div class="left">
            <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <a href="../index.php"><i class="fas fa-home"></i></a>
                <h1 class="gradient-text">Login</h1>

                <?php if (isset($_SESSION['registered'])): ?>
                    <p style="color:green;">Registration successful! Please log in.</p>
                    <?php unset($_SESSION['registered']);
                endif; ?>

                <label>User Name:</label>
                <span class="error"><?php echo $user_name_err; ?></span>
                <input type="text" name="user_name" placeholder="Enter your User Name"
                    value="<?php echo $user_name; ?>"><br><br>

                <label>Password:</label>
                <span class="error"><?php echo $password_err; ?></span>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" placeholder="Enter your Password">
                    <i class="far fa-eye" id="togglePassword"></i>
                </div>

                <p style="text-align:center;">Don't have an account? <a href="register.php">Register</a></p>

                <button type="submit" class="btn">Submit</button>
            </form>
        </div>
        <div class="right">
            <img src="../images/girl with login.png" alt="Login">
        </div>
    </div>

    <script>

        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        if (togglePassword) {
            togglePassword.addEventListener('click', function () {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
                this.classList.toggle('fa-eye-slash');
            });
        }
    </script>
</body>

</html>