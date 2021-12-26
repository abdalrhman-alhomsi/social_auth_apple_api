<?php

namespace Drupal\social_auth_apple_api\Controller;

use Drupal\social_auth_apple\Controller\AppleAuthController;
use Drupal\social_auth_decoupled\SocialAuthDecoupledTrait;
use Drupal\social_auth_decoupled\SocialAuthHttpInterface;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Drupal\Component\Serialization\Json;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Post login responses for Social Auth Apple.
 */
class AppleAuthHttpController Extends AppleAuthController implements SocialAuthHttpInterface{

  use SocialAuthDecoupledTrait;

  /**
   * {@inheritdoc}
   */
  public function authenticateUserByProfile(ResourceOwnerInterface $profile, $data) {
    $networkManager = \Drupal::service('plugin.network.manager');
    $provider = $networkManager->createInstance('social_auth_apple')->getSdk();
    if (!$provider) {
      $this->messenger->addError($this->t('%module not configured properly. Contact site administrator.', ['%module' => $this->module]));
      return NULL;
    }

    $token = $provider->getAccessToken('authorization_code', [
      'code' => \Drupal::request()->get('code')
    ]);

    $json = Json::encode($token);
    $this->dataHandler->set('access_token', new AccessToken(Json::decode($json)));
    
    $profile = $provider->getResourceOwner($token);
    $response = $this->userAuthenticatorHttp()
      ->authenticateUser($profile->getEmail(), $profile->getEmail(), $profile->getId(), $this->providerManager->getAccessToken(), NULL, []);
    unset($networkManager);
    unset($provider);
    unset($profile);
    unset($token);
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function apiLogin($profile, $data) {
    $networkManager = \Drupal::service('plugin.network.manager');
    $provider = $networkManager->createInstance('social_auth_apple')->getSdk();
    if (!$provider) {
      $this->messenger->addError($this->t('%module not configured properly. Contact site administrator.', ['%module' => $this->module]));
      return NULL;
    }

    $token = $provider->getAccessToken('authorization_code', [
      'code' => \Drupal::request()->get('code')
    ]);

    $this->dataHandler->set('access_token', $token);
    $profile = $provider->getResourceOwner($token);
    $data = $this->userAuthenticator->checkProviderIsAssociated($profile->getId()) ? NULL : $profile->toArray();

    $response = $this->userAuthenticatorHttp->authenticateUser(
      $profile->getEmail(),
      $profile->getEmail(),
      $profile->getId(),
      $this->providerManager->getAccessToken(),
      NULL,
      $data
    );
    return $response;
  }

}
