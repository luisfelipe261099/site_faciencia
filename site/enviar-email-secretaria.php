<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido']);
    exit;
}

// Ler dados JSON
$input = file_get_contents('php://input');
$dados = json_decode($input, true);

if (!$dados) {
    http_response_code(400);
    echo json_encode(['erro' => 'Dados inválidos']);
    exit;
}

// Configurações de email
$emailSecretaria = 'secretaria@faciencia.edu.br';
$emailRemetente = 'sistema@faciencia.edu.br';
$nomeRemetente = 'Sistema Vestibular FaCiencia';

// Obter IP do cliente
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'N/A';

try {
    if ($dados['tipo'] === 'tentativas_esgotadas') {
        $candidato = $dados['candidato'];
        $detalhes = $dados['detalhes'];
        
        // Atualizar IP nos detalhes
        $detalhes['ip'] = $ip;
        
        $assunto = 'ALERTA: Candidato esgotou tentativas de prova - ' . $candidato['nome'];
        
        $mensagem = "
        <html>
        <head>
            <title>Alerta - Tentativas Esgotadas</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .header { background-color: #9d359c; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .alert { background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .info-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .info-table th, .info-table td { border: 1px solid #ddd; padding: 12px; text-align: left; }
                .info-table th { background-color: #f2f2f2; font-weight: bold; }
                .footer { background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>🚨 ALERTA - TENTATIVAS ESGOTADAS</h1>
                <p>Sistema de Vestibular FaCiencia</p>
            </div>
            
            <div class='content'>
                <div class='alert'>
                    <strong>⚠️ ATENÇÃO:</strong> Um candidato esgotou todas as tentativas de prova e foi bloqueado temporariamente.
                </div>
                
                <h2>📋 Dados do Candidato</h2>
                <table class='info-table'>
                    <tr><th>Nome</th><td>" . htmlspecialchars($candidato['nome']) . "</td></tr>
                    <tr><th>CPF</th><td>" . htmlspecialchars($candidato['cpf']) . "</td></tr>
                    <tr><th>E-mail</th><td>" . htmlspecialchars($candidato['email']) . "</td></tr>
                </table>
                
                <h2>📊 Detalhes da Prova</h2>
                <table class='info-table'>
                    <tr><th>Acertos</th><td>" . $detalhes['acertos'] . " de 10 questões</td></tr>
                    <tr><th>Tentativas Realizadas</th><td>" . $detalhes['tentativas'] . " de 2</td></tr>
                    <tr><th>Data/Hora</th><td>" . $detalhes['data'] . "</td></tr>
                    <tr><th>IP do Candidato</th><td>" . $ip . "</td></tr>
                </table>
                
                <h2>🔒 Ações Tomadas</h2>
                <ul>
                    <li>Candidato foi bloqueado por 24 horas</li>
                    <li>Não poderá realizar nova tentativa até o desbloqueio automático</li>
                    <li>Dados salvos no sistema para acompanhamento</li>
                </ul>
                
                <h2>📞 Próximos Passos</h2>
                <p>Recomenda-se entrar em contato com o candidato para:</p>
                <ul>
                    <li>Verificar se houve algum problema técnico</li>
                    <li>Orientar sobre o processo de vestibular</li>
                    <li>Oferecer suporte adicional se necessário</li>
                    <li>Avaliar possibilidade de nova oportunidade (critério da coordenação)</li>
                </ul>
            </div>
            
            <div class='footer'>
                <p>Este é um e-mail automático do Sistema de Vestibular FaCiencia.</p>
                <p>Gerado em: " . date('d/m/Y H:i:s') . "</p>
            </div>
        </body>
        </html>
        ";
        
        // Headers para email HTML
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: $nomeRemetente <$emailRemetente>" . "\r\n";
        $headers .= "Reply-To: $emailRemetente" . "\r\n";
        $headers .= "X-Priority: 1" . "\r\n"; // Alta prioridade
        
        // Enviar email
        $emailEnviado = mail($emailSecretaria, $assunto, $mensagem, $headers);
        
        if ($emailEnviado) {
            // Log do evento
            $logData = [
                'timestamp' => date('Y-m-d H:i:s'),
                'tipo' => 'tentativas_esgotadas',
                'candidato' => $candidato,
                'detalhes' => $detalhes,
                'email_enviado' => true
            ];
            
            // Salvar log em arquivo
            $logFile = 'logs/vestibular_alertas.log';
            if (!file_exists('logs')) {
                mkdir('logs', 0755, true);
            }
            file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND | LOCK_EX);
            
            echo json_encode([
                'sucesso' => true,
                'mensagem' => 'Email enviado para secretaria com sucesso',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            throw new Exception('Falha ao enviar email');
        }
    } else {
        throw new Exception('Tipo de email não reconhecido');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'erro' => 'Erro ao enviar email: ' . $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
    // Log do erro
    $errorLog = [
        'timestamp' => date('Y-m-d H:i:s'),
        'erro' => $e->getMessage(),
        'dados' => $dados
    ];
    
    $logFile = 'logs/vestibular_erros.log';
    if (!file_exists('logs')) {
        mkdir('logs', 0755, true);
    }
    file_put_contents($logFile, json_encode($errorLog) . "\n", FILE_APPEND | LOCK_EX);
}
?>
