<?php
include "../config.php";  // adjust path if settings.php is inside profile/
session_start();

$is_logged_in = isset($_SESSION["id"]);
if (!$is_logged_in) {
    header("Location: ../login/login.php");
    exit;
}

if (isset($_GET['channel']) && is_numeric($_GET['channel'])) {
    $new_id = intval($_GET['channel']);
    foreach ($channels as $ch) {
        if ($ch['id'] == $new_id) {
            $_SESSION['channel_id'] = $new_id;
            $_SESSION['channel_name'] = $ch['channel_name'];
            $_SESSION['channel_profile_pic'] = $ch['profile_pic'] ?? '../images/default-channel.png';
            header("Location: settings.php");
            exit;
        }
    }
}

$user_id = $_SESSION["id"];
$success = "";
$error = "";

// Get user's channels (for channel selection if multiple)
$channels_sql = "SELECT id, channel_name, description, profile_pic FROM channels WHERE user_id = ?";
$stmt = $conn->prepare($channels_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$channels = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Default to the first channel (or the one stored in session)
$current_channel_id = $_SESSION['channel_id'] ?? ($channels[0]['id'] ?? 0);
$current_channel = null;
foreach ($channels as $ch) {
    if ($ch['id'] == $current_channel_id) {
        $current_channel = $ch;
        break;
    }
}
if (!$current_channel && !empty($channels)) {
    $current_channel = $channels[0];
    $current_channel_id = $current_channel['id'];
    $_SESSION['channel_id'] = $current_channel_id;
    $_SESSION['channel_name'] = $current_channel['channel_name'];
    $_SESSION['channel_profile_pic'] = $current_channel['profile_pic'] ?? 'images/default-channel.png';
}

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ------------------- ACCOUNT: Change Email -------------------
    if (isset($_POST['update_email'])) {
        $new_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email address.";
        } else {
            $sql = "UPDATE register SET email = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $new_email, $user_id);
            if ($stmt->execute()) {
                $_SESSION['email'] = $new_email;
                $success = "Email updated successfully.";
            } else {
                $error = "Failed to update email.";
            }
            $stmt->close();
        }
    }
    // ------------------- ACCOUNT: Change Password -------------------
    elseif (isset($_POST['update_password'])) {
        $old = $_POST['old_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        // Fetch current hash
        $sql = "SELECT password_hash FROM register WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        if (password_verify($old, $user['password_hash'])) {
            if ($new === $confirm && strlen($new) >= 6) {
                $new_hash = password_hash($new, PASSWORD_DEFAULT);
                $update = "UPDATE register SET password_hash = ? WHERE id = ?";
                $stmt2 = $conn->prepare($update);
                $stmt2->bind_param("si", $new_hash, $user_id);
                if ($stmt2->execute()) {
                    $success = "Password changed successfully.";
                } else {
                    $error = "Password update failed.";
                }
                $stmt2->close();
            } else {
                $error = "New password must be at least 6 characters and match.";
            }
        } else {
            $error = "Old password is incorrect.";
        }
    }
    // ------------------- ACCOUNT: Delete Account -------------------
    elseif (isset($_POST['delete_account'])) {
        // Confirm password again (optional but recommended)
        $confirm_password = $_POST['confirm_delete_password'];
        $sql = "SELECT password_hash FROM register WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        if (password_verify($confirm_password, $user['password_hash'])) {
            // Start transaction
            $conn->begin_transaction();
            try {
                // Get all channels to delete folders later
                $ch_sql = "SELECT id, channel_name FROM channels WHERE user_id = ?";
                $ch_stmt = $conn->prepare($ch_sql);
                $ch_stmt->bind_param("i", $user_id);
                $ch_stmt->execute();
                $channels_res = $ch_stmt->get_result();
                while ($ch = $channels_res->fetch_assoc()) {
                    $channel_folder = "uploads/" . $ch['channel_name'];
                    if (is_dir($channel_folder)) {
                        // Recursively delete folder
                        array_map('unlink', glob("$channel_folder/*/*"));
                        array_map('rmdir', glob("$channel_folder/*"));
                        rmdir($channel_folder);
                    }
                }
                $ch_stmt->close();
                // Delete user (cascades to channels and uploads if foreign keys set)
                $del_sql = "DELETE FROM register WHERE id = ?";
                $del_stmt = $conn->prepare($del_sql);
                $del_stmt->bind_param("i", $user_id);
                $del_stmt->execute();
                $del_stmt->close();
                $conn->commit();
                session_destroy();
                header("Location: ../index.php");
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Account deletion failed: " . $e->getMessage();
            }
        } else {
            $error = "Incorrect password. Account not deleted.";
        }
    }
    // ------------------- CHANNEL: Update Channel Info -------------------
    elseif (isset($_POST['update_channel'])) {
        $channel_id = intval($_POST['channel_id']);
        $new_name = preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['channel_name']);
        $description = htmlspecialchars($_POST['description']);
        // Verify channel belongs to user
        $check = "SELECT id, channel_name, profile_pic FROM channels WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($check);
        $stmt->bind_param("ii", $channel_id, $user_id);
        $stmt->execute();
        $ch_result = $stmt->get_result();
        if ($ch_result->num_rows === 0) {
            $error = "Invalid channel.";
        } else {
            $old_channel = $ch_result->fetch_assoc();
            $old_name = $old_channel['channel_name'];
            // If channel name changed, we need to rename the folder
            if ($old_name !== $new_name) {
                // Check new name uniqueness
                $name_check = "SELECT id FROM channels WHERE channel_name = ? AND id != ?";
                $nc_stmt = $conn->prepare($name_check);
                $nc_stmt->bind_param("si", $new_name, $channel_id);
                $nc_stmt->execute();
                $nc_result = $nc_stmt->get_result();
                if ($nc_result->num_rows > 0) {
                    $error = "Channel name already taken.";
                } else {
                    // Rename folder
                    $old_dir = "uploads/" . $old_name;
                    $new_dir = "uploads/" . $new_name;
                    if (is_dir($old_dir)) {
                        rename($old_dir, $new_dir);
                        // Update profile_pic path in database to reflect new folder
                        if ($old_channel['profile_pic']) {
                            $new_pic_path = str_replace($old_name, $new_name, $old_channel['profile_pic']);
                        } else {
                            $new_pic_path = null;
                        }
                    } else {
                        $new_dir = $old_dir; // fallback
                        $new_pic_path = $old_channel['profile_pic'];
                    }
                    // Update database
                    $update = "UPDATE channels SET channel_name = ?, description = ?, profile_pic = ? WHERE id = ?";
                    $stmt2 = $conn->prepare($update);
                    $stmt2->bind_param("sssi", $new_name, $description, $new_pic_path, $channel_id);
                    if ($stmt2->execute()) {
                        $success = "Channel updated.";
                        if ($_SESSION['channel_id'] == $channel_id) {
                            $_SESSION['channel_name'] = $new_name;
                            $_SESSION['channel_profile_pic'] = $new_pic_path ?? $_SESSION['channel_profile_pic'];
                        }
                        // Refresh current channel data
                        $current_channel['channel_name'] = $new_name;
                        $current_channel['description'] = $description;
                        $current_channel['profile_pic'] = $new_pic_path;
                    } else {
                        $error = "Channel update failed.";
                    }
                    $stmt2->close();
                }
                $nc_stmt->close();
            } else {
                // Only update description
                $update = "UPDATE channels SET description = ? WHERE id = ?";
                $stmt2 = $conn->prepare($update);
                $stmt2->bind_param("si", $description, $channel_id);
                if ($stmt2->execute()) {
                    $success = "Channel description updated.";
                    $current_channel['description'] = $description;
                } else {
                    $error = "Update failed.";
                }
                $stmt2->close();
            }
        }
        $stmt->close();
    }
    // ------------------- CHANNEL: Upload Profile Picture -------------------
    elseif (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
        $channel_id = intval($_POST['channel_id']);
        // Verify ownership
        $check = "SELECT channel_name, profile_pic FROM channels WHERE id = ? AND user_id = ?";
        $stmt = $conn->prepare($check);
        $stmt->bind_param("ii", $channel_id, $user_id);
        $stmt->execute();
        $ch_result = $stmt->get_result();
        if ($ch_result->num_rows === 0) {
            $error = "Invalid channel.";
        } else {
            $channel = $ch_result->fetch_assoc();
            $channel_name = $channel['channel_name'];
            $allowed = ['image/jpeg', 'image/png'];
            $tmp = $_FILES['profile_pic']['tmp_name'];
            $mime = mime_content_type($tmp);
            if (in_array($mime, $allowed)) {
                $ext = $mime == 'image/jpeg' ? 'jpg' : 'png';
                $filename = "profile." . $ext;
                $dest = "uploads/" . $channel_name . "/channel_pic/" . $filename;
                // Delete old picture if exists
                if ($channel['profile_pic'] && file_exists($channel['profile_pic'])) {
                    unlink($channel['profile_pic']);
                }
                if (move_uploaded_file($tmp, $dest)) {
                    // Update database
                    $update = "UPDATE channels SET profile_pic = ? WHERE id = ?";
                    $stmt2 = $conn->prepare($update);
                    $stmt2->bind_param("si", $dest, $channel_id);
                    if ($stmt2->execute()) {
                        $success = "Profile picture updated.";
                        if ($_SESSION['channel_id'] == $channel_id) {
                            $_SESSION['channel_profile_pic'] = $dest;
                        }
                        // Update current channel array
                        $current_channel['profile_pic'] = $dest;
                    } else {
                        $error = "Database update failed.";
                    }
                    $stmt2->close();
                } else {
                    $error = "Failed to save image. Check folder permissions.";
                }
            } else {
                $error = "Only JPG/PNG images are allowed.";
            }
        }
        $stmt->close();
    }
}

// Get fresh user data for account tab
$user_sql = "SELECT email FROM register WHERE id = ?";
$stmt = $conn->prepare($user_sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Mayathirai</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="../images/logo-tab.png">
    <link rel="stylesheet" href="../Stylesheet/stylesheet.css">
    <style>
        .settings-container {
            max-width: 800px;
            margin: 2rem auto;
            background: var(--card-bg);
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .tabs {
            display: flex;
            gap: 1rem;
            border-bottom: 1px solid var(--text-secondary);
            margin-bottom: 2rem;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            font-size: 1rem;
            color: var(--text-secondary);
            transition: 0.2s;
        }

        .tab-btn.active {
            color: var(--text-primary);
            border-bottom: 2px solid #4b13a4;
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-primary);
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 0.7rem;
            background: var(--bg-secondary);
            border: 1px solid var(--text-secondary);
            color: var(--text-primary);
            border-radius: 8px;
        }

        .btn {
            background: #4b13a4;
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .btn-danger {
            background: #cc0000;
        }

        .success {
            color: #4b13a4;
            margin-bottom: 1rem;
        }

        .error {
            color: #cc0000;
            margin-bottom: 1rem;
        }

        hr {
            margin: 2rem 0;
            border-color: var(--text-secondary);
        }

        .current-pic {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <header class="page-header">
        <div class="logo-area">
            <div id="menu">
                <button onclick="myFunction()" style="background-color: transparent;border: none;"><i class="fa fa-bars"
                        style="color: white;"></i></button>
            </div>
            <img onclick="location.href='../index.php'" src="../images/logo-final.png" alt="logo" class="logo-img">
        </div>
        <div style="align-items: center;">
            <form class="example" action="/action_page.php">
                <input type="text" placeholder="Search.." name="search">
                <button type="submit"><i class="fa fa-search"></i></button>
            </form>
        </div>
        <div class="header-right">
            <button class="btn" onclick="location.href='../create.php'">+ Create</button>
            <span style="padding: 10px;"></span>
            <label class="mayathirai-switch">
                <input type="checkbox" id="themeSwitch">
                <span class="switch-slider">
                    <span class="switch-icon switch-icon-dark"></span>
                    <span class="switch-icon switch-icon-light"></span>
                </span>
            </label>
            <span style="padding: 10px;"></span>
            <?php if ($is_logged_in): ?>
                <div class="dropdown">
                    <button class="dropbtn">
                        <img class="profile-pic" alt="Profile Picture"
                            src="<?php echo htmlspecialchars($_SESSION['channel_profile_pic'] ?? '../images/default-channel.png'); ?>">
                    </button>
                    <div class="dropdown-content">
                        <a href="profile.php">Profile</a>
                        <a href="settings.php">Settings</a>
                        <a href="../login/logout.php" style="color: red;">Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <button onclick="location.href='../login/login.php'" class="btn"><i class="fas fa-sign-in-alt"></i>
                    Login</button>
            <?php endif; ?>
        </div>
    </header>

    <main class="content">
        <div class="settings-container">
            <h1>Settings</h1>
            <?php if ($success): ?>
                <div class="success">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="tabs">
                <button class="tab-btn active" data-tab="account">Account</button>
                <button class="tab-btn" data-tab="channel">Channel</button>
            </div>

            <!-- Account Tab -->
            <div id="account" class="tab-pane active">
                <form method="post">
                    <h3>Change Email</h3>
                    <div class="form-group">
                        <label>New Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>"
                            required>
                    </div>
                    <button type="submit" name="update_email" class="btn">Update Email</button>
                </form>
                <hr>
                <form method="post">
                    <h3>Change Password</h3>
                    <div class="form-group">
                        <label>Old Password</label>
                        <input type="password" name="old_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="update_password" class="btn">Update Password</button>
                </form>
                <hr>
                <form method="post"
                    onsubmit="return confirm('WARNING: This will permanently delete your account and all videos. Type your password to confirm.');">
                    <h3>Delete Account</h3>
                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_delete_password" required>
                    </div>
                    <button type="submit" name="delete_account" class="btn btn-danger">Delete My Account</button>
                </form>
            </div>

            <!-- Channel Tab -->
            <div id="channel" class="tab-pane">
                <?php if (count($channels) > 1): ?>
                    <div class="form-group">
                        <label>Select Channel</label>
                        <select id="channelSelect">
                            <?php foreach ($channels as $ch): ?>
                                <option value="<?php echo $ch['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($ch['channel_name']); ?>"
                                    data-desc="<?php echo htmlspecialchars($ch['description']); ?>"
                                    data-pic="<?php echo $ch['profile_pic']; ?>" <?php echo ($ch['id'] == $current_channel_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ch['channel_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data" id="channelForm">
                    <input type="hidden" name="channel_id" id="channel_id" value="<?php echo $current_channel_id; ?>">
                    <div class="form-group">
                        <label>Channel Name</label>
                        <input type="text" name="channel_name" id="channel_name"
                            value="<?php echo htmlspecialchars($current_channel['channel_name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="description"
                            rows="4"><?php echo htmlspecialchars($current_channel['description']); ?></textarea>
                    </div>
                    <button type="submit" name="update_channel" class="btn">Save Channel Settings</button>
                </form>
                <hr>
                <form method="post" enctype="multipart/form-data">
                    <input type="hidden" name="channel_id" value="<?php echo $current_channel_id; ?>">
                    <div class="form-group">
                        <label>Profile Picture</label>
                        <input type="file" name="profile_pic" accept="image/jpeg,image/png">
                        <?php if (!empty($current_channel['profile_pic']) && file_exists($current_channel['profile_pic'])): ?>
                            <img class="current-pic" src="<?php echo htmlspecialchars($current_channel['profile_pic']); ?>"
                                alt="Current Profile Picture">
                        <?php else: ?>
                            <img class="current-pic" src="../images/default-channel.png" alt="Default Profile Picture">
                        <?php endif; ?>
                    </div>
                    <button type="submit" name="upload_profile_pic" class="btn">Upload Picture</button>
                </form>
            </div>
        </div>
    </main>

    <script src="../script.js"></script>
    <script>
        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
                btn.classList.add('active');
                document.getElementById(btn.dataset.tab).classList.add('active');
            });
        });
        // Channel switcher (reload page with selected channel)
        const chanSelect = document.getElementById('channelSelect');
        if (chanSelect) {
            chanSelect.addEventListener('change', function () {
                const selectedId = this.value;
                window.location.href = 'settings.php?channel=' + selectedId;
            });
        }
        // If URL has ?channel=, update current channel (simplified: just reload with session update)
        const urlParams = new URLSearchParams(window.location.search);
        const channelParam = urlParams.get('channel');
        if (channelParam) {
            // Optionally send AJAX to update session, or just reload after redirect.
            // For simplicity, we'll redirect to same page without param after setting session via a POST?
            // Better to handle in PHP at top. We'll just rely on PHP to process ?channel=.
            // Actually, we already fetch channels array; we can add logic in PHP to set session based on ?channel=
        }
    </script>
</body>

</html>