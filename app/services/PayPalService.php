<?php
class PayPalService
{
    private string $baseUrl;
    private string $clientId;
    private string $secret;

    public function __construct()
    {
        // Si en tu .env no pusiste PAYPAL_MODE, por defecto usará sandbox
        $mode = defined('PAYPAL_MODE') ? PAYPAL_MODE : 'sandbox';
        
        $this->baseUrl = ($mode === 'sandbox') 
            ? 'https://api-m.sandbox.paypal.com' 
            : 'https://api-m.paypal.com';
            
        $this->clientId = defined('PAYPAL_CLIENT_ID') ? PAYPAL_CLIENT_ID : '';
        $this->secret   = defined('PAYPAL_SECRET') ? PAYPAL_SECRET : '';
    }

    private function getAccessToken(): string
    {
        $ch = curl_init($this->baseUrl . '/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->clientId . ':' . $this->secret);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($response, true);
        return $data['access_token'] ?? '';
    }

    public function crearOrden(float $monto, string $descripcion, int $cursoId): array
    {
        $token = $this->getAccessToken();
        
        // IMPORTANTE: Estas URLs deben coincidir con la ruta de tu proyecto
        // Si usas localhost:8000, déjalo así. Si usas XAMPP (localhost/portal-cursos...), cámbialo.
        $urlBase = "http://localhost:8000"; 

        $payload = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'description' => $descripcion,
                'amount' => [
                    'currency_code' => 'USD',
                    'value' => number_format($monto, 2, '.', '')
                ]
            ]],
            'application_context' => [
                'return_url' => $urlBase . '/checkout/' . $cursoId . '/capturar',
                'cancel_url' => $urlBase . '/checkout/' . $cursoId
            ]
        ];

        $ch = curl_init($this->baseUrl . '/v2/checkout/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }

    public function capturarOrden(string $orderId): array
    {
        $token = $this->getAccessToken();
        
        $ch = curl_init($this->baseUrl . '/v2/checkout/orders/' . $orderId . '/capture');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{}');

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}