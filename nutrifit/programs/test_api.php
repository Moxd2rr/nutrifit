<?php
// Test de l'API Hugging Face
$hf_api_key = "hf_qnmPKLYdtlBfNQIEmSQYhrIGcfeepgyJjnQ";
$model = "mistralai/Mixtral-8x7B-Instruct-v0.1";

function test_hf_api($api_key, $model) {
    $url = "https://api-inference.huggingface.co/models/$model";
    $headers = [
        "Authorization: Bearer $api_key",
        "Content-Type: application/json"
    ];
    $data = [
        "inputs" => "Bonjour, comment allez-vous ?",
        "parameters" => [
            "max_new_tokens" => 50,
            "temperature" => 0.7
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    
    curl_close($ch);
    
    return [
        'http_code' => $httpCode,
        'result' => $result,
        'error' => $error,
        'info' => $info
    ];
}

$test_result = test_hf_api($hf_api_key, $model);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test API Hugging Face</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; max-width: 800px; margin: auto; }
        .status { padding: 15px; border-radius: 5px; margin: 20px 0; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .info { background: #d1ecf1; color: #0c5460; border: 1px solid #bee5eb; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Test API Hugging Face</h1>
        
        <div class="info">
            <h3>Informations de test :</h3>
            <p><strong>Modèle :</strong> <?= $model ?></p>
            <p><strong>Clé API :</strong> <?= substr($hf_api_key, 0, 10) ?>...</p>
        </div>
        
        <?php if ($test_result['error']): ?>
            <div class="error">
                <h3>❌ Erreur de connexion</h3>
                <p><?= htmlspecialchars($test_result['error']) ?></p>
            </div>
        <?php elseif ($test_result['http_code'] === 200): ?>
            <div class="success">
                <h3>✅ Connexion réussie</h3>
                <p>L'API Hugging Face répond correctement.</p>
            </div>
        <?php elseif ($test_result['http_code'] === 403): ?>
            <div class="error">
                <h3>❌ Erreur 403 - Accès refusé</h3>
                <p>Votre clé API est invalide ou expirée.</p>
                <h4>Solutions :</h4>
                <ul>
                    <li>Vérifiez que votre clé API est correcte</li>
                    <li>Assurez-vous que votre compte Hugging Face est actif</li>
                    <li>Vérifiez les permissions de votre clé API</li>
                    <li>Créez une nouvelle clé API si nécessaire</li>
                </ul>
            </div>
        <?php elseif ($test_result['http_code'] === 401): ?>
            <div class="error">
                <h3>❌ Erreur 401 - Non autorisé</h3>
                <p>Clé API manquante ou incorrecte.</p>
            </div>
        <?php elseif ($test_result['http_code'] === 429): ?>
            <div class="warning">
                <h3>⚠️ Erreur 429 - Limite dépassée</h3>
                <p>Vous avez dépassé la limite de requêtes.</p>
            </div>
        <?php else: ?>
            <div class="error">
                <h3>❌ Erreur HTTP <?= $test_result['http_code'] ?></h3>
                <p>Problème avec l'API Hugging Face.</p>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <h3>📊 Détails de la réponse :</h3>
            <p><strong>Code HTTP :</strong> <?= $test_result['http_code'] ?></p>
            <p><strong>Temps de réponse :</strong> <?= round($test_result['info']['total_time'], 2) ?> secondes</p>
        </div>
        
        <?php if ($test_result['result']): ?>
            <div class="info">
                <h3>📝 Réponse de l'API :</h3>
                <pre><?= htmlspecialchars($test_result['result']) ?></pre>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <h3>🔗 Liens utiles :</h3>
            <ul>
                <li><a href="https://huggingface.co/settings/tokens" target="_blank">Gérer vos clés API Hugging Face</a></li>
                <li><a href="https://huggingface.co/docs/api-inference/index" target="_blank">Documentation API Hugging Face</a></li>
                <li><a href="https://huggingface.co/models" target="_blank">Modèles disponibles</a></li>
            </ul>
        </div>
        
        <div class="warning">
            <h3>💡 Conseils :</h3>
            <ul>
                <li>Assurez-vous que votre clé API commence par "hf_"</li>
                <li>Vérifiez que vous avez accepté les conditions d'utilisation</li>
                <li>Certains modèles peuvent nécessiter des permissions spéciales</li>
                <li>Les clés API gratuites ont des limites de requêtes</li>
            </ul>
        </div>
    </div>
</body>
</html> 