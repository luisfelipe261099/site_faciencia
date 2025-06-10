      <?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "projeto");

    if ($conn->connect_error) {
        die("Falha na conexão: " . $conn->connect_error);
    }

    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role, polo_id FROM usuarios WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        
        // Definir polo_id na sessão para o polo e professor
        if ($user['role'] == 'polo' || $user['role'] == 'professor') {
            $_SESSION['polo_id'] = $user['polo_id'];
        }

        // Redirecionamento baseado no papel do usuário
        if ($user['role'] == 'secretaria') {
            header("Location: secretaria/secretaria.php");
        } elseif ($user['role'] == 'polo') {
            header("Location: polo/polo_dashboard.php");
        } elseif ($user['role'] == 'financeiro') {
            header("Location: financeiro/financeiro_dashboard.php");
        } elseif ($user['role'] == 'aluno') {
            header("Location: aluno/aluno_dashboard.php");
        } elseif ($user['role'] == 'professor') {
            header("Location: professor/professor_dashboard.php");
        }
        exit;
    } else {
        echo "<div class='alert alert-danger'>Nome de usuário ou senha incorretos.</div>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FaCiencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #e4e4e4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        .login-container img {
            display: block;
            margin: 0 auto 20px;
            max-width: 150px;
        }
        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #9D359C;
        }
        .login-container .btn-primary {
            background-color: #9D359C;
            border: none;
        }
        .login-container .btn-primary:hover {
            background-color: #fbff00;
            color: #9D359C;
        }
        .login-container .btn-secondary {
            background-color: #e4e4e4;
            color: #9D359C;
            border: none;
            margin-top: 10px;
        }
        .login-container .btn-secondary:hover {
            background-color: #9D359C;
            color: #ffffff;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <img src="logo.png" alt="FaCiencia Logo">
        <h2>Login</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Usuário</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Entrar</button>
            <a href="index.html" class="btn btn-secondary w-100">Voltar ao Site</a>
        </form>
    </div>

</body>
</html>
