<?php
namespace kkkdev\CalorieScanner\API;

use djchen\OAuth2\Client\Provider\Fitbit;

class MyFitBitAccessProvider extends \djchen\OAuth2\Client\Provider\Fitbit
{
    protected $expiresIn;
    protected $scope;

  /**
   * Returns all scopes available from Fitbit.
   * It is recommended you only request the scopes you need!
   *
   * @return array
   */
  protected function getDefaultScopes()
  {
      return [ 'profile','nutrition'];
  }

  /**
   * Returns authorization parameters based on provided options.
   *
   * @param  array $options
   * @return array Authorization parameters
   */
  protected function getAuthorizationParameters(array $options)
  {
      if (empty($options['state'])) {
          $options['state'] = $this->getRandomState();
      }

      if (empty($options['scope'])) {
          $options['scope'] = $this->getDefaultScopes();
      }

      $options += [
      'response_type' => 'token',
      'approval_prompt' => 'auto',
      'expires_in' => '31536000'
    ];

      if (is_array($options['scope'])) {
          $separator = $this->getScopeSeparator();
          $options['scope'] = implode($separator, $options['scope']);
      }

    // Store the state as it may need to be accessed later on.
    $this->state = $options['state'];

      $options['client_id'] = $this->clientId;
      $options['redirect_uri'] = $this->redirectUri;
      $options['state'] = $this->state;

      return $options;
  }
}
