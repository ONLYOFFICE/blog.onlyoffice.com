<?php
namespace FluentForm\App\Modules\Form;

defined('ABSPATH') or die;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Helpers\Protector;
use FluentForm\Framework\Helpers\ArrayHelper as Arr;

class TokenBasedSpamProtection
{
    
    public function __construct($app)
    {
        if (!$this->isEnabled()) {
            return;
        }
        
        $app->addAction('wp_ajax_fluentform_generate_protection_token', [$this, 'ajaxGenerateToken']);
        $app->addAction('wp_ajax_nopriv_fluentform_generate_protection_token', [$this, 'ajaxGenerateToken']);
       
        add_filter('fluentform/global_form_vars', function ($vars){
            $vars['token_nonce'] = wp_create_nonce('fluentform_generate_token_nonce');
            return $vars;
        });
        
    }
    
    public function renderTokenField($form)
    {
        if (!$this->isEnabled($form->id)) {
            return;
        }

        $fieldName = $this->getFieldName($form->id);
        ?>
        <input type="hidden" id="<?php echo esc_attr($fieldName); ?>" class="fluent-form-token-field" name="<?php echo esc_attr($fieldName); ?>">
        <?php
    }

    public function ajaxGenerateToken()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified on next line
        $nonce = sanitize_text_field(Arr::get($_POST, 'nonce'));
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified on next line
        $formId = (int)Arr::get($_POST,'form_id');
       
        $nonceVerified = wp_verify_nonce($nonce, 'fluentform_generate_token_nonce');
        if (!$formId || !$nonceVerified) {
            wp_send_json_error([
                'message' =>  __('Invalid request', 'fluentform')
            ]);
        }
        
        $token = $this->generateToken($formId);
        $response = apply_filters('fluentform/token_based_protection_response', [
            'token' => $token
        ], $formId);
        
        wp_send_json_success($response);
    }
    
    private function generateToken($formId)
    {
        $timeStamp = current_time('timestamp');
        $fieldName = $this->getFieldName($formId);
        $data = implode('|', [$timeStamp, $formId, $fieldName]);

        return apply_filters('fluentform/generated_protection_token', Protector::encrypt($data), $formId, $timeStamp);
    }

    /**
     * SECURITY (FINDING-25): mint a token input for the conversational form so it is carried in the
     * submission (the conversational JS forwards every extra_input) and the token check can be
     * enforced server-side instead of being skippable via the client isFFConversational flag. This
     * is static and self-contained to avoid the constructor's action registrations; it mirrors
     * isEnabled() / getFieldName() / generateToken() above (same filters, same payload format, so
     * validateToken() accepts it).
     *
     * @param int $formId
     * @return array
     */
    public static function getConversationalTokenInput($formId)
    {
        $option = get_option('_fluentform_global_form_settings');
        $enabled = 'yes' === Arr::get($option, 'misc.tokenBasedProtectionStatus');
        $enabled = apply_filters('fluentform/token_based_spam_protection_status', $enabled, $formId);
        if (!$enabled) {
            return [];
        }

        $fieldName = apply_filters('fluentform/token_protection_name', '__fluent_protection_token_' . $formId, $formId);
        $timeStamp = current_time('timestamp');
        $data = implode('|', [$timeStamp, $formId, $fieldName]);
        $token = apply_filters('fluentform/generated_protection_token', Protector::encrypt($data), $formId, $timeStamp);

        return [$fieldName => $token];
    }
    
    public function verify($insertData, $requestData, $formId)
    {
        // SECURITY (FINDING-25): do NOT skip the check for conversational forms based on the
        // client-supplied isFFConversational flag — that let an attacker bypass token protection by
        // adding one parameter. The conversational renderer now injects a valid token into the
        // submission (getConversationalTokenInput via extra_inputs), so the check is enforced for
        // conversational and regular forms alike; only a genuinely-disabled feature is skipped.
        if (!$this->isEnabled($formId)) {
            return;
        }

        $fieldName = $this->getFieldName($formId);
        $token = sanitize_text_field(Arr::get($requestData, $fieldName));
        if (!$token || !$this->validateToken($token, $formId)) {
            $errorMessage = apply_filters(
                'fluentform/token_based_validation_error_message',
                __('Suspicious activity detected. Form submission blocked', 'fluentform'),
                $formId
            );
            
            $this->handleSpam($errorMessage);
        }
    }
    
    private function validateToken($token, $formId)
    {
        try {
    
            $decrypted = Protector::decrypt($token);
            if (!$decrypted) {
                return false;
            }
    
            $parts = explode('|', $decrypted);
            if (count($parts) !== 3) {
                return false;
            }
    
            [$timestamp, $tokenFormId, $fieldName] = $parts;
    
            // Ensure all components are valid
            if (!is_numeric($timestamp) || !is_numeric($tokenFormId)) {
                return false;
            }
    
            $expirationTime = apply_filters('fluentform/token_expiration_time', 3600, $formId); //1 hour
            if ($timestamp + $expirationTime < current_time('timestamp')) {
                return false;
            }
    
            $isValid = (int)$tokenFormId === $formId && $fieldName === $this->getFieldName($formId);

            // NOTE (FINDING-27): a per-token single-use cap was intentionally NOT implemented here.
            // Storing one WordPress transient per minted token would create unbounded options-table
            // writes on this high-frequency public path (tokens are cheaply minted via the public
            // endpoint and expired transients are not proactively cleaned). The token remains a
            // defence-in-depth anti-bot control (a bot must fetch a nonce-gated, form/field-bound,
            // time-limited token); replay within the expiration window is the accepted LOW residual.

            return apply_filters('fluentform/token_based_validation_result',
                $isValid,
                $timestamp,
                $tokenFormId,
                $formId);
            
        } catch (\Exception $e) {
            return false;
        }
    }
    
    
    private function handleSpam($reason)
    {
        do_action('fluentform/spam_attempt_caught', $reason);
        
        wp_send_json([
            'errors' => $reason
        ], 422);
    }
    
    public function isEnabled($formId = false)
    {
        $option = get_option('_fluentform_global_form_settings');
        $status = 'yes' === Arr::get($option, 'misc.tokenBasedProtectionStatus');
        return apply_filters('fluentform/token_based_spam_protection_status', $status, $formId);
    }
    
    private function getFieldName($formId)
    {
        $tokenInputName = '__fluent_protection_token_'. $formId;
        return apply_filters('fluentform/token_protection_name', $tokenInputName, $formId);
    }
}
