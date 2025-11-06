<?php
// Production WhatsApp Service
class ProductionWhatsAppService {
    private $config;
    private $logFile;
    
    public function __construct() {
        $this->config = include __DIR__ . '/whatsapp_config.php';
        $this->logFile = __DIR__ . '/../logs/whatsapp.log';
        
        // Create logs directory if not exists
        if (!file_exists(dirname($this->logFile))) {
            mkdir(dirname($this->logFile), 0755, true);
        }
    }
    
    public function sendBookingNotification($phoneNumber, $bookingData) {
        $message = $this->generateBookingMessage($bookingData);
        return $this->sendMessage($phoneNumber, $message, 'booking_notification');
    }
    
    public function sendAdminAlert($phoneNumber, $bookingData) {
        $message = $this->generateAdminMessage($bookingData);
        return $this->sendMessage($phoneNumber, $message, 'admin_alert');
    }
    
    private function sendMessage($phoneNumber, $message, $messageType = 'general') {
        $phone = $this->formatPhoneNumber($phoneNumber);
        
        // Log the attempt
        $this->log("Attempting to send $messageType to $phone");
        
        // Check if production mode is enabled
        if (!$this->config['production_mode'] || empty($this->getApiToken())) {
            return $this->handleSimulationMode($phone, $message, $messageType);
        }
        
        // Send real WhatsApp message
        return $this->sendRealMessage($phone, $message, $messageType);
    }
    
    private function sendRealMessage($phone, $message, $messageType) {
        $provider = $this->config['active_provider'];
        $apiUrl = $this->config['providers'][$provider]['url'];
        $token = $this->getApiToken();
        
        $data = $this->prepareApiData($phone, $message, $provider);
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->config['timeout'],
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $this->formatApiData($data, $provider),
            CURLOPT_HTTPHEADER => $this->getApiHeaders($token, $provider),
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        // Log the response
        $this->log("API Response for $messageType: HTTP $httpCode - " . ($error ?: $response));
        
        if ($error) {
            $this->log("cURL Error: $error", 'ERROR');
            return ['success' => false, 'error' => $error, 'response' => null];
        }
        
        $success = $this->isSuccessResponse($response, $httpCode, $provider);
        
        return [
            'success' => $success,
            'response' => $response,
            'http_code' => $httpCode,
            'provider' => $provider,
            'phone' => $phone,
            'message_type' => $messageType
        ];
    }
    
    private function handleSimulationMode($phone, $message, $messageType) {
        $this->log("SIMULATION MODE: $messageType to $phone");
        $this->log("Message content: " . substr($message, 0, 100) . "...");
        
        return [
            'success' => true,
            'response' => 'Simulated success - message logged',
            'http_code' => 200,
            'provider' => 'simulation',
            'phone' => $phone,
            'message_type' => $messageType,
            'simulated' => true
        ];
    }
    
    private function prepareApiData($phone, $message, $provider) {
        switch ($provider) {
            case 'fonnte':
                return [
                    'target' => $phone,
                    'message' => $message,
                    'countryCode' => '62'
                ];
                
            case 'wablas':
                return [
                    'phone' => $phone,
                    'message' => $message,
                    'secret' => false,
                    'retry' => false,
                    'isGroup' => false
                ];
                
            case 'woowa':
                return [
                    'number' => $phone,
                    'message' => $message
                ];
                
            default:
                return [
                    'phone' => $phone,
                    'message' => $message
                ];
        }
    }
    
    private function formatApiData($data, $provider) {
        switch ($provider) {
            case 'wablas':
                return json_encode($data);
            default:
                return http_build_query($data);
        }
    }
    
    private function getApiHeaders($token, $provider) {
        switch ($provider) {
            case 'fonnte':
                return [
                    'Authorization: ' . $token,
                    'Content-Type: application/x-www-form-urlencoded'
                ];
                
            case 'wablas':
                return [
                    'Authorization: ' . $token,
                    'Content-Type: application/json'
                ];
                
            case 'woowa':
                return [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/x-www-form-urlencoded'
                ];
                
            default:
                return [
                    'Authorization: ' . $token,
                    'Content-Type: application/x-www-form-urlencoded'
                ];
        }
    }
    
    private function isSuccessResponse($response, $httpCode, $provider) {
        if ($httpCode !== 200) return false;
        
        switch ($provider) {
            case 'fonnte':
                $data = json_decode($response, true);
                return isset($data['status']) && $data['status'] === true;
                
            case 'wablas':
                $data = json_decode($response, true);
                return isset($data['status']) && $data['status'] === true;
                
            case 'woowa':
                $data = json_decode($response, true);
                return isset($data['status']) && $data['status'] === 'success';
                
            default:
                return true;
        }
    }
    
    private function formatPhoneNumber($phoneNumber) {
        $phone = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        if (substr($phone, 0, 1) == '0') {
            $phone = '62' . substr($phone, 1);
        } elseif (substr($phone, 0, 2) !== '62') {
            $phone = '62' . $phone;
        }
        
        return $phone;
    }
    
    private function getApiToken() {
        // Try to get from database or config file
        // For now, return from config
        return $this->config['api_token'];
    }
    
    public function setApiToken($token) {
        // Update configuration
        $configPath = __DIR__ . '/whatsapp_config.php';
        $config = include $configPath;
        $config['api_token'] = $token;
        
        $configContent = '<?php return ' . var_export($config, true) . ';';
        file_put_contents($configPath, $configContent);
        
        $this->config = $config;
    }
    
    public function enableProductionMode($enable = true) {
        $configPath = __DIR__ . '/whatsapp_config.php';
        $config = include $configPath;
        $config['production_mode'] = $enable;
        
        $configContent = '<?php return ' . var_export($config, true) . ';';
        file_put_contents($configPath, $configContent);
        
        $this->config = $config;
        $this->log("Production mode " . ($enable ? 'ENABLED' : 'DISABLED'));
    }
    
    private function generateBookingMessage($data) {
        $serviceType = $data['service_type'] == 'studio' ? '🏢 Studio Visit' : '🏠 Home Service';
        
        $message = "🎉 *BOOKING CONFIRMED - PIERCEFLOW*\n\n";
        $message .= "Halo *" . $data['user_name'] . "*! 👋\n\n";
        $message .= "Booking Anda telah berhasil dikonfirmasi:\n\n";
        $message .= "📋 *Detail Booking:*\n";
        $message .= "🆔 ID: #" . $data['id'] . "\n";
        $message .= "💎 Service: " . $data['service_name'] . "\n";
        $message .= "📅 Tanggal: " . date('d F Y', strtotime($data['date'])) . "\n";
        $message .= "⏰ Waktu: " . date('H:i', strtotime($data['time'])) . " WIB\n";
        $message .= "🏠 Tipe: " . $serviceType . "\n";
        $message .= "💰 Total: Rp " . number_format($data['price'], 0, ',', '.') . "\n\n";
        
        if ($data['service_type'] == 'home_service' && !empty($data['address'])) {
            $message .= "📍 Alamat: " . $data['address'] . "\n\n";
        }
        
        $message .= "⚠️ *Penting:*\n";
        $message .= "• Harap datang tepat waktu\n";
        $message .= "• Bawa dokumen identitas (KTP/SIM)\n";
        $message .= "• Jangan konsumsi alkohol 24 jam sebelumnya\n";
        $message .= "• Hubungi kami jika ada perubahan jadwal\n\n";
        $message .= "📞 *Butuh bantuan?*\n";
        $message .= "WhatsApp: wa.me/6281234567890\n\n";
        $message .= "_Terima kasih telah mempercayai PierceFlow Studio_ ✨\n";
        $message .= "🌐 www.pierceflow.com";
        
        return $message;
    }
    
    private function generateAdminMessage($data) {
        $serviceType = $data['service_type'] == 'studio' ? '🏢 Studio' : '🏠 Home Service';
        
        $message = "🔔 *BOOKING BARU MASUK!*\n\n";
        $message .= "📋 *Detail Booking:*\n";
        $message .= "🆔 ID: #" . $data['id'] . "\n";
        $message .= "👤 Customer: " . $data['user_name'] . "\n";
        $message .= "📱 Phone: " . $data['phone'] . "\n";
        $message .= "💎 Service: " . $data['service_name'] . "\n";
        $message .= "📅 Tanggal: " . date('d/m/Y', strtotime($data['date'])) . "\n";
        $message .= "⏰ Waktu: " . date('H:i', strtotime($data['time'])) . "\n";
        $message .= "🏠 Tipe: " . $serviceType . "\n";
        $message .= "💰 Total: Rp " . number_format($data['price'], 0, ',', '.') . "\n\n";
        
        if ($data['service_type'] == 'home_service' && !empty($data['address'])) {
            $message .= "📍 Alamat: " . $data['address'] . "\n\n";
        }
        
        $message .= "⚡ *Action Required:*\n";
        $message .= "Segera cek admin panel untuk konfirmasi!\n";
        $message .= "🌐 " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/PKPL_New/admin.php";
        
        return $message;
    }
    
    private function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message" . PHP_EOL;
        file_put_contents($this->logFile, $logEntry, FILE_APPEND | LOCK_EX);
    }
    
    public function getStatus() {
        return [
            'production_mode' => $this->config['production_mode'],
            'active_provider' => $this->config['active_provider'],
            'has_token' => !empty($this->getApiToken()),
            'admin_phone' => $this->config['admin_phone'],
            'log_file' => $this->logFile
        ];
    }
    
    public function testConnection($phoneNumber = null) {
        $testPhone = $phoneNumber ?: $this->config['admin_phone'];
        $testMessage = "🧪 *TEST MESSAGE - PIERCEFLOW*\n\nIni adalah test message dari sistem notifikasi PierceFlow.\n\n✅ Jika Anda menerima pesan ini, berarti WhatsApp API sudah berfungsi dengan baik!\n\n" . date('d F Y H:i:s');
        
        return $this->sendMessage($testPhone, $testMessage, 'test');
    }
}
?>