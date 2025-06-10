<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ajuste o caminho conforme a localização do seu autoload

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1) HONEYPOT: Se esse campo escondido vier preenchido, é provável SPAM
    if (!empty($_POST['telefone_honeypot'])) {
        die("<script>alert('SPAM detectado. Operação interrompida.'); window.history.back();</script>");
    }
    
    // 2) Dados do formulário (com sanitização básica)
    $indicador_nome = htmlspecialchars($_POST['indicador_nome'] ?? '');
    $indicador_email = filter_var($_POST['indicador_email'] ?? '', FILTER_SANITIZE_EMAIL);
    $indicador_telefone = htmlspecialchars($_POST['indicador_telefone'] ?? '');
    $indicador_relacao = htmlspecialchars($_POST['indicador_relacao'] ?? '');
    
    $indicado_nome = htmlspecialchars($_POST['indicado_nome'] ?? '');
    $indicado_email = filter_var($_POST['indicado_email'] ?? '', FILTER_SANITIZE_EMAIL);
    $indicado_telefone = htmlspecialchars($_POST['indicado_telefone'] ?? '');
    $indicado_cidade = htmlspecialchars($_POST['indicado_cidade'] ?? '');
    
    $area_interesse = htmlspecialchars($_POST['area_interesse'] ?? '');
    $curso_especifico = htmlspecialchars($_POST['curso_especifico'] ?? '');
    $mensagem = htmlspecialchars($_POST['mensagem'] ?? '');
    
    // 3) Envio de e-mail via PHPMailer
    try {
        $mail = new PHPMailer(true);
        // Configuração do servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com'; // Ajuste conforme seu provedor
        $mail->SMTPAuth   = true;
        $mail->Username   = 'desenvolvimento@lfmtecnologia.com'; // Seu usuário SMTP
        $mail->Password   = 'T3cn0l0g1a@';                       // Sua senha
        $mail->SMTPSecure = 'tls';                               // ou 'ssl' (verifique porta)
        $mail->Port       = 587;                                 // Porta TLS (ou 465 para SSL)

        // Endereços de envio
        $mail->setFrom('desenvolvimento@lfmtecnologia.com', 'Site Faciencia');
        $mail->addAddress('secretaria@faciencia.edu.br');
        $mail->addAddress('apoio2@faciencia.edu.br');
        $mail->addAddress('apoio@faciencia.edu.br');
        $mail->addReplyTo($indicador_email, $indicador_nome);

        // Conteúdo do e-mail
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = "Indicação de Amigo: {$indicador_nome}";
        
        $mail->Body = "
            <html>
            <head>
                <title>Nova Indicação de Amigo</title>
            </head>
            <body>
                <h2>Nova Indicação de Amigo</h2>
                
                <h3>Dados do Indicador</h3>
                <p><strong>Nome:</strong> {$indicador_nome}</p>
                <p><strong>E-mail:</strong> {$indicador_email}</p>
                <p><strong>Telefone:</strong> {$indicador_telefone}</p>
                <p><strong>Relação com a FaCiencia:</strong> {$indicador_relacao}</p>
                
                <h3>Dados do Indicado</h3>
                <p><strong>Nome:</strong> {$indicado_nome}</p>
                <p><strong>E-mail:</strong> {$indicado_email}</p>
                <p><strong>Telefone:</strong> {$indicado_telefone}</p>
                <p><strong>Cidade/Estado:</strong> {$indicado_cidade}</p>
                
                <h3>Detalhes da Indicação</h3>
                <p><strong>Área de Interesse:</strong> {$area_interesse}</p>
                <p><strong>Curso Específico:</strong> {$curso_especifico}</p>
                <p><strong>Mensagem:</strong><br>{$mensagem}</p>
            </body>
            </html>
        ";

        $mail->send();
        echo "<script>alert('Indicação enviada com sucesso!'); window.location.href = 'indicacao.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Erro ao enviar a indicação: {$mail->ErrorInfo}'); window.history.back();</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Programa de Indicação da FaCiencia. Indique amigos, familiares ou conhecidos para nossos cursos.">
    <title>Programa de Indicação - FaCiencia</title>
    <link rel="icon" type="image/png" href="https://faciencia.edu.br/logo.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #9d359c;
            --primary-dark: #7d287c;
            --primary-light: #c965cc;
            --secondary: #ffe925;
            --dark: #333333;
            --light: #f8f9fa;
            --white: #ffffff;
            --gray: #6c757d;
            --success: #28a745;
            --border-radius: 8px;
            --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        /* Base Styles */
        body {
            font-family: "Roboto", sans-serif;
            color: var(--dark);
            line-height: 1.6;
            background-color: var(--light);
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: "Montserrat", sans-serif;
            font-weight: 700;
        }

        section {
            padding: 80px 0;
        }

        .btn {
            font-family: "Montserrat", sans-serif;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 50px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-primary:hover, 
        .btn-primary:focus {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(157, 53, 156, 0.3);
        }

        .btn-secondary {
            background-color: var(--secondary);
            border-color: var(--secondary);
            color: var(--primary);
        }

        .btn-secondary:hover,
        .btn-secondary:focus {
            background-color: #e6d020;
            border-color: #e6d020;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(255, 233, 37, 0.3);
            color: var(--primary-dark);
        }

        .section-title {
            position: relative;
            margin-bottom: 50px;
            padding-bottom: 20px;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 80px;
            height: 4px;
            background: var(--primary);
            border-radius: 2px;
        }

        .section-title.text-center:after {
            left: 50%;
            transform: translateX(-50%);
        }

        /* Navbar */
        .navbar {
            background-color: var(--primary);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            padding: 10px 0;
            background-color: var(--primary-dark);
        }

        .navbar-brand img {
            height: 60px;
            transition: all 0.3s ease;
        }

        .navbar.scrolled .navbar-brand img {
            height: 50px;
        }

        .navbar-nav .nav-link {
            color: var(--white);
            font-weight: 500;
            padding: 10px 15px;
            position: relative;
        }

        .navbar-nav .nav-link:before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            right: 50%;
            height: 2px;
            background-color: var(--secondary);
            transition: all 0.3s ease;
            opacity: 0;
        }

        .navbar-nav .nav-link:hover:before,
        .navbar-nav .nav-link.active:before {
            left: 15px;
            right: 15px;
            opacity: 1;
        }

        .navbar-nav .nav-link:hover,
        .navbar-nav .nav-link.active {
            color: var(--secondary);
        }

        .dropdown-menu {
            background-color: var(--primary-dark);
            border: none;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 10px;
            margin-top: 10px;
        }

        .dropdown-item {
            color: var(--white);
            padding: 8px 15px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .dropdown-item:hover {
            background-color: var(--primary-light);
            color: var(--white);
        }

        /* Hero Section */
        .hero-section {
            position: relative;
            background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center/cover;
            padding: 150px 0 100px;
            color: var(--white);
            text-align: center;
        }

        .hero-section .container {
            position: relative;
            z-index: 1;
        }

        .hero-section h1 {
            font-size: 3.5rem;
            margin-bottom: 20px;
        }

        .hero-section p {
            font-size: 1.2rem;
            max-width: 700px;
            margin: 0 auto 30px;
        }

        /* Form Section */
        .form-section {
            background-color: var(--white);
            padding: 60px 0;
        }

        .form-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 40px;
            margin-bottom: 30px;
        }

        .form-card h3 {
            color: var(--primary);
            margin-bottom: 30px;
            font-size: 22px;
            border-bottom: 2px solid var(--primary-light);
            padding-bottom: 15px;
        }

        .form-label {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 8px;
        }

        .form-control {
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            font-size: 15px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(157, 53, 156, 0.2);
        }

        .form-required:after {
            content: '*';
            color: #dc3545;
            margin-left: 4px;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            font-size: 16px;
            margin-top: 15px;
        }

        /* Info Cards */
        .info-section {
            background-color: var(--light);
            padding: 60px 0;
        }

        .info-card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            height: 100%;
            transition: all 0.3s ease;
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }

        .info-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.15);
        }

        .info-icon {
            width: 80px;
            height: 80px;
            background-color: rgba(157, 53, 156, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 30px;
            margin: 0 auto 20px;
        }

        .info-card h3 {
            color: var(--primary);
            margin-bottom: 15px;
            font-size: 22px;
        }

        .info-card p {
            color: var(--gray);
        }

        /* CTA Section */
        .cta-section {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: var(--white);
            text-align: center;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }

        .cta-content {
            position: relative;
            z-index: 1;
        }

        .cta-title {
            font-size: 36px;
            margin-bottom: 20px;
        }

        .cta-text {
            font-size: 18px;
            margin-bottom: 30px;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-btn {
            background-color: var(--white);
            color: var(--primary);
            font-weight: 700;
            padding: 15px 40px;
            font-size: 16px;
        }

        .cta-btn:hover {
            background-color: var(--secondary);
            color: var(--primary-dark);
        }

        /* Footer */
        .footer {
            background-color: var(--primary-dark);
            color: var(--white);
            padding: 80px 0 30px;
        }

        .footer-logo {
            margin-bottom: 25px;
        }

        .footer-logo img {
            height: 80px;
        }

        .footer-info p {
            margin-bottom: 10px;
        }

        .footer-info i {
            margin-right: 10px;
            color: var(--secondary);
        }

        .footer h5 {
            color: var(--white);
            margin-bottom: 25px;
            position: relative;
            padding-bottom: 15px;
        }

        .footer h5:after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 50px;
            height: 3px;
            background: var(--secondary);
        }

        .footer-links {
            list-style: none;
            padding-left: 0;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--secondary);
            padding-left: 5px;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 30px;
            margin-top: 50px;
            text-align: center;
        }

        /* Floating WhatsApp Button */
        .floating-buttons {
            position: fixed;
            right: 30px;
            bottom: 30px;
            z-index: 99;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .floating-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--primary);
            color: var(--white);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
            text-decoration: none;
            font-size: 24px;
        }

        .floating-btn:hover {
            background-color: var(--primary-dark);
            transform: scale(1.1);
            color: var(--white);
        }

        .floating-btn.whatsapp {
            background-color: #25D366;
        }

        .floating-btn.whatsapp:hover {
            background-color: #128C7E;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .navbar-collapse {
                background-color: var(--primary);
                padding: 20px;
                border-radius: 0 0 var(--border-radius) var(--border-radius);
                margin-top: 10px;
            }
            
            section {
                padding: 60px 0;
            }
            
            .hero-section h1 {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 120px 0 80px;
            }
            
            .hero-section h1 {
                font-size: 2rem;
            }
            
            .hero-section p {
                font-size: 1rem;
            }
            
            .form-card {
                padding: 30px 20px;
            }
            
            .cta-title {
                font-size: 28px;
            }
            
            .cta-text {
                font-size: 16px;
            }
        }

        @media (max-width: 576px) {
            .hero-section h1 {
                font-size: 1.8rem;
            }
            
            .form-card {
                padding: 25px 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Header/Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="home">
                <img src="logo.png" alt="FaCiencia">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Institucional</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="quem-somos">Quem Somos</a></li>
                            <li><a class="dropdown-item" href="sede">Sede FaCiencia</a></li>
                            <li><a class="dropdown-item" href="infra">Infraestrutura</a></li>
                            <li><a class="dropdown-item" href="valida">Validador Diploma</a></li>
                            <li><a class="dropdown-item" href="doc-institucional">Doc Institucional</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Cursos</a>
                        <ul class="dropdown-menu">
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
                            <li><a class="dropdown-item" href="polo-apoio">Polo de Apoio</a></li>
                            <li><a class="dropdown-item" href="solicitacao">Solicitação do Polo</a></li>
                        </ul>
                    </li>
                    <!-- Biblioteca -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownBiblioteca" role="button"
                        data-bs-toggle="dropdown" aria-expanded="false">Biblioteca</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownBiblioteca">
                            <li><a class="dropdown-item" href="biblioteca">Biblioteca</a></li>
                            <li><a class="dropdown-item" href="revista">Revista</a></li>
                            <li><a class="dropdown-item" href="editora">Editora</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">Acesso</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="https://ava.faciencia.edu.br/login/index.php">AVA</a></li>
                            <li><a class="dropdown-item" href="https://lfmtecnologia.com/reinandus/loginaluno.php">Portal do Aluno</a></li>
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
                    <li class="nav-item">
                        <a class="nav-link active" href="indicacao.php">Indique um Amigo</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-secondary ms-lg-3" href="https://wa.link/vfs21y" target="_blank">
                            <i class="fas fa-user-plus me-2"></i>Inscreva-se
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section" id="hero">
        <div class="container">
            <h1>Indique um Amigo</h1>
            <p>Você conhece alguém que poderia se beneficiar dos nossos cursos? Faça uma indicação e ajude-nos a transformar mais vidas através da educação.</p>
            <a href="#form-section" class="btn btn-secondary">Fazer Indicação</a>
        </div>
    </section>

    <!-- Info Section -->
    <section class="info-section">
        <div class="container">
            <h2 class="section-title text-center">Como Funciona</h2>
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <h3>Preencha o Formulário</h3>
                        <p>Informe seus dados e os dados da pessoa que você deseja indicar para um de nossos cursos.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <h3>Envie sua Indicação</h3>
                        <p>Nossa equipe receberá sua indicação e entrará em contato com a pessoa indicada para apresentar nossas opções de cursos.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <div class="info-card">
                        <div class="info-icon">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h3>Transforme Vidas</h3>
                        <p>Ajude amigos, familiares ou colegas a descobrirem nossos cursos de qualidade e a avançarem em suas carreiras.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Form Section -->
    <section class="form-section" id="form-section">
        <div class="container">
            <h2 class="section-title text-center">Formulário de Indicação</h2>
            
            <form id="indicacaoForm" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>#form-section" method="POST">
                <!-- Campo honeypot (escondido para os usuários) -->
                <div style="display:none;">
                    <input type="text" name="telefone_honeypot">
                </div>
                
                <!-- Dados do Indicador -->
                <div class="form-card">
                    <h3><i class="fas fa-user me-2"></i> Seus Dados</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="indicador_nome" class="form-label form-required">Nome Completo</label>
                            <input type="text" class="form-control" id="indicador_nome" name="indicador_nome" required>
                        </div>
                        <div class="col-md-6">
                            <label for="indicador_email" class="form-label form-required">E-mail</label>
                            <input type="email" class="form-control" id="indicador_email" name="indicador_email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="indicador_telefone" class="form-label">Telefone/WhatsApp</label>
                            <input type="text" class="form-control" id="indicador_telefone" name="indicador_telefone">
                        </div>
                        <div class="col-md-6">
                            <label for="indicador_relacao" class="form-label form-required">Relação com a FaCiencia</label>
                            <select class="form-control" id="indicador_relacao" name="indicador_relacao" required>
                                <option value="">Selecione...</option>
                                <option value="Aluno">Sou aluno</option>
                                <option value="Ex-aluno">Sou ex-aluno</option>
                                <option value="Colaborador">Sou colaborador</option>
                                <option value="Parceiro">Sou parceiro</option>
                                <option value="Não tenho vínculo">Não tenho vínculo</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Dados do Indicado -->
                <div class="form-card">
                    <h3><i class="fas fa-user-friends me-2"></i> Dados da Pessoa Indicada</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="indicado_nome" class="form-label form-required">Nome Completo</label>
                            <input type="text" class="form-control" id="indicado_nome" name="indicado_nome" required>
                        </div>
                        <div class="col-md-6">
                            <label for="indicado_email" class="form-label form-required">E-mail</label>
                            <input type="email" class="form-control" id="indicado_email" name="indicado_email" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="indicado_telefone" class="form-label form-required">Telefone/WhatsApp</label>
                            <input type="text" class="form-control" id="indicado_telefone" name="indicado_telefone" required>
                        </div>
                        <div class="col-md-6">
                            <label for="indicado_cidade" class="form-label">Cidade/Estado</label>
                            <input type="text" class="form-control" id="indicado_cidade" name="indicado_cidade">
                        </div>
                    </div>
                </div>
                
                <!-- Detalhes da Indicação -->
                <div class="form-card">
                    <h3><i class="fas fa-info-circle me-2"></i> Detalhes da Indicação</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="area_interesse" class="form-label">Área de Interesse</label>
                            <select class="form-control" id="area_interesse" name="area_interesse">
                                <option value="">Selecione (se souber)...</option>
                                <option value="Saúde">Saúde</option>
                                <option value="Engenharia">Engenharia</option>
                                <option value="Direito">Direito</option>
                                <option value="Negócios">Negócios e Gestão</option>
                                <option value="Educação">Educação</option>
                                <option value="Tecnologia">Tecnologia</option>
                                <option value="Outros">Outros</option>
                                <option value="Não sei">Não sei</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="curso_especifico" class="form-label">Curso Específico (se souber)</label>
                            <input type="text" class="form-control" id="curso_especifico" name="curso_especifico">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <label for="mensagem" class="form-label">Mensagem ou Observações</label>
                            <textarea class="form-control" id="mensagem" name="mensagem" rows="4" placeholder="Conte-nos por que você está indicando esta pessoa ou qualquer informação adicional relevante..."></textarea>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="autorizacao" name="autorizacao" required>
                                <label class="form-check-label" for="autorizacao">
                                    Autorizo a FaCiencia a entrar em contato com a pessoa indicada, mencionando meu nome como indicador.
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary submit-btn">
                                <i class="fas fa-paper-plane me-2"></i> Enviar Indicação
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section" id="cta">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title">Quer saber mais sobre nossos cursos?</h2>
                <p class="cta-text">Entre em contato com um de nossos consultores educacionais para obter informações detalhadas sobre todos os cursos oferecidos pela FaCiencia.</p>
                <a href="https://wa.link/vfs21y" class="btn cta-btn" target="_blank">
                    <i class="fab fa-whatsapp"></i> Fale com um Consultor
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-5 mb-lg-0">
                    <div class="footer-logo">
                        <img src="logo.png" alt="FaCiencia">
                    </div>
                    <div class="footer-info">
                        <p><i class="fas fa-map-marker-alt"></i> Rua Visconde de Nacar, 1510 – Centro – Curitiba/PR</p>
                        <p><i class="fas fa-phone"></i> (41) 9 9256-2500</p>
                        <p><i class="fas fa-envelope"></i> contato@faciencia.edu.br</p>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4 mb-lg-0">
                    <h5>Institucional</h5>
                    <ul class="footer-links">
                        <li><a href="quem-somos">Quem Somos</a></li>
                        <li><a href="sede">Sede FaCiencia</a></li>
                        <li><a href="infra">Infraestrutura</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4 mb-lg-0">
                    <h5>Cursos</h5>
                    <ul class="footer-links">
                        <li><a href="tecnologo">Tecnólogo</a></li>
                        <li><a href="extensao">Extensão</a></li>
                        <li><a href="pos-graduacao">Pós-Graduação</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4 mb-lg-0">
                    <h5>Links Rápidos</h5>
                    <ul class="footer-links">
                        <li><a href="https://ava.faciencia.edu.br/login/index.php">AVA</a></li>
                        <li><a href="https://portal.faciencia.edu.br/usuarios/login">Portal do Aluno</a></li>
                        <li><a href="biblioteca">Biblioteca</a></li>
                        <li><a href="contato">Contato</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4 mb-lg-0">
                    <h5>Atendimento</h5>
                    <ul class="footer-links">
                        <li><a href="ouvidoria">Ouvidoria</a></li>
                        <li><a href="contato">Contato</a></li>
                        <li><a href="enviar-documento">Enviar Documento</a></li>
                        <li><a href="https://wa.link/vfs21y">WhatsApp</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="row align-items-center">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <p>&copy; 2025 FaCiencia. Todos os direitos reservados.</p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <a href="https://emec.mec.gov.br/emec/consulta-cadastro/detalhamento/d96957f455f6405d14c6542552b0f6eb/MjQ2Nzc=" target="_blank">
                            <img src="https://faciencia.edu.br/antigo/wp-content/webp-express/webp-images/uploads/2023/11/Mec-QRcode.png.webp" alt="QR Code MEC" height="60">
                        </a>
                        <img src="https://faciencia.edu.br/antigo/wp-content/webp-express/webp-images/uploads/2023/11/ULTIMO-SELO.png.webp" alt="Selo MEC" height="60" class="ms-3">
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Button -->
    <div class="floating-buttons">
        <a href="https://wa.link/vfs21y" class="floating-btn whatsapp" target="_blank" aria-label="WhatsApp">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (para máscara de telefone) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Máscara para telefone
            $('#indicador_telefone, #indicado_telefone').mask('(00) 00000-0000');
            
            // Navbar Scroll Effect
            $(window).on('scroll', function() {
                if ($(window).scrollTop() > 50) {
                    $('.navbar').addClass('scrolled');
                } else {
                    $('.navbar').removeClass('scrolled');
                }
            });
            
            // Scroll suave para links
            $('a[href^="#"]').on('click', function(e) {
                e.preventDefault();
                
                $('html, body').animate({
                    scrollTop: $($(this).attr('href')).offset().top - 80
                }, 500);
            });
        });
    </script>
</body>
</html>