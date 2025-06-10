<?php
// Iniciar sessão para possíveis usos futuros
session_start();

// Função para registrar erros
function logError($message) {
    $logFile = 'logs/error_log.txt';
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";

    // Verificar se o diretório de logs existe, se não, criar
    if (!file_exists('logs')) {
        mkdir('logs', 0755, true);
    }

    // Registrar o erro
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Variáveis para controlar o estado da verificação
$verificado = false;
$resultado = null;
$mensagem = "";
$detalhes_documento = null;

// Processar o código de verificação (via formulário POST ou parâmetro GET)
$codigo = "";

// Verificar se o código foi enviado via POST (formulário)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['codigo_verificacao'])) {
    $codigo = trim($_POST['codigo_verificacao']);
    $verificado = true; // Marcar como verificado quando enviado via POST
}
// Verificar se o código foi enviado via GET (QR code)
elseif (isset($_GET['codigo'])) {
    $codigo = trim($_GET['codigo']);
    // Não marcamos como verificado aqui para evitar que a verificação seja executada duas vezes
    // A verificação via GET será feita pelo JavaScript que submeterá o formulário
}

// Se temos um código para verificar
if (!empty($codigo)) {
    // Validar o código de verificação
    if (!is_numeric($codigo)) {
        $mensagem = "O código de verificação deve conter apenas números.";
    } else {
        // Conectar ao banco de dados
        try {
            $conn = new mysqli('srv1487.hstgr.io', 'u682219090_faciencia_erp', 'T3cn0l0g1a@', 'u682219090_faciencia_erp');

            // Verificar conexão
            if ($conn->connect_error) {
                logError("Erro de conexão: " . $conn->connect_error);
                throw new Exception('Erro de conexão com banco de dados: ' . $conn->connect_error);
            }

            // Preparar e executar a consulta
            $stmt = $conn->prepare("SELECT d.*,
                                    td.nome as tipo_documento,
                                    a.nome as nome_aluno,
                                    c.nome as nome_curso,
                                    p.nome as nome_polo
                                    FROM documentos_emitidos d
                                    LEFT JOIN tipos_documentos td ON d.tipo_documento_id = td.id
                                    LEFT JOIN alunos a ON d.aluno_id = a.id
                                    LEFT JOIN cursos c ON d.curso_id = c.id
                                    LEFT JOIN polos p ON d.polo_id = p.id
                                    WHERE d.codigo_verificacao = ? AND d.status = 'ativo'");

            $stmt->bind_param("i", $codigo);
            $stmt->execute();
            $result = $stmt->get_result();

            $verificado = true;

            if ($result->num_rows > 0) {
                $resultado = true;
                $detalhes_documento = $result->fetch_assoc();
                $mensagem = "Documento verificado com sucesso!";
            } else {
                $resultado = false;
                $mensagem = "Documento não encontrado ou inválido.";
            }

            $stmt->close();
            $conn->close();

        } catch (Exception $e) {
            $mensagem = "Erro ao verificar o documento: " . $e->getMessage();
            logError($e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificação de Documentos - FaCiencia</title>

    <!-- Configurações de cache -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Lottie Player -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <style>
        :root {
            --primary: #9D359C;
            --primary-dark: #7a2879;
            --primary-light: #c165c0;
            --secondary: #FFD700;
            --dark: #333;
            --light: #f8f9fa;
            --white: #fff;
            --gray: #6c757d;
            --border-radius: 8px;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Estilos Globais */
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #e4e4e4;
            margin: 0;
            padding-top: 76px;
        }

        /* Header */
        .navbar {
            padding: 10px 0;
            background-color: var(--primary);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .navbar.scrolled {
            padding: 5px 0;
            background-color: var(--primary-dark);
        }

        .navbar-brand img {
            height: 50px;
            transition: all 0.3s ease;
        }

        .navbar.scrolled .navbar-brand img {
            height: 40px;
        }

        .navbar-nav .nav-link {
            color: var(--white);
            font-weight: 500;
            padding: 8px 12px;
            position: relative;
            font-size: 15px;
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
            left: 12px;
            right: 12px;
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
            padding: 10px 15px;
            border-radius: 4px;
            transition: all 0.3s ease;
            font-size: 15px;
        }

        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: var(--primary-light);
            color: var(--white);
        }

        .btn-secondary {
            background-color: var(--secondary);
            color: var(--primary-dark);
            border: none;
            font-weight: 600;
            padding: 8px 15px;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #e6c200;
            transform: translateY(-2px);
        }

        .btn-inscreva {
            margin-left: 10px;
        }

        /* Conteúdo */
        .content-container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-top: 30px;
            margin-bottom: 30px;
        }

        h1 {
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 30px;
        }

        .verification-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .verification-result {
            margin-top: 40px;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }

        .verification-success {
            background-color: rgba(40, 167, 69, 0.1);
            border: 1px solid #28a745;
        }

        .verification-error {
            background-color: rgba(220, 53, 69, 0.1);
            border: 1px solid #dc3545;
        }

        .document-details {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .document-details h3 {
            color: var(--primary);
            margin-bottom: 20px;
        }

        .document-details .row {
            margin-bottom: 10px;
        }

        .document-details .label {
            font-weight: 600;
        }

        .animation-container {
            display: none;
            text-align: center;
            margin: 30px 0;
        }

        /* Footer */
        .footer {
            background-color: var(--primary);
            color: white;
            padding: 20px 0;
            text-align: center;
            font-family: 'Roboto', sans-serif;
        }

        .footer a {
            color: var(--secondary);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer a:hover {
            color: #fff;
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" aria-label="Menu de navegação">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home">Home</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Institucional</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="quem-somos">Quem Somos</a></li>
                            <li><a class="dropdown-item" href="sede">Sede FaCiencia</a></li>
                            <li><a class="dropdown-item" href="infra">Infraestrutura</a></li>
                            <li><a class="dropdown-item" href="valida">Validador Diploma</a></li>
                            <li><a class="dropdown-item" href="doc-institucional">Doc Institucional</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Cursos</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="tecnologo">Tecnólogo</a></li>
                            <li><a class="dropdown-item" href="extensao">Extensão</a></li>
                            <li><a class="dropdown-item" href="pos-graduacao">Pós-Graduação</a></li>
                        </ul>
                    </li>
                    <!-- Parceria -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownParceria" role="button" data-bs-toggle="dropdown" aria-expanded="false">Parceria</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownParceria">
                            <li><a class="dropdown-item" href="polo-apoio">Polo de Apoio</a></li>
                            <li><a class="dropdown-item" href="solicitacao">Solicitação do Polo</a></li>
                        </ul>
                    </li>
                    <!-- Biblioteca -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownBiblioteca" role="button" data-bs-toggle="dropdown" aria-expanded="false">Biblioteca</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownBiblioteca">
                            <li><a class="dropdown-item" href="biblioteca">Biblioteca</a></li>
                            <li><a class="dropdown-item" href="revista">Revista</a></li>
                            <li><a class="dropdown-item" href="editora">Editora</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Acesso</a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="https://ava.faciencia.edu.br/login/index.php">AVA</a></li>
                            <li><a class="dropdown-item" href="https://lfmtecnologia.com/reinandus/loginaluno.php">Portal do Aluno</a></li>
                        </ul>
                    </li>
                    <!-- Atendimento -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownAtendimento" role="button" data-bs-toggle="dropdown" aria-expanded="false">Atendimento</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownAtendimento">
                            <li><a class="dropdown-item" href="ouvidoria">Ouvidoria</a></li>
                            <li><a class="dropdown-item" href="contato">Contato</a></li>
                            <li><a class="dropdown-item" href="enviar-documento">Enviar Documento</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="indicacao.php">Indique um Amigo</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-secondary btn-inscreva" href="vestibular">
                            <i class="fas fa-graduation-cap me-2"></i>Vestibular
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="content-container">
            <h1 class="text-center">Verificação de Documentos</h1>
            <p class="text-center text-muted mb-4">Insira o código de verificação para validar a autenticidade do documento.</p>

            <div class="verification-form">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="verification-form">
                    <div class="mb-3">
                        <label for="codigo_verificacao" class="form-label">Código de Verificação</label>
                        <input type="text" class="form-control" id="codigo_verificacao" name="codigo_verificacao"
                               value="<?php echo htmlspecialchars(isset($_GET['codigo']) ? $_GET['codigo'] : ''); ?>"
                               placeholder="Digite o código de verificação" required>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="verificar-btn">Verificar Documento</button>
                    </div>
                </form>

                <!-- Container para animação -->
                <div class="animation-container" id="animation-container">
                    <lottie-player src="https://assets2.lottiefiles.com/packages/lf20_kxsd2ytq.json" background="transparent" speed="1" style="width: 300px; height: 300px; margin: 0 auto;" loop autoplay></lottie-player>
                    <p class="mt-3">Verificando documento, por favor aguarde...</p>
                </div>

                <?php if ($verificado): ?>
                    <div class="verification-result <?php echo $resultado ? 'verification-success' : 'verification-error'; ?>">
                        <h3>
                            <?php if ($resultado): ?>
                                <i class="fas fa-check-circle text-success"></i> Documento Válido
                            <?php else: ?>
                                <i class="fas fa-times-circle text-danger"></i> Documento Inválido
                            <?php endif; ?>
                        </h3>
                        <p><?php echo $mensagem; ?></p>
                    </div>

                    <?php if ($resultado && $detalhes_documento): ?>
                        <div class="document-details">
                            <h3>Detalhes do Documento</h3>
                            <div class="row">
                                <div class="col-md-4 label">Tipo de Documento:</div>
                                <div class="col-md-8"><?php echo htmlspecialchars($detalhes_documento['tipo_documento'] ?? 'Não informado'); ?></div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 label">Número do Documento:</div>
                                <div class="col-md-8"><?php echo htmlspecialchars($detalhes_documento['numero_documento'] ?? 'Não informado'); ?></div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 label">Data de Emissão:</div>
                                <div class="col-md-8"><?php echo date('d/m/Y', strtotime($detalhes_documento['data_emissao'])); ?></div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 label">Data de Validade:</div>
                                <div class="col-md-8"><?php echo date('d/m/Y', strtotime($detalhes_documento['data_validade'])); ?></div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 label">Aluno:</div>
                                <div class="col-md-8"><?php echo htmlspecialchars($detalhes_documento['nome_aluno'] ?? 'Não informado'); ?></div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 label">Curso:</div>
                                <div class="col-md-8"><?php echo htmlspecialchars($detalhes_documento['nome_curso'] ?? 'Não informado'); ?></div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 label">Polo:</div>
                                <div class="col-md-8"><?php echo htmlspecialchars($detalhes_documento['nome_polo'] ?? 'Não informado'); ?></div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 label">Status:</div>
                                <div class="col-md-8">
                                    <?php if ($detalhes_documento['status'] == 'ativo'): ?>
                                        <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Cancelado</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>© 2024 Faculdade FaCiencia. Todos os direitos reservados. | <a href="#">Política de Privacidade</a></p>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script para efeito de rolagem do menu
        document.addEventListener('DOMContentLoaded', function() {
            // Efeito de rolagem para o menu
            window.addEventListener('scroll', function() {
                const navbar = document.querySelector('.navbar');
                if (window.scrollY > 50) {
                    navbar.classList.add('scrolled');
                } else {
                    navbar.classList.remove('scrolled');
                }
            });

            // Mostrar animação ao enviar o formulário
            const form = document.getElementById('verification-form');
            const animationContainer = document.getElementById('animation-container');
            const verificarBtn = document.getElementById('verificar-btn');
            const codigoInput = document.getElementById('codigo_verificacao');

            if (form && animationContainer && verificarBtn && codigoInput) {
                // Verificar se o código foi fornecido via URL (QR code)
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('codigo') && codigoInput.value.trim() !== '') {
                    // Submeter o formulário automaticamente se o código veio da URL
                    setTimeout(function() {
                        // Esconder o botão e mostrar a animação
                        verificarBtn.style.display = 'none';
                        animationContainer.style.display = 'block';

                        // Submeter o formulário
                        form.submit();
                    }, 500); // Pequeno atraso para permitir que a página carregue completamente
                }

                // Adicionar evento para submissão manual do formulário
                form.addEventListener('submit', function(e) {
                    // Verificar se o campo está preenchido
                    if (codigoInput.value.trim() !== '') {
                        // Esconder o botão e mostrar a animação
                        verificarBtn.style.display = 'none';
                        animationContainer.style.display = 'block';

                        // Permitir que o formulário seja enviado
                        return true;
                    }
                });
            }
        });
    </script>
</body>
</html>
