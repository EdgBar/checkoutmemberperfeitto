<?php
/**
 * Gateway Stripe - Integração com API Stripe para Cartão de Crédito
 *
 * Cria PaymentIntent e permite confirmar no frontend com Stripe.js.
 * Webhook payment_intent.succeeded deve atualizar venda e liberar acesso.
 */

/**
 * Cria um PaymentIntent no Stripe (valor em centavos, moeda BRL)
 *
 * @param string $secret_key Chave secreta Stripe (sk_...)
 * @param int $amount_cents Valor em centavos (ex: 10000 = R$ 100,00)
 * @param string $currency Código da moeda (brl, usd, etc.)
 * @param array $metadata Metadados (ex: checkout_session_uuid)
 * @param string|null $description Descrição opcional
 * @return array|false ['client_secret' => string, 'payment_intent_id' => string] ou false em erro
 */
function stripe_create_payment_intent($secret_key, $amount_cents, $currency = 'brl', $metadata = [], $description = null) {
    $secret_key = trim($secret_key);
    if (empty($secret_key) || $amount_cents < 50) {
        error_log('Stripe: secret_key vazia ou amount inválido');
        return false;
    }

    $url = 'https://api.stripe.com/v1/payment_intents';
    $post = [
        'amount' => (int) $amount_cents,
        'currency' => strtolower($currency),
        'automatic_payment_methods[enabled]' => 'true',
    ];
    if ($description) {
        $post['description'] = $description;
    }
    foreach ($metadata as $k => $v) {
        $post['metadata[' . $k . ']'] = (string) $v;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, $secret_key . ':');
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        error_log('Stripe create PaymentIntent cURL error: ' . $curl_error);
        return false;
    }

    $data = json_decode($response, true);
    if ($http_code < 200 || $http_code >= 300) {
        $err = $data['error']['message'] ?? $response;
        error_log('Stripe create PaymentIntent HTTP ' . $http_code . ': ' . $err);
        return ['error' => true, 'message' => is_string($err) ? $err : ($err['message'] ?? 'Erro Stripe')];
    }

    if (empty($data['client_secret']) || empty($data['id'])) {
        error_log('Stripe: resposta sem client_secret ou id');
        return false;
    }

    return [
        'client_secret' => $data['client_secret'],
        'payment_intent_id' => $data['id'],
    ];
}

/**
 * Recupera um PaymentIntent para verificar status (succeeded = aprovado)
 *
 * @param string $secret_key Chave secreta Stripe
 * @param string $payment_intent_id ID do PaymentIntent (pi_xxx)
 * @return array|false ['status' => string, 'amount_received' => int] ou false
 */
function stripe_retrieve_payment_intent($secret_key, $payment_intent_id) {
    $secret_key = trim($secret_key);
    $payment_intent_id = trim($payment_intent_id);
    if (empty($secret_key) || empty($payment_intent_id)) {
        return false;
    }

    $url = 'https://api.stripe.com/v1/payment_intents/' . urlencode($payment_intent_id);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $secret_key . ':');
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        error_log('Stripe retrieve PaymentIntent HTTP ' . $http_code);
        return false;
    }

    $data = json_decode($response, true);
    if (!$data || !isset($data['status'])) {
        return false;
    }

    return [
        'status' => $data['status'],
        'amount_received' => (int) ($data['amount_received'] ?? 0),
    ];
}

/**
 * Valida assinatura do webhook Stripe (Stripe-Signature)
 *
 * @param string $payload Corpo bruto do POST
 * @param string $signature Header Stripe-Signature
 * @param string $webhook_secret Webhook signing secret (whsec_...)
 * @return bool
 */
function stripe_verify_webhook_signature($payload, $signature, $webhook_secret) {
    if (empty($signature) || empty($webhook_secret)) {
        return false;
    }
    $elements = explode(',', $signature);
    $v1 = null;
    foreach ($elements as $el) {
        $parts = explode('=', $el, 2);
        if (count($parts) === 2 && trim($parts[0]) === 'v1') {
            $v1 = trim($parts[1]);
            break;
        }
    }
    if ($v1 === null) {
        return false;
    }
    $expected = hash_hmac('sha256', $payload, $webhook_secret);
    return hash_equals($expected, $v1);
}
