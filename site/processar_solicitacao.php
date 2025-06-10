<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Configurações de segurança
header('Content-Type: application/json; charset=utf-8');

try {
    // Habilitar log de erros para debug
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    // Verificar se é uma requisição POST
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        die(json_encode(['status' => 'error', 'message' => 'Método inválido']));
    }

    // Coletar dados do formulário
    $nome_empresa = $_POST['nome_empresa'] ?? '';
    $cnpj = $_POST['cnpj'] ?? '';
    $nome_solicitante = $_POST['nome_solicitante'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $email = $_POST['email'] ?? '';
    $link_planilha = $_POST['link_planilha'] ?? '';
    $tipo_solicitacao = $_POST['tipo_solicitacao'] ?? '';
    $quantidade = intval($_POST['quantidade'] ?? 0);
    $observacao = $_POST['observacao'] ?? '';
    $protocolo = $_POST['protocolo'] ?? ('POLO-' . date('Ymd') . '-' . mt_rand(10000, 99999));

    // Log dos dados recebidos
    error_log("Dados recebidos: " . json_encode($_POST));

    // Conectar ao banco de dados
    $conn = new mysqli('srv1487.hstgr.io', 'u682219090_faciencia_erp', 'T3cn0l0g1a@', 'u682219090_faciencia_erp');

    // Verificar conexão
    if ($conn->connect_error) {
        error_log("Erro de conexão: " . $conn->connect_error);
        throw new Exception('Erro de conexão com banco de dados: ' . $conn->connect_error);
    }
    
    error_log("Conexão com banco de dados estabelecida");
    
    // Verificar se a tabela existe
    $tableCheck = $conn->query("SHOW TABLES LIKE 'solicitacoes_site'");
    if ($tableCheck->num_rows == 0) {
        error_log("Tabela 'solicitacoes_site' não encontrada. Criando tabela...");
        
        // Criar tabela se não existir
        $createTable = "CREATE TABLE IF NOT EXISTS `solicitacoes_site` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `protocolo` varchar(30) NOT NULL,
          `nome_empresa` varchar(255) NOT NULL,
          `cnpj` varchar(20) NOT NULL,
          `nome_solicitante` varchar(255) NOT NULL,
          `telefone` varchar(20) NOT NULL,
          `email` varchar(255) NOT NULL,
          `link_planilha` varchar(255) NOT NULL,
          `tipo_solicitacao` varchar(50) NOT NULL,
          `quantidade` int(11) NOT NULL,
          `observacao` text,
          `data_solicitacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `status` varchar(30) NOT NULL DEFAULT 'Pendente',
          PRIMARY KEY (`id`),
          UNIQUE KEY `protocolo` (`protocolo`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        if (!$conn->query($createTable)) {
            error_log("Erro ao criar tabela: " . $conn->error);
            throw new Exception('Erro ao criar tabela: ' . $conn->error);
        }
        
        error_log("Tabela criada com sucesso");
    }

    // Tentativa alternativa com query direta
    $query = "INSERT INTO solicitacoes_site 
              (protocolo, nome_empresa, cnpj, nome_solicitante, telefone, email, 
               link_planilha, tipo_solicitacao, quantidade, observacao) 
              VALUES (
                  '{$conn->real_escape_string($protocolo)}',
                  '{$conn->real_escape_string($nome_empresa)}',
                  '{$conn->real_escape_string($cnpj)}',
                  '{$conn->real_escape_string($nome_solicitante)}',
                  '{$conn->real_escape_string($telefone)}',
                  '{$conn->real_escape_string($email)}',
                  '{$conn->real_escape_string($link_planilha)}',
                  '{$conn->real_escape_string($tipo_solicitacao)}',
                  {$quantidade},
                  '{$conn->real_escape_string($observacao)}'
              )";
    
    error_log("Executando query: " . $query);
    
    if (!$conn->query($query)) {
        error_log("Erro na inserção direta: " . $conn->error);
        throw new Exception('Erro ao salvar no banco de dados: ' . $conn->error);
    }
    
    error_log("Dados salvos com sucesso. ID inserido: " . $conn->insert_id);
    
    // Fechar conexão
    $conn->close();

    // Configurar PHPMailer
    $mail = new PHPMailer(true);
    
    // Configurações SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.hostinger.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'desenvolvimento@lfmtecnologia.com';
    $mail->Password   = 'T3cn0l0g1a@';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';

    // E-mail para o polo solicitante
    $mailPolo = clone $mail;
    $mailPolo->setFrom('desenvolvimento@lfmtecnologia.com', 'FaCiencia - Solicitação de Polo');
    $mailPolo->addAddress($email, $nome_solicitante);
    $mailPolo->isHTML(true);
    $mailPolo->Subject = "Solicitação Recebida - Protocolo {$protocolo}";
    $mailPolo->Body = "
        <html>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; line-height: 1.6;'>
            <div style='background-color: #9d359c; color: white; padding: 20px; text-align: center;'>
                <h1>Solicitação de Polo Recebida</h1>
            </div>
            <div style='padding: 20px;'>
                <h2>Prezado(a) {$nome_solicitante},</h2>
                
                <p>Informamos que a FaCiencia recebeu sua solicitação de polo com sucesso!</p>
                
                <div style='background-color: #f4f4f4; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    <h3>Detalhes do Protocolo</h3>
                    <ul style='list-style-type: none; padding: 0;'>
                        <li><strong>Número do Protocolo:</strong> {$protocolo}</li>
                        <li><strong>Empresa:</strong> {$nome_empresa}</li>
                        <li><strong>Tipo de Solicitação:</strong> {$tipo_solicitacao}</li>
                        <li><strong>Data da Solicitação:</strong> " . date('d/m/Y H:i:s') . "</li>
                    </ul>
                </div>
                
                <p>Sua solicitação está em análise pela nossa equipe. Em breve, entraremos em contato para dar continuidade ao processo.</p>
                
                <p>Caso tenha dúvidas, entre em contato conosco:</p>
                <p>
                    <strong>Telefone:</strong> (41) 9 9256-2500<br>
                    <strong>Email:</strong> contato@faciencia.edu.br
                </p>
                
                <p style='color: #666; margin-top: 30px;'>
                    <em>Atenciosamente,<br>
                    Equipe FaCiencia<br>
                    Educação de qualidade e inovação para seu futuro profissional.</em>
                </p>
            </div>
        </body>
        </html>
    ";

    // E-mail para equipe interna
    $mailInterno = clone $mail;
    $mailInterno->setFrom('desenvolvimento@lfmtecnologia.com', 'FaCiencia - Solicitação de Polo');
    $mailInterno->addAddress('adriana.basso@faciencia.edu.br');
    $mailInterno->addAddress('mariane.souza@faciencia.edu.br');
    $mailInterno->addAddress('secretaria@faciencia.edu.br');
    $mailInterno->addAddress('barbara.batista@faciencia.edu.br');
    $mailInterno->addAddress('niceia.rodrigues@faciencia.edu.br');
    $mailInterno->addReplyTo($email, $nome_solicitante);
    $mailInterno->isHTML(true);
    $mailInterno->Subject = "Nova Solicitação : {$nome_empresa} - {$protocolo}";
    $mailInterno->Body = "
        <html>
        <body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #9d359c; color: white; padding: 20px; text-align: center;'>
                <h1>Nova Solicitação </h1>
            </div>
            <div style='padding: 20px;'>
                <h2>Detalhes Completos da Solicitação</h2>
                
                <p><strong>Protocolo:</strong> {$protocolo}</p>
                <p><strong>Nome da Empresa:</strong> {$nome_empresa}</p>
                <p><strong>CNPJ:</strong> {$cnpj}</p>
                <p><strong>Nome do Solicitante:</strong> {$nome_solicitante}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Telefone:</strong> {$telefone}</p>
                <p><strong>Link da Planilha:</strong> {$link_planilha}</p>
                <p><strong>Tipo de Solicitação:</strong> {$tipo_solicitacao}</p>
                <p><strong>Quantidade:</strong> {$quantidade}</p>
                <p><strong>Observações:</strong><br>{$observacao}</p>
                <p><strong>Registro no banco:</strong> Sim</p>
            </div>
        </body>
        </html>
    ";

    // Enviar e-mails
    $emailPoloEnviado = $mailPolo->send();
    $emailInternoEnviado = $mailInterno->send();

    // Verificar envio dos e-mails
    if ($emailPoloEnviado && $emailInternoEnviado) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Solicitação salva e e-mails enviados com sucesso!',
            'protocolo' => $protocolo,
            'debug' => 'Dados salvos no banco'
        ]);
    } else {
        $errorMessage = '';
        if (!$emailPoloEnviado) $errorMessage .= "Erro no e-mail do polo: " . $mailPolo->ErrorInfo . " | ";
        if (!$emailInternoEnviado) $errorMessage .= "Erro no e-mail interno: " . $mailInterno->ErrorInfo;

        echo json_encode([
            'status' => 'error', 
            'message' => trim($errorMessage)
        ]);
    }

} catch (Exception $e) {
    error_log("Erro na execução: " . $e->getMessage());
    echo json_encode([
        'status' => 'error', 
        'message' => 'Erro: ' . $e->getMessage()
    ]);
}

exit();
?>