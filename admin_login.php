<?php
session_start();
include 'db.php';


if (isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_panel.php");
    exit;
}


if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// for login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Security token invalid!";
    } else {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        $adminHash = password_hash("password123", PASSWORD_DEFAULT);

if ($username === "admin" && password_verify($password, $adminHash)) {
    $_SESSION['admin_logged_in'] = true;
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    header("Location: admin_panel.php");
    exit;
} else {
    $error = "Invalid username or password!";
}
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Secure Access</title>
  <style>

    @import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap');
    
    
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Quicksand', sans-serif;
    }

    /* Body styling */
    body {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        background: #000;
        overflow: hidden;
    }

   
    .container {
        position: relative;
        width: 100%;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* for animation*/
    .animated-bg {
        position: absolute;
        width: 100%;
        height: 100%;
        display: grid;
        grid-template-columns: repeat(20, 1fr);
        grid-template-rows: repeat(20, 1fr);
        gap: 2px;
        z-index: 1;
    }

    .animated-bg::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background: linear-gradient(#000, red, #000);
        animation: animate 5s linear infinite;
        z-index: 2;
    }

    .box {
        position: relative;
        background: #181818;
        z-index: 3;
        transition: 1.5s;
    }

    .box:hover {
        background: red;
        transition: 0s;
    }

   
    @keyframes animate {
        0% {
            transform: translateY(-100%);
        }
        100% {
            transform: translateY(100%);
        }
    }

    /* Full box for login form */
    .login-box {
        position: relative;
        width: 90%;
        max-width: 500px;
        background: rgba(34, 34, 34, 0.95);
        z-index: 1000;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 50px 40px;
        border-radius: 8px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.9);
        border: 2px solid rgba(255, 0, 0, 0.3);
    }

    .login-box::before {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: linear-gradient(45deg, #ff0000, #000, #ff0000);
        z-index: -1;
        border-radius: 10px;
        animation: borderGlow 3s linear infinite;
    }

    @keyframes borderGlow {
        0%, 100% {
            opacity: 0.5;
        }
        50% {
            opacity: 1;
        }
    }

    .login-content {
        position: relative;
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        gap: 40px;
    }

    .login-content h2 {
        font-size: 2.2em;
        color: red;
        text-transform: uppercase;
        text-align: center;
        letter-spacing: 2px;
        text-shadow: 0 0 10px rgba(255, 0, 0, 0.7);
    }

    .login-form {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .input-group {
        position: relative;
        width: 100%;
    }

    .input-group input {
        position: relative;
        width: 100%;
        background: rgba(51, 51, 51, 0.8);
        border: none;
        outline: none;
        padding: 25px 15px 10px;
        border-radius: 4px;
        color: #fff;
        font-weight: 500;
        font-size: 1.1em;
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: 0.3s;
    }

    .input-group input:focus {
        border-color: red;
        box-shadow: 0 0 10px rgba(255, 0, 0, 0.5);
    }

    .input-group label {
        position: absolute;
        left: 15px;
        top: 15px;
        color: #aaa;
        transition: 0.5s;
        pointer-events: none;
        font-size: 1em;
    }

    .input-group input:focus ~ label,
    .input-group input:valid ~ label {
        transform: translateY(-20px);
        font-size: 0.8em;
        color: red;
    }

    .login-btn {
        padding: 15px;
        background: linear-gradient(45deg, red, darkred);
        color: #000;
        font-weight: 700;
        font-size: 1.3em;
        letter-spacing: 1px;
        cursor: pointer;
        border: none;
        border-radius: 4px;
        transition: 0.3s;
        text-transform: uppercase;
        margin-top: 10px;
    }

    .login-btn:hover {
        background: linear-gradient(45deg, darkred, red);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 0, 0, 0.4);
    }

    .login-btn:active {
        transform: translateY(0);
    }

    .error-message {
        color: red;
        text-align: center;
        padding: 10px;
        background: rgba(255, 0, 0, 0.1);
        border-radius: 4px;
        border: 1px solid red;
    }

    .hacker-text {
        color: #0f0;
        font-family: 'Courier New', monospace;
        text-align: center;
        font-size: 0.9em;
        margin-top: 20px;
        opacity: 0.7;
    }

    /* Shaking animation if the user not enter the correct admin or pass*/
    @keyframes shake {
        0%, 100% { transform: translateX(0); }
        10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
        20%, 40%, 60%, 80% { transform: translateX(5px); }
    }

    .shake {
        animation: shake 0.5s;
    }

    /*for Responsive design */
    @media (max-width: 768px) {
        .login-box {
            width: 95%;
            padding: 40px 30px;
        }
        
        .login-content h2 {
            font-size: 1.8em;
        }
        
        .box {
            width: calc(5vw - 2px);
            height: calc(5vw - 2px);
        }
    }

    @media (max-width: 480px) {
        .login-box {
            padding: 30px 20px;
        }
        
        .login-content h2 {
            font-size: 1.5em;
        }
        
        .input-group input {
            padding: 20px 10px 8px;
        }
        
        .box {
            width: calc(10vw - 2px);
            height: calc(10vw - 2px);
        }
    }
  </style>
</head>
<body>
  <div class="container">
    
    <div class="animated-bg" id="animatedBg">
       
    </div>
    
    <!-- for Login form -->
    <div class="login-box" id="loginBox">
      <div class="login-content">
        <h2>Admin Access</h2>
        
        <!--Error Message Display -->
        <?php if(isset($error)): ?>
          <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form class="login-form" method="POST" action="">
          <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
          
          <div class="input-group">
            <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            <label for="username">Username</label>
          </div>
          <div class="input-group">
            <input type="password" id="password" name="password" required>
            <label for="password">Password</label>
          </div>
          <button type="submit" class="login-btn">Login</button>
        </form>
        
        </div>
      </div>
    </div>
  </div>

  <script>
    // Create animated background boxes
    function createBoxes() {
      const animatedBg = document.getElementById('animatedBg');
      const boxCount = 400; 
      
      for (let i = 0; i < boxCount; i++) {
        const box = document.createElement('div');
        box.classList.add('box');
        animatedBg.appendChild(box);
      }
    }
    
   
    <?php if(isset($error)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const loginBox = document.getElementById('loginBox');
            loginBox.classList.add('shake');
            setTimeout(() => {
                loginBox.classList.remove('shake');
            }, 500);
        });
    <?php endif; ?>
    
  
    window.addEventListener('DOMContentLoaded', () => {
      createBoxes();
    });
  </script>
</body>
</html>