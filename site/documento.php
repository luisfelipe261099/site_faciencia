<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitiza e valida os dados enviados
    $nome = htmlspecialchars(trim($_POST['nome']));
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    if (!$nome || !$email) {
        $error_message = "Nome ou e-mail inválido.";
    }

    // Validação do reCAPTCHA
    if (empty($_POST['g-recaptcha-response'])) {
        $error_message = "Por favor, confirme que você não é um robô.";
    } else {
        $recaptcha_secret = '6LeRruwqAAAAAN24suoC0Bx_0FqCeoIlHZYI6Ek6';
        $recaptcha_response = $_POST['g-recaptcha-response'];
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . $recaptcha_secret . "&response=" . $recaptcha_response);
        $response_keys = json_decode($response, true);
        if (!$response_keys["success"]) {
            $error_message = "Falha na verificação do reCAPTCHA. Tente novamente.";
        }
    }

    // Prossegue somente se não houver erros
    if (!isset($error_message)) {
        // Carrega o autoload do Composer e inicializa o PHPMailer
        require 'vendor/autoload.php';
        $mail = new PHPMailer(true);

        try {
            // Configuração do servidor SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com'; // Ajuste conforme seu provedor
            $mail->SMTPAuth   = true;
            $mail->Username   = 'desenvolvimento@lfmtecnologia.com'; // Usuário SMTP
            $mail->Password   = 'T3cn0l0g1a@';                       // Senha SMTP
            $mail->SMTPSecure = 'tls';                               // ou 'ssl'
            $mail->Port       = 587;                                 // Porta TLS

            // Remetente e destinatário
            $mail->setFrom('desenvolvimento@lfmtecnologia.com', 'Site Faciencia');
            $mail->addAddress('contato@faciencia.edu.br', 'Secretaria FaCiencia');

            // Assunto e corpo do e-mail
            $mail->Subject = 'Documentos enviados por ' . $nome;
            $mail->Body = "Nome: $nome\nE-mail: $email\n\nDocumentos anexados:";

            // Anexa os arquivos – adicione aqui validação de tamanho e tipo se necessário
            $arquivos = ['rg', 'cpf', 'diploma_frente', 'diploma_verso', 'comprovante_residencia', 'outros_documentos'];
            foreach ($arquivos as $arquivo) {
                if (isset($_FILES[$arquivo]) && $_FILES[$arquivo]['error'] == UPLOAD_ERR_OK) {
                    // Exemplo: você pode verificar o tamanho ou a extensão do arquivo aqui
                    $mail->addAttachment($_FILES[$arquivo]['tmp_name'], $_FILES[$arquivo]['name']);
                }
            }

            // Envia o e-mail
            $mail->send();
            $success_message = "Documentos enviados com sucesso!";
        } catch (Exception $e) {
            $error_message = "Erro ao enviar os documentos: {$mail->ErrorInfo}";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Documentos - FaCiencia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <!-- Script do Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        /* Estilos Globais */
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #e4e4e4;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        /* Navbar */
        .navbar {
            background-color: #9D359C;
            padding: 10px 20px;
        }
        .navbar-brand img {
            height: 60px;
        }
        .navbar-nav .nav-link {
            color: #ffffff;
            font-weight: bold;
            font-size: 16px;
            margin-right: 20px;
            transition: color 0.3s;
        }
        .navbar-nav .nav-link:hover {
            color: #fbff00;
        }
        .dropdown-menu {
            background-color: #9D359C;
            border: none;
            border-radius: 0;
        }
        .dropdown-menu .dropdown-item {
            color: #ffffff;
            font-size: 16px;
        }
        .dropdown-menu .dropdown-item:hover {
            background-color: #fbff00;
            color: #9D359C;
        }
        /* Content Section */
        .content-section {
            flex: 1;
            padding: 40px 0;
            background-color: #ffffff;
            box-sizing: border-box;
        }
        .content-section h2 {
            color: #9D359C;
            text-align: center;
            margin-bottom: 40px;
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
        }
        .form-container h4 {
            color: #9D359C;
            margin-bottom: 20px;
            text-align: center;
        }
        .form-container .form-group {
            margin-bottom: 15px;
        }
        .form-container .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333333;
        }
        .form-container .form-group input[type="text"],
        .form-container .form-group input[type="email"],
        .form-container .form-group input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #cccccc;
            border-radius: 5px;
            font-family: 'Roboto', sans-serif;
            font-size: 14px;
        }
        .form-container .form-group button {
            background-color: #9D359C;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-family: 'Montserrat', sans-serif;
            font-weight: bold;
            transition: background-color 0.3s;
            width: 100%;
        }
        .form-container .form-group button:hover {
            background-color: #fbff00;
            color: #9D359C;
        }
        /* Footer */
        .footer {
            background-color: #9D359C;
            color: white;
            padding: 20px 0;
            text-align: center;
            font-family: 'Roboto', sans-serif;
        }
        .footer img {
            max-height: 40px;
            margin: 0 10px;
        }
        .footer .footer-logo {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .footer .footer-contact {
            margin-top: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <nav class="custom-navbar navbar navbar-expand-md navbar-dark" aria-label="Furni navigation bar">
        <div class="container">
            <a class="navbar-brand" href="#"><img src="logo.png" alt="FaCiencia"></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsFurni"
                aria-controls="navbarsFurni" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarsFurni">
                <ul class="navbar-nav ms-auto mb-2 mb-md-0">
                    <li class="nav-item active">
                        <a class="nav-link" href="home">Home</a>
                    </li>
                    <!-- Institucional -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownInstitucional" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">Institucional</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownInstitucional">
                            <li><a class="dropdown-item" href="quem-somos">Quem Somos</a></li>
                            <li><a class="dropdown-item" href="sede">Sede FaCiencia</a></li>
                            <li><a class="dropdown-item" href="valida">Validador Diploma</a></li>
                            <li><a class="dropdown-item" href="doc-institucional">Doc Institucional</a></li>
                            <li><a class="dropdown-item" href="relatorios">Relatorio Autoavaliaçao</a></li>
                        </ul>
                    </li>
                    <!-- Cursos -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownCursos" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">Cursos</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownCursos">
                            <li><a class="dropdown-item" href="tecnologo">Tecnólogo</a></li>
                            <li><a class="dropdown-item" href="extensao">Extensão</a></li>
                            <li><a class="dropdown-item" href="pos-graduacao">Pós-Graduação</a></li>
                        </ul>
                    </li>
                    <!-- Parceria -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownParceria" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">Parceria</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownParceria">
                            <li><a class="dropdown-item" href="influenciadores">Influenciadores</a></li>
                            <li><a class="dropdown-item" href="polo-apoio">Polo de Apoio</a></li>
                            <li><a class="dropdown-item" href="solicitacao">Solicitação do Polo</a></li>
                        </ul>
                    </li>
                    <!-- Biblioteca -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownBiblioteca" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">Biblioteca</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownBiblioteca">
                            <li><a class="dropdown-item" href="biblioteca">Normas Biblioteca</a></li>
                            <li><a class="dropdown-item" href="revista">Revista</a></li>
                            <li><a class="dropdown-item" href="editora">Editora</a></li>
                        </ul>
                    </li>
                    <!-- Atendimento -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAtendimento" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">Atendimento</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownAtendimento">
                            <li><a class="dropdown-item" href="ouvidoria">Ouvidoria</a></li>
                            <li><a class="dropdown-item" href="contato">Contato</a></li>
                            <li><a class="dropdown-item" href="enviar-documento">Enviar Documento</a></li>
                        </ul>
                    </li>
                    <!-- login -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownlogin" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">Login</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownlogin">
                            <li><a class="dropdown-item" href="https://ava.faciencia.edu.br/">AVA</a></li>
                            <li><a class="dropdown-item" href="https://portal.faciencia.edu.br/">Portal</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Content Section -->
    <div class="content-section">
        <div class="container">
            <h2>Envie seus documentos</h2>
            <p class="text-center">Aproveite e nos envie as cópias do RG, CPF, Diploma (frente e verso) e comprovante de residência.</p>
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success text-center"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger text-center"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <h4>Formulário de Envio de Documentos</h4>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Seu melhor e-mail</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="rg">Seu RG</label>
                        <input type="file" id="rg" name="rg" required>
                    </div>
                    <div class="form-group">
                        <label for="cpf">Seu CPF</label>
                        <input type="file" id="cpf" name="cpf" required>
                    </div>
                    <div class="form-group">
                        <label for="diploma_frente">Diploma (Frente)</label>
                        <input type="file" id="diploma_frente" name="diploma_frente" required>
                    </div>
                    <div class="form-group">
                        <label for="diploma_verso">Diploma (Verso)</label>
                        <input type="file" id="diploma_verso" name="diploma_verso" required>
                    </div>
                    <div class="form-group">
                        <label for="comprovante_residencia">Comprovante de residência</label>
                        <input type="file" id="comprovante_residencia" name="comprovante_residencia" required>
                    </div>
                    <div class="form-group">
                        <label for="outros_documentos">Outros documentos</label>
                        <input type="file" id="outros_documentos" name="outros_documentos">
                    </div>
                    <!-- Widget do Google reCAPTCHA -->
                    <div class="form-group my-3 text-center">
                        <div class="g-recaptcha" data-sitekey="6LeRruwqAAAAADpIB49hkh1f7bAr9GPyxUGd1jpY"></div>
                    </div>
                    <div class="form-group">
                        <button type="submit">Enviar Documentos</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer py-4">
        <div class="container">
            <div class="row justify-content-between align-items-center">
                <!-- Logo Section -->
                <div class="col-lg-3 col-md-6 text-center text-lg-start mb-3 mb-lg-0">
                    <img src="logo.png" alt="FaCiencia" class="img-fluid">
                </div>
                <!-- Address Section -->
                <div class="col-lg-5 col-md-6 text-center text-lg-start mb-3 mb-lg-0">
                    <div class="footer-contact">
                        <p>Rua Visconde de Nacar, 1510 – 10. Andar – Conj. 1003 – Centro – Curitiba/PR – 80.410-201</p>
                        <p>📞 (41) 9 9256-2500 | 📧 contato@faciencia.edu.br</p>
                        <a href="https://www.google.com/maps/place/R.+Visconde+de+Nacar,+1510+-+Centro,+Curitiba+-+PR,+80410-201" target="_blank">
                            Ver no Google Maps
                        </a>
                    </div>
                </div>
                <!-- QR Code and Seal Section -->
                <div class="col-lg-4 col-md-12 text-center text-lg-end">
                    <div class="footer-logos">
                        <img src="https://faciencia.edu.br/antigo/wp-content/webp-express/webp-images/uploads/2023/11/Mec-QRcode.png.webp"
                            alt="QR Code" class="img-fluid mb-2">
                        <img src="https://faciencia.edu.br/antigo/wp-content/webp-express/webp-images/uploads/2023/11/ULTIMO-SELO.png.webp"
                            alt="MEC Seal" class="img-fluid mb-2">
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
