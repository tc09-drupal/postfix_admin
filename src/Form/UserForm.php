<?php
/**
 * @file
 * Contains \Drupal\postfix_admin\Form\UserForm
 */
namespace Drupal\postfix_admin\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an Postfix Domain Form
 */
class UserForm extends FormBase {
  /**
   * (@inheritdoc)
   */
  public function getFormId() {
    return 'postfix_admin_user_form';
  }
  /**
   * (@inheritdoc)
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['email'] = array(
      '#title' => t('Email Address'),
      '#type' => 'textfield',
      '#size' => 50,
      '#description' => t("Email Address"),
      '#required' => TRUE,
    );
    $form['domain'] = array(
      '#title' => t('Domain Name'),
      '#type' => 'textfield',
      '#size' => 25,
      '#description' => t("Email Domain Name"),
      '#required' => TRUE,
    );
    $form['password'] = array(
      '#title' => t('Password'),
      '#type' => 'password',
      '#size' => 25,
      '#description' => t("Password"),
      '#required' => TRUE,
    );
    $form['password2'] = array(
      '#title' => t('Password'),
      '#type' => 'password',
      '#size' => 25,
      '#description' => t("Password to match"),
      '#required' => TRUE,
    );
    $form['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
    );
    return $form;
  }
  /**
   * (@inheritdoc)
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email = $form_state->getValue('email');
    if ($email == !\Drupal::service('email.validator')->isValid($email)) {
      $form_state->setErrorByName ('email', t('The email address %mail is not valid,',
        array('%mail' => $email)));
      return;
    }
    $domain = $form_state->getValue('domain');
    $select = Database::getConnection()->select('postfix_user', 'r');
    $select->fields('r', array('email', 'domain'));
    $select->condition('email', $email);  
    $select->condition('domain', $domain);  
    $results = $select->execute();
    if (!empty($results->fetchCol())) {
      // we found a row with this domain and email
      $form_state->setErrorByName('domain',
        t('The email %email of the domain %domain is already in the list.',
        array('%domain' => $domain, '%email' => $email))
      );
      return;
    }
    $p = $form_state->getValue('password');
    $p2 = $form_state->getValue('password2');
    if (strcmp($p, $p2) !== 0) {
      // passwords mismatch
      $form_state->setErrorByName('password',
        t('passwords mismatch for email %email.',
        array('%email' => $email))
      );
      return;
    }
  }
  /**
   * (@inheritdoc)
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    db_insert('postfix_user')
      ->fields(array(
        'email' => $form_state->getValue('email'),
        'domain' => $form_state->getValue('domain'),
        'password' => md5($form_state->getValue('password')),
      ))->execute();
    drupal_set_message(t('email added'));
  }
}
