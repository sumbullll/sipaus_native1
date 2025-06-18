<?php
// views/auth/login.php
// Ambil pesan error atau status dari session jika ada
$errors = $_SESSION['errors'] ?? [];
$status = $_SESSION['status'] ?? null;
unset($_SESSION['errors'], $_SESSION['status']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SiManis</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            font-family: 'Poppins', sans-serif;
            height: 100vh;
            margin: 0;
            background-color: #f6f5f7;
        }

        .login-container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
            width: 100%;
            max-width: 450px;
            min-height: 400px;
            padding: 2rem 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-sizing: border-box;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            width: 100%;
        }

        .title {
            font-weight: bold;
            margin: 0;
            margin-bottom: 1.5rem;
            font-size: 2rem;
            color: #333;
        }

        .input-field {
            background-color: #eee;
            border: none;
            padding: 12px 15px;
            margin: 8px 0;
            width: 100%;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .input-field:focus {
            outline: 2px solid #007bff;
        }

        .btn-action {
            border-radius: 20px;
            border: 1px solid #0056b3;
            background-color: #0056b3;
            color: #FFFFFF;
            font-size: 12px;
            font-weight: bold;
            padding: 12px 45px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: transform 80ms ease-in, background-color 0.2s;
            cursor: pointer;
            margin-top: 1.5rem;
        }

        .btn-action:hover {
            background-color: #004494;
        }

        .btn-action:active {
            transform: scale(0.95);
        }

        .btn-action:focus {
            outline: none;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            width: 100%;
            box-sizing: border-box;
            text-align: left;
        }
        .error-message p {
            margin: 0;
            font-size: 0.9rem;
        }

        .status-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            width: 100%;
            box-sizing: border-box;
            text-align: left;
        }
        .status-message p {
            margin: 0;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <form method="POST" action="<?php echo BASE_URL; ?>/login-process">
            <h1 class="title">Login SIPAUS</h1>

            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($status): ?>
                <div class="status-message">
                    <p><?php echo htmlspecialchars($status); ?></p>
                </div>
            <?php endif; ?>
            
            <input class="input-field" type="text" name="username" placeholder="Username" required autofocus />
            <input class="input-field" type="password" name="password" placeholder="Password" required />
            <button type="submit" class="btn-action">Login</button>
        </form>
    </div>
    </body>
</html>