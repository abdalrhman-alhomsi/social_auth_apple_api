<?php

namespace Drupal\social_auth_apple_api\Controller;

use Drupal\social_auth_decoupled\SocialAuthDecoupledTrait;
use Drupal\social_auth_decoupled\SocialAuthHttpInterface;
use Drupal\social_auth_apple\Controller\AppleAuthController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;

/**
 * Post login responses for Social Auth Google.
 */
class AppleTestAuthHttpController extends AppleAuthController implements SocialAuthHttpInterface {

  use SocialAuthDecoupledTrait;

  /**
   * {@inheritdoc}
   */
  public function authenticateUserByProfile(ResourceOwnerInterface $profile, $data) {
    // Check for email.
    \Drupal::messenger()->addMessage(var_dump($profile->toArray(), TRUE));
    \Drupal::messenger()->addMessage(var_dump($data, TRUE));

    return $this->userAuthenticatorHttp()
      ->authenticateUser($profile->getEmail(), $profile->getEmail(), $profile->getId(), $this->providerManager->getAccessToken(), NULL, $data);
  }

}
