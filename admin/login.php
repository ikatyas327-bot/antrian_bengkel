<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

require '../koneksi.php'; // pastikan file ini menghasilkan variabel $conn

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // ubah $mysqli jadi $conn
    $stmt = $conn->prepare("SELECT id, nama, password FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $nama, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['admin_id'] = $id;
            $_SESSION['admin_nama'] = $nama;
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Email tidak ditemukan!";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Sistem Antrian Bengkel</title>
    <style>
        /* Reset dasar */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1e90ff, #00bcd4);
        }

        .login-container {
            background: #fff;
            padding: 2rem;
            border-radius: 1rem;
            width: 100%;
            max-width: 380px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
            animation: fadeIn 0.6s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-container h2 {
            text-align: center;
            color: #333;
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.4rem;
            color: #444;
            font-weight: 500;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 0.7rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: 0.3s;
        }

        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #1e90ff;
            outline: none;
            box-shadow: 0 0 6px rgba(30,144,255,0.3);
        }

        button {
            width: 100%;
            padding: 0.8rem;
            border: none;
            border-radius: 8px;
            background-color: #1e90ff;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background-color: #0078e7;
            transform: scale(1.02);
        }

        .error {
            background-color: #ffe6e6;
            border: 1px solid #ff4d4d;
            color: #b30000;
            padding: 0.6rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .footer-text {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: #666;
        }

        .footer-text a {
            color: #1e90ff;
            text-decoration: none;
        }

        .footer-text a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login Admin</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="email">Email</label>
            <input type="text" id="email" name="email" placeholder="Masukkan email" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Masukkan password" required>

            <button type="submit">Masuk</button>
        </form>

        <div class="footer-text">
            © <?= date("Y") ?> Sistem Antrian Bengkel
        </div>
    </div>
</body>
</html>
